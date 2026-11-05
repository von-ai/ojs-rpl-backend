<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Backup\DatabaseBackupService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class BackupDatabaseToAzureJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        public ?string $disk = null,
        public ?string $filename = null
    ) {}

    public function handle(DatabaseBackupService $backupService): void
    {
        Log::info('BackupDatabaseToAzureJob: Memulai job pencadangan basis data ke Azure Blob Storage...');

        try {
            $result = $backupService->backup($this->filename, $this->disk);

            Log::info('BackupDatabaseToAzureJob: Pencadangan basis data berhasil.', [
                'disk' => $result['disk'],
                'path' => $result['path'],
                'size' => $result['size_human'],
                'checksum' => $result['checksum_sha256'],
                'pruned' => $result['pruned_count'],
                'duration' => "{$result['duration_seconds']}s",
            ]);
        } catch (Exception $e) {
            Log::error('BackupDatabaseToAzureJob: Gagal mencadangkan database: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
