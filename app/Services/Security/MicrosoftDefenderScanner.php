<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\FileEntry;
use App\Services\Storage\AzureBlobStorageAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Driver pemindai produksi menggunakan Microsoft Defender for Storage (Malware Scanning).
 * Memeriksa Blob Index Tags ('Malware Scanning scan result') yang disematkan secara otomatis
 * oleh Microsoft Defender saat naskah diunggah ke Azure Blob Storage.
 */
class MicrosoftDefenderScanner implements MalwareScannerInterface
{
    public const MALWARE_TAG_KEY = 'Malware Scanning scan result';

    public const RESULT_CLEAN = 'No threats found';

    public const RESULT_MALICIOUS = 'Malicious';

    public function scan(FileEntry $file): string
    {
        $disk = Storage::disk($file->storage_disk);

        if (! $disk->exists($file->storage_path)) {
            throw new RuntimeException('Objek pemindaian tidak tersedia pada storage.');
        }

        $client = null;
        try {
            $adapter = $disk->getAdapter();
            if ($adapter instanceof AzureBlobStorageAdapter) {
                $client = $adapter->getClient();
            }
        } catch (Throwable) {
            $client = null;
        }

        if ($client !== null) {
            try {
                $tags = $client->getBlobTags($file->storage_path);
                if (isset($tags[self::MALWARE_TAG_KEY])) {
                    $result = trim($tags[self::MALWARE_TAG_KEY]);
                    if (strcasecmp($result, self::RESULT_CLEAN) === 0) {
                        return 'clean';
                    }
                    if (strcasecmp($result, self::RESULT_MALICIOUS) === 0) {
                        return 'infected';
                    }
                }
            } catch (Throwable $e) {
                Log::warning("DefenderScanner: Gagal membaca index tags pada '{$file->storage_path}': ".$e->getMessage());
            }
        }

        // Defender masih dalam proses pemindaian (tags belum siap)
        return 'pending';
    }
}
