<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected Journal $journalA;

    protected Journal $journalB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->journalA = Journal::create([
            'name' => 'Jurnal Riset Kebijakan Publik Makassar',
            'slug' => 'jurnal-kebijakan',
            'initials' => 'JRKP',
            'issn' => '2746-8194',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->journalB = Journal::create([
            'name' => 'Jurnal Sains dan Teknologi Terapan',
            'slug' => 'jurnal-sains',
            'initials' => 'JSTT',
            'issn' => '2746-8208',
            'status' => 'active',
            'version' => 1,
        ]);
    }

    /**
     * Komponen journal-switcher dan role-switcher tampil di header portal.
     */
    public function test_komponen_journal_switcher_dan_role_switcher_tampil_di_header(): void
    {
        $user = User::factory()->create([
            'full_name' => 'Andi Yusuf, M.Si.',
            'email' => 'yusuf@brida.makassar.go.id',
        ]);

        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journalA->id,
            'role_code' => 'manager',
        ]);

        $response = $this->actingAs($user)->get(route('journalmanager.issues.index'));

        $response->assertOk();
        $response->assertSee('data-journal-switcher', false);
        $response->assertSee('data-role-switcher', false);
        $response->assertSee('Beralih Konteks Jurnal');
        $response->assertSee('Jurnal Riset Kebijakan Publik Makassar');
    }

    /**
     * Pengguna dengan peran yang sama di dua jurnal dapat beralih konteks jurnal.
     */
    public function test_pengguna_multi_jurnal_dapat_beralih_jurnal_dengan_peran_sama(): void
    {
        $user = User::factory()->create();

        // User adalah Journal Manager di Jurnal A dan Jurnal B
        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journalA->id,
            'role_code' => 'manager',
        ]);

        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journalB->id,
            'role_code' => 'manager',
        ]);

        $response = $this->actingAs($user)->post(route('journal.switch'), [
            'journal_id' => $this->journalB->id,
            'current_role' => 'manager',
        ]);

        $response->assertRedirect(route('journalmanager.issues.index'));
        $response->assertSessionHas('active_journal_id', $this->journalB->id);
        $response->assertSessionHas('journal_switched_toast', "Anda sekarang bekerja di {$this->journalB->name}.");

        // Periksa bahwa pengaturan memuat informasi Jurnal B setelah beralih
        $settingsResponse = $this->actingAs($user)
            ->withSession(['active_journal_id' => $this->journalB->id])
            ->get(route('journalmanager.settings.index'));

        $settingsResponse->assertOk();
        $settingsResponse->assertSee($this->journalB->name);
    }

    /**
     * Pengguna yang beralih ke jurnal dengan peran berbeda otomatis dialihkan ke portal perannya di jurnal tujuan (Standar OJS).
     */
    public function test_pengguna_beralih_ke_jurnal_dengan_peran_berbeda_otomatis_alihkan_portal(): void
    {
        $user = User::factory()->create();

        // Di Jurnal A: Journal Manager
        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journalA->id,
            'role_code' => 'manager',
        ]);

        // Di Jurnal B: Hanya Mitra Bestari (Reviewer)
        UserRole::create([
            'user_id' => $user->id,
            'journal_id' => $this->journalB->id,
            'role_code' => 'reviewer',
        ]);

        // Pengguna saat ini di portal manager Jurnal A, lalu beralih ke Jurnal B
        $response = $this->actingAs($user)->post(route('journal.switch'), [
            'journal_id' => $this->journalB->id,
            'current_role' => 'manager',
        ]);

        // Di Jurnal B tidak ada peran manager, maka otomatis dialihkan ke reviewer portal
        $response->assertRedirect(route('reviewer.assignments.index'));
        $response->assertSessionHas('active_journal_id', $this->journalB->id);
        $response->assertSessionHas('active_role', 'reviewer');
    }

    /**
     * Administrator sistem (sysadmin) dapat beralih ke jurnal mana pun.
     */
    public function test_sysadmin_dapat_beralih_ke_jurnal_manapun(): void
    {
        $admin = User::factory()->create();

        UserRole::create([
            'user_id' => $admin->id,
            'journal_id' => null,
            'role_code' => 'sysadmin',
        ]);

        $response = $this->actingAs($admin)->post(route('journal.switch'), [
            'journal_id' => $this->journalA->id,
            'current_role' => 'sysadmin',
        ]);

        $response->assertRedirect(route('sysadmin.journals.index'));
        $response->assertSessionHas('active_journal_id', $this->journalA->id);
    }

    /**
     * Pengguna sebagai Penulis (Author) dapat beralih konteks jurnal.
     */
    public function test_pengguna_sebagai_penulis_dapat_beralih_jurnal(): void
    {
        $author = User::factory()->create();

        $response = $this->actingAs($author)->post(route('journal.switch'), [
            'journal_id' => $this->journalB->id,
            'current_role' => 'author',
        ]);

        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('active_journal_id', $this->journalB->id);
    }

    /**
     * Pengalihan ditolak jika jurnal tidak ditemukan di database.
     */
    public function test_pengalihan_gagal_jika_jurnal_tidak_valid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('journal.switch'), [
            'journal_id' => 'non-existent-journal-id',
        ]);

        $response->assertSessionHasErrors('journal_id');
    }
}
