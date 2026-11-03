<?php

declare(strict_types=1);

namespace Tests\Feature\SysAdmin;

use App\Mail\SysAdmin\JournalStatusChangedMail;
use App\Mail\SysAdmin\SysAdminRoleAssignedMail;
use App\Mail\SysAdmin\SysAdminRoleRevokedMail;
use App\Mail\SysAdmin\UserImportCompletedMail;
use App\Models\AuditLog;
use App\Models\Journal;
use App\Models\Section;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SysAdmin\SysAdminService;
use App\ViewModels\SysAdminJournalsViewModel;
use App\ViewModels\SysAdminSettingsViewModel;
use App\ViewModels\SysAdminUsersViewModel;
use Database\Seeders\SysAdminSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SysAdminEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $secondAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan administrator utama
        $this->admin = User::create([
            'name' => 'Fahri Azis, S.T.',
            'email' => 'fahri.azis@makassar.go.id',
            'username' => 'fahri.azis',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
            'version' => 1,
        ]);

        UserRole::create([
            'user_id' => $this->admin->id,
            'journal_id' => null,
            'role_code' => 'sysadmin',
        ]);

        // Siapkan administrator sekunder agar tidak memicu invariant guard 'last admin'
        $this->secondAdmin = User::create([
            'name' => 'Muh. Ilham, S.Kom., M.T.',
            'email' => 'ilham.kominfo@makassar.go.id',
            'username' => 'ilham.kominfo',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
            'version' => 1,
        ]);

        UserRole::create([
            'user_id' => $this->secondAdmin->id,
            'journal_id' => null,
            'role_code' => 'sysadmin',
        ]);
    }

    /**
     * 1. Siklus Hidup Instansi Jurnal Ilmiah (Multi-Tenancy).
     */
    public function test_siklus_hidup_instansi_jurnal_multi_tenancy(): void
    {
        Mail::fake();

        // A. Pendaftaran instansi jurnal baru
        $response = $this->actingAs($this->admin)->post('/sysadmin/journals', [
            'name' => 'Jurnal Sains Terapan Makassar (JSTM)',
            'slug' => 'jstm-makassar',
            'primary_contact' => 'Dr. H. Rusdianto, M.Si.',
            'issn' => '2810-1122',
            'eissn' => '2810-3344',
            'description' => 'Publikasi sains terapan dan perekayasaan teknologi.',
        ]);

        $response->assertRedirect(route('sysadmin.journals.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('journals', [
            'slug' => 'jstm-makassar',
            'name' => 'Jurnal Sains Terapan Makassar (JSTM)',
            'status' => 'active',
        ]);

        $journal = Journal::where('slug', 'jstm-makassar')->firstOrFail();

        // Pastikan default section otomatis diinisialisasi
        $this->assertDatabaseHas('sections', [
            'journal_id' => $journal->id,
            'name' => 'Artikel Riset',
            'slug' => 'artikel-riset',
        ]);

        // Pastikan audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'journal.created',
            'resource_type' => 'journal',
            'resource_id' => $journal->id,
            'actor_id' => $this->admin->id,
        ]);

        // B. Invariant guard: Mencegah duplikasi slug jurnal
        $sysAdminService = app(SysAdminService::class);
        $this->expectException(DomainException::class);
        $sysAdminService->createJournal([
            'name' => 'Duplikat JSTM',
            'slug' => 'jstm-makassar',
            'primary_contact' => 'Kontak Uji',
        ], $this->admin);
    }

    /**
     * Pembaruan status publikasi jurnal dan pengiriman surel.
     */
    public function test_pembaruan_status_publikasi_jurnal(): void
    {
        Mail::fake();

        $journal = Journal::create([
            'name' => 'Jurnal Riset Lingkungan',
            'slug' => 'jurnal-lingkungan',
            'publisher' => 'Prof. Saleh Pallu',
            'editorial_email' => 'editor.lingkungan@makassar.go.id',
            'status' => 'active',
            'version' => 1,
        ]);

        // Arsipkan jurnal
        $response = $this->actingAs($this->admin)->post("/sysadmin/journals/{$journal->id}/status", [
            'status' => 'archived',
        ]);

        $response->assertRedirect(route('sysadmin.journals.index'));
        $this->assertEquals('archived', $journal->fresh()->status);

        // Pastikan audit log mutasi status tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'journal.status_updated',
            'resource_type' => 'journal',
            'resource_id' => $journal->id,
        ]);

        Mail::assertSent(JournalStatusChangedMail::class, function ($mail) use ($journal) {
            return $mail->journal->id === $journal->id && $mail->newStatus === 'archived';
        });
    }

    /**
     * 2. Manajemen Pengguna & Hak Akses Istimewa (SysAdmin Role).
     */
    public function test_pemberian_dan_pencabutan_hak_akses_sysadmin(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Dr. Andi Nurul Hidayah, M.Si.',
            'email' => 'andi.nurul@unm.ac.id',
            'username' => 'andi.nurul',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
            'version' => 1,
        ]);

        $this->assertFalse($user->isSysAdmin());

        // A. Berikan hak akses SysAdmin
        $resAssign = $this->actingAs($this->admin)->post('/sysadmin/users/assign-sysadmin', [
            'user_id' => $user->id,
        ]);

        $resAssign->assertRedirect(route('sysadmin.users.index'));
        $this->assertTrue($user->fresh()->isSysAdmin());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.sysadmin_assigned',
            'resource_type' => 'user',
            'resource_id' => $user->id,
        ]);

        Mail::assertSent(SysAdminRoleAssignedMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });

        // B. Cabut hak akses SysAdmin
        $resRevoke = $this->actingAs($this->admin)->post('/sysadmin/users/revoke-sysadmin', [
            'user_id' => $user->id,
        ]);

        $resRevoke->assertRedirect(route('sysadmin.users.index'));
        $this->assertFalse($user->fresh()->isSysAdmin());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.sysadmin_revoked',
            'resource_type' => 'user',
            'resource_id' => $user->id,
        ]);

        Mail::assertSent(SysAdminRoleRevokedMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });
    }

    /**
     * Invariant Guard: Sistem menolak mencabut sysadmin terakhir.
     */
    public function test_mencegah_pencabutan_sysadmin_terakhir(): void
    {
        // Hapus admin sekunder terlebih dahulu
        UserRole::where('user_id', $this->secondAdmin->id)->where('role_code', 'sysadmin')->delete();

        $this->assertEquals(1, UserRole::where('role_code', 'sysadmin')->count());

        $sysAdminService = app(SysAdminService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Tidak dapat mencabut peran System Administrator terakhir pada sistem.');

        $sysAdminService->revokeSysAdmin($this->admin->id, $this->admin);
    }

    /**
     * 3. Pengimporan Massal Pengguna via Berkas CSV.
     */
    public function test_pengimporan_massal_pengguna_via_csv(): void
    {
        Mail::fake();

        $journal = Journal::create([
            'name' => 'JTIIK Makassar',
            'slug' => 'jtiik-makassar',
            'status' => 'active',
            'version' => 1,
        ]);

        // Buat berkas CSV tiruan
        $csvContent = "name,email,affiliation,role\n";
        $csvContent .= "Mitra Ahli Satu,ahli1@makassar.go.id,Diskominfo Makassar,reviewer\n";
        $csvContent .= "Mitra Ahli Dua,ahli2@unhas.ac.id,Fakultas Teknik Unhas,reviewer\n";
        $csvContent .= "Email Rusak,format-salah,Badan Riset,author\n";

        $file = UploadedFile::fake()->createWithContent('reviewer_import_batch.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post('/sysadmin/users/import', [
            'journal_id' => $journal->id,
            'file' => $file,
            'send_invitation_email' => '1',
        ]);

        $response->assertRedirect(route('sysadmin.users.index'));

        // Pastikan tabel user_imports mencatat hasil impor
        $this->assertDatabaseHas('user_imports', [
            'file_name' => 'reviewer_import_batch.csv',
            'total_rows' => 3,
            'processed_rows' => 2,
            'failed_rows' => 1,
            'status' => 'completed',
        ]);

        // Pastikan pengguna baru berhasil tersimpan di DB
        $this->assertDatabaseHas('users', ['email' => 'ahli1@makassar.go.id']);
        $this->assertDatabaseHas('users', ['email' => 'ahli2@unhas.ac.id']);
        $this->assertDatabaseMissing('users', ['email' => 'format-salah']);

        $user1 = User::where('email', 'ahli1@makassar.go.id')->firstOrFail();
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user1->id,
            'journal_id' => $journal->id,
            'role_code' => 'reviewer',
        ]);

        // Pastikan log audit tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_imports.processed',
            'resource_type' => 'user_imports',
            'actor_id' => $this->admin->id,
        ]);

        Mail::assertSent(UserImportCompletedMail::class);
    }

    /**
     * 4. Pengaturan Global Platform & Pembersihan Cache.
     */
    public function test_pembaruan_pengaturan_platform_dan_pembersihan_cache(): void
    {
        $response = $this->actingAs($this->admin)->post('/sysadmin/settings', [
            'site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar Terpadu',
            'site_description' => 'Repositori dan publikasi ilmiah terpadu BRIDA 2026.',
            'maintenance_mode' => '1',
            'maintenance_message' => 'Sistem sedang dalam proses pemeliharaan darurat.',
            'max_upload_size_mb' => '50',
            'allowed_file_extensions' => 'pdf, docx, csv, xlsx',
            'session_lifetime_minutes' => '180',
        ]);

        $response->assertRedirect(route('sysadmin.settings.index'));

        $this->assertEquals('SIRIDA — Portal Ilmiah BRIDA Kota Makassar Terpadu', SystemSetting::getValue('site_name'));
        $this->assertTrue((bool) SystemSetting::getValue('maintenance_mode'));
        $this->assertEquals(50, SystemSetting::getValue('max_upload_size_mb'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system_settings.updated',
            'resource_type' => 'system_settings',
            'actor_id' => $this->admin->id,
        ]);

        // Pembersihan cache sistem
        $resCache = $this->actingAs($this->admin)->post('/sysadmin/settings/clear-cache');
        $resCache->assertRedirect(route('sysadmin.settings.index'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'system.cache_purged',
            'resource_type' => 'cache',
            'actor_id' => $this->admin->id,
        ]);
    }

    /**
     * 5. Jejak Audit Append-Only dan Filter Aktor.
     */
    public function test_jejak_audit_append_only_dan_filter_aktor(): void
    {
        // Catat log aktor user
        AuditLog::record([
            'actor_id' => $this->admin->id,
            'actor_type' => 'user',
            'action' => 'user.sysadmin_assigned',
            'reason' => 'Pengujian jejak audit user',
        ]);

        // Catat log aktor system
        AuditLog::record([
            'actor_type' => 'system',
            'system_actor' => 'malware_scanner',
            'action' => 'cron_worker.scheduled_scan',
            'reason' => 'Pengujian jejak audit sistem',
        ]);

        // Akses halaman audit logs dengan filter aktor
        $resUser = $this->actingAs($this->admin)->get('/sysadmin/audit-logs?actor_type=user');
        $resUser->assertStatus(200);
        $resUser->assertSee('Pengujian jejak audit user');
        $resUser->assertDontSee('malware_scanner');

        $resSys = $this->actingAs($this->admin)->get('/sysadmin/audit-logs?actor_type=system');
        $resSys->assertStatus(200);
        $resSys->assertSee('malware_scanner');
        $resSys->assertDontSee('Pengujian jejak audit user');
    }

    /**
     * 6. SysAdminSeeder berhasil mengeksekusi populasi data realistis.
     */
    public function test_sysadmin_seeder_populates_data(): void
    {
        $this->seed(SysAdminSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'fahri.azis@makassar.go.id']);
        $this->assertDatabaseHas('journals', ['slug' => 'jtiik-makassar']);
        $this->assertDatabaseHas('user_imports', ['file_name' => 'Reviewer_Kepakaran_SmartCity_2026.csv']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'system_settings.updated']);
    }

    /**
     * 7. Sinkronisasi Dinamis Angka Badge Sidebar SysAdmin dengan Database.
     */
    public function test_sysadmin_sidebar_badge_counts_are_dynamically_synchronized_with_database(): void
    {
        // Kondisi awal (setUp membuat 2 user admin, 0 jurnal di database)
        $this->assertSame(2, SysAdminUsersViewModel::getSidebarBadgeCount());
        $this->assertSame(0, SysAdminJournalsViewModel::getSidebarBadgeCount());

        // Buat jurnal baru
        Journal::create([
            'name' => 'Jurnal Teknologi Informasi',
            'slug' => 'jtiik',
            'status' => 'active',
            'version' => 1,
        ]);
        Journal::create([
            'name' => 'Jurnal Inovasi Daerah',
            'slug' => 'sirida-inovasi',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->assertSame(2, SysAdminJournalsViewModel::getSidebarBadgeCount());

        // Buat user tambahan
        User::factory()->count(3)->create();
        $this->assertSame(5, SysAdminUsersViewModel::getSidebarBadgeCount());
    }

    /**
     * 8. Komponen Sidebar SysAdmin me-render badge dinamis secara tepat.
     */
    public function test_sysadmin_sidebar_component_renders_cleanly_without_number_badges(): void
    {
        // 1. Rendering dengan nilai custom eksplisit bersih tanpa badge angka
        $renderedCustom = Blade::render('<x-sysadmin.sidebar :journals-count="8" :users-count="88" />');
        $this->assertStringNotContainsString('sidebar-badge-full', $renderedCustom);
        $this->assertStringContainsString('Jurnal Ilmiah', $renderedCustom);
        $this->assertStringContainsString('Pengguna &amp; Akses', $renderedCustom);

        // 2. Rendering tanpa props juga bersih tanpa badge angka
        Journal::create([
            'name' => 'Jurnal Uji Render',
            'slug' => 'jurnal-uji-render',
            'status' => 'active',
            'version' => 1,
        ]);

        $renderedAuto = Blade::render('<x-sysadmin.sidebar />');
        $this->assertStringNotContainsString('sidebar-badge-full', $renderedAuto);
        $this->assertStringContainsString('Jurnal Ilmiah', $renderedAuto);
        $this->assertStringContainsString('Pengguna &amp; Akses', $renderedAuto);
    }

    /**
     * 9. SysAdminSettingsViewModel menghitung diagnostik kesehatan server dan pengaturan secara valid.
     */
    public function test_sysadmin_settings_view_model_computes_dynamic_server_health_metrics(): void
    {
        $vm = new SysAdminSettingsViewModel;

        $health = $vm->serverHealth();
        $this->assertArrayHasKey('php_version', $health);
        $this->assertArrayHasKey('database_driver', $health);
        $this->assertArrayHasKey('disk_usage_formatted', $health);
        $this->assertArrayHasKey('disk_percentage', $health);
        $this->assertArrayHasKey('disk_free_formatted', $health);
        $this->assertArrayHasKey('timezone', $health);
        $this->assertStringContainsString('PHP', $health['php_version']);

        $metrics = $vm->summaryMetrics();
        $this->assertCount(4, $metrics);
        $this->assertStringContainsString('Terpakai', $metrics[2]['value']);
    }
}
