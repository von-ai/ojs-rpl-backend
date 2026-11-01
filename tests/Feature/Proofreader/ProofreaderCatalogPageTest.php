<?php

declare(strict_types=1);

namespace Tests\Feature\Proofreader;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProofreaderCatalogPageTest extends TestCase
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
        $this->actingAs($this->proofreader);
    }

    public function test_tamu_tidak_bisa_mengakses_portal_proofreader_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/proofreader/submissions');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman index memuat 4 ringkasan metrik statistik proofreading.
     */
    public function test_halaman_index_memuat_ringkasan_metrik_statistik(): void
    {
        $response = $this->get('/proofreader/submissions');

        $response->assertStatus(200);
        $response->assertSee('Total Penugasan Proofread');
        $response->assertSee('Perlu Diperiksa');
        $response->assertSee('Menunggu Revisi Layout');
        $response->assertSee('Pemeriksaan Selesai');
        $response->assertSee('Dra. Sitti Aminah, M.Hum.');
    }

    /**
     * Filter tab status berfungsi menyaring daftar naskah proofreading.
     */
    public function test_filter_tab_status_menyaring_daftar_naskah(): void
    {
        // 1. Tab Perlu Diperiksa (pending)
        $responsePending = $this->get('/proofreader/submissions?tab=pending');
        $responsePending->assertStatus(200);
        $responsePending->assertSee('SUB-2026-00107');
        $responsePending->assertSee('SUB-2026-00106');
        $responsePending->assertDontSee('SUB-2026-00105');

        // 2. Tab Menunggu Revisi Layout (waiting_layout)
        $responseWaiting = $this->get('/proofreader/submissions?tab=waiting_layout');
        $responseWaiting->assertStatus(200);
        $responseWaiting->assertSee('SUB-2026-00105');
        $responseWaiting->assertSee('SUB-2026-00104');
        $responseWaiting->assertDontSee('SUB-2026-00107');

        // 3. Tab Pemeriksaan Selesai (completed)
        $responseCompleted = $this->get('/proofreader/submissions?tab=completed');
        $responseCompleted->assertStatus(200);
        $responseCompleted->assertSee('SUB-2026-00103');
        $responseCompleted->assertSee('SUB-2026-00102');
        $responseCompleted->assertDontSee('SUB-2026-00107');
    }

    /**
     * Pencarian naskah berdasarkan kata kunci judul atau penulis berfungsi.
     */
    public function test_pencarian_naskah_berfungsi_sesuai_kata_kunci(): void
    {
        // Pencarian dengan judul spesifik
        $responseSearch = $this->get('/proofreader/submissions?q=Sentinel-1A');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('SUB-2026-00107');
        $responseSearch->assertSee('Sentinel-1A');
        $responseSearch->assertDontSee('SUB-2026-00106');

        // Pencarian tanpa hasil menampilkan empty state
        $responseEmpty = $this->get('/proofreader/submissions?q=KataKunciTidakAdaSamaSekali123');
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertSee('Tidak ada penugasan proofreading');
        $responseEmpty->assertSee('Reset Filter');
    }

    /**
     * Tautan periksa galai mengarahkan ke ruang kerja naskah yang benar.
     */
    public function test_tautan_periksa_galai_tersedia_pada_setiap_baris(): void
    {
        $response = $this->get('/proofreader/submissions');

        $response->assertStatus(200);
        $response->assertSee(route('proofreader.submissions.show', 'SUB-2026-00107'));
        $response->assertSee(route('proofreader.submissions.show', 'SUB-2026-00106'));
        $response->assertSee('Periksa Galai');
    }
}
