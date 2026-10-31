<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected Journal $journal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->journal = Journal::create([
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar',
            'slug' => 'sirida-makassar',
            'initials' => 'SIRIDA',
            'issn' => '2746-8194',
            'eissn' => '2746-8208',
            'status' => 'active',
            'version' => 1,
        ]);
    }

    /**
     * Pengguna dengan lebih dari satu peran dapat beralih peran dan diarahkan ke portal tujuan.
     */
    public function test_pengguna_multi_role_dapat_beralih_peran_antar_portal(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Prof. Dr. Ir. H. Nasaruddin, M.T.',
            'email' => 'nasaruddin.eic@unhas.ac.id',
        ]);

        foreach (['editor_in_chief', 'section_editor', 'reviewer', 'author'] as $roleCode) {
            UserRole::create([
                'user_id' => $user->id,
                'journal_id' => $this->journal->id,
                'role_code' => $roleCode,
            ]);
        }

        $this->assertSame(['editor_in_chief', 'section_editor', 'reviewer', 'author'], $user->availableRoleCodes());

        // 1. Beralih ke peran Editor Bagian (section_editor)
        $resSectionEditor = $this->actingAs($user)->post(route('role.switch'), [
            'role_code' => 'section_editor',
        ]);

        $resSectionEditor->assertRedirect(route('section-editor.submissions.index'));
        $resSectionEditor->assertSessionHas('active_role', 'section_editor');
        $resSectionEditor->assertSessionHas('role_switched_toast', 'Peran aktif berhasil dialihkan ke Editor Bagian.');

        // 2. Beralih ke peran Mitra Bestari (reviewer)
        $resReviewer = $this->actingAs($user)->post(route('role.switch'), [
            'role_code' => 'reviewer',
        ]);

        $resReviewer->assertRedirect(route('reviewer.assignments.index'));
        $resReviewer->assertSessionHas('active_role', 'reviewer');
        $resReviewer->assertSessionHas('role_switched_toast', 'Peran aktif berhasil dialihkan ke Mitra Bestari.');

        // 3. Beralih ke peran Penulis (author)
        $resAuthor = $this->actingAs($user)->post(route('role.switch'), [
            'role_code' => 'author',
        ]);

        $resAuthor->assertRedirect(route('author.submissions.index'));
        $resAuthor->assertSessionHas('active_role', 'author');

        // 4. Beralih kembali ke peran Pemimpin Redaksi (editor_in_chief)
        $resEic = $this->actingAs($user)->post(route('role.switch'), [
            'role_code' => 'editor_in_chief',
        ]);

        $resEic->assertRedirect(route('eic.dashboard'));
        $resEic->assertSessionHas('active_role', 'editor_in_chief');
    }

    /**
     * Pengguna ditolak (403 Forbidden) apabila mencoba beralih ke peran yang tidak dimilikinya.
     */
    public function test_pengguna_tidak_dapat_beralih_ke_peran_yang_tidak_dimiliki(): void
    {
        $user = User::factory()->create();

        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'author',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_role' => 'author'])
            ->post(route('role.switch'), [
                'role_code' => 'sysadmin',
            ]);

        $response->assertStatus(403);
        $this->assertSame('author', session('active_role'));
    }

    /**
     * Rute /dashboard mengarahkan pengguna sesuai peran aktif pada session atau peran utama.
     */
    public function test_rute_dashboard_mengarahkan_ke_portal_peran_aktif(): void
    {
        $user = User::factory()->create();

        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'editor_in_chief',
        ]);
        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'author',
        ]);

        // Tanpa session active_role -> arahkan ke peran prioritas pertama (editor_in_chief -> eic.dashboard)
        $resDefault = $this->actingAs($user)->get(route('dashboard'));
        $resDefault->assertRedirect(route('eic.dashboard'));

        // Dengan session active_role = author -> arahkan ke author.submissions.index
        $resSessionAuthor = $this->actingAs($user)
            ->withSession(['active_role' => 'author'])
            ->get(route('dashboard'));
        $resSessionAuthor->assertRedirect(route('author.submissions.index'));
    }

    /**
     * Komponen UI <x-role-switcher /> hanya menampilkan peran yang dimiliki oleh pengguna tersebut.
     */
    public function test_komponen_role_switcher_hanya_menampilkan_peran_yang_dimiliki_pengguna(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
        ]);

        foreach (['editor_in_chief', 'author'] as $roleCode) {
            UserRole::create([
                'user_id' => $user->id,
                'journal_id' => $this->journal->id,
                'role_code' => $roleCode,
            ]);
        }

        $response = $this->actingAs($user)->get(route('eic.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Beralih Ruang Kerja Peran');
        $response->assertSee('2 Peran');
        $response->assertSee('Pemimpin Redaksi');
        $response->assertSee('Penulis');
        $response->assertSee(route('role.switch'), false);

        // Pastikan peran yang TIDAK dimiliki pengguna tidak muncul di dropdown role switcher
        $response->assertDontSee('Penyunting Naskah');
        $response->assertDontSee('Penata Letak');
        $response->assertDontSee('Korektor Galai');
        $response->assertDontSee('Manajer Jurnal');
        $response->assertDontSee('Administrator Sistem');
    }

    /**
     * Pengguna yang mengakses URL portal secara langsung tanpa memiliki peran terkait akan dialihkan kembali ke portal perannya.
     */
    public function test_pengguna_ditolak_mengakses_url_portal_yang_bukan_perannya(): void
    {
        $user = User::factory()->create();

        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'author',
        ]);

        // Mencoba mengakses Portal Administrator Sistem secara langsung melalui URL
        $response = $this->actingAs($user)->get(route('sysadmin.journals.index'));

        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('error', 'Anda tidak memiliki hak akses ke Portal Administrator Sistem.');
    }
}
