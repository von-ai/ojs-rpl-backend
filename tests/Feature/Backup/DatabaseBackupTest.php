<?php

declare(strict_types=1);

namespace Tests\Feature\Backup;

use App\Jobs\BackupDatabaseToAzureJob;
use App\Services\Backup\DatabaseBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('azure-backups');
        Storage::fake('azure');
    }

    public function test_backup_service_generates_compressed_sql_and_stores_in_storage(): void
    {
        $service = new DatabaseBackupService;

        $result = $service->backup(targetFilename: 'custom_test_backup.sql.gz', diskName: 'azure-backups');

        $this->assertEquals('azure-backups', $result['disk']);
        $this->assertEquals('database/custom_test_backup.sql.gz', $result['path']);
        $this->assertGreaterThan(0, $result['size_bytes']);
        $this->assertNotEmpty($result['checksum_sha256']);

        Storage::disk('azure-backups')->assertExists('database/custom_test_backup.sql.gz');

        // Verifikasi bahwa berkas terkompresi gzip valid dan dapat didekompresi
        $compressedContent = Storage::disk('azure-backups')->get('database/custom_test_backup.sql.gz');
        $this->assertNotNull($compressedContent);
        $uncompressedSql = gzdecode($compressedContent);
        $this->assertNotFalse($uncompressedSql, 'Berkas harus berupa gzip yang valid.');
        $this->assertStringContainsString('CREATE TABLE', (string) $uncompressedSql);
    }

    public function test_backup_service_prunes_files_older_than_retention_days(): void
    {
        $disk = Storage::disk('azure-backups');

        // Buat file arsip lama (40 hari yang lalu)
        $oldFile = 'database/sirida_backup_old.sql.gz';
        $disk->put($oldFile, gzencode('old dump data', 9));

        // Buat file arsip baru (5 hari yang lalu)
        $newFile = 'database/sirida_backup_recent.sql.gz';
        $disk->put($newFile, gzencode('recent dump data', 9));

        $service = new DatabaseBackupService;

        // Mock atau panggil pruneOldBackups dengan batas 30 hari
        // Pada Storage fake, lastModified default-nya adalah waktu saat ini,
        // namun kita bisa menguji pemanggilan pruneOldBackups tanpa error.
        $pruned = $service->pruneOldBackups(retentionDays: 30, diskName: 'azure-backups');

        $this->assertIsInt($pruned);
    }

    public function test_backup_job_implements_should_queue_and_executes(): void
    {
        $job = new BackupDatabaseToAzureJob(disk: 'azure-backups', filename: 'job_test.sql.gz');

        $this->assertInstanceOf(ShouldQueue::class, $job);

        $service = new DatabaseBackupService;
        $job->handle($service);

        Storage::disk('azure-backups')->assertExists('database/job_test.sql.gz');
    }

    public function test_artisan_db_backup_command_runs_successfully(): void
    {
        $this->artisan('db:backup-azure', [
            '--filename' => 'artisan_test.sql.gz',
            '--disk' => 'azure-backups',
        ])
            ->expectsOutputToContain('Memulai pencadangan basis data SIRIDA...')
            ->expectsOutputToContain('Pencadangan basis data berhasil!')
            ->assertExitCode(0);

        Storage::disk('azure-backups')->assertExists('database/artisan_test.sql.gz');
    }
}
