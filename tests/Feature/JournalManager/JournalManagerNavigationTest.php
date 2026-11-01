<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JournalManagerNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\JournalManagerSeeder::class);
        $this->manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->manager->update(['name' => 'Andi M. Yusuf, M.Si.']);
        $this->actingAs($this->manager);
    }

    public function test_tamu_tidak_bisa_mengakses_portal_journal_manager_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/journalmanager/issues');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman katalog edisi & terbitan dapat diakses dengan respons 200 OK.
     */
    public function test_halaman_index_edisi_terbitan_dapat_diakses(): void
    {
        $response = $this->get('/journalmanager/issues');

        $response->assertStatus(200);
        $response->assertSee('Edisi');
        $response->assertSee('Terbitan Berkala');
        $response->assertSee('BRIDA MAKASSAR');
        $response->assertSee('Journal Manager');

        // Memastikan sidebar ergonomis memuat 4 menu pokok
        $response->assertSee('Edisi');
        $response->assertSee('Pengguna');
        $response->assertSee('Redaksi');
        $response->assertSee('Bidang Naskah');
        $response->assertSee('Pengaturan Jurnal');
        $response->assertSee('Andi M. Yusuf, M.Si.');
    }

    /**
     * Halaman ruang kerja penyusunan edisi terbitan dapat diakses.
     */
    public function test_halaman_show_edisi_terbitan_dapat_diakses(): void
    {
        $response = $this->get('/journalmanager/issues/ISSUE-01JARK960A');

        $response->assertStatus(200);
        $response->assertSee('Kembali ke Katalog Terbitan');
        $response->assertSee('ISSUE-01JARK960A');
    }

    /**
     * Halaman manajemen pengguna & tim redaksi dapat diakses.
     */
    public function test_halaman_users_dapat_diakses(): void
    {
        $response = $this->get('/journalmanager/users');

        $response->assertStatus(200);
        $response->assertSee('Pengguna');
        $response->assertSee('Tim Redaksi');
        $response->assertSee('Direktori Penelaah');
    }

    /**
     * Halaman pengelolaan bidang naskah (sections) dapat diakses.
     */
    public function test_halaman_sections_dapat_diakses(): void
    {
        $response = $this->get('/journalmanager/sections');

        $response->assertStatus(200);
        $response->assertSee('Bidang Naskah');
        $response->assertSee('Seksi Penelitian');
    }

    /**
     * Halaman konfigurasi identitas & pengaturan jurnal dapat diakses.
     */
    public function test_halaman_settings_dapat_diakses(): void
    {
        $response = $this->get('/journalmanager/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Jurnal');
        $response->assertSee('Kebijakan Editorial');
        $response->assertSee('Simpan Perubahan Pengaturan');
    }

    /**
     * Aksi transaksional dasar journal manager menghasilkan redirect dengan status.
     */
    public function test_aksi_transaksional_journal_manager_mengembalikan_redirect(): void
    {
        // 1. Simpan edisi baru
        $resStoreIssue = $this->post('/journalmanager/issues', [
            'volume' => '7',
            'number' => '1',
            'year' => '2027',
        ]);
        $resStoreIssue->assertRedirect('/journalmanager/issues');
        $resStoreIssue->assertSessionHas('status');

        // 2. Tambah artikel ke edisi
        $resAddArticle = $this->post('/journalmanager/issues/ISSUE-01JARK960A/add-article', [
            'article_title' => 'Analisis Spasial RTH Makassar',
        ]);
        $resAddArticle->assertRedirect('/journalmanager/issues/ISSUE-01JARK960A');
        $resAddArticle->assertSessionHas('status');

        // 3. Publikasikan edisi
        $resPublish = $this->post('/journalmanager/issues/ISSUE-01JARK960A/publish');
        $resPublish->assertRedirect('/journalmanager/issues/ISSUE-01JARK960A');
        $resPublish->assertSessionHas('status');

        // 4. Tetapkan current issue
        $resCurrent = $this->post('/journalmanager/issues/ISSUE-01JARK960A/set-current');
        $resCurrent->assertRedirect('/journalmanager/issues');
        $resCurrent->assertSessionHas('status');

        // 5. Tetapkan peran pengguna
        $resAssignRole = $this->post('/journalmanager/users/assign-role', [
            'role_code' => 'Section Editor',
        ]);
        $resAssignRole->assertRedirect('/journalmanager/users');
        $resAssignRole->assertSessionHas('status');

        // 6. Reset password pengguna
        $resReset = $this->post('/journalmanager/users/reset-password', [
            'email' => 'budi.hartono@eng.unhas.ac.id',
        ]);
        $resReset->assertRedirect('/journalmanager/users');
        $resReset->assertSessionHas('status');

        // 7. Simpan seksi baru
        $resStoreSection = $this->post('/journalmanager/sections', [
            'name' => 'Inovasi Tata Kelola Perkotaan',
        ]);
        $resStoreSection->assertRedirect('/journalmanager/sections');
        $resStoreSection->assertSessionHas('status');

        // 8. Update pengaturan jurnal
        $resUpdateSettings = $this->post('/journalmanager/settings');
        $resUpdateSettings->assertRedirect('/journalmanager/settings');
        $resUpdateSettings->assertSessionHas('status');
    }
}
