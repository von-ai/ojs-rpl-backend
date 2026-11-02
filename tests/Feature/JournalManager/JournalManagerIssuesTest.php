<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use App\Models\Issue;
use App\Models\Journal;
use App\Models\User;
use Database\Seeders\JournalManagerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalManagerIssuesTest extends TestCase
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

    public function test_tamu_tidak_bisa_mengakses_portal_journal_manager_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/journalmanager/issues');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman katalog edisi terbitan menampilkan 4 kartu metrik, tabel edisi, dan modal buat baru.
     */
    public function test_katalog_edisi_terbitan_menampilkan_metrik_dan_daftar_edisi(): void
    {
        $response = $this->get('/journalmanager/issues');

        $response->assertStatus(200);
        $response->assertSee('Edisi');
        $response->assertSee('Terbitan Berkala');
        $response->assertSee('Buat Edisi Baru');

        // 4 Kartu Metrik
        $response->assertSee('Edisi Terbit Resmi');
        $response->assertSee('Draf & Terjadwal');
        $response->assertSee('Artikel Siap Terbit');
        $response->assertSee('Total Artikel Terbit');

        // Data Edisi
        $response->assertSee('Vol. 6 No. 2 (2026)');
        $response->assertSee('Vol. 6 No. 1 (2026)');
        $response->assertSee('Current Issue');
        $response->assertSee('Daftar Isi');

        // Modal Form Buat Edisi Baru
        $response->assertSee('Buat Edisi Nomor Terbitan Baru');
        $response->assertSee('Simpan Draf Edisi');
    }

    /**
     * Filter tab status edisi terbitan berfungsi memilah edisi.
     */
    public function test_filter_tab_status_edisi_terbitan(): void
    {
        $journal = Journal::first();
        Issue::create([
            'journal_id' => $journal->id,
            'volume' => '7',
            'number' => '1',
            'year' => 2027,
            'title' => 'Vol. 7 No. 1 (2027): Edisi Terjadwal',
            'status' => 'scheduled',
            'is_current' => false,
            'doi_prefix' => '10.61234/sirida.v7i1',
        ]);

        // Tab All: menampilkan semua edisi
        $responseAll = $this->get('/journalmanager/issues?tab=all');
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Vol. 6 No. 2 (2026)');
        $responseAll->assertSee('Vol. 6 No. 1 (2026)');
        $responseAll->assertSee('Vol. 5 No. 2 (2025)');

        // Tab Published: hanya edisi yang telah terbit resmi
        $responsePublished = $this->get('/journalmanager/issues?tab=published');
        $responsePublished->assertStatus(200);
        $responsePublished->assertSee('Vol. 6 No. 1 (2026)');
        $responsePublished->assertSee('Vol. 5 No. 2 (2025)');
        $responsePublished->assertDontSee('Vol. 6 No. 2 (2026)');

        // Tab Future: draf dan terjadwal
        $responseFuture = $this->get('/journalmanager/issues?tab=future');
        $responseFuture->assertStatus(200);
        $responseFuture->assertSee('Vol. 6 No. 2 (2026)');
        $responseFuture->assertSee('Vol. 7 No. 1 (2027)');
        $responseFuture->assertDontSee('Vol. 6 No. 1 (2026)');
    }

    /**
     * Fitur pencarian edisi berdasarkan volume, nomor, tahun, atau judul.
     */
    public function test_pencarian_edisi_terbitan(): void
    {
        // Cari judul edisi
        $resSearch1 = $this->get('/journalmanager/issues?q=Ketahanan+Perkotaan');
        $resSearch1->assertStatus(200);
        $resSearch1->assertSee('Vol. 6 No. 2 (2026)');
        $resSearch1->assertDontSee('Vol. 5 No. 2 (2025)');

        // Cari tahun
        $resSearch2 = $this->get('/journalmanager/issues?q=2025');
        $resSearch2->assertStatus(200);
        $resSearch2->assertSee('Vol. 5 No. 2 (2025)');
        $resSearch2->assertSee('Vol. 5 No. 1 (2025)');
        $resSearch2->assertDontSee('Vol. 6 No. 2 (2026)');

        // Pencarian tidak ditemukan
        $resEmpty = $this->get('/journalmanager/issues?q=TahunTidakAda9999');
        $resEmpty->assertStatus(200);
        $resEmpty->assertSee('Tidak ada edisi terbitan');
        $resEmpty->assertSee('Reset Filter');
    }

    /**
     * Ruang kerja penyusunan edisi draf memuat daftar isi artikel dan antrean naskah siap terbit.
     */
    public function test_ruang_kerja_penyusunan_edisi_draf(): void
    {
        $response = $this->get('/journalmanager/issues/ISSUE-01JARK960A');

        $response->assertStatus(200);
        $response->assertSee('Vol. 6 No. 2 (2026)');
        $response->assertSee('Draf Kurasi Naskah');
        $response->assertSee('10.61234/sirida.v6i2');

        // Daftar Isi Naskah
        $response->assertSee('Daftar Isi Edisi');
        $response->assertSee('Efektivitas Program Lorong Wisata');
        $response->assertSee('Halaman: 1-14');
        $response->assertSee('Inovasi Pelayanan Kesehatan Terpadu');
        $response->assertSee('Halaman: 15-28');
        $response->assertSee('Unduh Galai PDF');

        // Antrean Naskah Siap Terbit
        $response->assertSee('Antrean Naskah Siap Terbit');
        $response->assertSee('Model Partisipasi Pemuda');
        $response->assertSee('Masukkan ke Edisi');

        // Otorisasi Publikasi
        $response->assertSee('Publikasikan Edisi Sekarang');
        $response->assertSee('Linimasa Edisi Terbitan');
    }

    /**
     * Ruang kerja edisi yang telah terbit resmi menampilkan status published dan konfirmasi.
     */
    public function test_ruang_kerja_edisi_terbit_resmi(): void
    {
        $response = $this->get('/journalmanager/issues/ISSUE-01JARK961B');

        $response->assertStatus(200);
        $response->assertSee('Vol. 6 No. 1 (2026)');
        $response->assertSee('Terbit (Current Issue)');
        $response->assertSee('Edisi Telah Terbit Resmi');
        $response->assertSee('Studi Komparasi Efisiensi Pelayanan Perizinan');
    }

    /**
     * Aksi transaksional penambahan artikel, publikasi, dan current issue.
     */
    public function test_aksi_transaksional_manajemen_edisi(): void
    {
        // 1. Tambah artikel ke edisi
        $resAdd = $this->post('/journalmanager/issues/ISSUE-01JARK960A/add-article', [
            'article_title' => 'Model Partisipasi Pemuda',
        ]);
        $resAdd->assertRedirect('/journalmanager/issues/ISSUE-01JARK960A');
        $resAdd->assertSessionHas('status', "Naskah 'Model Partisipasi Pemuda' berhasil ditambahkan ke daftar isi edisi.");

        // 2. Publikasikan edisi
        $resPublish = $this->post('/journalmanager/issues/ISSUE-01JARK960A/publish');
        $resPublish->assertRedirect('/journalmanager/issues/ISSUE-01JARK960A');
        $resPublish->assertSessionHas('status', 'Edisi terbitan berkala resmi dipublikasikan ke pembaca dan katalog indeks.');

        // 3. Tetapkan current issue
        $resCurrent = $this->post('/journalmanager/issues/ISSUE-01JARK960A/set-current');
        $resCurrent->assertRedirect('/journalmanager/issues');
        $resCurrent->assertSessionHas('status', 'Edisi berhasil ditetapkan sebagai Terbitan Berjalan (Current Issue) jurnal.');
    }
}
