<?php

declare(strict_types=1);

namespace App\Services\Backup;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use PDO;
use RuntimeException;

class DatabaseBackupService
{
    /**
     * Menjalankan pencadangan basis data terkompresi dan mengunggahnya ke Azure Blob Storage.
     * Spesifikasi: SRS NF-08 (Backup otomatis tiap 24 jam dengan retensi 30 hari).
     *
     * @return array{
     *     disk: string,
     *     path: string,
     *     filename: string,
     *     size_bytes: int,
     *     size_human: string,
     *     checksum_sha256: string,
     *     pruned_count: int,
     *     duration_seconds: float
     * }
     */
    public function backup(?string $targetFilename = null, ?string $diskName = null, int $retentionDays = 30): array
    {
        $startTime = microtime(true);
        $targetDisk = $diskName ?: $this->resolveDefaultBackupDisk();

        $timestamp = Carbon::now('UTC')->format('Y-m-d_His');
        $filename = $targetFilename ?: "sirida_backup_{$timestamp}.sql.gz";
        if (! str_ends_with($filename, '.sql.gz')) {
            $filename .= '.sql.gz';
        }

        if (basename($filename) !== $filename || ! preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/', $filename)) {
            throw new RuntimeException('Nama berkas cadangan tidak valid.');
        }

        $relativePath = "database/{$filename}";

        Log::info("Memulai pencadangan database ke disk '{$targetDisk}'...", ['path' => $relativePath]);

        // 1. Ekspor dump SQL (mysqldump dengan fallback native PDO)
        $rawSql = $this->generateSqlDump();

        // 2. Kompresi gzip level 9 untuk reduksi volume bita
        $compressedData = gzencode($rawSql, 9);
        if ($compressedData === false) {
            throw new RuntimeException('Gagal mengompresi arsip cadangan SQL.');
        }

        // 3. Simpan ke Storage (Azure Blob)
        $storage = Storage::disk($targetDisk);
        $saved = $storage->put($relativePath, $compressedData);
        if (! $saved) {
            throw new RuntimeException("Gagal menulis arsip cadangan ke penyimpanan '{$targetDisk}' di path '{$relativePath}'.");
        }

        $sizeBytes = strlen($compressedData);
        $checksum = hash('sha256', $compressedData);

        // 4. Terapkan retensi 30 hari (NF-08: hapus arsip yang lebih lama dari 30 hari)
        $prunedCount = 0;
        try {
            $prunedCount = $this->pruneOldBackups($retentionDays, $targetDisk);
        } catch (Exception $e) {
            Log::warning("Pembersihan retensi backup gagal dijalankan: {$e->getMessage()}");
        }

        $duration = round(microtime(true) - $startTime, 2);

        $result = [
            'disk' => $targetDisk,
            'path' => $relativePath,
            'filename' => $filename,
            'size_bytes' => $sizeBytes,
            'size_human' => $this->formatBytes($sizeBytes),
            'checksum_sha256' => $checksum,
            'pruned_count' => $prunedCount,
            'duration_seconds' => $duration,
        ];

        Log::info("Pencadangan database berhasil diselesaikan dalam {$duration}s", $result);

        return $result;
    }

    /**
     * Menghapus berkas cadangan yang melampaui batas retensi hari (NF-08).
     */
    public function pruneOldBackups(int $retentionDays = 30, ?string $diskName = null): int
    {
        $targetDisk = $diskName ?: $this->resolveDefaultBackupDisk();
        $storage = Storage::disk($targetDisk);

        $files = $storage->files('database');
        $cutoffTimestamp = Carbon::now('UTC')->subDays($retentionDays)->timestamp;
        $prunedCount = 0;

        foreach ($files as $file) {
            if (! str_ends_with($file, '.sql.gz')) {
                continue;
            }

            try {
                $lastModified = $storage->lastModified($file);
                if ($lastModified < $cutoffTimestamp) {
                    if (! $storage->delete($file)) {
                        throw new RuntimeException('Penyimpanan menolak penghapusan cadangan.');
                    }
                    $prunedCount++;
                    Log::info("Menghapus cadangan lama (retensi {$retentionDays} hari): {$file}");
                }
            } catch (Exception $e) {
                Log::warning("Gagal mengevaluasi waktu berkas {$file}: {$e->getMessage()}");
            }
        }

        return $prunedCount;
    }

    /**
     * Membentuk dump SQL lengkap.
     * Mengutamakan utility CLI `mysqldump` jika tersedia di sistem,
     * dengan fallback otomatis ke PHP PDO Dumper.
     */
    protected function generateSqlDump(): string
    {
        $driver = config('database.default', 'mysql');

        if ($driver === 'sqlite') {
            return $this->dumpSqliteDatabase();
        }

        // Coba jalankan mysqldump binary terlebih dahulu
        try {
            $cliDump = $this->dumpViaMysqldumpCli();
            if (! empty($cliDump)) {
                return $cliDump;
            }
        } catch (Exception $e) {
            Log::debug("mysqldump CLI tidak tersedia atau gagal ({$e->getMessage()}), beralih ke PDO native dumper.");
        }

        return $this->dumpViaNativePdo();
    }

    /**
     * Ekspor dump SQL menggunakan perintah CLI mysqldump resmi.
     */
    protected function dumpViaMysqldumpCli(): string
    {
        $connection = config('database.default', 'mysql');
        $host = config("database.connections.{$connection}.host", '127.0.0.1');
        $port = config("database.connections.{$connection}.port", '3306');
        $database = config("database.connections.{$connection}.database", '');
        $username = config("database.connections.{$connection}.username", '');
        $password = config("database.connections.{$connection}.password", '');

        if (empty($database)) {
            throw new RuntimeException('Nama database tidak dikonfigurasi.');
        }

        $command = [
            'mysqldump',
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--no-tablespaces',
            '--hex-blob',
            '--routines',
            '--triggers',
            $database,
        ];

        $defaults = tmpfile();
        if (! is_resource($defaults)) {
            throw new RuntimeException('Tidak dapat membuat konfigurasi mysqldump sementara.');
        }

        try {
            $escapedPassword = addcslashes($password, "\\\"\r\n\t");
            $options = "[client]\npassword=\"{$escapedPassword}\"\n";
            if (fwrite($defaults, $options) !== strlen($options) || ! fflush($defaults)) {
                throw new RuntimeException('Tidak dapat menyimpan konfigurasi mysqldump sementara.');
            }
            $defaultsPath = stream_get_meta_data($defaults)['uri'];
            array_splice($command, 1, 0, ["--defaults-extra-file={$defaultsPath}"]);
            $process = Process::timeout(480)->run($command);
        } finally {
            fclose($defaults);
        }

        if (! $process->successful()) {
            throw new RuntimeException('Perintah mysqldump gagal dieksekusi: '.$process->errorOutput());
        }

        return $process->output();
    }

    /**
     * Fallback native PHP PDO SQL Dumper untuk menjamin kemandirian tanpa dependensi binary eksternal.
     */
    protected function dumpViaNativePdo(): string
    {
        $pdo = DB::connection()->getPdo();
        $ownsTransaction = ! $pdo->inTransaction();
        if ($ownsTransaction) {
            $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->beginTransaction();
        }

        try {
            $output = [
                'SET FOREIGN_KEY_CHECKS = 0;',
                "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';",
                "SET time_zone = '+00:00';",
            ];
            $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $name = $this->quoteIdentifier($table);
                $definition = $pdo->query("SHOW CREATE TABLE {$name}")->fetch(PDO::FETCH_NUM);
                $output[] = "DROP TABLE IF EXISTS {$name};";
                $output[] = $definition[1].';';

                $columns = [];
                foreach ($pdo->query("SHOW FULL COLUMNS FROM {$name}") as $column) {
                    if (! preg_match('/(?:VIRTUAL|STORED) GENERATED/i', $column['Extra'])) {
                        $columns[] = $this->quoteIdentifier($column['Field']);
                    }
                }
                $this->appendRows($output, $pdo, $name, $columns);
            }

            foreach ($pdo->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_ASSOC) as $trigger) {
                $name = $this->quoteIdentifier($trigger['Trigger']);
                $table = $this->quoteIdentifier($trigger['Table']);
                $timing = $trigger['Timing'];
                $event = $trigger['Event'];
                $statement = $trigger['Statement'];
                $output[] = "DROP TRIGGER IF EXISTS {$name};";
                // A trigger body can contain semicolons, so use a dedicated client delimiter.
                $delimiter = '$$';
                while (str_contains($statement, $delimiter)) {
                    $delimiter .= '$';
                }
                $output[] = "DELIMITER {$delimiter}";
                $output[] = "CREATE TRIGGER {$name} {$timing} {$event} ON {$table} FOR EACH ROW {$statement}{$delimiter}";
                $output[] = 'DELIMITER ;';
            }

            $output[] = 'SET FOREIGN_KEY_CHECKS = 1;';
            if ($ownsTransaction) {
                $pdo->commit();
            }

            return implode("\n", $output)."\n";
        } catch (\Throwable $e) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @param  list<string>  $output
     * @param  list<string>  $columns
     */
    private function appendRows(array &$output, PDO $pdo, string $table, array $columns): void
    {
        $columnList = implode(', ', $columns);
        $statement = $pdo->query("SELECT {$columnList} FROM {$table}");
        $batch = [];
        while (($row = $statement->fetch(PDO::FETCH_NUM)) !== false) {
            $values = array_map(
                fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value),
                $row
            );
            $batch[] = '('.implode(', ', $values).')';
            if (count($batch) === 200) {
                $output[] = "INSERT INTO {$table} ({$columnList}) VALUES\n".implode(",\n", $batch).';';
                $batch = [];
            }
        }
        if ($batch !== []) {
            $output[] = "INSERT INTO {$table} ({$columnList}) VALUES\n".implode(",\n", $batch).';';
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    /**
     * Dumper khusus basis data SQLite untuk pengujian otomatis (PHPUnit).
     */
    protected function dumpSqliteDatabase(): string
    {
        $pdo = DB::connection()->getPdo();
        $output = [];
        $output[] = '-- SIRIDA SQLite Backup';
        $output[] = '-- Generated At: '.Carbon::now('UTC')->toIso8601String();

        $output[] = 'PRAGMA foreign_keys = OFF;';
        $output[] = 'BEGIN;';
        $tables = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tables as $table) {
            $output[] = $table['sql'].';';
            $name = $this->quoteIdentifier($table['name']);
            $columns = [];
            foreach ($pdo->query("PRAGMA table_xinfo({$name})") as $column) {
                if ((int) $column['hidden'] === 0) {
                    $columns[] = $this->quoteIdentifier($column['name']);
                }
            }
            $this->appendRows($output, $pdo, $name, $columns);
        }
        foreach ($pdo->query("SELECT sql FROM sqlite_master WHERE type IN ('index', 'trigger', 'view') AND sql IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN) as $sql) {
            $output[] = $sql.';';
        }
        $output[] = 'COMMIT;';
        $output[] = 'PRAGMA foreign_keys = ON;';

        return implode("\n", $output)."\n";
    }

    /**
     * Menentukan disk penyimpanan cadangan yang tersedia.
     */
    protected function resolveDefaultBackupDisk(): string
    {
        $configuredDisks = config('filesystems.disks', []);

        if (isset($configuredDisks['azure-backups'])) {
            return 'azure-backups';
        }

        if (isset($configuredDisks['azure'])) {
            return 'azure';
        }

        return config('filesystems.default', 'azure');
    }

    /**
     * Format ukuran bita menjadi teks yang mudah dibaca.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 2).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }
}
