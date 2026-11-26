<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use App\Models\Journal;
use App\Models\User;
use Database\Seeders\JournalManagerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JournalManagerSettingsTest extends TestCase
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

    public function test_tamu_tidak_bisa_mengakses_pengaturan_journal_manager_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/journalmanager/settings');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman seksi naskah (sections) menampilkan 3 kartu metrik dan tabel bidang riset.
     */
    public function test_halaman_bidang_naskah_sections_menampilkan_metrik_dan_daftar(): void
    {
        $response = $this->get('/journalmanager/sections');

        $response->assertStatus(200);
        $response->assertSee('Bidang Naskah');
        $response->assertSee('Seksi Penelitian');
        $response->assertSee('Tambah Bidang Naskah');

        // 3 Kartu Metrik
        $response->assertSee('Total Bidang Naskah');
        $response->assertSee('Aktif Menerima Naskah');
        $response->assertSee('Seksi Dinonaktifkan');

        // Daftar Seksi
        $response->assertSee('Inovasi Daerah &amp; Kebijakan Publik', false);
        $response->assertSee('Sosial Humaniora &amp; Tata Kelola', false);
        $response->assertSee('Teknologi &amp; Infrastruktur Berkelanjutan', false);
        $response->assertSee('Menerima Naskah');
        $response->assertSee('Pengajuan Ditutup');

        // Modal Form Buat Seksi
        $response->assertSee('Tambah Bidang Naskah Baru');
        $response->assertSee('Simpan Bidang Naskah');
    }

    /**
     * Halaman pengaturan jurnal menampilkan form identitas, kebijakan editorial, dan pengumuman publik.
     */
    public function test_halaman_pengaturan_jurnal_menampilkan_identitas_kebijakan_dan_pengumuman(): void
    {
        $response = $this->get('/journalmanager/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Jurnal');
        $response->assertSee('Kebijakan Editorial');

        // Metadata Identitas
        $response->assertSee('Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)');
        $response->assertSee('2746-8194'); // p-ISSN
        $response->assertSee('2746-8208'); // e-ISSN
        $response->assertSee('jurnal.brida@makassar.go.id');

        // Kebijakan Publikasi
        $response->assertSee('Gold Open Access');
        $response->assertSee('CC BY 4.0');
        $response->assertSee('Double-Blind Peer Review');

        // Pengumuman Warta Publik
        $response->assertSee('Panggilan Pengajuan Naskah');
        $response->assertSee('Simpan Perubahan Pengaturan');
    }

    /**
     * Aksi transaksional penambahan bidang naskah dan pembaharuan pengaturan jurnal.
     */
    public function test_aksi_transaksional_simpan_seksi_dan_update_pengaturan(): void
    {
        // 1. Simpan bidang naskah baru
        $resSection = $this->post('/journalmanager/sections', [
            'name' => 'Tata Kelola Perkotaan & Smart Governance',
        ]);
        $resSection->assertRedirect('/journalmanager/sections');
        $resSection->assertSessionHas('status', "Bidang naskah 'Tata Kelola Perkotaan & Smart Governance' berhasil ditambahkan ke jurnal.");

        // 2. Simpan pembaharuan pengaturan jurnal
        $resSettings = $this->post('/journalmanager/settings');
        $resSettings->assertRedirect('/journalmanager/settings');
        $resSettings->assertSessionHas('status', 'Pengaturan identitas dan kebijakan jurnal berhasil disimpan.');
    }

    public function test_journal_manager_dapat_mengunggah_mengunduh_dan_menghapus_template_panduan_penulisan(): void
    {
        Storage::fake();

        $file = UploadedFile::fake()->create(
            'Template-Resmi-SIRIDA-2026.docx',
            128,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        $uploadResponse = $this->post(route('journalmanager.settings.template.upload'), [
            'guideline_file' => $file,
        ]);

        $uploadResponse->assertRedirect(route('journalmanager.settings.index'));
        $uploadResponse->assertSessionHas('status');

        $journal = Journal::query()->firstOrFail();
        $this->assertNotNull($journal->author_guideline_path);
        $this->assertSame('Template-Resmi-SIRIDA-2026.docx', $journal->author_guideline_filename);
        Storage::disk()->assertExists($journal->author_guideline_path);

        $settingsPage = $this->get(route('journalmanager.settings.index'));
        $settingsPage->assertStatus(200);
        $settingsPage->assertSee('Template-Resmi-SIRIDA-2026.docx');
        $settingsPage->assertSee('Template Aktif');

        $publicGuide = $this->get(route('journal.scoped.about.submissions', ['journalSlug' => $journal->slug]));
        $publicGuide->assertStatus(200);
        $publicGuide->assertSee('Template-Resmi-SIRIDA-2026.docx');

        $downloadResponse = $this->get(route('journal.scoped.about.submissions.template', ['journalSlug' => $journal->slug]));
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-disposition');
        $this->assertStringContainsString(
            'Template-Resmi-SIRIDA-2026.docx',
            (string) $downloadResponse->headers->get('content-disposition')
        );

        $storedPath = (string) $journal->author_guideline_path;

        $deleteResponse = $this->delete(route('journalmanager.settings.template.destroy'));
        $deleteResponse->assertRedirect(route('journalmanager.settings.index'));
        $deleteResponse->assertSessionHas('status');

        $journal->refresh();
        $this->assertNull($journal->author_guideline_path);
        $this->assertNull($journal->author_guideline_filename);
        Storage::disk()->assertMissing($storedPath);
    }

    public function test_upload_template_panduan_menolak_format_berkas_tidak_diizinkan(): void
    {
        Storage::fake();

        $invalidFile = UploadedFile::fake()->create('script-berbahaya.php', 10, 'application/x-httpd-php');

        $response = $this->post(route('journalmanager.settings.template.upload'), [
            'guideline_file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('guideline_file');
    }
}
