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
    public function backup(?string $targetFilename = null, ?string $diskName = null): array
    {
        $startTime = microtime(true);
        $targetDisk = $diskName ?: $this->resolveDefaultBackupDisk();

        $timestamp = Carbon::now('UTC')->format('Y-m-d_His');
        $filename = $targetFilename ?: "sirida_backup_{$timestamp}.sql.gz";
        if (! str_ends_with($filename, '.sql.gz')) {
            $filename .= '.sql.gz';
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
            $prunedCount = $this->pruneOldBackups(30, $targetDisk);
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
                    $storage->delete($file);
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
            "--password={$password}",
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            $database,
        ];

        $process = Process::run($command);

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
        $databaseName = DB::connection()->getDatabaseName();

        $output = [];
        $output[] = '-- -------------------------------------------------------------';
        $output[] = '-- SIRIDA DATABASE BACKUP (Cloud-Native Ephemeral Dump)';
        $output[] = '-- Generated At: '.Carbon::now('UTC')->toIso8601String().' (UTC)';
        $output[] = "-- Target Database: {$databaseName}";
        $output[] = '-- -------------------------------------------------------------';
        $output[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $output[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $output[] = "SET time_zone = '+00:00';\n";

        // Ambil daftar seluruh tabel
        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        }

        foreach ($tables as $table) {
            $output[] = "-- Table structure for `{$table}`";
            $output[] = "DROP TABLE IF EXISTS `{$table}`;";

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            if ($createStmt) {
                $createRow = $createStmt->fetch(PDO::FETCH_ASSOC);
                $createSql = $createRow['Create Table'] ?? ($createRow['create table'] ?? null);
                if ($createSql) {
                    $output[] = $createSql.";\n";
                }
            }

            // Data rows
            $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
            if ($dataStmt) {
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
                if (! empty($rows)) {
                    $output[] = "-- Dumping data for table `{$table}`";
                    $columns = array_map(fn ($col) => "`{$col}`", array_keys($rows[0]));
                    $colList = implode(', ', $columns);

                    $insertStatements = [];
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function ($val) use ($pdo) {
                            if ($val === null) {
                                return 'NULL';
                            }

                            return $pdo->quote((string) $val);
                        }, array_values($row));

                        $insertStatements[] = '('.implode(', ', $escapedValues).')';
                    }

                    // Pecah batch 200 baris per query
                    $chunks = array_chunk($insertStatements, 200);
                    foreach ($chunks as $chunk) {
                        $output[] = "INSERT INTO `{$table}` ({$colList}) VALUES\n".implode(",\n", $chunk).';';
                    }
                    $output[] = '';
                }
            }
        }

        $output[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $output[] = '-- Backup dump completed successfully.';

        return implode("\n", $output);
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

        $stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (! empty($row['sql'])) {
                    $output[] = $row['sql'].';';
                }
            }
        }

        return implode("\n", $output);
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
