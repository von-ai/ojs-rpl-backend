<?php

declare(strict_types=1);

namespace Tests\Feature\Author;

use App\Models\User;
use Database\Seeders\SubmissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthorSubmissionsPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(\Database\Seeders\SubmissionSeeder::class);

        $this->author = User::where('email', 'andi.ilyas@unhas.ac.id')->firstOrFail();
        $this->actingAs($this->author);
    }

    /**
     * Tamu yang belum login tidak bisa mengakses halaman author dan dialihkan ke login.
     */
    public function test_tamu_tidak_bisa_mengakses_halaman_author_dan_dialihkan_ke_login(): void
    {
        Auth::logout();

        $response = $this->get(route('author.submissions.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman Naskah Saya dapat diakses dengan respons 200 OK ketika sudah terautentikasi.
     */
    public function test_halaman_naskah_saya_dapat_diakses(): void
    {
        $response = $this->get(route('author.submissions.index'));

        $response->assertStatus(200);
        $response->assertSee('Naskah Saya');
        $response->assertDontSee('(My Submissions)');
        $response->assertDontSee('PORTAL OJS 3.4');
        $response->assertDontSee('e-ISSN 2985-7821');
        $response->assertSee('BRIDA MAKASSAR');
        $response->assertSee('Portal Penulis');
        $response->assertSee('Unduh Rekap CSV');
        $response->assertSee('Tambah Naskah');
        $response->assertSee('Dr. Andi Muhammad Ilyas, M.Si.');
        $response->assertSee('andi.ilyas@unhas.ac.id');
        $response->assertDontSee('title="Masuk"');
        $response->assertSee('Keluar');

        // Ringkasan 4 Kartu Metrik Interaktif Flat
        $response->assertSee('Total Naskah');
        $response->assertSee('Perlu Revisi');
        $response->assertSee('Dalam Produksi');
        $response->assertSee('Telah Terbit');

        // Header Tabel Kolom Nomor, Judul, Seksi, Tanggal Submit, Pembaruan & Status Naskah
        $response->assertSee('NO');
        $response->assertSee('JUDUL ARTIKEL');
        $response->assertSee('SEKSI JURNAL');
        $response->assertSee('TANGGAL SUBMIT');
        $response->assertSee('PEMBARUAN');
        $response->assertDontSee('JUDUL ARTIKEL & PENULIS');
        $response->assertDontSee('Kata Kunci:');
        $response->assertDontSee('TAHAPAN ALUR');
        $response->assertSee('STATUS NASKAH');
        $response->assertDontSee('STATUS PUBLIKASI');
        $response->assertSee('Semua Seksi');

        // Tidak Boleh Ada Persentase pada Status
        $response->assertDontSee('%');

        // Daftar Naskah Hasil Seeding Riil
        $response->assertSee('SUB-2026-00104');
        $response->assertSee('Evaluasi Kebijakan Lorong Wisata');
        $response->assertSee('SUB-2026-00102');
        $response->assertSee('Analisis Efektivitas Insentif Pajak');
        $response->assertSee('SUB-2026-00103');
        $response->assertSee('Pemodelan Spasial Bahaya Banjir Rob');
        $response->assertSee('SUB-2026-00101');
        $response->assertSee('Inovasi Bank Sampah Digital');
        $response->assertSee('SUB-2026-00107');
        $response->assertSee('Strategi Transformasi Digital');

        $response->assertSee('Detail');
        $response->assertDontSee('Lanjutkan Draf');
        $response->assertDontSee('Tinjau Galai');
        $response->assertDontSee('Unggah Revisi');
        $response->assertDontSee('Tersimpan otomatis');
        $response->assertDontSee('Layout Editor BRIDA');
        $response->assertDontSee('Redaksi BRIDA');

        // Panduan Alur Kerja OJS 3 di Bagian Bawah
        $response->assertSee('Panduan Alur Kerja Editorial');
        $response->assertSee('1. Draf Submisi');
        $response->assertSee('2. Pemeriksaan Awal');
        $response->assertSee('3. Proses Telaah');
        $response->assertSee('4. Perlu Revisi');
        $response->assertSee('6. Telah Terbit');
    }

    /**
     * Filter tab menampilkan naskah sesuai kategori status
     */
    public function test_filter_tab_menampilkan_naskah_sesuai_kategori(): void
    {
        // Tab: Perlu Revisi
        $revResponse = $this->get(route('author.submissions.index', ['tab' => 'perlu-revisi']));
        $revResponse->assertStatus(200);
        $revResponse->assertSee('SUB-2026-00104');
        $revResponse->assertDontSee('SUB-2026-00107');

        // Tab: Draf
        $draftResponse = $this->get(route('author.submissions.index', ['tab' => 'draf']));
        $draftResponse->assertStatus(200);
        $draftResponse->assertSee('SUB-2026-00101');
        $draftResponse->assertDontSee('SUB-2026-00104');

        // Tab: Telah Terbit
        $publishedResponse = $this->get(route('author.submissions.index', ['tab' => 'terbit']));
        $publishedResponse->assertStatus(200);
        $publishedResponse->assertSee('SUB-2026-00107');
        $publishedResponse->assertDontSee('SUB-2026-00102');
    }

    /**
     * Pencarian kata kunci menyaring naskah berdasarkan judul atau ID.
     */
    public function test_pencarian_kata_kunci_menyaring_naskah(): void
    {
        // Cari berdasarkan judul
        $searchTitle = $this->get(route('author.submissions.index', ['search' => 'Lorong Wisata']));
        $searchTitle->assertStatus(200);
        $searchTitle->assertSee('SUB-2026-00104');
        $searchTitle->assertDontSee('SUB-2026-00103');

        // Cari berdasarkan ID Naskah
        $searchId = $this->get(route('author.submissions.index', ['search' => 'SUB-2026-00103']));
        $searchId->assertStatus(200);
        $searchId->assertSee('SUB-2026-00103');
        $searchId->assertDontSee('SUB-2026-00104');
    }

    /**
     * Filter tahun dan jenis artikel menyaring naskah dengan akurat tanpa istilah bahasa Inggris.
     */
    public function test_filter_tahun_dan_jenis_artikel(): void
    {
        // Filter tahun 2026
        $yearResponse = $this->get(route('author.submissions.index', ['year' => date('Y')]));
        $yearResponse->assertStatus(200);
        $yearResponse->assertSee('SUB-2026-00104');

        // Filter tipe Kajian Kebijakan Strategis
        $typeResponse = $this->get(route('author.submissions.index', ['type' => 'Kajian Kebijakan Strategis']));
        $typeResponse->assertStatus(200);
        $typeResponse->assertSee('SUB-2026-00102');
        $typeResponse->assertDontSee('SUB-2026-00104');
    }

    /**
     * Tampilan kondisi kosong muncul jika tidak ada naskah yang cocok dengan pencarian.
     */
    public function test_tampilan_kondisi_kosong_ketika_naskah_tidak_ditemukan(): void
    {
        $response = $this->get(route('author.submissions.index', ['search' => 'naskah-fiktif-tidak-ada-sama-sekali']));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Naskah Ditemukan');
        $response->assertSee('Reset Filter');
        $response->assertSee('Mulai Kirim Naskah Baru');
        $response->assertDontSee('SUB-2026-00124');
    }

    /**
     * Fitur unduh rekap CSV dapat diunduh dan menghasilkan payload data yang tepat.
     */
    public function test_ekspor_rekap_csv_dapat_diunduh(): void
    {
        $response = $this->get(route('author.submissions.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="rekap-naskah-penulis-brida-makassar-', (string) $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('ID Naskah', $content);
        $this->assertStringContainsString('Judul Artikel', $content);
        $this->assertStringContainsString('Status Naskah', $content);
        $this->assertStringNotContainsString('Tahapan Alur', $content);
        $this->assertStringNotContainsString('Detail Tahapan', $content);
        $this->assertStringNotContainsString('Tersimpan otomatis', $content);
        $this->assertStringContainsString('SUB-2026-00104', $content);
        $this->assertStringContainsString('Evaluasi Kebijakan Lorong Wisata', $content);
        $this->assertStringContainsString('SUB-2026-00107', $content);
    }

    /**
     * Rute /submissions dan /dashboard mengarahkan pengguna ke halaman naskah penulis.
     */
    public function test_route_redirect_ke_author_submissions(): void
    {
        $subRedirect = $this->get('/submissions');
        $subRedirect->assertRedirect('/author/submissions');

        $dashRedirect = $this->get('/dashboard');
        $dashRedirect->assertRedirect('/author/submissions');
    }

    /**
     * Halaman memiliki markup aksesibilitas ARIA tooltip, viewport dinamis dvh, dan transformasi kartu mobile sesuai guide-code.md.
     */
    public function test_halaman_naskah_memiliki_aksesibilitas_aria_dan_tata_letak_responsif(): void
    {
        $response = $this->get(route('author.submissions.index'));

        $response->assertStatus(200);
        $response->assertSee('min-h-dvh', false);
        $response->assertSee('role="tooltip"', false);
        $response->assertSee('aria-describedby=', false);
        $response->assertSee('block sm:hidden', false);
        $response->assertSee('hidden sm:block', false);
        $response->assertSee('tabular-nums', false);
        $response->assertSee('sidebar-collapse-toggle-btn');
    }

    /**
     * Penomoran urut baris dihitung dinamis dan optimal mengikuti paginasi tanpa terikat ke ID naskah.
     */
    public function test_penomoran_urut_tabel_dinamis_dan_optimal(): void
    {
        $response = $this->get(route('author.submissions.index'));
        $response->assertStatus(200);
        $response->assertSee('NO');
        $response->assertSee('#1');

        // Halaman 2: memastikan penomoran urut berlanjut (misal: 11) jika page=2
        $page2Response = $this->get(route('author.submissions.index', ['page' => 2]));
        $page2Response->assertStatus(200);
        $page2Response->assertSee('#11');
    }

    /**
     * Permintaan filter AJAX mengembalikan payload JSON teroptimasi untuk transisi mulus tanpa reload halaman.
     */
    public function test_filter_ajax_mengembalikan_json_dengan_transisi_optimal(): void
    {
        // Query persis sesuai skenario: tab=aktif, search=Banjir Rob, year=semua, type=semua, sort=-updated_at
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get(route('author.submissions.index', [
            'tab' => 'aktif',
            'search' => 'Banjir Rob',
            'year' => 'semua',
            'type' => 'semua',
            'sort' => '-updated_at',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'table_html',
            'export_url',
            'tabs',
            'total',
            'filters',
            'has_filters',
        ]);

        $responseData = $response->json();
        $this->assertEquals(1, $responseData['total']);
        $this->assertTrue($responseData['has_filters']);
        $this->assertStringContainsString('SUB-2026-00103', $responseData['table_html']);
        $this->assertStringContainsString('Pemodelan Spasial Bahaya Banjir Rob', $responseData['table_html']);
        $this->assertStringNotContainsString('SUB-2026-00104', $responseData['table_html']);
        $this->assertStringContainsString('export-csv', $responseData['export_url']);
    }

    /**
     * Memastikan naskah dari SubmissionSeeder terpetakan secara tepat ke seluruh tab kategori filter.
     */
    public function test_seeder_memetakan_naskah_ke_tab_filter_secara_tepat(): void
    {
        // Tab Draf
        $respDraft = $this->get(route('author.submissions.index', ['tab' => 'draf']));
        $respDraft->assertStatus(200);
        $respDraft->assertSee('SUB-2026-00101');
        $respDraft->assertDontSee('SUB-2026-00102');

        // Tab Perlu Revisi
        $respRevisi = $this->get(route('author.submissions.index', ['tab' => 'perlu-revisi']));
        $respRevisi->assertStatus(200);
        $respRevisi->assertSee('SUB-2026-00104');
        $respRevisi->assertSee('SUB-2026-00105');

        // Tab Produksi
        $respProd = $this->get(route('author.submissions.index', ['tab' => 'produksi']));
        $respProd->assertStatus(200);
        $respProd->assertSee('SUB-2026-00106');

        // Tab Terbit
        $respTerbit = $this->get(route('author.submissions.index', ['tab' => 'terbit']));
        $respTerbit->assertStatus(200);
        $respTerbit->assertSee('SUB-2026-00107');

        // Tab Arsip & Ditolak
        $respArsip = $this->get(route('author.submissions.index', ['tab' => 'arsip']));
        $respArsip->assertStatus(200);
        $respArsip->assertSee('SUB-2026-00108');
        $respArsip->assertSee('SUB-2026-00109');
    }

    /**
     * Memastikan empty state tampil dengan ramah ketika database tidak memiliki naskah.
     */
    public function test_empty_state_tampil_ketika_belum_ada_naskah(): void
    {
        \App\Models\Submission::query()->delete();

        $response = $this->get(route('author.submissions.index'));
        $response->assertStatus(200);
        $response->assertSee('Belum Ada Naskah Ditemukan');
        $response->assertSee('Mulai Kirim Naskah Baru');
    }
}
