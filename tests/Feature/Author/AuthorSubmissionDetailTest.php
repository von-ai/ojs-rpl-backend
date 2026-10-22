<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthorSubmissionDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->author = User::factory()->create([
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'email' => 'andi.ilyas@unhas.ac.id',
        ]);

        $this->actingAs($this->author);
    }

    /**
     * Tamu yang belum login dialihkan ke form login.
     */
    public function test_tamu_tidak_bisa_mengakses_detail_naskah(): void
    {
        Auth::logout();

        $response = $this->get(route('author.submissions.show', 'SUB-2026-00124'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Penulis dapat mengakses halaman detail naskah bawaan dengan status 200 OK dan metadata lengkap.
     */
    public function test_penulis_dapat_mengakses_detail_naskah_bawaan(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00124'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00124');
        $response->assertSee('Evaluasi Kebijakan Lorong Wisata');
        $response->assertSee('Riset Orisinil');
        $response->assertSee('Perlu Revisi Penulis');
        $response->assertSee('Tahap 4 dari 6');
        $response->assertSee('Abstrak & Metadata Naskah', false);
        $response->assertSee('font-serif');
        $response->assertSee('Penulis & Tim Kontributor', false);
        $response->assertSee('Dr. Andi Muhammad Ilyas, M.Si.');
        $response->assertSee('andi.ilyas@unhas.ac.id');
        $response->assertSee('Penulis Korespondensi');
        $response->assertSee('Berkas Manuskrip & Lampiran', false);
        $response->assertSee('Pindai Bersih (ClamAV)');
        $response->assertSee('Tahapan Alur Editorial');
        $response->assertSee('Linimasa Aktivitas');
        $response->assertSee('Perlu Tindakan Penulis');
        $response->assertSee('Unggah Revisi');
    }

    /**
     * Naskah dengan status terbit menampilkan informasi DOI, edisi publikasi, dan sitasi ilmiah.
     */
    public function test_detail_naskah_status_terbit_menampilkan_informasi_publikasi(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2025-00041'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2025-00041');
        $response->assertSee('Strategi Transformasi Digital');
        $response->assertSee('Telah Terbit Resmi');
        $response->assertSee('10.55981/brida.v2i2.88');
        $response->assertSee('Vol. 2 No. 2 (2025)');
        $response->assertSee('Kutipan Ilmiah (Sitasi APA 7th)');
        $response->assertSee('Unduh Galai Cetak PDF');
    }

    /**
     * Naskah dengan status draf menampilkan pemberitahuan draf dan tombol lanjutkan formulir.
     */
    public function test_detail_naskah_status_draf_menampilkan_opsi_lanjutkan(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00155'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00155');
        $response->assertSee('Kajian Implementasi Geodatabase');
        $response->assertSee('Draf Belum Diajukan');
        $response->assertSee('Naskah Draf Parsial');
        $response->assertSee('Lanjutkan Pengisian Formulir');
    }

    /**
     * Naskah dari database relasional dapat dibuka dan dirender dengan presisi.
     */
    public function test_naskah_database_dapat_dibuka_melalui_id_atau_nomor_submisi(): void
    {
        $journal = Journal::create([
            'slug' => 'sirida-makassar-test',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar',
            'issn' => '2985-7821',
            'eissn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);

        $section = Section::create([
            'journal_id' => $journal->id,
            'slug' => 'riset-orisinil-test',
            'name' => 'Riset Orisinil Khusus',
            'description' => 'Seksi riset orisinil uji coba',
            'policy' => 'Kebijakan seksi uji coba',
            'accepting_submissions' => true,
            'position' => 1,
            'version' => 1,
        ]);

        $sub = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-99999',
            'title' => 'Studi Empiris Rekayasa Lalu Lintas Cerdas di Kawasan Panakkukang Makassar',
            'abstract' => 'Penelitian implementasi sistem kendali adaptif berbasis AI untuk mengurai kemacetan perkotaan.',
            'keywords' => ['Lalu Lintas Cerdas', 'Smart Mobility', 'Panakkukang'],
            'status' => 'submitted',
            'submitted_at' => now(),
            'version' => 1,
        ]);

        SubmissionContributor::create([
            'submission_id' => $sub->id,
            'user_id' => $this->author->id,
            'given_name' => 'Andi',
            'family_name' => 'Ilyas',
            'email' => 'andi.ilyas@unhas.ac.id',
            'affiliation' => 'Universitas Hasanuddin',
            'orcid' => '0000-0002-1825-0097',
            'is_corresponding' => true,
            'position' => 1,
        ]);

        // Akses via nomor pengajuan unik
        $responseNumber = $this->get(route('author.submissions.show', 'SUB-2026-99999'));
        $responseNumber->assertStatus(200);
        $responseNumber->assertSee('SUB-2026-99999');
        $responseNumber->assertSee('Studi Empiris Rekayasa Lalu Lintas Cerdas');
        $responseNumber->assertSee('Riset Orisinil Khusus');
        $responseNumber->assertSee('Pemeriksaan Awal Editor');

        // Akses via ID ULID
        $responseId = $this->get(route('author.submissions.show', $sub->id));
        $responseId->assertStatus(200);
        $responseId->assertSee('SUB-2026-99999');
    }

    /**
     * Akses naskah yang tidak ada sama sekali mengembalikan 404 Not Found.
     */
    public function test_naskah_tidak_ditemukan_mengembalikan_404(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-NONEXISTENT-00000'));

        $response->assertStatus(404);
    }

    /**
     * Halaman detail menjamin privasi telaah sejawat tertutup (Double-Blind Privacy).
     */
    public function test_halaman_detail_menjaga_privasi_double_blind_tanpa_bocor_reviewer(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00124'));

        $response->assertStatus(200);
        // Memastikan tidak ada nama reviewer atau confidential comments internal yang bocor
        $response->assertDontSee('Reviewer 1');
        $response->assertDontSee('Reviewer 2');
        $response->assertDontSee('Mitra Bestari:');
        $response->assertDontSee('internal_note');
        $response->assertDontSee('confidential');
    }
}
