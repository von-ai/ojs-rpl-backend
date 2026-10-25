<?php

declare(strict_types=1);

namespace Tests\Feature\Proofreader;

use Tests\TestCase;

class ProofreaderWorkspaceTest extends TestCase
{
    /**
     * Ruang kerja proofreader memuat detail naskah, galai PDF, dan naskah acuan copyediting.
     */
    public function test_ruang_kerja_memuat_informasi_naskah_dan_berkas_rujukan(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('SUB-2026-00106');
        $response->assertSee('Benteng Somba Opu');
        $response->assertSee('Dr. Andi Nurul Hidayah, M.Si.');
        $response->assertSee('Rahmat Hidayat, S.Kom.');

        // Berkas galai PDF dan berkas rujukan copyedit
        $response->assertSee('SUB-2026-00106_Galley_v2.pdf');
        $response->assertSee('Unduh Galai PDF');
        $response->assertSee('SUB-2026-00106_Copyedited_Final.docx');
        $response->assertSee('Unduh Acuan Teks Final (.docx)');
    }

    /**
     * Ruang kerja memuat formulir cepat penambahan catatan koreksi dan daftar catatan aktif.
     */
    public function test_ruang_kerja_memuat_form_dan_daftar_catatan_koreksi(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('Tambah Catatan Koreksi Galai');
        $response->assertSee('Kirim Catatan Koreksi');

        // Catatan koreksi dengan kategori dan keparahan
        $response->assertSee('NOTE-01JARK-01');
        $response->assertSee('Tipografi / Ejaan');
        $response->assertSee('NOTE-01JARK-02');
        $response->assertSee('Tata Letak / Format');
        $response->assertSee('NOTE-01JARK-03');
        $response->assertSee('Sitasi');
        $response->assertSee('Pustaka');
        $response->assertSee('Kritis');
    }

    /**
     * Resolusi dari Layout Editor dan tombol verifikasi muncul pada catatan yang telah diperbaiki.
     */
    public function test_resolusi_layout_editor_dan_tombol_verifikasi_tersedia(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        // Note 1 sudah diverifikasi
        $response->assertSee('Terverifikasi Proofreader');
        // Note 2 siap diverifikasi
        $response->assertSee('Tindakan Layout Editor', false);
        $response->assertSee('Verifikasi Sesuai');
    }

    /**
     * Guard rail aktif: Tombol sign-off terkunci jika masih ada catatan kritis yang belum tuntas.
     */
    public function test_guard_rail_mengunci_signoff_jika_ada_catatan_terbuka(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('Sign-off Ditangguhkan');
        $response->assertSee('Sign-off Terkunci (Ada Catatan Terbuka)');
        $response->assertSee('Terdapat 1 catatan berkategori Kritis');
    }

    /**
     * Guard rail lolos: Naskah bersih dapat langsung disetujui (sign-off).
     */
    public function test_naskah_bersih_dapat_diberikan_persetujuan_signoff(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00107');

        $response->assertStatus(200);
        $response->assertSee('Kelaikan Galai Terpenuhi');
        $response->assertSee('Setujui');
        $response->assertSee('Selesaikan Proofreading');
    }

    /**
     * Naskah yang sudah selesai menampilkan konfirmasi status selesai.
     */
    public function test_naskah_selesai_menampilkan_konfirmasi_signoff(): void
    {
        $response = $this->get('/proofreader/submissions/SUB-2026-00103');

        $response->assertStatus(200);
        $response->assertSee('Proofreading Selesai');
        $response->assertSee('Disetujui');
    }
}
