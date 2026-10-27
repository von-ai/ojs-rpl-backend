<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use Tests\TestCase;

class CopyeditorCatalogPageTest extends TestCase
{
    /**
     * Halaman daftar penugasan copyediting dapat diakses dengan UX yang simpel dan bersih.
     */
    public function test_halaman_daftar_penugasan_copyeditor_dapat_diakses(): void
    {
        $response = $this->get('/copyeditor/submissions');

        $response->assertStatus(200);
        $response->assertSee('Penugasan Naskah Copyediting');
        $response->assertSee('SUB-2026-00106');
        $response->assertSee('Dr. Nurfadillah Bahar');
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
        $response->assertSee('Analisis Spasial Ruang Terbuka Hijau');
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
        $response->assertSee('Kajian Karakteristik Geoteknik Sedimen');
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
        $response->assertSee('Transformasi Digital Pelayanan Publik Kelurahan');
        $response->assertSee('Keputusan Mediasi Section Editor');
        $response->assertSee('Dr. Hj. St. Rohani, M.Si.');
        $response->assertSee('Selesai Diputuskan');
    }

    /**
     * Halaman ruang kerja naskah dengan status disetujui penulis dan siap serah terima (SUB-2026-00109).
     */
    public function test_halaman_ruang_kerja_naskah_siap_serah_terima(): void
    {
        $response = $this->get('/copyeditor/submissions/SUB-2026-00109');

        $response->assertStatus(200);
        $response->assertSee('Efektivitas Program Lorong Wisata');
        $response->assertSee('Disetujui Penulis');
        $response->assertSee('Penulis Telah Menyetujui Naskah');
        $response->assertSee('Hj. Fatmawati, S.E., M.Si.');
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
    public function test_aksi_transaksional_copyeditor_mengembalikan_redirect_dengan_status(): void
    {
        $responseUpload = $this->post('/copyeditor/upload', ['submission_id' => 'SUB-2026-00106']);
        $responseUpload->assertRedirect('/copyeditor/submissions/SUB-2026-00106');
        $responseUpload->assertSessionHas('status');

        $responseApproval = $this->post('/copyeditor/request-approval', ['submission_id' => 'SUB-2026-00106']);
        $responseApproval->assertRedirect('/copyeditor/submissions/SUB-2026-00106');
        $responseApproval->assertSessionHas('status');

        $responseEscalate = $this->post('/copyeditor/escalate', ['submission_id' => 'SUB-2026-00106']);
        $responseEscalate->assertRedirect('/copyeditor/submissions/SUB-2026-00106');
        $responseEscalate->assertSessionHas('status');

        $responseHandoff = $this->post('/copyeditor/complete-handoff', ['submission_id' => 'SUB-2026-00106']);
        $responseHandoff->assertRedirect('/copyeditor/submissions/SUB-2026-00106');
        $responseHandoff->assertSessionHas('status');
    }

    /**
     * Pengujian filter tab Sedang Disunting menampilkan naskah in-progress dan approved secara konsisten.
     */
    public function test_halaman_daftar_penugasan_filter_tab_sedang_disunting_konsisten(): void
    {
        $response = $this->get('/copyeditor/submissions?tab=editing');

        $response->assertStatus(200);
        $response->assertSee('Sedang Disunting');
        $response->assertSee('SUB-2026-00106');
        $response->assertSee('SUB-2026-00109');
        $response->assertDontSee('SUB-2026-00107');
        $response->assertDontSee('SUB-2026-00108');
        $response->assertDontSee('SUB-2026-00110');
    }
}
