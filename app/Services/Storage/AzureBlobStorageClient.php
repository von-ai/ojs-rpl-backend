<?php

declare(strict_types=1);

namespace App\Services\Storage;

use DateTimeInterface;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\UriInterface;
use RuntimeException;

/**
 * Client HTTP murni untuk berinteraksi dengan Azure Blob Storage dan Azurite Emulator.
 * Mendukung autentikasi SharedKey bawaan Azure serta pembuatan SAS (Shared Access Signature) URL.
 */
class AzureBlobStorageClient
{
    protected HttpClient $http;

    protected string $accountName;

    protected string $accountKey;

    protected string $container;

    protected string $baseUrl;

    protected ?string $prefix;

    protected ?string $publicBaseUrl;

    protected string $apiVersion = '2021-08-06';

    public function __construct(
        string $accountName,
        string $accountKey,
        string $container,
        ?string $baseUrl = null,
        ?string $prefix = null,
        int $timeout = 30,
        ?string $publicBaseUrl = null
    ) {
        $this->accountName = trim($accountName);
        $this->accountKey = trim($accountKey);
        $this->container = trim($container, '/');
        $this->prefix = $prefix ? trim($prefix, '/') : null;
        $this->publicBaseUrl = ! empty($publicBaseUrl) ? rtrim($publicBaseUrl, '/') : null;

        if (! empty($baseUrl)) {
            $this->baseUrl = rtrim($baseUrl, '/');
        } else {
            $this->baseUrl = "https://{$this->accountName}.blob.core.windows.net";
        }

        $this->http = new HttpClient([
            'timeout' => $timeout,
            'http_errors' => true,
        ]);
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function getContainer(): string
    {
        return $this->container;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Resolusi path lengkap dengan prefix (jika ada).
     */
    public function resolvePath(string $path): string
    {
        $path = ltrim($path, '/');
        if ($this->prefix !== null && $this->prefix !== '') {
            return "{$this->prefix}/{$path}";
        }

        return $path;
    }

    /**
     * Membentuk URL absolut untuk blob tertentu.
     */
    public function getBlobUrl(string $path): string
    {
        $resolved = implode('/', array_map('rawurlencode', explode('/', $this->resolvePath($path))));

        return "{$this->baseUrl}/{$this->container}/{$resolved}";
    }

    /**
     * Membentuk URL publik yang dapat diakses oleh browser/klien (mendukung override via publicBaseUrl).
     */
    public function getPublicBlobUrl(string $path): string
    {
        $base = ! empty($this->publicBaseUrl) ? $this->publicBaseUrl : $this->baseUrl;
        $resolved = implode('/', array_map('rawurlencode', explode('/', $this->resolvePath($path))));

        return "{$base}/{$this->container}/{$resolved}";
    }

    /**
     * Unggah konten ke blob storage (PUT Blob).
     */
    public function putBlob(string $path, string $contents, ?string $mimeType = null): bool
    {
        $url = $this->getBlobUrl($path);
        $length = strlen($contents);
        $mime = $mimeType ?: 'application/octet-stream';

        $headers = [
            'x-ms-blob-type' => 'BlockBlob',
            'Content-Type' => $mime,
            'Content-Length' => (string) $length,
        ];

        try {
            $signedHeaders = $this->signRequest('PUT', $url, $headers);
            $response = $this->http->request('PUT', $url, [
                'headers' => $signedHeaders,
                'body' => $contents,
            ]);

            return $response->getStatusCode() === 201;
        } catch (GuzzleException $e) {
            Log::error("AzureBlobStorage: Gagal mengunggah blob '{$path}': ".$e->getMessage());
            throw new RuntimeException("Gagal mengunggah berkas ke Azure Blob Storage: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Mengambil konten blob (GET Blob).
     */
    public function getBlob(string $path): string
    {
        $url = $this->getBlobUrl($path);
        try {
            $signedHeaders = $this->signRequest('GET', $url);
            $response = $this->http->request('GET', $url, [
                'headers' => $signedHeaders,
            ]);

            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            Log::error("AzureBlobStorage: Gagal membaca blob '{$path}': ".$e->getMessage());
            throw new RuntimeException("Gagal membaca berkas dari Azure Blob Storage: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Mengambil stream resource untuk blob.
     *
     * @return resource
     */
    public function getBlobStream(string $path)
    {
        $url = $this->getBlobUrl($path);
        try {
            $signedHeaders = $this->signRequest('GET', $url);
            $response = $this->http->request('GET', $url, [
                'headers' => $signedHeaders,
                'stream' => true,
            ]);

            return Utils::streamFor($response->getBody())->detach();
        } catch (GuzzleException $e) {
            Log::error("AzureBlobStorage: Gagal stream blob '{$path}': ".$e->getMessage());
            throw new RuntimeException("Gagal stream berkas dari Azure Blob Storage: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Memeriksa apakah blob ada (HEAD Blob).
     */
    public function blobExists(string $path): bool
    {
        $url = $this->getBlobUrl($path);
        try {
            $signedHeaders = $this->signRequest('HEAD', $url);
            $response = $this->http->request('HEAD', $url, [
                'headers' => $signedHeaders,
            ]);

            return $response->getStatusCode() === 200;
        } catch (ClientException $e) {
            if ($e->getResponse()?->getStatusCode() === 404) {
                return false;
            }
            throw $e;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * Mengambil metadata / properti blob (HEAD Blob).
     *
     * @return array{size: int, mime: string, last_modified: int}
     */
    public function getBlobProperties(string $path): array
    {
        $url = $this->getBlobUrl($path);
        try {
            $signedHeaders = $this->signRequest('HEAD', $url);
            $response = $this->http->request('HEAD', $url, [
                'headers' => $signedHeaders,
            ]);

            $length = (int) ($response->getHeaderLine('Content-Length') ?: 0);
            $mime = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';
            $lastModStr = $response->getHeaderLine('Last-Modified');
            $lastMod = $lastModStr ? (int) strtotime($lastModStr) : time();

            return [
                'size' => $length,
                'mime' => $mime,
                'last_modified' => $lastMod,
            ];
        } catch (GuzzleException $e) {
            throw new RuntimeException("Gagal membaca metadata blob '{$path}': {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Menghapus blob (DELETE Blob).
     */
    public function deleteBlob(string $path): bool
    {
        $url = $this->getBlobUrl($path);
        try {
            $signedHeaders = $this->signRequest('DELETE', $url);
            $response = $this->http->request('DELETE', $url, [
                'headers' => $signedHeaders,
            ]);

            return in_array($response->getStatusCode(), [200, 202, 204], true);
        } catch (ClientException $e) {
            if ($e->getResponse()?->getStatusCode() === 404) {
                return true; // Idempotent delete
            }
            throw $e;
        } catch (GuzzleException $e) {
            Log::error("AzureBlobStorage: Gagal menghapus blob '{$path}': ".$e->getMessage());
            throw new RuntimeException("Gagal menghapus berkas dari Azure Blob Storage: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Menyalin blob dari satu lokasi ke lokasi lain di container yang sama (Copy Blob).
     */
    public function copyBlob(string $sourcePath, string $destinationPath): bool
    {
        $sourceUrl = $this->getBlobUrl($sourcePath);
        $destUrl = $this->getBlobUrl($destinationPath);

        $headers = [
            'x-ms-copy-source' => $sourceUrl,
        ];

        try {
            $signedHeaders = $this->signRequest('PUT', $destUrl, $headers);
            $response = $this->http->request('PUT', $destUrl, [
                'headers' => $signedHeaders,
            ]);

            return in_array($response->getStatusCode(), [201, 202], true);
        } catch (GuzzleException $e) {
            Log::error("AzureBlobStorage: Gagal menyalin blob dari '{$sourcePath}' ke '{$destinationPath}': ".$e->getMessage());
            throw new RuntimeException("Gagal menyalin berkas di Azure Blob Storage: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * @return iterable<array{path: string, size: int, modified: int}>
     */
    public function listBlobs(string $directory): iterable
    {
        $prefix = $this->resolvePath($directory === '' ? '' : rtrim($directory, '/').'/');
        $marker = '';
        do {
            $query = http_build_query(['restype' => 'container', 'comp' => 'list', 'prefix' => $prefix, 'marker' => $marker]);
            $url = "{$this->baseUrl}/{$this->container}?{$query}";
            $response = $this->http->request('GET', $url, ['headers' => $this->signRequest('GET', $url)]);
            $listing = simplexml_load_string((string) $response->getBody(), options: LIBXML_NONET);
            if ($listing === false) {
                throw new RuntimeException('Daftar Blob Azure tidak dapat dibaca.');
            }

            foreach ($listing->Blobs->Blob as $blob) {
                $name = (string) $blob->Name;
                if (! str_starts_with($name, $prefix)) {
                    continue;
                }
                yield [
                    'path' => $this->prefix === null ? $name : substr($name, strlen($this->prefix) + 1),
                    'size' => (int) $blob->Properties->{'Content-Length'},
                    'modified' => (int) strtotime((string) $blob->Properties->{'Last-Modified'}),
                ];
            }

            $marker = (string) $listing->NextMarker;
        } while ($marker !== '');
    }

    /**
     * Menghasilkan Shared Access Signature (SAS) URL untuk akses langsung (Direct Download / Upload).
     *
     * @param  string  $permissions  'r' untuk download (read), 'c' untuk membuat blob baru
     */
    public function generateSasUrl(string $path, DateTimeInterface $expiry, string $permissions = 'r'): string
    {
        $resolved = $this->resolvePath($path);
        $blobUrl = $this->getPublicBlobUrl($path);

        $signedPermissions = $permissions;
        $signedStart = gmdate('Y-m-d\TH:i:s\Z', time() - 300); // 5 menit mundur untuk toleransi clock skew
        $signedExpiry = gmdate('Y-m-d\TH:i:s\Z', $expiry->getTimestamp());
        $canonicalizedResource = "/blob/{$this->accountName}/{$this->container}/{$resolved}";
        $signedProtocol = parse_url($blobUrl, PHP_URL_SCHEME) === 'https' ? 'https' : 'https,http';
        $signedVersion = $this->apiVersion;
        $signedResource = 'b'; // 'b' for blob

        // String to sign spesifikasi Azure Storage Service SAS untuk Blob versi 2020-10-02+
        $stringToSign = implode("\n", [
            $signedPermissions,
            $signedStart,
            $signedExpiry,
            $canonicalizedResource,
            '', // signedIdentifier
            '', // signedIP
            $signedProtocol,
            $signedVersion,
            $signedResource,
            '', // signedSnapshotTime
            '', // signedEncryptionScope
            '', // rscc (cache-control)
            '', // rscd (content-disposition)
            '', // rsce (content-encoding)
            '', // rscl (content-language)
            '', // rsct (content-type)
        ]);

        $rawKey = base64_decode($this->accountKey);
        $sig = base64_encode(hash_hmac('sha256', $stringToSign, $rawKey, true));

        $queryParams = http_build_query([
            'sp' => $signedPermissions,
            'st' => $signedStart,
            'se' => $signedExpiry,
            'spr' => $signedProtocol,
            'sv' => $signedVersion,
            'sr' => $signedResource,
            'sig' => $sig,
        ]);

        return "{$blobUrl}?{$queryParams}";
    }

    /**
     * Membubuhkan signature otentikasi SharedKey pada request headers.
     *
     * @param  array<string, string>  $customHeaders
     * @return array<string, string>
     */
    protected function signRequest(string $method, string $url, array $customHeaders = []): array
    {
        $uri = Utils::uriFor($url);
        $date = gmdate('D, d M Y H:i:s \G\M\T');

        $headers = array_merge([
            'x-ms-date' => $date,
            'x-ms-version' => $this->apiVersion,
        ], $customHeaders);

        // Canonicalized Headers (seluruh x-ms-* di-lowercase dan diurutkan)
        $xMsHeaders = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower((string) $k);
            if (str_starts_with($lk, 'x-ms-')) {
                $xMsHeaders[$lk] = trim((string) $v);
            }
        }
        ksort($xMsHeaders);
        $canonicalHeaders = [];
        foreach ($xMsHeaders as $k => $v) {
            $canonicalHeaders[] = "{$k}:{$v}";
        }

        $canonicalResource = $this->canonicalizedResource($uri);

        $stringToSign = implode("\n", [
            strtoupper($method),
            $headers['Content-Encoding'] ?? '',
            $headers['Content-Language'] ?? '',
            ! empty($headers['Content-Length']) ? (string) $headers['Content-Length'] : '',
            $headers['Content-MD5'] ?? '',
            $headers['Content-Type'] ?? '',
            '', // Date kosong karena x-ms-date digunakan
            $headers['If-Modified-Since'] ?? '',
            $headers['If-Match'] ?? '',
            $headers['If-None-Match'] ?? '',
            $headers['If-Unmodified-Since'] ?? '',
            $headers['Range'] ?? '',
            implode("\n", $canonicalHeaders),
            $canonicalResource,
        ]);

        $rawKey = base64_decode($this->accountKey);
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $rawKey, true));

        $headers['Authorization'] = "SharedKey {$this->accountName}:{$signature}";

        return $headers;
    }

    private function canonicalizedResource(UriInterface $uri): string
    {
        // The emulator includes the account in the request path as well as the signature prefix.
        $resource = '/'.$this->accountName.$uri->getPath();
        parse_str($uri->getQuery(), $parameters);
        ksort($parameters);
        foreach ($parameters as $key => $value) {
            $resource .= "\n{$key}:{$value}";
        }

        return $resource;
    }
}
