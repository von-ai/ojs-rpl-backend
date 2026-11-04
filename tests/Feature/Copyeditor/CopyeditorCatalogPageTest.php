<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\ProductionEscalation;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\CopyeditSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CopyeditorCatalogPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $copyeditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->copyeditor = User::firstOrCreate(
            ['email' => 'andi.kurniawan@brida.makassar.go.id'],
            [
                'username' => 'andi.kurniawan',
                'name' => 'Andi Kurniawan, S.Hum., M.Pd.',
                'full_name' => 'Andi Kurniawan, S.Hum., M.Pd.',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        UserRole::firstOrCreate([
            'user_id' => $this->copyeditor->id,
            'role_code' => 'copyeditor',
        ]);

        $this->seed(CopyeditSeeder::class);

        $this->actingAs($this->copyeditor);
    }

    public function test_tamu_tidak_bisa_mengakses_portal_copyeditor_dan_dialihkan_ke_login(): void
    {
        auth()->logout();

        $response = $this->get('/copyeditor/submissions');
        $response->assertRedirect(route('login'));
    }

    /**
     * Halaman daftar penugasan copyediting dapat diakses dengan UX yang simpel dan bersih.
     */
    public function test_halaman_daftar_penugasan_copyeditor_dapat_diakses(): void
    {
        $response = $this->get('/copyeditor/submissions');

        $response->assertStatus(200);
        $response->assertSee('Penugasan Naskah Copyediting');
        $response->assertSee('SUB-2026-00106');
        $response->assertSee('Dr. Andi Muhammad Ilyas');
        $response->assertSee('Buka Naskah');

        // Memastikan sidebar ringkas hanya memuat menu penting
        $response->assertSee('Penugasan Naskah');
        $response->assertSee('Panduan Selingkung');
        $response->assertDontSee('Modal Dialog Aktif');
        $response->assertDontSee('Dashboard Ringkasan');
    }

    /**
     * Halaman dedikasi panduan gaya selingkung dapat diakses dan memuat template Word.
     */
    public function test_halaman_panduan_gaya_selingkung_dapat_diakses(): void
    {
        $response = $this->get('/copyeditor/guidelines');

        $response->assertStatus(200);
        $response->assertSee('Panduan Gaya Selingkung');
        $response->assertSee('Format Jurnal');
        $response->assertSee('EYD Edisi V');
        $response->assertSee('APA Style 7th Edition');
        $response->assertSee('Unduh Template Word (.docx)');
    }

    /**
     * Halaman ruang kerja naskah copyeditor dapat diakses dengan data naskah in-progress.
     */
    public function test_halaman_ruang_kerja_naskah_in_progress(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('Penerapan Sensor IoT dan Telemetri Kualitas Udara');
        $response->assertSee('Unduh Naskah Asli (.docx)');
        $response->assertSee('Berkas Hasil Suntingan');
        $response->assertSee('Persetujuan Penulis');
        $response->assertSee('Serah Terima ke Layout Editor');
        $response->assertSee('Panduan Gaya Selingkung Jurnal');
        $response->assertSee('Permohonan Mediasi Redaksi');
        $response->assertSee('Konfirmasi Serah Terima ke Layout');
    }

    /**
     * Halaman ruang kerja naskah dengan status eskalasi aktif (SUB-2026-00108).
     */
    public function test_halaman_ruang_kerja_naskah_dengan_eskalasi_aktif(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00108');

        $response->assertStatus(200);
        $response->assertSee('Analisis Makroekonomi dan Dampak Fluktuasi Nilai Tukar');
        $response->assertSee('Dalam Mediasi Redaksi');
        $response->assertSee('ESC-2026-0042');
        $response->assertSee('Tiket Mediasi Redaksi Aktif');
        $response->assertSee('Prof. Hamka, Ph.D.');
    }

    /**
     * Halaman ruang kerja naskah dengan resolusi mediasi selesai diputuskan oleh Section Editor (SUB-2026-00107).
     */
    public function test_halaman_ruang_kerja_naskah_resolusi_mediasi_selesai(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00107');

        $response->assertStatus(200);
        $response->assertSee('Strategi Transformasi Digital Birokrasi Pelayanan Publik Terpadu');
        $response->assertSee('Keputusan Mediasi Section Editor');
        $response->assertSee('Selesai Diputuskan');
    }

    /**
     * Halaman ruang kerja naskah dengan status disetujui penulis dan siap serah terima (SUB-2026-00109).
     */
    public function test_halaman_ruang_kerja_naskah_siap_serah_terima(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00109');

        $response->assertStatus(200);
        $response->assertSee('Observasi Awal Distribusi Mikroplastik');
        $response->assertSee('Disetujui Penulis');
        $response->assertSee('Penulis Telah Menyetujui Naskah');
    }

    /**
     * Halaman ruang kerja naskah yang telah selesai diserahterimakan / Mode Arsip (SUB-2026-00110).
     */
    public function test_halaman_ruang_kerja_naskah_mode_arsip_selesai(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00110');

        $response->assertStatus(200);
        $response->assertSee('Strategi Pengelolaan Sampah Berbasis Komunitas');
        $response->assertSee('Mode Arsip');
        $response->assertSee('Serah Terima Selesai');
        $response->assertSee('Tahap Copyediting Rampung');
        $response->assertSee('Rahmat Hidayat, S.Kom.');

        // Memastikan form upload tidak muncul pada mode arsip
        $response->assertDontSee('Unggah / Ganti Berkas Suntingan Baru:');
    }

    /**
     * Pengujian aksi form transaksional menghasilkan flash message dan pengalihan ke ruang kerja naskah.
     */
    public function test_aksi_copyeditor_tanpa_berkas_ditolak_tanpa_pesan_sukses(): void
    {
        $response = $this->from('/copyeditor/submissions/SUB-2026-00106')->post('/copyeditor/upload', ['submission_id' => 'SUB-2026-00106']);
        $response->assertRedirect('/copyeditor/submissions/SUB-2026-00106')->assertSessionHasErrors('copyedit_file')->assertSessionMissing('status');
    }

    /**
     * Pengujian filter tab Sedang Disunting menampilkan naskah in-progress dan approved secara konsisten.
     */
    public function test_halaman_daftar_penugasan_filter_tab_sedang_disunting_konsisten(): void
    {
        $response = $this->get('/copyeditor/submissions?tab=editing');

        $response->assertStatus(200);
        $response->assertSee('Sedang Disunting');
        $response->assertSee('SUB-2026-00107');
        $response->assertSee('SUB-2026-00109');
        $response->assertSee('SUB-2026-00111');
        $response->assertDontSee('SUB-2026-00106');
        $response->assertDontSee('SUB-2026-00108');
        $response->assertDontSee('SUB-2026-00110');
    }

    /**
     * Halaman ruang kerja naskah dengan status catatan revisi penulis / revision requested (SUB-2026-00111).
     * Memastikan banner peringatan revisi penulis dan tombol Minta Mediasi Editor tampil dengan benar.
     */
    public function test_halaman_ruang_kerja_naskah_dengan_catatan_revisi_penulis(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00111');

        $response->assertStatus(200);
        $response->assertSee('Model Optimasi Rute Distribusi Logistik Maritim');
        $response->assertSee('Permintaan Revisi Penulis');
        $response->assertSee('Penulis Mengajukan Catatan Revisi');
        $response->assertSee('Minta Mediasi Editor');
        $response->assertSee('Hub-and-Spoke Maritim');
        $response->assertSee('openEscalateModal()');
    }

    /**
     * Memastikan empty state tampil dengan ramah ketika database tidak memiliki penugasan copyediting.
     */
    public function test_empty_state_tampil_ketika_belum_ada_penugasan_copyedit(): void
    {
        CopyeditApprovedFile::query()->delete();
        CopyeditApproval::query()->delete();
        CopyeditOutput::query()->delete();
        ProductionEscalation::query()->delete();
        Copyedit::query()->delete();

        $response = $this->get('/copyeditor/submissions');
        $response->assertStatus(200);
        $response->assertSee('Tidak ada naskah ditemukan');
    }
}
