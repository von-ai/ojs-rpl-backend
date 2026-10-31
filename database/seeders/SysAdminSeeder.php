<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Journal;
use App\Models\Section;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserImport;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SysAdminSeeder extends Seeder
{
    /**
     * Menjalankan seeding komprehensif data Portal System Administrator.
     */
    public function run(): void
    {
        // 1. Pengguna Utama System Administrator (Fahri Azis, S.T.)
        $primaryAdmin = User::firstOrCreate(
            ['email' => 'fahri.azis@makassar.go.id'],
            [
                'username' => 'fahri.azis',
                'name' => 'Fahri Azis, S.T.',
                'full_name' => 'Fahri Azis, S.T.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'version' => 1,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $primaryAdmin->id],
            [
                'affiliation' => 'Bidang Aplikasi Informatika & Inovasi BRIDA Makassar',
                'country_code' => 'ID',
            ]
        );

        UserRole::firstOrCreate(
            [
                'user_id' => $primaryAdmin->id,
                'journal_id' => null,
                'role_code' => 'sysadmin',
            ],
            [
                'expertise_keywords' => ['Superadmin Platform', 'Sistem Infrastruktur IT'],
            ]
        );

        // 2. Administrator Sekunder (Muh. Ilham, S.Kom., M.T. - Diskominfo)
        $secondaryAdmin = User::firstOrCreate(
            ['email' => 'ilham.kominfo@makassar.go.id'],
            [
                'username' => 'ilham.kominfo',
                'name' => 'Muh. Ilham, S.Kom., M.T.',
                'full_name' => 'Muh. Ilham, S.Kom., M.T.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'version' => 1,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $secondaryAdmin->id],
            [
                'affiliation' => 'Dinas Komunikasi dan Informatika Kota Makassar',
                'country_code' => 'ID',
            ]
        );

        UserRole::firstOrCreate(
            [
                'user_id' => $secondaryAdmin->id,
                'journal_id' => null,
                'role_code' => 'sysadmin',
            ],
            [
                'expertise_keywords' => ['Keamanan Siber', 'Jaringan & Infrastruktur'],
            ]
        );

        // 3. Instansi Jurnal Ilmiah (Multi-Tenancy)
        $jtiik = Journal::firstOrCreate(
            ['slug' => 'jtiik-makassar'],
            [
                'name' => 'Jurnal Teknologi Informasi dan Inovasi Perkotaan (JTIIK)',
                'initials' => 'JTIIK',
                'issn' => '2655-9012',
                'eissn' => '2722-4567',
                'publisher' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'editorial_email' => 'jtiik@makassar.go.id',
                'contact_phone' => '+62 411 855-123',
                'focus_scope' => 'Publikasi ilmiah berkala memuat riset terapan smart city, IoT, dan transformasi digital pemerintahan Kota Makassar.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        $jikpm = Journal::firstOrCreate(
            ['slug' => 'sirida-inovasi'],
            [
                'name' => 'Jurnal Inovasi Daerah & Kebijakan Publik Makassar (JIKPM)',
                'initials' => 'JIKPM',
                'issn' => '2774-8890',
                'eissn' => '2809-1234',
                'publisher' => 'Dr. Andi Nurul Hidayah, M.Si.',
                'editorial_email' => 'jikpm@makassar.go.id',
                'contact_phone' => '+62 411 855-456',
                'focus_scope' => 'Kajian empiris efektivitas tata kelola birokrasi, inovasi pelayanan publik, dan pembiayaan pembangunan daerah.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        $jrlmbp = Journal::firstOrCreate(
            ['slug' => 'jurnal-lingkungan'],
            [
                'name' => 'Jurnal Riset Lingkungan & Mitigasi Bencana Pesisir (JRLMBP)',
                'initials' => 'JRLMBP',
                'issn' => '2985-3341',
                'eissn' => '2986-7789',
                'publisher' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                'editorial_email' => 'lingkungan@makassar.go.id',
                'contact_phone' => '+62 411 855-789',
                'focus_scope' => 'Kajian multidisiplin oseanografi, mitigasi banjir rob kawasan kepulauan spermonde, dan adaptasi perubahan iklim.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        $arsip = Journal::firstOrCreate(
            ['slug' => 'arsip-litbang-makassar'],
            [
                'name' => 'Buletin Riset Litbang Kota Makassar (Historis 2018-2022)',
                'initials' => 'BRLM',
                'issn' => '2301-4455',
                'eissn' => null,
                'publisher' => 'Fahri Azis, S.T. (SysAdmin)',
                'editorial_email' => 'litbang.arsip@makassar.go.id',
                'focus_scope' => 'Arsip terbitan monograf dan buletin penelitian Badan Penelitian dan Pengembangan Daerah pra-transformasi BRIDA.',
                'frequency' => 'Tahunan (Arsip)',
                'status' => 'archived',
                'version' => 1,
            ]
        );

        // Inisialisasi seksi default untuk seluruh instansi jurnal
        foreach ([$jtiik, $jikpm, $jrlmbp, $arsip] as $jnl) {
            Section::firstOrCreate(
                [
                    'journal_id' => $jnl->id,
                    'slug' => 'artikel-riset',
                ],
                [
                    'name' => 'Artikel Riset',
                    'position' => 1,
                ]
            );
        }

        // Pengguna Instansi Jurnal Multi-Tenancy (Dewan Redaksi & Penulis)
        $rita = User::firstOrCreate(
            ['email' => 'rita.irmawaty@unhas.ac.id'],
            [
                'username' => 'rita.irmawaty',
                'name' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'full_name' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'version' => 1,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $rita->id],
            [
                'affiliation' => 'Departemen Teknik Sipil & Lingkungan Universitas Hasanuddin',
                'country_code' => 'ID',
            ]
        );
        UserRole::firstOrCreate(
            ['user_id' => $rita->id, 'journal_id' => $jtiik->id, 'role_code' => 'editor_in_chief'],
            ['expertise_keywords' => ['Smart City', 'Infrastruktur Kota']]
        );
        UserRole::firstOrCreate(
            ['user_id' => $rita->id, 'journal_id' => $jtiik->id, 'role_code' => 'reviewer'],
            ['expertise_keywords' => ['Teknik Sipil', 'Sensor IoT']]
        );

        $andiNurul = User::firstOrCreate(
            ['email' => 'andi.nurul@unm.ac.id'],
            [
                'username' => 'andi.nurul',
                'name' => 'Dr. Andi Nurul Hidayah, M.Si.',
                'full_name' => 'Dr. Andi Nurul Hidayah, M.Si.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'version' => 1,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $andiNurul->id],
            [
                'affiliation' => 'Fakultas Ilmu Sosial dan Hukum Universitas Negeri Makassar',
                'country_code' => 'ID',
            ]
        );
        UserRole::firstOrCreate(
            ['user_id' => $andiNurul->id, 'journal_id' => $jikpm->id, 'role_code' => 'section_editor']
        );
        UserRole::firstOrCreate(
            ['user_id' => $andiNurul->id, 'journal_id' => $jikpm->id, 'role_code' => 'author']
        );

        $salehPallu = User::firstOrCreate(
            ['email' => 'saleh.pallu@unhas.ac.id'],
            [
                'username' => 'saleh.pallu',
                'name' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                'full_name' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'version' => 1,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $salehPallu->id],
            [
                'affiliation' => 'Pusat Riset Mitigasi Bencana & Oseanografi BRIDA',
                'country_code' => 'ID',
            ]
        );
        UserRole::firstOrCreate(
            ['user_id' => $salehPallu->id, 'journal_id' => $jrlmbp->id, 'role_code' => 'section_editor']
        );
        UserRole::firstOrCreate(
            ['user_id' => $salehPallu->id, 'journal_id' => $jrlmbp->id, 'role_code' => 'reviewer'],
            ['expertise_keywords' => ['Mitigasi Rob', 'Oseanografi']]
        );

        // 4. Konfigurasi Parameter Global Platform (system_settings)
        $defaultSettings = SystemSetting::defaults();
        foreach ($defaultSettings as $key => $val) {
            $type = is_bool($val) ? 'boolean' : (is_int($val) ? 'integer' : 'string');
            $group = match ($key) {
                'maintenance_mode', 'maintenance_message' => 'maintenance',
                'max_upload_size_mb', 'allowed_file_extensions' => 'storage',
                'session_lifetime_minutes' => 'security',
                default => 'general',
            };
            SystemSetting::setValue($key, $val, $type, $group);
        }

        // 5. Riwayat Pengimporan Pengguna CSV (user_imports)
        UserImport::firstOrCreate(
            ['file_name' => 'Reviewer_Kepakaran_SmartCity_2026.csv'],
            [
                'id' => 'IMPORT-01JARK-01',
                'journal_id' => $jtiik->id,
                'total_rows' => 35,
                'processed_rows' => 35,
                'failed_rows' => 0,
                'status' => 'completed',
                'error_log' => [],
                'imported_by' => $primaryAdmin->id,
                'created_at' => now()->subDays(8),
            ]
        );

        UserImport::firstOrCreate(
            ['file_name' => 'Dosen_Fisip_UNM_Kontributor.csv'],
            [
                'id' => 'IMPORT-01JARK-02',
                'journal_id' => $jikpm->id,
                'total_rows' => 20,
                'processed_rows' => 19,
                'failed_rows' => 1,
                'status' => 'completed',
                'error_log' => ['Baris 14: Format email tidak valid.'],
                'imported_by' => $primaryAdmin->id,
                'created_at' => now()->subDays(12),
            ]
        );

        // 6. Jejak Rekam Audit Append-Only (audit_logs)
        AuditLog::record([
            'id' => 'LOG-01JARK-01',
            'actor_id' => $primaryAdmin->id,
            'actor_type' => 'user',
            'effective_role' => 'System Administrator',
            'action' => 'system_settings.updated',
            'resource_type' => 'system_settings',
            'resource_id' => 'site_name',
            'request_id' => 'req_01jark_8849af',
            'reason' => 'Penyesuaian nama instansi sesuai nomenklatur Perwali BRIDA 2026.',
            'before_after' => [
                'before' => ['site_name' => 'SIRIDA Makassar'],
                'after' => ['site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar'],
            ],
            'occurred_at' => now()->subDays(5),
        ]);

        AuditLog::record([
            'id' => 'LOG-01JARK-02',
            'actor_id' => $primaryAdmin->id,
            'user_id' => $secondaryAdmin->id,
            'actor_type' => 'user',
            'effective_role' => 'System Administrator',
            'action' => 'user.sysadmin_assigned',
            'resource_type' => 'user',
            'resource_id' => $secondaryAdmin->id,
            'request_id' => 'req_01jark_1900be',
            'reason' => 'Penambahan administrator sekunder dari Diskominfo Kota Makassar.',
            'before_after' => [
                'before' => ['is_sysadmin' => false],
                'after' => ['is_sysadmin' => true],
            ],
            'occurred_at' => now()->subDays(6),
        ]);

        AuditLog::record([
            'id' => 'LOG-01JARK-03',
            'actor_type' => 'system',
            'system_actor' => 'clamav_scan_daemon',
            'effective_role' => 'System Daemon',
            'action' => 'cron_worker.scheduled_scan',
            'resource_type' => 'scan_attempts',
            'resource_id' => 'SCAN-01JARK-12',
            'request_id' => 'cron_job_daily_security_audit',
            'reason' => 'Rutinitas pemindaian harian virus/malware seluruh berkas galai PDF & naskah.',
            'before_after' => ['scanned_files' => 114, 'threats_found' => 0, 'status' => 'clean'],
            'occurred_at' => now()->subDays(6)->addHours(4),
        ]);

        AuditLog::record([
            'journal_id' => $arsip->id,
            'actor_id' => $primaryAdmin->id,
            'actor_type' => 'user',
            'effective_role' => 'System Administrator',
            'action' => 'journal.status_updated',
            'resource_type' => 'journal',
            'resource_id' => $arsip->id,
            'request_id' => 'req_01jark_4482dc',
            'reason' => 'Pengarsipan resmi Buletin Riset Litbang periode 2018-2022.',
            'before_after' => [
                'before' => ['status' => 'active'],
                'after' => ['status' => 'archived'],
            ],
            'occurred_at' => now()->subDays(7),
        ]);

        AuditLog::record([
            'journal_id' => $jtiik->id,
            'actor_id' => $primaryAdmin->id,
            'actor_type' => 'user',
            'effective_role' => 'System Administrator',
            'action' => 'user_imports.processed',
            'resource_type' => 'user_imports',
            'resource_id' => 'IMPORT-01JARK-01',
            'request_id' => 'req_01jark_9912aa',
            'reason' => 'Impor 35 mitra bestari bidang kepakaran Smart City untuk JTIIK.',
            'before_after' => ['total_rows' => 35, 'processed_rows' => 35, 'failed_rows' => 0],
            'occurred_at' => now()->subDays(8),
        ]);

        AuditLog::record([
            'actor_type' => 'system',
            'system_actor' => 'cache_purger',
            'effective_role' => 'System Service',
            'action' => 'system.cache_purged',
            'resource_type' => 'cache',
            'resource_id' => 'app_compiled_views',
            'request_id' => 'cron_weekly_cache_purge',
            'reason' => 'Pembersihan berkas view blade dan rute usang mingguan.',
            'before_after' => ['cleared_items' => 450, 'freed_space' => '84 MB'],
            'occurred_at' => now()->subDays(9),
        ]);
    }
}
