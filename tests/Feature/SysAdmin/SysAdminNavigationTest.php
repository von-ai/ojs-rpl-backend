<?php

declare(strict_types=1);

namespace Tests\Feature\SysAdmin;

use App\Models\Journal;
use App\Models\User;
use Database\Seeders\SysAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SysAdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SysAdminSeeder::class);

        $admin = User::where('email', 'fahri.azis@makassar.go.id')->firstOrFail();
        $this->actingAs($admin);
    }

    public function test_tamu_tidak_bisa_mengakses_portal_sysadmin_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/sysadmin/journals');
        $response->assertRedirect(route('login'));
    }

    /**
     * Rute dashboard sysadmin dialihkan ke manajemen jurnal.
     */
    public function test_dashboard_sysadmin_dialihkan_ke_journals_index(): void
    {
        $response = $this->get('/sysadmin');

        $response->assertRedirect('/sysadmin/journals');
    }

    /**
     * Halaman index jurnal ilmiah dapat diakses dengan respons 200 OK dan navigasi lengkap.
     */
    public function test_halaman_index_jurnal_dapat_diakses(): void
    {
        $response = $this->get('/sysadmin/journals');

        $response->assertStatus(200);
        $response->assertSee('BRIDA MAKASSAR');
        $response->assertSee('System Admin');
        $response->assertSee('Fahri Azis, S.T.');
        $response->assertSee('FA');

        // Memastikan sidebar navigasi memuat 4 menu tata kelola sistem
        $response->assertSee('Jurnal Ilmiah');
        $response->assertSee('Pengguna');
        $response->assertSee('Akses');
        $response->assertSee('Pengaturan Situs');
        $response->assertSee('Jejak Audit');
    }

    /**
     * Halaman direktori pengguna global dapat diakses dengan status 200 OK.
     */
    public function test_halaman_users_dapat_diakses(): void
    {
        $response = $this->get('/sysadmin/users');

        $response->assertStatus(200);
        $response->assertSee('Direktori Pengguna');
        $response->assertSee('Hak Akses Global');
    }

    /**
     * Halaman pengaturan platform dan kesehatan server dapat diakses.
     */
    public function test_halaman_settings_dapat_diakses(): void
    {
        $response = $this->get('/sysadmin/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Platform');
        $response->assertSee('Kesehatan Server');
    }

    /**
     * Halaman jejak audit sistem dapat diakses.
     */
    public function test_halaman_audit_logs_dapat_diakses(): void
    {
        $response = $this->get('/sysadmin/audit-logs');

        $response->assertStatus(200);
        $response->assertSee('Jejak Audit');
        $response->assertSee('Pemantauan Sistem');
    }

    /**
     * Seluruh aksi transaksional sysadmin menghasilkan respons redirect yang valid.
     */
    public function test_aksi_transaksional_sysadmin_mengembalikan_redirect(): void
    {
        // 1. Simpan jurnal baru
        $resStoreJournal = $this->post('/sysadmin/journals', [
            'name' => 'Jurnal Kebijakan Publik Makassar',
            'slug' => 'jkpm',
        ]);
        $resStoreJournal->assertRedirect(route('sysadmin.journals.index'));
        $resStoreJournal->assertSessionHas('status');

        // 2. Perbarui status publikasi jurnal
        $resUpdateStatus = $this->post('/sysadmin/journals/'.Journal::where('slug', 'jtiik-makassar')->firstOrFail()->id.'/status', [
            'status' => 'archived',
        ]);
        $resUpdateStatus->assertRedirect(route('sysadmin.journals.index'));
        $resUpdateStatus->assertSessionHas('status');

        // 3. Berikan hak akses SysAdmin
        $resAssign = $this->post('/sysadmin/users/assign-sysadmin', [
            'user_id' => User::where('email', 'rita.irmawaty@unhas.ac.id')->firstOrFail()->id,
        ]);
        $resAssign->assertRedirect(route('sysadmin.users.index'));
        $resAssign->assertSessionHas('status');
        $this->assertTrue(User::where('email', 'rita.irmawaty@unhas.ac.id')->firstOrFail()->isSysAdmin());

        // 4. Cabut hak akses SysAdmin
        $resRevoke = $this->post('/sysadmin/users/revoke-sysadmin', [
            'user_id' => User::where('email', 'rita.irmawaty@unhas.ac.id')->firstOrFail()->id,
        ]);
        $resRevoke->assertRedirect(route('sysadmin.users.index'));
        $resRevoke->assertSessionHas('status');

        // 5. Impor pengguna CSV
        $resImport = $this->post('/sysadmin/users/import', [
            'journal_id' => 'JOURNAL-01JARK-01',
        ]);
        $resImport->assertSessionHasErrors(['file', 'journal_id']);
        $resImport->assertSessionMissing('status');

        // 6. Perbarui konfigurasi platform
        $resSettings = $this->post('/sysadmin/settings', [
            'site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar',
        ]);
        $resSettings->assertRedirect(route('sysadmin.settings.index'));
        $resSettings->assertSessionHas('status');

        // 7. Bersihkan cache sistem
        $resCache = $this->post('/sysadmin/settings/clear-cache');
        $resCache->assertRedirect(route('sysadmin.settings.index'));
        $resCache->assertSessionHas('status');
    }
}
