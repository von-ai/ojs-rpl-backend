<?php

declare(strict_types=1);

namespace Tests\Feature\LayoutEditor;

use Tests\TestCase;

class LayoutEditorWorkspaceTest extends TestCase
{
    /**
     * Skenario 1: Ruang kerja naskah baru dari copyeditor yang belum memiliki draf galai PDF.
     */
    public function test_halaman_ruang_kerja_draf_awal_belum_ada_galai(): void
    {
        $response = $this->get('/layouteditor/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('Penerapan Sensor IoT dan Telemetri Kualitas Udara Real-Time');
        $response->assertSee('Dr. Nurfadillah Bahar, M.Kes.');
        $response->assertSee('SUB2026-00106_Copyedit_Final_Approved.docx');
        $response->assertSee('Unduh Naskah Final (.docx)');

        // Langkah 1: Belum ada galai & tombol unggah draf pertama
        $response->assertSee('Berkas Galai Publikasi');
        $response->assertSee('Belum ada berkas galai publikasi');
        $response->assertSee('Unggah Draf Galai PDF Pertama');

        // Langkah 2: Info proofreader & tombol berstatus perlu PDF
        $response->assertSee('Putaran Proofreading');
        $response->assertSee('Dra. Sitti Aminah, M.Hum.');
        $response->assertSee('Kirim Galai ke Proofreader (Perlu PDF)');

        // Langkah 3: Persetujuan akhir belum aktif
        $response->assertSee('Persetujuan Akhir Galai Publikasi');
        $response->assertSee('Menunggu Persetujuan Final');

        // Sidebar info & modal dialog
        $response->assertSee('Informasi Penugasan');
        $response->assertSee('Standar Format Galai');
        $response->assertSee('Unggah Berkas Galai Publikasi');
        $response->assertSee('Selesaikan Catatan Proofreading');
        $response->assertSee('Eskalasi Masalah Tata Letak');
    }

    /**
     * Skenario 2: Ruang kerja naskah dalam putaran proofreading dengan catatan koreksi.
     */
    public function test_halaman_ruang_kerja_putaran_proofreading_dengan_catatan(): void
    {
        $response = $this->get('/layouteditor/submissions/SUB-2026-00107');

        $response->assertStatus(200);
        $response->assertSee('Strategi Transformasi Digital Birokrasi Pelayanan Publik Terpadu');
        $response->assertSee('SUB2026-00107_Galley_v1.pdf');
        $response->assertSee('Unduh Galai');

        // Catatan proofreading
        $response->assertSee('2 Catatan Perlu Revisi');
        $response->assertSee('Tabel 2 (Matriks Evaluasi Fitur Aplikasi Lorong) terpotong');
        $response->assertSee('Daftar Pustaka ke-12');
        $response->assertSee('Tandai Selesai');

        // Guard Invariant: Belum bisa diajukan ke EIC sebelum catatan selesai
        $response->assertSee('Selesaikan 2 catatan koreksi sebelum mengajukan persetujuan final.');
    }

    /**
     * Skenario 3: Ruang kerja naskah dengan tiket eskalasi masalah tata letak aktif.
     */
    public function test_halaman_ruang_kerja_eskalasi_aktif(): void
    {
        $response = $this->get('/layouteditor/submissions/SUB-2026-00108');

        $response->assertStatus(200);
        $response->assertSee('Analisis Makroekonomi dan Dampak Fluktuasi Nilai Tukar');
        $response->assertSee('Dalam Eskalasi Tata Letak');

        // Kotak tiket eskalasi aktif
        $response->assertSee('ESC-2026-0071');
        $response->assertSee('Menunggu Arahan Section Editor');
        $response->assertSee('Gambar peta kontur batimetri pantai berdimensi lanskap sangat lebar');
    }

    /**
     * Skenario 4: Ruang kerja naskah yang seluruh catatan selesai dan disetujui EIC (siap terbit).
     */
    public function test_halaman_ruang_kerja_galai_disetujui_siap_terbit(): void
    {
        $response = $this->get('/layouteditor/submissions/SUB-2026-00109');

        $response->assertStatus(200);
        $response->assertSee('Observasi Awal Distribusi Mikroplastik pada Sedimen Muara Sungai Jeneberang Makassar');

        // Versi galai v2 aktif dan v1 superseded
        $response->assertSee('PDF Full Text (v2 Final Terbit)');
        $response->assertSee('PDF Full Text (v1 Draf Awal)');
        $response->assertSee('Tergantikan (Superseded)');

        // Catatan telah selesai diperbaiki
        $response->assertSee('Semua Catatan Selesai');
        $response->assertSee('Header running DOI telah disesuaikan');

        // Status persetujuan
        $response->assertSee('Disetujui Penulis');
        $response->assertSee('Siap Dijadwalkan Terbit');
        $response->assertSee('Galai Disetujui');
    }

    /**
     * Memastikan semua endpoint aksi transaksional Layout Editor bekerja dan melempar redirect status.
     */
    public function test_semua_aksi_transaksional_layout_editor_berfungsi(): void
    {
        // 1. Upload Galley
        $resUpload = $this->post('/layouteditor/upload-galley', [
            'submission_id' => 'SUB-2026-00106',
            'galley_label' => 'PDF Full Text (Draf v1)',
            'format' => 'pdf',
        ]);
        $resUpload->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $resUpload->assertSessionHas('status', 'Berkas galai PDF berhasil diunggah dan diverifikasi checksum.');

        // 2. Resolve Note
        $resResolve = $this->post('/layouteditor/resolve-note', [
            'submission_id' => 'SUB-2026-00107',
            'note_id' => 'NOTE-01JARK-01',
            'resolution' => 'Tabel 2 telah diposisikan ke awal halaman 5 format 2-kolom penuh.',
        ]);
        $resResolve->assertRedirect('/layouteditor/submissions/SUB-2026-00107');
        $resResolve->assertSessionHas('status', 'Catatan koreksi proofreading berhasil ditandai selesai.');

        // 3. Request Proofread
        $resProofread = $this->post('/layouteditor/request-proofread', [
            'submission_id' => 'SUB-2026-00106',
        ]);
        $resProofread->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $resProofread->assertSessionHas('status', 'Berkas galai berhasil dikirim ke putaran pemeriksaan Proofreader.');

        // 4. Request Final Approval
        $resApproval = $this->post('/layouteditor/request-final-approval', [
            'submission_id' => 'SUB-2026-00109',
        ]);
        $resApproval->assertRedirect('/layouteditor/submissions/SUB-2026-00109');
        $resApproval->assertSessionHas('status', 'Permohonan persetujuan akhir galai berhasil diajukan ke Editor in Chief.');

        // 5. Submit Escalation
        $resEscalate = $this->post('/layouteditor/escalate', [
            'submission_id' => 'SUB-2026-00106',
            'reason' => 'Perlu penambahan halaman lampiran peta berformat lanskap.',
        ]);
        $resEscalate->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $resEscalate->assertSessionHas('status', 'Tiket eskalasi kendala tata letak berhasil diterbitkan ke Section Editor.');
    }
}
