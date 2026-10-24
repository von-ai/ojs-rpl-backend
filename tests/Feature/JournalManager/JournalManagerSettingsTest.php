<?php

declare(strict_types=1);

namespace Tests\Feature\JournalManager;

use Tests\TestCase;

class JournalManagerSettingsTest extends TestCase
{
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
        $response->assertSee('Buka Submission');
        $response->assertSee('Tutup Submission');

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
        $response->assertSee('Call for Papers');
        $response->assertSee('Pembaruan Panduan Gaya Selingkung');
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
}
