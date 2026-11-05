<?php

namespace App\Jobs;

use App\Models\FileEntry;
use App\Services\ClamAvScanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ScanFile implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public array $backoff = [60, 300, 900];

    public function __construct(public string $fileId)
    {
        $this->onConnection('database')->onQueue('file-security')->beforeCommit();
    }

    public function handle(ClamAvScanner $scanner): void
    {
        $file = FileEntry::findOrFail($this->fileId);
        if (! in_array($file->virus_scan_status, ['pending', 'error'], true)) {
            return;
        }
        $temporary = tmpfile();
        if (! $temporary) {
            throw new RuntimeException('Tidak dapat membuat berkas pemindaian sementara.');
        }
        try {
            $source = Storage::disk($file->storage_disk)->readStream($file->storage_path);
            if (! is_resource($source)) {
                throw new RuntimeException('Objek unggahan tidak tersedia.');
            }
            try {
                stream_copy_to_stream($source, $temporary);
            } finally {
                fclose($source);
            }
            $path = stream_get_meta_data($temporary)['uri'];
            if (! $file->checksum_sha256 || ! hash_equals($file->checksum_sha256, hash_file('sha256', $path)) || filesize($path) !== $file->byte_size) {
                FileEntry::whereKey($file->id)->whereIn('virus_scan_status', ['pending', 'error'])->update(['virus_scan_status' => 'error']);
                throw new RuntimeException('Integritas objek unggahan tidak sesuai.');
            }
            $status = $scanner->scan($path);
            if (! in_array($status, ['clean', 'infected'], true)) {
                throw new RuntimeException('Hasil pemindaian tidak sah.');
            }
            FileEntry::whereKey($file->id)->where('checksum_sha256', $file->checksum_sha256)->whereIn('virus_scan_status', ['pending', 'error'])->update([
                'virus_scan_status' => $status,
            ]);
        } finally {
            fclose($temporary);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        FileEntry::whereKey($this->fileId)->whereIn('virus_scan_status', ['pending', 'error'])->update(['virus_scan_status' => 'error']);
    }
}
