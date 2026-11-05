<?php

declare(strict_types=1);

namespace Tests\Feature\Backup;

use App\Jobs\BackupDatabaseToAzureJob;
use App\Models\User;
use App\Services\Backup\DatabaseBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PDO;
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

        $oldFile = 'database/sirida_backup_old.sql.gz';
        $newFile = 'database/sirida_backup_recent.sql.gz';
        $disk->put($oldFile, gzencode('old dump data'));
        $disk->put($newFile, gzencode('recent dump data'));
        touch($disk->path($oldFile), now()->subDays(40)->timestamp);
        touch($disk->path($newFile), now()->subDays(5)->timestamp);

        $this->assertSame(1, (new DatabaseBackupService)->pruneOldBackups(30, 'azure-backups'));
        $disk->assertMissing($oldFile);
        $disk->assertExists($newFile);
    }

    public function test_sqlite_backup_restores_rows_and_append_only_triggers(): void
    {
        if (config('database.default') !== 'sqlite') {
            $this->markTestSkipped('This test restores the SQLite backup format.');
        }

        $user = User::factory()->create();
        $disk = Storage::disk('local');
        $result = (new DatabaseBackupService)->backup('restore-test.sql.gz', 'local');
        $restored = new PDO('sqlite::memory:');
        $restored->exec(gzdecode($disk->get($result['path'])));

        $this->assertSame($user->id, $restored->query('SELECT id FROM users')->fetchColumn());
        $this->assertSame(2, (int) $restored->query("SELECT count(*) FROM sqlite_master WHERE type='trigger' AND name LIKE 'audit_logs_forbid_%'")->fetchColumn());
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
