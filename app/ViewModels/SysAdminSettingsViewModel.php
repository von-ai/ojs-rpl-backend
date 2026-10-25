<?php

declare(strict_types=1);

namespace App\ViewModels;

class SysAdminSettingsViewModel
{
    /**
     * Konfigurasi pengaturan platform level global (Tabel 55 system_settings).
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
        return [
            'site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar',
            'site_description' => 'Sistem Informasi Riset dan Inovasi Daerah Kota Makassar — Repositori dan penerbitan berkala ilmiah terpadu Badan Riset dan Inovasi Daerah.',
            'maintenance_mode' => false,
            'maintenance_message' => 'Sistem sedang dalam proses pemeliharaan rutin server. Silakan coba beberapa saat lagi.',
            'max_upload_size_mb' => 32,
            'allowed_file_extensions' => 'pdf, docx, odt, jpg, png, csv',
            'session_lifetime_minutes' => 120,
        ];
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
        return [
            'php_version' => 'PHP '.PHP_VERSION,
            'database_driver' => 'SQLite (pdo_sqlite engine)',
            'environment' => app()->environment(),
            'disk_usage_formatted' => '4.2 GB / 20 GB',
            'disk_percentage' => 21,
            'cache_driver' => (string) config('cache.default', 'file'),
            'malware_scanner_status' => 'Aktif (ClamAV Background Scanner)',
            'timezone' => 'Asia/Makassar (WITA)',
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
        return [
            [
                'title' => 'Status Operasional',
                'value' => 'Online',
                'badge_text' => 'Normal',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
            [
                'title' => 'Mode Pemeliharaan',
                'value' => 'Non-Aktif',
                'badge_text' => 'Publik Terbuka',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Kapasitas Disk Storage',
                'value' => '21% Terpakai',
                'badge_text' => '15.8 GB Bebas',
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
