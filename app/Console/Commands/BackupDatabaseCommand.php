<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Backup\DatabaseBackupService;
use Exception;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * Nama dan signature perintah console.
     *
     * @var string
     */
    protected $signature = 'db:backup-azure
                            {--filename= : Nama berkas arsip sql.gz kustom}
                            {--disk= : Storage disk tujuan pencadangan (default: azure-backups)}
                            {--prune-days=30 : Batas hari retensi pembersihan arsip lama}';

    /**
     * Deskripsi perintah console.
     *
     * @var string
     */
    protected $description = 'Mencadangkan basis data terkompresi gzip ke Azure Blob Storage (NF-08)';

    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Memulai pencadangan basis data SIRIDA...');

        $filename = $this->option('filename');
        $disk = $this->option('disk');
        $retentionDays = filter_var($this->option('prune-days'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($retentionDays === false) {
            $this->error('Retensi cadangan harus berupa jumlah hari positif.');

            return self::INVALID;
        }

        try {
            $result = $backupService->backup(
                targetFilename: is_string($filename) ? $filename : null,
                diskName: is_string($disk) ? $disk : null,
                retentionDays: $retentionDays
            );

            $this->newLine();
            $this->info('✓ Pencadangan basis data berhasil!');
            $this->table(
                ['Parameter', 'Nilai'],
                [
                    ['Storage Disk', $result['disk']],
                    ['Arsip Berkas', $result['path']],
                    ['Ukuran', $result['size_human'].' ('.$result['size_bytes'].' bytes)'],
                    ['SHA-256 Checksum', $result['checksum_sha256']],
                    ['Arsip Lama Dibersihkan', $result['pruned_count'].' berkas'],
                    ['Durasi', $result['duration_seconds'].' detik'],
                ]
            );

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->newLine();
            $this->error('✗ Gagal melakukan pencadangan: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
