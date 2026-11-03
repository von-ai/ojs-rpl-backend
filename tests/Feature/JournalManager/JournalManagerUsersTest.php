<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use App\Models\User;
use Database\Seeders\JournalManagerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalManagerUsersTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(JournalManagerSeeder::class);
        $this->manager = User::where('email', 'yusuf.brida@makassar.go.id')->firstOrFail();
        $this->actingAs($this->manager);
    }

    public function test_tamu_tidak_bisa_mengakses_manajemen_pengguna_journal_manager_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/journalmanager/users');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman manajemen pengguna menampilkan 4 kartu metrik, tabel anggota, dan modal penetapan peran.
     */
    public function test_halaman_manajemen_pengguna_menampilkan_metrik_dan_daftar(): void
    {
        $response = $this->get('/journalmanager/users');

        $response->assertStatus(200);
        $response->assertSee('Pengguna');
        $response->assertSee('Tim Redaksi');
        $response->assertSee('Tetapkan Peran Pengguna');

        // 4 Kartu Metrik
        $response->assertSee('Total Pengguna Jurnal');
        $response->assertSee('Anggota');
        $response->assertSee('Dewan Redaksi (Editorial)');
        $response->assertSee('Direktori Penelaah');
        $response->assertSee('Penulis & Pembaca');

        // Pengguna dan Peran
        $response->assertSee('Journal Manager');

        $managerResponse = $this->get('/journalmanager/users?q=Yusuf');
        $managerResponse->assertSee('Andi Muhammad Yusuf, M.Si.');
        $managerResponse->assertSee('Journal Manager');
        $managerResponse->assertSee('Last Manager');

        $searchResponse = $this->get('/journalmanager/users?q=Nasaruddin');
        $searchResponse->assertSee('Prof. Dr. Ir. H. Nasaruddin, M.T.');
        $searchResponse->assertSee('Editor in Chief');
        $searchResponse->assertSee('Last EIC');

        // Tombol Aksi
        $response->assertSee('Reset Sandi');
        $response->assertSee('Ubah Peran');
    }

    /**
     * Filter tab peran pengguna berfungsi memilah dewan redaksi, tim produksi, reviewer, dan author.
     */
    public function test_filter_peran_pengguna(): void
    {
        // Tab All: menampilkan semua pengguna
        $responseAll = $this->get('/journalmanager/users?role=all');
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Andi Muhammad Yusuf, M.Si.');
        $responseAll->assertSee('Prof. Dr. Ir. Muhammad Basri, M.T.');

        // Tab Editorial: dewan editor
        $responseEditorial = $this->get('/journalmanager/users?role=editorial');
        $responseEditorial->assertStatus(200);
        $responseEditorial->assertSee('Prof. Dr. Ir. H. Nasaruddin, M.T.');
        $responseEditorial->assertSee('Prof. Hamka, Ph.D.');
        $responseEditorial->assertDontSee('Prof. Dr. Ir. Muhammad Basri, M.T.');

        // Tab Reviewer: direktori penelaah
        $responseReviewer = $this->get('/journalmanager/users?role=reviewer');
        $responseReviewer->assertStatus(200);
        $responseReviewer->assertSee('Dr. Ir. Budi Hartono, M.Eng.');
        $responseReviewer->assertSee('Dr. Nurfadillah Bahar, M.Kes.');
        $responseReviewer->assertDontSee('Prof. Hamka, Ph.D.');

        // Tab Production: tim produksi tata letak & copyedit
        $responseProduction = $this->get('/journalmanager/users?role=production');
        $responseProduction->assertStatus(200);
        $responseProduction->assertSee('Rahmat Hidayat, S.Kom.');
        $responseProduction->assertSee('Dra. Sitti Aminah, M.Hum.');
        $responseProduction->assertDontSee('Prof. Dr. Ir. H. Nasaruddin, M.T.');
    }

    /**
     * Pencarian pengguna berdasarkan nama, afiliasi, atau kata kunci kepakaran penelaah.
     */
    public function test_pencarian_pengguna_dan_kepakaran(): void
    {
        // Cari nama EIC
        $resName = $this->get('/journalmanager/users?q=Nasaruddin');
        $resName->assertStatus(200);
        $resName->assertSee('Prof. Dr. Ir. H. Nasaruddin, M.T.');
        $resName->assertDontSee('Rahmat Hidayat, S.Kom.');

        // Cari kepakaran reviewer (Reviewer Directory Search)
        $resExpertise = $this->get('/journalmanager/users?q=Geoteknik');
        $resExpertise->assertStatus(200);
        $resExpertise->assertSee('Dr. Ir. Budi Hartono, M.Eng.');
        $resExpertise->assertSee('Geoteknik Pesisir');
        $resExpertise->assertDontSee('Rahmat Hidayat, S.Kom.');

        // Pencarian tidak ditemukan
        $resEmpty = $this->get('/journalmanager/users?q=AkunTidakAdaPalsu');
        $resEmpty->assertStatus(200);
        $resEmpty->assertSee('Tidak ada pengguna ditemukan');
        $resEmpty->assertSee('Reset Filter');
    }

    /**
     * Aksi transaksional penetapan peran dan pemicuan reset kata sandi email.
     */
    public function test_aksi_transaksional_manajemen_pengguna(): void
    {
        // 1. Tetapkan peran jurnal
        $resAssign = $this->post('/journalmanager/users/assign-role', [
            'role_code' => 'Section Editor',
        ]);
        $resAssign->assertRedirect('/journalmanager/users');
        $resAssign->assertSessionHas('status', "Peran 'Section Editor' berhasil ditetapkan kepada pengguna.");

        // 2. Memicu reset password email
        $resReset = $this->post('/journalmanager/users/reset-password', [
            'email' => 'budi.hartono@eng.unhas.ac.id',
        ]);
        $resReset->assertRedirect('/journalmanager/users');
        $resReset->assertSessionHas('status', 'Tautan reset kata sandi berhasil dikirimkan ke alamat email budi.hartono@eng.unhas.ac.id.');
    }
}
