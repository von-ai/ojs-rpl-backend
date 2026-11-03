<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\DeskReview;
use App\Models\Journal;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionContributor;
use App\Models\User;
use Database\Seeders\SubmissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

        $this->seed(SubmissionSeeder::class);

        $this->author = User::where('email', 'andi.ilyas@unhas.ac.id')->firstOrFail();
        $this->actingAs($this->author);
    }

    /**
     * Tamu yang belum login dialihkan ke form login.
     */
    public function test_tamu_tidak_bisa_mengakses_detail_naskah(): void
    {
        Auth::logout();

        $response = $this->get(route('author.submissions.show', 'SUB-2026-00104'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Penulis dapat mengakses halaman detail naskah bawaan dengan status 200 OK dan metadata lengkap.
     */
    public function test_penulis_dapat_mengakses_detail_naskah_bawaan(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00104'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00104');
        $response->assertSee('Evaluasi Kebijakan Lorong Wisata');
        $response->assertSee('Perlu Revisi Penulis');
        $response->assertSee('Tahap 4 dari 6');
        $response->assertSee('Abstrak & Metadata Naskah', false);
        $response->assertSee('Penelitian ini bertujuan untuk mengevaluasi efektivitas program Lorong Wisata', false);
        $response->assertSee('Penulis & Tim Kontributor', false);
        $response->assertSee('Dr. Andi Muhammad Ilyas, M.Si.');
        $response->assertSee('andi.ilyas@unhas.ac.id');
        $response->assertSee('Penulis Korespondensi');
        $response->assertSee('Berkas Manuskrip & Lampiran', false);
        $response->assertSee('Manuskrip-Evaluasi-Lorong-Wisata-v1.docx');
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
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00107'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00107');
        $response->assertSee('Strategi Transformasi Digital');
        $response->assertSee('Telah Terbit Resmi');
        $response->assertSee('10.55981/brida.v2i2.88');
        $response->assertSee('Kutipan Ilmiah (Sitasi APA 7th)');
    }

    /**
     * Naskah dengan status draf menampilkan pemberitahuan draf dan tombol lanjutkan formulir.
     */
    public function test_detail_naskah_status_draf_menampilkan_opsi_lanjutkan(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00101'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00101');
        $response->assertSee('Inovasi Bank Sampah Digital');
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
     * Halaman detail menjamin privasi telaah tertutup (Double-Blind Privacy).
     */
    public function test_halaman_detail_menjaga_privasi_double_blind_tanpa_bocor_reviewer(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00104'));

        $response->assertStatus(200);
        // Memastikan tidak ada nama reviewer atau confidential comments internal yang bocor
        $response->assertDontSee('Reviewer 1');
        $response->assertDontSee('Reviewer 2');
        $response->assertDontSee('Mitra Bestari:');
        $response->assertDontSee('internal_note');
        $response->assertDontSee('confidential');
    }

    /**
     * Seeder berhasil membuat seluruh 9 skenario dan setiap skenario tampil akurat di halaman detail.
     */
    public function test_seeder_membuat_seluruh_skenario_dan_dapat_diakses_di_halaman_detail(): void
    {
        $this->seed(SubmissionSeeder::class);

        // 1. Skenario Draf
        $respDraft = $this->get(route('author.submissions.show', 'SUB-2026-00101'));
        $respDraft->assertStatus(200);
        $respDraft->assertSee('SUB-2026-00101');
        $respDraft->assertSee('Draf Belum Diajukan');
        $respDraft->assertSee('Naskah Draf Parsial');
        $respDraft->assertSee('Lanjutkan Pengisian Formulir');

        // 2. Skenario Pemeriksaan Awal (Submitted / Desk Review)
        $respSubmitted = $this->get(route('author.submissions.show', 'SUB-2026-00102'));
        $respSubmitted->assertStatus(200);
        $respSubmitted->assertSee('SUB-2026-00102');
        $respSubmitted->assertSee('Pemeriksaan Awal Editor');
        $respSubmitted->assertSee('Pemeriksaan Awal Sedang Berlangsung');
        $respSubmitted->assertSee('Turnitin / iThenticate');

        // 3. Skenario Proses Telaah (In Review)
        $respReview = $this->get(route('author.submissions.show', 'SUB-2026-00103'));
        $respReview->assertStatus(200);
        $respReview->assertSee('SUB-2026-00103');
        $respReview->assertSee('Proses Telaah');
        $respReview->assertSee('Double-Blind');
        $respReview->assertSee('Mitra Bestari Ditugaskan');

        // 4. Skenario Perlu Revisi Minor
        $respRevMinor = $this->get(route('author.submissions.show', 'SUB-2026-00104'));
        $respRevMinor->assertStatus(200);
        $respRevMinor->assertSee('SUB-2026-00104');
        $respRevMinor->assertSee('Perlu Revisi Penulis');
        $respRevMinor->assertSee('Perlu Tindakan Penulis');
        $respRevMinor->assertSee('Unggah Revisi Naskah & Response Letter', false);

        // 5. Skenario Perlu Revisi Mayor
        $respRevMajor = $this->get(route('author.submissions.show', 'SUB-2026-00105'));
        $respRevMajor->assertStatus(200);
        $respRevMajor->assertSee('SUB-2026-00105');
        $respRevMajor->assertSee('Perlu Revisi Penulis');
        $respRevMajor->assertSee('Perlu Tindakan Penulis');

        // 6. Skenario Produksi & Galai (In Production)
        $respProd = $this->get(route('author.submissions.show', 'SUB-2026-00106'));
        $respProd->assertStatus(200);
        $respProd->assertSee('SUB-2026-00106');
        $respProd->assertSee('Tahap Produksi &amp; Pemeriksaan Galai', false);
        $respProd->assertSee('Penataan Tata Letak');

        // 7. Skenario Telah Terbit Resmi (Published)
        $respPub = $this->get(route('author.submissions.show', 'SUB-2026-00107'));
        $respPub->assertStatus(200);
        $respPub->assertSee('SUB-2026-00107');
        $respPub->assertSee('Telah Terbit Resmi');
        $respPub->assertSee('Open Access');
        $respPub->assertSee('10.55981/brida.v2i2.88');
        $respPub->assertSee('Kutipan Ilmiah (Sitasi APA 7th)');

        // 8. Skenario Ditolak (Declined)
        $respDec = $this->get(route('author.submissions.show', 'SUB-2026-00108'));
        $respDec->assertStatus(200);
        $respDec->assertSee('SUB-2026-00108');
        $respDec->assertSee('Naskah Ditolak');
        $respDec->assertSee('Alur Terhenti (Ditolak)');
        $respDec->assertSee('Keputusan Editorial: Naskah Tidak Diterima');

        // 9. Skenario Diarsipkan (Archived - Sesuai status patokan erd.md)
        $respArchived = $this->get(route('author.submissions.show', 'SUB-2026-00109'));
        $respArchived->assertStatus(200);
        $respArchived->assertSee('SUB-2026-00109');
        $respArchived->assertSee('Naskah Diarsipkan');
        $respArchived->assertSee('Naskah Telah Diarsipkan Resmi');
    }

    /**
     * Naskah tahap awal (submitted) yang belum ditindaklanjuti menampilkan tombol Ajukan Penarikan Naskah.
     */
    public function test_naskah_tahap_awal_tanpa_tindak_lanjut_menampilkan_tombol_ajukan_penarikan(): void
    {
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00102'));

        $response->assertStatus(200);
        $response->assertSee('Ajukan Penarikan Naskah');
        $response->assertDontSee('Ajukan Penarikan Naskah (Withdraw)');
    }

    /**
     * Naskah yang sudah masuk tahap review atau memiliki tindak lanjut tidak dapat ditarik.
     */
    public function test_naskah_tahap_review_atau_ada_tindak_lanjut_tidak_dapat_ditarik(): void
    {
        // Naskah berstatus in_review (tahap 3)
        $respReview = $this->get(route('author.submissions.show', 'SUB-2026-00103'));
        $respReview->assertStatus(200);
        $respReview->assertDontSee('Ajukan Penarikan Naskah');
        $respReview->assertSee('Naskah Tidak Dapat Ditarik');

        // Naskah berstatus revisions_requested (tahap 4)
        $respRev = $this->get(route('author.submissions.show', 'SUB-2026-00104'));
        $respRev->assertStatus(200);
        $respRev->assertDontSee('Ajukan Penarikan Naskah');
        $respRev->assertSee('Naskah Tidak Dapat Ditarik');
    }

    /**
     * Penulis dapat melakukan POST pengajuan penarikan pada naskah tahap submitted di database.
     */
    public function test_penulis_dapat_menarik_naskah_tahap_awal_di_database(): void
    {
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-test-withdraw'],
            ['name' => 'Jurnal Uji Withdraw', 'status' => 'active', 'version' => 1]
        );

        $section = Section::firstOrCreate(
            ['journal_id' => $journal->id, 'slug' => 'sec-withdraw-test'],
            ['name' => 'Seksi Uji Withdraw', 'accepting_submissions' => true, 'version' => 1]
        );

        $submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-W001',
            'title' => 'Naskah Uji Coba Penarikan Tahap Awal',
            'abstract' => 'Abstrak naskah uji coba penarikan.',
            'status' => 'submitted',
            'submitted_at' => now(),
            'version' => 1,
        ]);

        $response = $this->post(route('author.submissions.withdraw', $submission->id));

        $response->assertRedirect(route('author.submissions.show', $submission->id));
        $this->assertEquals('withdrawn', $submission->fresh()->status);
    }

    /**
     * Penulis ditolak saat mencoba menarik naskah yang sudah masuk tahap review di database.
     */
    public function test_penulis_tidak_bisa_menarik_naskah_yang_sudah_masuk_tahap_review_di_database(): void
    {
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-test-withdraw'],
            ['name' => 'Jurnal Uji Withdraw', 'status' => 'active', 'version' => 1]
        );

        $section = Section::firstOrCreate(
            ['journal_id' => $journal->id, 'slug' => 'sec-withdraw-test'],
            ['name' => 'Seksi Uji Withdraw', 'accepting_submissions' => true, 'version' => 1]
        );

        $submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-W002',
            'title' => 'Naskah Uji Coba Tolak Penarikan Review',
            'abstract' => 'Abstrak naskah dalam tahap telaah.',
            'status' => 'in_review',
            'submitted_at' => now()->subDays(5),
            'version' => 1,
        ]);

        $response = $this->post(route('author.submissions.withdraw', $submission->id));

        $response->assertRedirect(route('author.submissions.show', $submission->id));
        $response->assertSessionHas('error');
        $this->assertEquals('in_review', $submission->fresh()->status);
    }

    /**
     * Penulis dapat mengakses halaman detail naskah baru berstatus submitted via submission_number.
     */
    public function test_penulis_dapat_mengakses_detail_naskah_baru_status_submitted(): void
    {
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-test-new-sub'],
            ['name' => 'Jurnal Uji Naskah Baru', 'status' => 'active', 'version' => 1]
        );

        $section = Section::firstOrCreate(
            ['journal_id' => $journal->id, 'slug' => 'sec-new-test'],
            ['name' => 'Seksi Naskah Baru', 'accepting_submissions' => true, 'version' => 1]
        );

        $submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-00099',
            'title' => 'Naskah Baru Mandiri Penulis',
            'abstract' => 'Abstrak naskah baru mandiri penulis.',
            'comments_for_editor' => 'Catatan khusus pengantar untuk dewan redaksi jurnal.',
            'status' => 'submitted',
            'submitted_at' => now(),
            'version' => 1,
        ]);

        $response = $this->get(route('author.submissions.show', 'SUB-2026-00099'));

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00099');
        $response->assertSee('Naskah Baru Mandiri Penulis');
        $response->assertSee('Pemeriksaan Awal');
        $response->assertSee('Catatan Pengantar untuk Dewan Redaksi');
        $response->assertSee('Catatan khusus pengantar untuk dewan redaksi jurnal.');
    }

    /**
     * Penulis lain tidak dapat mengakses halaman detail naskah milik penulis lain (404 Not Found / Unauthorized).
     */
    public function test_penulis_lain_tidak_dapat_mengakses_detail_naskah_bukan_miliknya(): void
    {
        $otherAuthor = User::factory()->create([
            'email' => 'penulis.lain@example.com',
            'full_name' => 'Penulis Asing',
        ]);

        $response = $this->actingAs($otherAuthor)->get(route('author.submissions.show', 'SUB-2026-00104'));

        $response->assertStatus(404);
    }

    /**
     * Saat EIC meminta perbaikan format pada skrining awal, catatan desk review muncul dan batas waktu revisi tidak ditampilkan.
     */
    public function test_catatan_perbaikan_format_skrining_awal_ditampilkan_tanpa_batas_waktu(): void
    {
        $journal = Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            ['name' => 'Jurnal SIRIDA', 'status' => 'active', 'version' => 1]
        );
        $section = Section::firstOrCreate(
            ['journal_id' => $journal->id, 'slug' => 'sec-format-test'],
            ['name' => 'Seksi Format Test', 'accepting_submissions' => true, 'version' => 1]
        );

        $submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-00888',
            'title' => 'Naskah Evaluasi Perbaikan Format Skrining Awal',
            'abstract' => 'Abstrak naskah dengan perbaikan format.',
            'status' => 'revisions_requested',
            'submitted_at' => now()->subDays(2),
            'version' => 2,
        ]);

        // EIC melakukan desk review dengan permintaan perbaikan format dan catatan khusus
        DeskReview::create([
            'submission_id' => $submission->id,
            'assessed_by' => $this->author->id,
            'scope_fit' => true,
            'files_complete' => false,
            'guidelines_compliant' => false,
            'similarity_score' => 12.5,
            'notes' => 'Format daftar pustaka belum mengikuti gaya APA 7th dan abstrak belum memuat kata kunci bahasa Inggris.',
            'status' => 'revision_required',
            'version' => 1,
        ]);

        // Buka halaman detail naskah author
        $response = $this->get(route('author.submissions.show', 'SUB-2026-00888'));

        $response->assertStatus(200);
        $response->assertSee('Perbaikan Format Berkas');
        $response->assertSee('Perbaikan Format Naskah (Pemeriksaan Awal)');
        // Memastikan catatan yang diinput EIC tampil persis
        $response->assertSee('Format daftar pustaka belum mengikuti gaya APA 7th dan abstrak belum memuat kata kunci bahasa Inggris.');
        // Memastikan tidak muncul kalimat default mitra bestari
        $response->assertDontSee('Dewan redaksi meminta perbaikan naskah sesuai catatan telaah mitra bestari.');
        // Memastikan baris Batas Waktu Revisi tidak muncul (karena di ERD desk_reviews tidak ada kolom batas waktu)
        $response->assertDontSee('Batas Waktu Revisi:');

        // Buka halaman katalog naskah author
        $indexResponse = $this->get(route('author.submissions.index', ['tab' => 'perlu-revisi']));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('SUB-2026-00888');
        $indexResponse->assertSee('Perbaikan Format Berkas');
        $indexResponse->assertDontSee('15 Nov 2026');
    }

    /**
     * Memastikan pengiriman revisi format skrining awal mengembalikan status naskah ke submitted (Pemeriksaan Awal).
     */
    public function test_pengunggahan_revisi_format_skrining_awal_mengembalikan_status_ke_submitted(): void
    {
        $journal = Journal::first() ?? Journal::create([
            'name' => 'Jurnal Inovasi Daerah',
            'slug' => 'sirida',
            'is_active' => true,
        ]);

        $section = Section::first() ?? Section::create([
            'journal_id' => $journal->id,
            'name' => 'Artikel Riset',
            'slug' => 'artikel-riset',
            'is_active' => true,
        ]);

        $submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-00999',
            'title' => 'Pengujian Alur Revisi Format Skrining Awal',
            'abstract' => 'Abstrak pengujian.',
            'status' => 'revisions_requested',
            'submitted_at' => now()->subDays(1),
            'version' => 1,
        ]);

        DeskReview::create([
            'submission_id' => $submission->id,
            'assessed_by' => $this->author->id,
            'scope_fit' => true,
            'files_complete' => false,
            'guidelines_compliant' => false,
            'similarity_score' => 15.0,
            'notes' => 'Tolong sesuaikan format margin dan referensi sesuai template.',
            'status' => 'revision_required',
            'version' => 1,
        ]);

        $fakeManuscript = UploadedFile::fake()->create('manuskrip_perbaikan_format.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->post(route('author.submissions.revisions.store', $submission->id), [
            'manuscript_file' => $fakeManuscript,
            'note' => 'Format sudah diperbaiki sesuai panduan.',
        ]);

        $response->assertRedirect(route('author.submissions.show', $submission->id));

        // Status naskah harus kembali ke submitted (Pemeriksaan Awal), BUKAN langsung in_review
        $submission->refresh();
        $this->assertSame('submitted', $submission->status);

        // Tampilan detail naskah harus menunjukkan pemeriksaan awal dan bukan proses telaah putaran 1
        $detailResponse = $this->get(route('author.submissions.show', 'SUB-2026-00999'));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Tahap 2 dari 6');
        $detailResponse->assertSee('Verifikasi Perbaikan Format Sedang Berlangsung');
        $detailResponse->assertDontSee('Proses Telaah Putaran 1');
        $detailResponse->assertDontSee('Mitra Bestari Ditugaskan');
    }

    /**
     * Memastikan naskah dalam tahap telaah tanpa reviewer yang ditugaskan tidak memalsukan jumlah reviewer.
     */
    public function test_penelaahan_in_review_tanpa_mitra_bestari_menampilkan_sedang_dialokasikan(): void
    {
        $journal = Journal::first() ?? Journal::create([
            'name' => 'Jurnal Inovasi Daerah',
            'slug' => 'sirida',
            'is_active' => true,
        ]);

        $section = Section::first() ?? Section::create([
            'journal_id' => $journal->id,
            'name' => 'Artikel Riset',
            'slug' => 'artikel-riset',
            'is_active' => true,
        ]);

        $submission = Submission::create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-00777',
            'title' => 'Pengujian Telaah Tanpa Penugasan Reviewer',
            'abstract' => 'Abstrak pengujian telaah.',
            'status' => 'in_review',
            'submitted_at' => now()->subDays(5),
            'version' => 1,
        ]);

        ReviewRound::create([
            'submission_id' => $submission->id,
            'round' => 1,
            'status' => 'active',
            'review_type' => 'double_blind',
            'version' => 1,
        ]);

        $response = $this->get(route('author.submissions.show', 'SUB-2026-00777'));
        $response->assertStatus(200);
        $response->assertSee('Proses Telaah');
        $response->assertSee('Sedang Dialokasikan');
        $response->assertDontSee('2 Penilai Independen');
    }
}
