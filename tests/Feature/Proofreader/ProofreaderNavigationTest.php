<?php

declare(strict_types=1);

namespace Tests\Feature\Proofreader;

use App\Models\User;
use App\Models\UserRole;
use App\ViewModels\ProofreaderSubmissionsViewModel;
use Database\Seeders\ProofreaderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProofreaderNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $proofreader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->proofreader = User::firstOrCreate(
            ['email' => 'sitti.aminah@brida.makassar.go.id'],
            [
                'username' => 'sitti.aminah',
                'name' => 'Dra. Sitti Aminah, M.Hum.',
                'full_name' => 'Dra. Sitti Aminah, M.Hum.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        UserRole::firstOrCreate([
            'user_id' => $this->proofreader->id,
            'role_code' => 'proofreader',
        ]);
        $this->seed(ProofreaderSeeder::class);
        $this->actingAs($this->proofreader);
    }

    public function test_tamu_tidak_bisa_mengakses_portal_proofreader_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/proofreader/submissions');
        $response->assertRedirect(route('login'));
    }

    /**
     * Rute dashboard proofreader dialihkan ke daftar penugasan.
     */
    public function test_dashboard_proofreader_dialihkan_ke_submissions(): void
    {
        $response = $this->get('/proofreader');

        $response->assertRedirect('/proofreader/submissions');
    }

    /**
     * Halaman daftar penugasan proofreading dapat diakses dengan respons 200 OK.
     */
    public function test_halaman_index_penugasan_proofreader_dapat_diakses(): void
    {
        $response = $this->get('/proofreader/submissions');

        $response->assertStatus(200);
        $response->assertSee('Penugasan Proofreading');
        $response->assertSee('BRIDA MAKASSAR');
        $response->assertSee('Proofreader');

        // Memastikan sidebar navigasi ringkas dan ergonomis (2 menu)
        $response->assertSee('Penugasan Proofreading');
        $response->assertSee('Panduan Bukti Baca');
        $response->assertSee('Dra. Sitti Aminah, M.Hum.');
        $response->assertSee('SA');
    }

    /**
     * Halaman panduan bukti baca dapat diakses dengan status 200 OK.
     */
    public function test_halaman_guidelines_proofreader_dapat_diakses(): void
    {
        $response = $this->get('/proofreader/guidelines');

        $response->assertStatus(200);
        $response->assertSee('Panduan Bukti Baca');
        $response->assertSee('Standar Koreksi Galai');
        $response->assertSee('Klasifikasi Kategori Catatan Koreksi');
        $response->assertSee('Checklist Pemeriksaan Berkas Galai PDF');
        $response->assertSee('EYD V');
        $response->assertSee('Lihat Penugasan Proofread');
    }

    /**
     * Halaman ruang kerja pemeriksaan galai dapat diakses.
     */
    public function test_halaman_show_ruang_kerja_dapat_diakses(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('Pemeriksaan Galai');
        $response->assertSee('SUB-2026-00106');
    }

    /**
     * Aksi transaksional proofreader mengembalikan respons redirect yang valid.
     */
    public function test_aksi_transaksional_proofreader_mengembalikan_redirect(): void
    {
        // 1. Tambah catatan koreksi
        $responseNote = $this->post('/proofreader/add-note', [
            'submission_id' => 'SUB-2026-00106',
            'category' => 'typo',
            'page' => 4,
            'severity' => 'major',
            'description' => 'Kesalahan ketik pada judul subbab 3.1',
        ]);
        $responseNote->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00106'));
        $responseNote->assertSessionHas('status');

        // 2. Verifikasi resolusi perbaikan
        $responseVerify = $this->post('/proofreader/verify-note', [
            'submission_id' => 'SUB-2026-00106',
            'note_id' => 'NOTE-01JARK-02',
        ]);
        $responseVerify->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00106'));
        $responseVerify->assertSessionHas('status');

        // 3. Persetujuan akhir proofreading (sign-off)
        $responseComplete = $this->post('/proofreader/complete', [
            'submission_id' => 'SUB-2026-00107',
        ]);
        $responseComplete->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00107'));
        $responseComplete->assertSessionHas('status');

        // 4. Eskalasi ketidaksesuaian naskah
        $responseEscalate = $this->post('/proofreader/escalate', [
            'submission_id' => 'SUB-2026-00106',
            'reason' => 'Terdapat bagian teks yang terpotong dari versi copyediting asli.',
        ]);
        $responseEscalate->assertRedirect(route('proofreader.submissions.show', 'SUB-2026-00106'));
        $responseEscalate->assertSessionHas('status');
    }

    /**
     * Keterangan angka badge pada navigasi sejalan dengan total penugasan yang ada.
     */
    public function test_keterangan_angka_navbar_sejalan_dengan_jumlah_penugasan_yang_ada(): void
    {
        $response = $this->get('/proofreader/submissions');
        $response->assertStatus(200);

        $badgeCount = ProofreaderSubmissionsViewModel::getSidebarBadgeCount();
        $this->assertGreaterThan(0, $badgeCount);

        // Angka pada navbar harus tercetak di HTML
        $response->assertSee((string) $badgeCount);
    }
}
