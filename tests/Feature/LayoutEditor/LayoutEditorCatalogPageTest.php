<?php

declare(strict_types=1);

namespace Tests\Feature\LayoutEditor;

use Tests\TestCase;

class LayoutEditorCatalogPageTest extends TestCase
{
    /**
     * Halaman daftar penugasan layout editor dapat diakses dengan UX yang simpel dan bersih.
     */
    public function test_halaman_daftar_penugasan_layout_editor_dapat_diakses(): void
    {
        $response = $this->get('/layouteditor/submissions');

        $response->assertStatus(200);
        $response->assertSee('Penugasan Galai');
        $response->assertSee('Tata Letak Publikasi');
        $response->assertSee('Kelola naskah yang diteruskan dari copyediting');

        // Sidebar ringkas & profil
        $response->assertSee('Penugasan Galai');
        $response->assertSee('Panduan Galai');
        $response->assertSee('Rahmat Hidayat, S.Kom.');
        $response->assertSee('Layout Editor');

        // 4 Kartu Metrik
        $response->assertSee('Total Penugasan Layout');
        $response->assertSee('8 Naskah');
        $response->assertSee('Perlu Draf Galai');
        $response->assertSee('Koreksi Proofreading');
        $response->assertSee('Galai Siap Terbit');
        $response->assertSee('Disetujui EIC');

        // Header Kolom Tabel
        $response->assertSee('Informasi Naskah');
        $response->assertSee('Format Galai');
        $response->assertSee('Proofreader');
        $response->assertSee('Batas Waktu');
        $response->assertSee('Status');
        $response->assertSee('Aksi');

        // Baris Naskah
        $response->assertSee('SUB-2026-00106');
        $response->assertSee('Penerapan Sensor IoT dan Telemetri Kualitas Udara');
        $response->assertSee('Dr. Andi Muhammad Ilyas');
        $response->assertSee('Buka Galai');
    }

    /**
     * Filter tab status penugasan berfungsi memilah naskah.
     */
    public function test_filter_tab_penugasan_layout(): void
    {
        // Tab All: memuat semua naskah
        $responseAll = $this->get('/layouteditor/submissions?tab=all');
        $responseAll->assertStatus(200);
        $responseAll->assertSee('SUB-2026-00106');
        $responseAll->assertSee('SUB-2026-00107');
        $responseAll->assertSee('SUB-2026-00109');

        // Tab Drafting: naskah yang perlu draf galai PDF awal
        $responseDrafting = $this->get('/layouteditor/submissions?tab=drafting');
        $responseDrafting->assertStatus(200);
        $responseDrafting->assertSee('SUB-2026-00106');
        $responseDrafting->assertSee('SUB-2026-00110');
        $responseDrafting->assertDontSee('SUB-2026-00107');
        $responseDrafting->assertDontSee('SUB-2026-00109');

        // Tab Proofreading: naskah dengan catatan koreksi proofreader
        $responseProofreading = $this->get('/layouteditor/submissions?tab=proofreading');
        $responseProofreading->assertStatus(200);
        $responseProofreading->assertSee('SUB-2026-00107');
        $responseProofreading->assertSee('SUB-2026-00108');
        $responseProofreading->assertDontSee('SUB-2026-00106');
        $responseProofreading->assertDontSee('SUB-2026-00109');

        // Tab Ready: naskah yang siap diterbitkan
        $responseReady = $this->get('/layouteditor/submissions?tab=ready');
        $responseReady->assertStatus(200);
        $responseReady->assertSee('SUB-2026-00109');
        $responseReady->assertSee('SUB-2026-00111');
        $responseReady->assertDontSee('SUB-2026-00106');
        $responseReady->assertDontSee('SUB-2026-00107');
    }

    /**
     * Fitur pencarian naskah berdasarkan judul dan nama penulis.
     */
    public function test_pencarian_naskah_layout(): void
    {
        // Pencarian dengan judul naskah
        $responseSearch1 = $this->get('/layouteditor/submissions?q=Sensor+IoT');
        $responseSearch1->assertStatus(200);
        $responseSearch1->assertSee('SUB-2026-00106');
        $responseSearch1->assertDontSee('SUB-2026-00107');
        $responseSearch1->assertDontSee('SUB-2026-00108');

        // Pencarian dengan nama penulis
        $responseSearch2 = $this->get('/layouteditor/submissions?q=Muhammad+Basri');
        $responseSearch2->assertStatus(200);
        $responseSearch2->assertSee('SUB-2026-00107');
        $responseSearch2->assertDontSee('SUB-2026-00106');

        // Pencarian yang tidak menemukan hasil (Empty State)
        $responseEmpty = $this->get('/layouteditor/submissions?q=KataKunciPalsuTidakAda');
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertSee('Tidak ada penugasan galai');
        $responseEmpty->assertSee('Reset Filter');
    }

    /**
     * Memastikan badge catatan koreksi proofreading dan batas waktu mendekati tampil akurat.
     */
    public function test_tampilan_indikator_catatan_dan_urgensi(): void
    {
        $response = $this->get('/layouteditor/submissions');
        $response->assertStatus(200);

        // Naskah dengan catatan koreksi proofreading
        $response->assertSee('2 Catatan Belum Selesai');
        $response->assertSee('1 Catatan Belum Selesai');
        $response->assertSee('Tidak ada catatan tertunda');

        // Indikator mendekati batas waktu
        $response->assertSee('Mendekati Batas');
    }
}
