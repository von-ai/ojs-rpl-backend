<?php

declare(strict_types=1);

namespace App\Services\Storage;

use DateTimeInterface;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToWriteFile;
use Throwable;

class AzureBlobStorageAdapter implements FilesystemAdapter
{
    public function __construct(protected AzureBlobStorageClient $client) {}

    public function getClient(): AzureBlobStorageClient
    {
        return $this->client;
    }

    public function fileExists(string $path): bool
    {
        try {
            return $this->client->blobExists($path);
        } catch (Throwable $e) {
            throw UnableToCheckExistence::forLocation($path, $e);
        }
    }

    public function directoryExists(string $path): bool
    {
        // Dalam Azure Blob (flat namespace), folder bersifat virtual prefix
        return true;
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $mimeType = $config->get('ContentType') ?? $config->get('mimetype');
            $this->client->putBlob($path, $contents, $mimeType);
        } catch (Throwable $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $streamContents = stream_get_contents($contents);
        if ($streamContents === false) {
            throw UnableToWriteFile::atLocation($path, 'Gagal membaca stream unggahan');
        }

        $this->write($path, $streamContents, $config);
    }

    public function read(string $path): string
    {
        try {
            return $this->client->getBlob($path);
        } catch (Throwable $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function readStream(string $path)
    {
        try {
            return $this->client->getBlobStream($path);
        } catch (Throwable $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->client->deleteBlob($path);
        } catch (Throwable $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
        // Operasi virtual folder di Azure Blob
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Flat namespace tidak membutuhkan pembuatan direktori fisik
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Hak akses dikontrol pada level container / SAS URL
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'private');
    }

    public function mimeType(string $path): FileAttributes
    {
        try {
            $props = $this->client->getBlobProperties($path);

            return new FileAttributes($path, $props['size'], 'private', $props['last_modified'], $props['mime']);
        } catch (Throwable $e) {
            throw UnableToRetrieveMetadata::mimeType($path, $e->getMessage(), $e);
        }
    }

    public function lastModified(string $path): FileAttributes
    {
        try {
            $props = $this->client->getBlobProperties($path);

            return new FileAttributes($path, $props['size'], 'private', $props['last_modified']);
        } catch (Throwable $e) {
            throw UnableToRetrieveMetadata::lastModified($path, $e->getMessage(), $e);
        }
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            $props = $this->client->getBlobProperties($path);

            return new FileAttributes($path, $props['size']);
        } catch (Throwable $e) {
            throw UnableToRetrieveMetadata::fileSize($path, $e->getMessage(), $e);
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = $path === '' ? '' : rtrim($path, '/').'/';
        $directories = [];
        foreach ($this->client->listBlobs($path) as $blob) {
            $relativePath = substr($blob['path'], strlen($prefix));
            if (! $deep && str_contains($relativePath, '/')) {
                $directory = $prefix.strstr($relativePath, '/', true);
                if (! isset($directories[$directory])) {
                    yield new DirectoryAttributes($directory);
                    $directories[$directory] = true;
                }

                continue;
            }

            yield new FileAttributes($blob['path'], $blob['size'], lastModified: $blob['modified']);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->copy($source, $destination, $config);
            $this->delete($source);
        } catch (Throwable $e) {
            throw UnableToMoveFile::fromLocationTo($source, $destination, $e);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->client->copyBlob($source, $destination);
        } catch (Throwable $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
    }

    /**
     * Mendapatkan URL publik atau SAS download berkas (NF-01).
     */
    public function getUrl(string $path): string
    {
        return $this->client->getBlobUrl($path);
    }

    /**
     * Menghasilkan Shared Access Signature (SAS) URL sementara untuk download aman (Double-Blind Privacy).
     */
    public function getTemporaryUrl(string $path, DateTimeInterface $expiration, array $options = []): string
    {
        return $this->client->generateSasUrl($path, $expiration, 'r');
    }

    /**
     * Menghasilkan SAS URL untuk direct upload dari browser (Bypass memory server web - SRS NF-01).
     *
     * @return array{url: string, headers: array<string, string>}
     */
    public function temporaryUploadUrl(string $path, DateTimeInterface $expiration, array $options = []): array
    {
        $url = $this->client->generateSasUrl($path, $expiration, 'rcw');

        return [
            'url' => $url,
            'headers' => [
                'x-ms-blob-type' => 'BlockBlob',
            ],
        ];
    }
}
