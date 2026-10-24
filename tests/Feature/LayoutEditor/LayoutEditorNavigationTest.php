<?php

declare(strict_types=1);

namespace Tests\Feature\LayoutEditor;

use Tests\TestCase;

class LayoutEditorNavigationTest extends TestCase
{
    /**
     * Halaman daftar penugasan layout dapat diakses dengan respons 200 OK.
     */
    public function test_halaman_index_penugasan_layout_dapat_diakses(): void
    {
        $response = $this->get('/layouteditor/submissions');

        $response->assertStatus(200);
        $response->assertSee('Penugasan Galai & Tata Letak');
        $response->assertSee('BRIDA MAKASSAR');
        $response->assertSee('Layout Editor');

        // Memastikan sidebar navigasi ringkas dan fokus
        $response->assertSee('Penugasan Galai');
        $response->assertSee('Panduan Galai');
        $response->assertDontSee('Modal Dialog Aktif');
        $response->assertDontSee('Dashboard Ringkasan');
    }

    /**
     * Halaman panduan tata letak & standar format galai dapat diakses.
     */
    public function test_halaman_guidelines_galai_dapat_diakses(): void
    {
        $response = $this->get('/layouteditor/guidelines');

        $response->assertStatus(200);
        $response->assertSee('Panduan Tata Letak');
        $response->assertSee('Grid Dua Kolom');
        $response->assertSee('Unduh Template Galley');
    }

    /**
     * Halaman ruang kerja galai naskah dapat diakses.
     */
    public function test_halaman_show_ruang_kerja_dapat_diakses(): void
    {
        $response = $this->get('/layouteditor/submissions/SUB-2026-00106');

        $response->assertStatus(200);
        $response->assertSee('Kembali ke Penugasan Galai');
        $response->assertSee('SUB-2026-00106');
    }

    /**
     * Aksi-aksi transaksional layout editor menghasilkan redirect dengan status.
     */
    public function test_aksi_transaksional_layout_mengembalikan_redirect(): void
    {
        $responseUpload = $this->post('/layouteditor/upload-galley', ['submission_id' => 'SUB-2026-00106']);
        $responseUpload->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $responseUpload->assertSessionHas('status');

        $responseResolve = $this->post('/layouteditor/resolve-note', ['submission_id' => 'SUB-2026-00106']);
        $responseResolve->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $responseResolve->assertSessionHas('status');

        $responseProofread = $this->post('/layouteditor/request-proofread', ['submission_id' => 'SUB-2026-00106']);
        $responseProofread->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $responseProofread->assertSessionHas('status');

        $responseEscalate = $this->post('/layouteditor/escalate', ['submission_id' => 'SUB-2026-00106']);
        $responseEscalate->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $responseEscalate->assertSessionHas('status');

        $responseApproval = $this->post('/layouteditor/request-final-approval', ['submission_id' => 'SUB-2026-00106']);
        $responseApproval->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $responseApproval->assertSessionHas('status');
    }
}
