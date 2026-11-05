<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\FileEntry;
use App\Services\Security\MalwareScannerInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ScanFile implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 180;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(public string $fileId)
    {
        $this->onConnection('database')->onQueue('file-security')->beforeCommit();
    }

    public function handle(MalwareScannerInterface $scanner): void
    {
        $file = FileEntry::findOrFail($this->fileId);
        if (! in_array($file->virus_scan_status, ['pending', 'error'], true)) {
            return;
        }

        $disk = Storage::disk($file->storage_disk);
        if (! $disk->exists($file->storage_path)) {
            throw new RuntimeException('Objek unggahan tidak tersedia pada storage.');
        }

        // Verifikasi integritas objek unggahan jika checksum_sha256 tersedia
        if ($file->checksum_sha256) {
            $stream = $disk->readStream($file->storage_path);
            if (! is_resource($stream)) {
                throw new RuntimeException('Objek unggahan tidak dapat dibaca dari storage.');
            }
            try {
                $hash = hash_init('sha256');
                hash_update_stream($hash, $stream);
                $calculated = hash_final($hash);
                if (! hash_equals(strtolower((string) $file->checksum_sha256), strtolower($calculated))) {
                    FileEntry::whereKey($file->id)->whereIn('virus_scan_status', ['pending', 'error'])->update(['virus_scan_status' => 'error']);
                    throw new RuntimeException('Integritas objek unggahan tidak sesuai.');
                }
            } finally {
                fclose($stream);
            }
        }

        $status = $scanner->scan($file);

        if ($status === 'pending') {
            // Microsoft Defender for Storage masih memindai, jadwalkan ulang job
            $this->release(10);

            return;
        }

        if (! in_array($status, ['clean', 'infected'], true)) {
            throw new RuntimeException('Hasil pemindaian tidak sah.');
        }

        FileEntry::whereKey($file->id)
            ->where('checksum_sha256', $file->checksum_sha256)
            ->whereIn('virus_scan_status', ['pending', 'error'])
            ->update([
                'virus_scan_status' => $status,
            ]);
    }

    public function failed(?\Throwable $exception): void
    {
        FileEntry::whereKey($this->fileId)
            ->whereIn('virus_scan_status', ['pending', 'error'])
            ->update(['virus_scan_status' => 'error']);
    }
}
