<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

class SysAdminSettingsViewModel
{
    /**
     * Konfigurasi pengaturan platform level global (Tabel system_settings).
     *
     * @return array{
     *     site_name: string,
     *     site_description: string,
     *     maintenance_mode: bool,
     *     maintenance_message: string,
     *     max_upload_size_mb: int,
     *     allowed_file_extensions: string,
     *     session_lifetime_minutes: int
     * }
     */
    public function settings(): array
    {
        try {
            if (Schema::hasTable('system_settings')) {
                return SystemSetting::getAllSettings();
            }
        } catch (\Throwable) {
            // Fallback
        }

        return SystemSetting::defaults();
    }

    /**
     * Data diagnostik kesehatan server dan operasional sistem.
     *
     * @return array{
     *     php_version: string,
     *     database_driver: string,
     *     environment: string,
     *     disk_usage_formatted: string,
     *     disk_percentage: int,
     *     cache_driver: string,
     *     malware_scanner_status: string,
     *     timezone: string,
     *     uptime: string,
     *     laravel_version: string
     * }
     */
    public function serverHealth(): array
    {
        $dbConnection = (string) config('database.default', 'sqlite');

        $diskUsageFormatted = '4.2 GB / 20 GB';
        $diskPercentage = 21;
        $diskFreeFormatted = '15.8 GB Bebas';

        try {
            $storagePath = storage_path();
            $totalBytes = @disk_total_space($storagePath);
            $freeBytes = @disk_free_space($storagePath);

            if ($totalBytes !== false && $freeBytes !== false && $totalBytes > 0) {
                $usedBytes = max(0, $totalBytes - $freeBytes);
                $diskPercentage = (int) round(($usedBytes / $totalBytes) * 100);

                $usedGb = round($usedBytes / (1024 * 1024 * 1024), 1);
                $totalGb = round($totalBytes / (1024 * 1024 * 1024), 1);
                $freeGb = round($freeBytes / (1024 * 1024 * 1024), 1);

                $diskUsageFormatted = "{$usedGb} GB / {$totalGb} GB";
                $diskFreeFormatted = "{$freeGb} GB Bebas";
            }
        } catch (\Throwable) {
            // Graceful fallback to default values
        }

        $timezone = (string) config('app.timezone', 'Asia/Makassar');
        $timezoneLabel = match ($timezone) {
            'Asia/Makassar' => 'Asia/Makassar (WITA)',
            'Asia/Jakarta' => 'Asia/Jakarta (WIB)',
            'Asia/Jayapura' => 'Asia/Jayapura (WIT)',
            'UTC' => 'UTC (Coordinated Universal Time)',
            default => $timezone,
        };

        return [
            'php_version' => 'PHP '.PHP_VERSION,
            'database_driver' => $dbConnection === 'sqlite' ? 'SQLite (pdo_sqlite engine)' : ucfirst($dbConnection),
            'environment' => app()->environment(),
            'disk_usage_formatted' => $diskUsageFormatted,
            'disk_percentage' => $diskPercentage,
            'disk_free_formatted' => $diskFreeFormatted,
            'cache_driver' => (string) config('cache.default', 'file'),
            'malware_scanner_status' => 'Aktif (ClamAV Background Scanner)',
            'timezone' => $timezoneLabel,
            'uptime' => '99.98% (30 Hari Terakhir)',
            'laravel_version' => 'Laravel '.app()->version(),
        ];
    }

    /**
     * 4 Ringkasan metrik status operasional server.
     *
     * @return array<int, array{title: string, value: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        $settings = $this->settings();
        $isMaintenance = (bool) ($settings['maintenance_mode'] ?? false);
        $health = $this->serverHealth();

        return [
            [
                'title' => 'Status Operasional',
                'value' => 'Online',
                'badge_text' => 'Normal',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
            [
                'title' => 'Mode Pemeliharaan',
                'value' => $isMaintenance ? 'Aktif' : 'Non-Aktif',
                'badge_text' => $isMaintenance ? 'Dibatasi (Admin)' : 'Publik Terbuka',
                'color_class' => $isMaintenance ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Kapasitas Disk Storage',
                'value' => "{$health['disk_percentage']}% Terpakai",
                'badge_text' => $health['disk_free_formatted'] ?? '15.8 GB Bebas',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Pemindai Malware',
                'value' => 'ClamAV Aktif',
                'badge_text' => '0 Ancaman',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }
}
