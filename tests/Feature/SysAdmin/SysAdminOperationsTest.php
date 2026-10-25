<?php

declare(strict_types=1);

namespace Tests\Feature\SysAdmin;

use Tests\TestCase;

class SysAdminOperationsTest extends TestCase
{
    /**
     * Halaman settings memuat form konfigurasi global dan kartu diagnostik kesehatan server.
     */
    public function test_halaman_settings_memuat_konfigurasi_dan_diagnostik_server(): void
    {
        $response = $this->get('/sysadmin/settings');

        $response->assertStatus(200);
        $response->assertSee('Konfigurasi Parameter Global Situs');
        $response->assertSee('SIRIDA');
        $response->assertSee('Mode Pemeliharaan Sistem');

        // Diagnostik kesehatan server
        $response->assertSee('Diagnostik Kesehatan Server');
        $response->assertSee('PHP');
        $response->assertSee('SQLite');
        $response->assertSee('ClamAV');
        $response->assertSee('Kosongkan Cache Aplikasi');
    }

    /**
     * Aksi pembaruan pengaturan situs dan pembersihan cache menghasilkan redirect.
     */
    public function test_aksi_pengaturan_dan_pembersihan_cache_berhasil(): void
    {
        // 1. Simpan pengaturan situs
        $resUpdate = $this->post('/sysadmin/settings', [
            'site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar Terpadu',
            'max_upload_size_mb' => 40,
        ]);
        $resUpdate->assertRedirect(route('sysadmin.settings.index'));
        $resUpdate->assertSessionHas('status');

        // 2. Bersihkan cache sistem
        $resClear = $this->post('/sysadmin/settings/clear-cache');
        $resClear->assertRedirect(route('sysadmin.settings.index'));
        $resClear->assertSessionHas('status');
    }

    /**
     * Halaman audit logs memuat 4 metrik append-only dan daftar log komprehensif.
     */
    public function test_halaman_audit_logs_memuat_metrik_dan_daftar_log(): void
    {
        $response = $this->get('/sysadmin/audit-logs');

        $response->assertStatus(200);
        $response->assertSee('Total Log Tercatat');
        $response->assertSee('Aktivitas Pengguna');
        $response->assertSee('Eksekusi Sistem Otomatis');
        $response->assertSee('Integritas Log Mutasi');

        // Memuat log tertentu
        $response->assertSee('LOG-01JARK-01');
        $response->assertSee('Pembaruan Pengaturan Platform');
        $response->assertSee('system_settings.updated');
    }

    /**
     * Filter tab tipe aktor audit log menyaring entri pengguna dan sistem dengan benar.
     */
    public function test_filter_tab_aktor_audit_log_berfungsi(): void
    {
        // 1. Filter aktor Sistem (system)
        $responseSystem = $this->get('/sysadmin/audit-logs?actor_type=system');
        $responseSystem->assertStatus(200);
        $responseSystem->assertSee('LOG-01JARK-03');
        $responseSystem->assertSee('clamav_scan_daemon');
        $responseSystem->assertDontSee('LOG-01JARK-01');

        // 2. Filter aktor Pengguna (user)
        $responseUser = $this->get('/sysadmin/audit-logs?actor_type=user');
        $responseUser->assertStatus(200);
        $responseUser->assertSee('LOG-01JARK-01');
        $responseUser->assertSee('LOG-01JARK-02');
        $responseUser->assertDontSee('LOG-01JARK-03');
    }

    /**
     * Pencarian log audit berdasarkan kata kunci aksi atau request_id berfungsi.
     */
    public function test_pencarian_log_audit_berfungsi_sesuai_kata_kunci(): void
    {
        $responseSearch = $this->get('/sysadmin/audit-logs?q=req_01jark_8849af');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('LOG-01JARK-01');
        $responseSearch->assertSee('req_01jark_8849af');
        $responseSearch->assertDontSee('LOG-01JARK-02');
    }
}
