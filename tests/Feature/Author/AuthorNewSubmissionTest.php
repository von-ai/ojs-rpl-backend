<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\User;
use Database\Seeders\SectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthorNewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create([
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'email' => 'andi.ilyas@unhas.ac.id',
        ]);

        $this->seed(SectionSeeder::class);
        $this->actingAs($this->user);
    }

    /**
     * Tamu yang belum login tidak bisa mengakses formulir wizard pengajuan naskah dan dialihkan ke login.
     */
    public function test_tamu_tidak_bisa_mengakses_formulir_dan_dialihkan_ke_login(): void
    {
        Auth::logout();

        $response = $this->get(route('author.submissions.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Tamu yang belum login tidak bisa mengirimkan naskah dan dialihkan ke login.
     */
    public function test_tamu_tidak_bisa_mengirim_naskah_dan_dialihkan_ke_login(): void
    {
        Auth::logout();

        $response = $this->post(route('author.submissions.store'), [
            'title' => 'Judul Naskah Ilmiah',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman formulir wizard pengajuan naskah baru (5-Step Wizard) dapat diakses dengan respons 200 OK.
     */
    public function test_halaman_formulir_pengajuan_naskah_dapat_diakses(): void
    {
        $response = $this->get(route('author.submissions.create'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Pengajuan Naskah Baru');
        $response->assertDontSee('Standar OJS 3.4');
        $response->assertSee('sessionStorage');

        // Memastikan 5 Langkah Tertera Jelas
        $response->assertSee('Ketentuan', false);
        $response->assertSee('Unggah Berkas', false);
        $response->assertSee('Metadata Naskah', false);
        $response->assertSee('Kontributor', false);
        $response->assertSee('Konfirmasi & Kirim', false);

        // Memastikan Seksi Jurnal Aktif Tersedia
        $response->assertSee('Artikel Riset Orisinil');
        $response->assertSee('Kajian Kebijakan Strategis');
        $response->assertSee('Studi Kasus');
        $response->assertSee('Inovasi Daerah');

        // Memastikan Poin Checklist Etika Publikasi
        $response->assertSee('Keaslian');
        $response->assertSee('Panduan Penulisan BRIDA');
        $response->assertSee('Pematuhan Penelaahan Tertutup');
        $response->assertSee('Manajemen Referensi');
        $response->assertSee('Persetujuan Etika');

        // Memastikan Tidak Ada Teks Bahasa Inggris Dalam Kurung
        $response->assertDontSee('(Original Research)');
        $response->assertDontSee('(Policy Brief & Review)');
        $response->assertDontSee('(Case Study & Innovation)');
        $response->assertDontSee('(Blind Review)');
        $response->assertDontSee('(*Primary Manuscript File*)');
        $response->assertDontSee('(*Keywords*)');
        $response->assertDontSee('(*References*)');
        $response->assertDontSee('(*Corresponding Author*)');
        $response->assertDontSee('(*Co-Author*)');

        // Memastikan Kontributor Default Terisi
        $response->assertSee('Ilyas');
        $response->assertSee('andi.ilyas@unhas.ac.id');
    }

    /**
     * Tombol Kirim Naskah Baru pada halaman Naskah Saya mengarah ke rute wizard.
     */
    public function test_tombol_kirim_naskah_baru_pada_index_mengarah_ke_create(): void
    {
        $response = $this->get(route('author.submissions.index'));

        $response->assertStatus(200);
        $response->assertSee(route('author.submissions.create'));
    }

    /**
     * Validasi formulir pengajuan naskah menolak request tanpa field wajib saat pengiriman resmi.
     */
    public function test_validasi_formulir_pengajuan_naskah_memerlukan_metadata_wajib(): void
    {
        $response = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => '',
            'title' => '',
            'abstract' => '',
            'keywords' => [],
        ]);

        $response->assertRedirect(route('author.submissions.create'));
        $response->assertSessionHasErrors([
            'section_id',
            'title',
            'abstract',
            'keywords',
            'author_guidelines_accepted',
            'manuscript_file',
        ]);
    }

    /**
     * Validasi kontributor wajib memiliki tepat satu Penulis Korespondensi (Corresponding Author).
     */
    public function test_validasi_kontributor_memerlukan_tepat_satu_penulis_korespondensi(): void
    {
        // Kasus: Dua kontributor, tidak ada yang korespondensi
        $responseZero = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Artikel Riset Empiris',
            'abstract' => 'Abstrak riset empiris komprehensif.',
            'keywords' => ['Kebijakan', 'Makassar'],
            'manuscript_file' => UploadedFile::fake()->create('naskah.docx', 500),
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => false,
                ],
                [
                    'family_name' => 'Fadly',
                    'email' => 'fadly@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => false,
                ],
            ],
        ]);

        $responseZero->assertRedirect(route('author.submissions.create'));
        $responseZero->assertSessionHasErrors(['contributors']);

        // Kasus: Dua kontributor, KEDUA-DUANYA ditandai korespondensi
        $responseTwo = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Artikel Riset Empiris',
            'abstract' => 'Abstrak riset empiris komprehensif.',
            'keywords' => ['Kebijakan', 'Makassar'],
            'manuscript_file' => UploadedFile::fake()->create('naskah.docx', 500),
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
                [
                    'family_name' => 'Fadly',
                    'email' => 'fadly@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
            ],
        ]);

        $responseTwo->assertRedirect(route('author.submissions.create'));
        $responseTwo->assertSessionHasErrors(['contributors']);
    }

    /**
     * Pengajuan naskah dapat disimpan sebagai draf sementara (mode draft fleksibel).
     */
    public function test_pengajuan_dapat_disimpan_sebagai_draf(): void
    {
        $response = $this->post(route('author.submissions.store'), [
            'action' => 'draft',
            'section_id' => 'sec-riset-orisinil',
            'title' => 'Draf Judul Naskah Awal',
            'abstract' => 'Draf abstrak pengantar.',
            'keywords' => ['Draf', 'Inovasi'],
            'comments_for_editor' => 'Catatan draf sementara.',
        ]);

        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('success');

        $flash = session('success');
        $this->assertStringContainsString('Draf pengajuan naskah berhasil disimpan', (string) $flash);
    }

    /**
     * Pengajuan naskah final berhasil dikirim resmi (mode submit sukses).
     */
    public function test_pengajuan_final_berhasil_dikirim(): void
    {
        $file = UploadedFile::fake()->create('manuskrip-riset.pdf', 1024, 'application/pdf');

        $response = $this->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Analisis Kebijakan Pengelolaan Ruang Terbuka Hijau Kota Makassar',
            'abstract' => 'Penelitian ini menganalisis efektivitas tata kelola ruang terbuka hijau di wilayah perkotaan Makassar berbasis data citra satelit dan survei spasial empiris.',
            'keywords' => ['Ruang Terbuka Hijau', 'Tata Kelola Kota', 'Makassar', 'Urban Planning'],
            'subject_area' => 'Perencanaan Wilayah & Kota',
            'references' => 'Ilyas, A. M. (2025). Urban Policy in Makassar.',
            'manuscript_file' => $file,
            'contributors' => [
                [
                    'given_name' => 'Andi Muhammad',
                    'family_name' => 'Ilyas',
                    'email' => 'andi.ilyas@unhas.ac.id',
                    'affiliation' => 'Universitas Hasanuddin',
                    'orcid_id' => '0000-0002-1825-0097',
                    'corresponding' => true,
                ],
                [
                    'given_name' => 'Budi',
                    'family_name' => 'Santoso',
                    'email' => 'budi.santoso@brida.makassar.go.id',
                    'affiliation' => 'BRIDA Kota Makassar',
                    'corresponding' => false,
                ],
            ],
        ]);

        $response->assertRedirect(route('author.submissions.index'));
        $response->assertSessionHas('success');

        $flash = session('success');
        $this->assertStringContainsString('SUB-2026-', (string) $flash);
        $this->assertStringContainsString('Pemeriksaan Awal', (string) $flash);
    }

    /**
     * Validasi berkas manuskrip menolak berkas non-dokumen dan berkas melebihi batas 20 MiB.
     */
    public function test_validasi_berkas_manuskrip_menolak_format_ilegal_dan_over_limit(): void
    {
        // 1. Format tidak diizinkan (.exe)
        $invalidExtensionFile = UploadedFile::fake()->create('naskah.exe', 500);
        $responseExt = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Artikel Valid',
            'abstract' => 'Abstrak naskah valid.',
            'keywords' => ['Riset'],
            'manuscript_file' => $invalidExtensionFile,
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
            ],
        ]);

        $responseExt->assertRedirect(route('author.submissions.create'));
        $responseExt->assertSessionHasErrors(['manuscript_file']);

        // 2. Ukuran melebihi 20 MiB (20.481 KB)
        $oversizedFile = UploadedFile::fake()->create('naskah-raksasa.pdf', 21000, 'application/pdf');
        $responseSize = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Artikel Valid',
            'abstract' => 'Abstrak naskah valid.',
            'keywords' => ['Riset'],
            'manuscript_file' => $oversizedFile,
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
            ],
        ]);

        $responseSize->assertRedirect(route('author.submissions.create'));
        $responseSize->assertSessionHasErrors(['manuscript_file']);
    }

    /**
     * Memastikan naskah yang diajukan benar-benar tersimpan di database dan tampil di daftar naskah.
     */
    public function test_pengajuan_naskah_tersimpan_di_database_dan_muncul_di_daftar(): void
    {
        $file = UploadedFile::fake()->create('manuskrip-uji-db.pdf', 1024, 'application/pdf');

        $payload = [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Studi Integrasi Sistem Database MySQL untuk OJS BRIDA',
            'abstract' => 'Abstrak pengujian integrasi database naskah ilmiah.',
            'keywords' => ['MySQL', 'OJS', 'BRIDA'],
            'subject_area' => 'Teknologi Informasi & Tata Kelola Data',
            'manuscript_file' => $file,
            'contributors' => [
                [
                    'given_name' => 'Muhammad',
                    'family_name' => 'Fadly',
                    'email' => 'fadly@brida.makassar.go.id',
                    'affiliation' => 'BRIDA Kota Makassar',
                    'corresponding' => true,
                ],
            ],
        ];

        $response = $this->post(route('author.submissions.store'), $payload);

        $response->assertRedirect(route('author.submissions.index'));

        // Pastikan record tersimpan di tabel submissions
        $this->assertDatabaseHas('submissions', [
            'title' => 'Studi Integrasi Sistem Database MySQL untuk OJS BRIDA',
            'status' => 'submitted',
        ]);

        // Pastikan kontributor tersimpan di tabel submission_contributors
        $this->assertDatabaseHas('submission_contributors', [
            'email' => 'fadly@brida.makassar.go.id',
            'family_name' => 'Fadly',
            'is_corresponding' => true,
        ]);

        // Pastikan berkas tersimpan di tabel files & submission_files
        $this->assertDatabaseHas('files', [
            'original_name' => 'manuskrip-uji-db.pdf',
        ]);
        $this->assertDatabaseHas('submission_files', [
            'genre' => 'manuscript',
        ]);

        // Pastikan naskah muncul di halaman daftar Naskah Saya
        $indexResponse = $this->get(route('author.submissions.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Studi Integrasi Sistem Database MySQL untuk OJS BRIDA');
    }

    /**
     * Memastikan langkah kedua bersih dari berkas tambahan/lampiran, tanpa duplikasi info, dan bidang keilmuan tetap ada.
     */
    public function test_langkah_kedua_bersih_dari_lampiran_dan_duplikasi_informasi(): void
    {
        $response = $this->get(route('author.submissions.create'));

        $response->assertStatus(200);
        $response->assertSee('Unggah Berkas Naskah');
        $response->assertDontSee('Unggah Berkas Naskah & Lampiran');
        $response->assertDontSee('supplementary_files');
        $response->assertDontSee('Berkas Tambahan / Lampiran');
        $response->assertDontSee('20 MiB');

        // Memastikan pembatasan judul dan abstrak per kata
        $response->assertSee('title-word-count');
        $response->assertSee('20 kata');
        $response->assertSee('abstract-word-count');
        $response->assertSee('250 kata');

        // Memastikan bidang keilmuan tetap dipertahankan sesuai skema ERD
        $response->assertSee('subject_area');
        $response->assertSee('Bidang Keilmuan / Fokus Riset');
    }

    /**
     * Memastikan validasi backend menolak judul melebihi 20 kata dan abstrak melebihi 250 kata.
     */
    public function test_validasi_menolak_judul_melebihi_20_kata_dan_abstrak_melebihi_250_kata(): void
    {
        // 1. Judul 21 kata
        $longTitle = implode(' ', array_fill(0, 21, 'Kata'));
        $file = UploadedFile::fake()->create('manuskrip.pdf', 500, 'application/pdf');

        $responseTitle = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => $longTitle,
            'abstract' => 'Abstrak singkat dan jelas.',
            'keywords' => ['Kebijakan'],
            'manuscript_file' => $file,
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
            ],
        ]);

        $responseTitle->assertRedirect(route('author.submissions.create'));
        $responseTitle->assertSessionHasErrors(['title']);

        // 2. Abstrak 251 kata
        $longAbstract = implode(' ', array_fill(0, 251, 'Abstrak'));
        $responseAbstract = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Pendek Valid',
            'abstract' => $longAbstract,
            'keywords' => ['Kebijakan'],
            'manuscript_file' => $file,
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
            ],
        ]);

        $responseAbstract->assertRedirect(route('author.submissions.create'));
        $responseAbstract->assertSessionHasErrors(['abstract']);
    }

    /**
     * Memastikan pesan kesalahan berkas melebihi 20 MB menggunakan bahasa non-teknis yang ramah.
     */
    public function test_pesan_error_berkas_oversize_menggunakan_bahasa_ramah_non_teknis(): void
    {
        $oversizedFile = UploadedFile::fake()->create('naskah-besar.pdf', 25000, 'application/pdf');

        $response = $this->from(route('author.submissions.create'))->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Judul Valid',
            'abstract' => 'Abstrak valid.',
            'keywords' => ['Inovasi'],
            'manuscript_file' => $oversizedFile,
            'contributors' => [
                [
                    'family_name' => 'Ilyas',
                    'email' => 'ilyas@unhas.ac.id',
                    'affiliation' => 'Unhas',
                    'corresponding' => true,
                ],
            ],
        ]);

        $response->assertRedirect(route('author.submissions.create'));
        $response->assertSessionHasErrors([
            'manuscript_file' => 'Ukuran berkas naskah terlalu besar. Batas maksimal yang diperbolehkan adalah 20 MB.',
        ]);
    }

    /**
     * Memastikan toast notification reusable tersedia di layout, alert bawaan browser dihapus,
     * garis waktu progress bar ditiadakan (clean), dan kata kunci duplikat tidak memicu toast.
     */
    public function test_toast_notifikasi_tersedia_dan_alert_bawaan_browser_dihapus(): void
    {
        $response = $this->get(route('author.submissions.create'));

        $response->assertStatus(200);

        // Memastikan container toast hadir di layout
        $response->assertSee('toast-notification-container');
        $response->assertSee('window.showToast');
        $response->assertSee('window.toast.warning');

        // Memastikan native alert('Maksimal 20 kata kunci...') sudah tidak ada
        $response->assertDontSee("alert('Maksimal 20 kata kunci");
        $response->assertDontSee('alert("Maksimal 20 kata kunci');

        // Memastikan garis waktu (progress bar) ditiadakan agar clean
        $response->assertDontSee('toast-progress-bar');
        $response->assertDontSee('toastProgressBar');

        // Memastikan tidak memicu toast saat kata kunci duplikat
        $response->assertDontSee('Kata Kunci Duplikat');
    }

    /**
     * Memastikan fitur pengurutan kontributor dan penentuan Penulis Pertama tersedia di UI dan tersimpan di database.
     */
    public function test_pengurutan_kontributor_dan_penulis_pertama_tersedia(): void
    {
        $response = $this->get(route('author.submissions.create'));
        $response->assertStatus(200);

        // Memastikan kotak panduan teks dihilangkan agar tampilan lebih bersih
        $response->assertDontSee('Panduan Penataan Urutan Penulis');
        $response->assertSee('input-author-first');
        $response->assertSee('Jadikan sebagai Penulis Pertama');
        $response->assertSee('btn-move-up');
        $response->assertSee('btn-move-down');
        $response->assertSee('btn-set-first');
        $response->assertSee('moveContributor');

        // Uji pengiriman formulir dengan kontributor yang diurutkan (Penulis Pertama kustom)
        $submitResponse = $this->post(route('author.submissions.store'), [
            'action' => 'submit',
            'section_id' => 'sec-riset-orisinil',
            'author_guidelines_accepted' => '1',
            'title' => 'Studi Inovasi Penataan Kota Makassar',
            'abstract' => 'Abstrak riset mendalam mengenai transformasi perkotaan.',
            'keywords' => ['Urban Planning', 'Makassar', 'Smart City'],
            'manuscript_file' => UploadedFile::fake()->create('naskah_final.docx', 800),
            'contributors' => [
                [
                    'family_name' => 'Santoso',
                    'given_name' => 'Budi',
                    'email' => 'budi.santoso@unhas.ac.id',
                    'affiliation' => 'Universitas Hasanuddin',
                    'sequence' => 1,
                    'corresponding' => false,
                ],
                [
                    'family_name' => 'Ilyas',
                    'given_name' => 'Andi Muhammad',
                    'email' => 'andi.ilyas@unhas.ac.id',
                    'affiliation' => 'BRIDA Kota Makassar',
                    'sequence' => 2,
                    'corresponding' => true,
                ],
            ],
        ]);

        $submitResponse->assertSessionHasNoErrors();
        $submitResponse->assertRedirect(route('author.submissions.index'));

        // Pastikan urutan tersimpan persis di database: Santoso di posisi 1, Ilyas di posisi 2
        $this->assertDatabaseHas('submission_contributors', [
            'family_name' => 'Santoso',
            'position' => 1,
            'is_corresponding' => false,
        ]);

        $this->assertDatabaseHas('submission_contributors', [
            'family_name' => 'Ilyas',
            'position' => 2,
            'is_corresponding' => true,
        ]);
    }
}
