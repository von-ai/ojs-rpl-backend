<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use App\Models\Copyedit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CopyeditingEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Menguji bahwa DatabaseSeeder menghasilkan dataset copyediting nyata yang tampil di dashboard.
     */
    public function test_seeded_copyedits_populate_index_and_detail_views(): void
    {
        $copyEditor = User::where('email', 'andi.kurniawan@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($copyEditor);

        // Akses daftar naskah
        $indexResponse = $this->get('/copyeditor/submissions');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('SUB-2026-00106');
        $indexResponse->assertSee('SUB-2026-00107');
        $indexResponse->assertSee('SUB-2026-00108');
        $indexResponse->assertSee('SUB-2026-00109');

        // Akses naskah 106
        $detailResponse = $this->get('/copyeditor/submissions/SUB-2026-00106');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Unduh Naskah Asli (.docx)');
        $detailResponse->assertSee('SUB2026-00106_Copyedit_Final_Clean.docx');
    }

    /**
     * Menguji siklus hidup lengkap (End-to-End Workflow):
     * Unggah revisi -> Invalidation Guard -> Minta Persetujuan -> Persetujuan Penulis & Checksum Freeze -> Serah Terima Layout.
     */
    public function test_complete_copyediting_lifecycle_workflow(): void
    {
        $copyEditor = User::where('email', 'andi.kurniawan@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($copyEditor);

        $copyedit = Copyedit::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00106'))->firstOrFail();

        // 1. Copyeditor mengunggah revisi naskah baru
        $revisedFile = UploadedFile::fake()->create('SUB2026-00106_Copyedit_v2.docx', 512);
        $uploadResp = $this->post('/copyeditor/upload', [
            'submission_id' => 'SUB-2026-00106',
            'copyedit_file' => $revisedFile,
        ]);
        $uploadResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');

        // Pastikan file baru tercatat di database
        $this->assertDatabaseHas('files', ['original_name' => 'SUB2026-00106_Copyedit_v2.docx']);

        // Pastikan approval lama berstatus invalidated (Invalidation Guard)
        $invalidatedApprovals = $copyedit->fresh()->approvals()->whereNotNull('invalidated_at')->count();
        $this->assertGreaterThanOrEqual(1, $invalidatedApprovals);

        // 2. Copyeditor meminta persetujuan penulis ulang
        $reqResp = $this->post('/copyeditor/request-approval', [
            'submission_id' => 'SUB-2026-00106',
            'note' => 'Mohon persetujuan naskah versi 2 setelah perbaikan sitasi.',
        ]);
        $reqResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');

        // 3. Penulis menyetujui naskah (Author Approval & Checksum Freeze)
        $author = $copyedit->submission->author ?? $copyEditor;
        $this->actingAs($author);

        $decisionResp = $this->post('/copyeditor/author-decision', [
            'submission_id' => 'SUB-2026-00106',
            'decision' => 'approved',
            'note' => 'Disetujui, teks dan tabel sudah tepat.',
        ]);
        $decisionResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');

        $activeApproval = $copyedit->fresh()->approvals()->whereNull('invalidated_at')->firstOrFail();
        $this->assertEquals('approved', $activeApproval->decision);
        $this->assertGreaterThan(0, $activeApproval->approvedFiles()->count());

        // 4. Copyeditor melakukan serah terima naskah ke Layout Editor
        $this->actingAs($copyEditor);
        $handoffResp = $this->post('/copyeditor/complete-handoff', [
            'submission_id' => 'SUB-2026-00106',
            'notes' => 'Naskah siap dilayout dengan format 2 kolom margin standar BRIDA.',
        ]);
        $handoffResp->assertRedirect('/copyeditor/submissions/SUB-2026-00106');
        $handoffResp->assertSessionHas('status');

        // 5. Verifikasi status akhir di database
        $this->assertEquals('completed', $copyedit->fresh()->status);
        $this->assertEquals('layout', $copyedit->submission->fresh()->status);
        $this->assertDatabaseHas('layout_assignments', [
            'submission_id' => $copyedit->submission_id,
        ]);

        // 6. Verifikasi halaman naskah menampilkan mode arsip terkunci
        $finalShowResp = $this->get('/copyeditor/submissions/SUB-2026-00106');
        $finalShowResp->assertStatus(200);
        $finalShowResp->assertSee('Mode Arsip');
        $finalShowResp->assertSee('Serah Terima Selesai');
    }
}
