<?php

declare(strict_types=1);

namespace Tests\Feature\LayoutEditor;

use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use App\Services\LayoutEditor\LayoutEditorService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LayoutEditorEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Memastikan data hasil seeding realistis mengisi tampilan katalog penugasan dan detail ruang kerja.
     */
    public function test_seeded_layout_editor_populates_index_and_detail_views(): void
    {
        $layoutEditor = User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($layoutEditor);

        // Akses daftar penugasan layout
        $indexResponse = $this->get('/layouteditor/submissions');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('SUB-2026-00106');
        $indexResponse->assertSee('SUB-2026-00107');
        $indexResponse->assertSee('SUB-2026-00108');
        $indexResponse->assertSee('SUB-2026-00109');
        $indexResponse->assertSee('SUB-2026-00110');
        $indexResponse->assertSee('SUB-2026-00111');

        // Pastikan sidebar badge menampilkan jumlah penugasan secara dinamis (8)
        $indexResponse->assertSee('sidebar-badge-full', false);
        $indexResponse->assertSee('8');

        // Akses naskah 107 (dalam putaran proofreading)
        $detailResponse = $this->get('/layouteditor/submissions/SUB-2026-00107');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Transformasi Digital');
        $detailResponse->assertSee('Pelayanan Publik');
        $detailResponse->assertSee('SUB2026-00107_Galley_v1.pdf');
        $detailResponse->assertSee('2 Catatan Perlu Revisi');
        $detailResponse->assertDontSee('#unduh-naskah-final-copyedit');
        $detailResponse->assertSee('/layouteditor/files/');

        // Unduh berkas naskah acuan final copyeditor (.docx)
        $downloadFinalDocx = $this->get(route('layouteditor.files.download', 'SUB-2026-00107'));
        $downloadFinalDocx->assertStatus(200);
        $downloadFinalDocx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // Unduh berkas galai v1 dan pastikan Content-Length cocok dengan byte_size database & disk
        $galley107 = Galley::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00107'))->firstOrFail();
        $downloadGalley = $this->get(route('layouteditor.galleys.download', $galley107->id));
        $downloadGalley->assertStatus(200);
        $downloadGalley->assertHeader('Content-Type', 'application/pdf');
        $downloadGalley->assertHeader('Content-Length', (string) $galley107->file->byte_size);
    }

    /**
     * Menguji alur kerja end-to-end produksi galai:
     * Unggah Draf v1 -> Kirim Proofreading -> Catatan Koreksi -> Penyelesaian Catatan -> Unggah Revisi v2 (Genealogi) -> Persetujuan Final EIC.
     */
    public function test_complete_layout_and_galley_lifecycle_workflow(): void
    {
        $layoutEditor = User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($layoutEditor);

        $submission = Submission::where('submission_number', 'SUB-2026-00106')->firstOrFail();

        // 1. Unggah Draf Galai PDF Pertama (v1)
        $pdfFilev1 = UploadedFile::fake()->create('SUB2026-00106_Galley_Draft_v1.pdf', 800, 'application/pdf');
        $uploadResp = $this->post('/layouteditor/upload-galley', [
            'submission_id' => 'SUB-2026-00106',
            'galley_label' => 'PDF Full Text (Draf v1)',
            'format' => 'pdf',
            'file' => $pdfFilev1,
        ]);
        $uploadResp->assertRedirect('/layouteditor/submissions/SUB-2026-00106');

        $galleyV1 = Galley::where('submission_id', $submission->id)->latest('version')->firstOrFail();
        $this->assertSame(1, $galleyV1->version);
        $this->assertSame('active', $galleyV1->status);
        $this->assertSame('draft', $galleyV1->stage);
        $this->assertNull($galleyV1->supersedes_galley_id);

        // Pastikan checksum presisi
        $disk = Storage::disk('local');
        $this->assertSame((int) $disk->size($galleyV1->file->storage_path), $galleyV1->file->byte_size);
        $this->assertSame(hash('sha256', (string) $disk->get($galleyV1->file->storage_path)), $galleyV1->checksum_sha256);

        // Invariant Anti-Bypass Guard: Permohonan persetujuan akhir DITOLAK keras jika galai belum melalui proofreading
        $bypassAttemptResp = $this->post('/layouteditor/request-final-approval', [
            'submission_id' => 'SUB-2026-00106',
        ]);
        $bypassAttemptResp->assertSessionHasErrors(['general']);
        $this->assertDatabaseMissing('galley_approvals', ['galley_id' => $galleyV1->id]);

        // 2. Kirim galai ke Proofreader
        $proofreadResp = $this->post('/layouteditor/request-proofread', [
            'submission_id' => 'SUB-2026-00106',
        ]);
        $proofreadResp->assertRedirect('/layouteditor/submissions/SUB-2026-00106');

        $proofAssign = ProofAssignment::where('galley_id', $galleyV1->id)->firstOrFail();
        $this->assertSame('assigned', $proofAssign->status);
        $this->assertSame('proofreading', $galleyV1->fresh()->stage);

        // Invariant Anti-Bypass Guard: Ditolak jika putaran proofreading sedang berlangsung (belum diselesaikan proofreader)
        $ongoingProofreadResp = $this->post('/layouteditor/request-final-approval', [
            'submission_id' => 'SUB-2026-00106',
        ]);
        $ongoingProofreadResp->assertSessionHasErrors(['general']);

        // Simulasi proofreader menambahkan catatan koreksi
        $note = ProofreadingNote::create([
            'proof_assignment_id' => $proofAssign->id,
            'galley_id' => $galleyV1->id,
            'file_id' => $galleyV1->file_id,
            'checksum_sha256' => $galleyV1->checksum_sha256,
            'category' => 'layout',
            'page' => 4,
            'severity' => 'critical',
            'description' => 'Tabel 3 terpotong batas halaman bawah.',
            'resolved' => false,
            'version' => 1,
        ]);

        // 3. Invariant Guard: Permohonan persetujuan akhir harus ditolak jika ada catatan belum selesai
        $approvalFailResp = $this->post('/layouteditor/request-final-approval', [
            'submission_id' => 'SUB-2026-00106',
        ]);
        $approvalFailResp->assertSessionHasErrors(['general']);
        $this->assertDatabaseMissing('galley_approvals', ['galley_id' => $galleyV1->id]);

        // 4. Selesaikan catatan koreksi proofreading
        $resolveResp = $this->post('/layouteditor/resolve-note', [
            'submission_id' => 'SUB-2026-00106',
            'note_id' => $note->id,
            'resolution' => 'Posisi Tabel 3 telah dipindahkan ke awal halaman 5 dengan lebar 2 kolom.',
        ]);
        $resolveResp->assertRedirect('/layouteditor/submissions/SUB-2026-00106');

        $this->assertTrue($note->fresh()->resolved);
        $this->assertNotNull($note->fresh()->resolved_at);
        $this->assertSame('completed', $proofAssign->fresh()->status);

        // 5. Unggah Revisi Galai v2 (Version Genealogy Guard)
        $pdfFilev2 = UploadedFile::fake()->create('SUB2026-00106_Galley_Final_v2.pdf', 820, 'application/pdf');
        $uploadV2Resp = $this->post('/layouteditor/upload-galley', [
            'submission_id' => 'SUB-2026-00106',
            'galley_label' => 'PDF Full Text (v2 Final)',
            'format' => 'pdf',
            'file' => $pdfFilev2,
        ]);
        $uploadV2Resp->assertRedirect('/layouteditor/submissions/SUB-2026-00106');

        $galleyV2 = Galley::where('submission_id', $submission->id)->latest('version')->firstOrFail();
        $this->assertSame(2, $galleyV2->version);
        $this->assertSame('active', $galleyV2->status);
        $this->assertSame($galleyV1->id, $galleyV2->supersedes_galley_id);
        $this->assertSame('superseded', $galleyV1->fresh()->status);

        // 6. Ajukan persetujuan akhir EIC (berhasil karena semua catatan selesai)
        $approvalResp = $this->post('/layouteditor/request-final-approval', [
            'submission_id' => 'SUB-2026-00106',
        ]);
        $approvalResp->assertRedirect('/layouteditor/submissions/SUB-2026-00106');
        $approvalResp->assertSessionHas('status', 'Permohonan persetujuan akhir galai berhasil diajukan ke Editor in Chief.');

        $this->assertDatabaseHas('galley_approvals', [
            'galley_id' => $galleyV2->id,
            'stage' => 'editor_final',
        ]);
        $this->assertSame('approved', $galleyV2->fresh()->stage);
        $this->assertSame($galleyV2->checksum_sha256, $galleyV2->fresh()->approved_checksum);

        // 7. Verifikasi Kesiapan Terbit (UC-15 Readiness Guard)
        $service = app(LayoutEditorService::class);
        $readiness = $service->checkPublicationReadiness($submission->fresh());
        $this->assertTrue($readiness['is_ready']);
        $this->assertEmpty($readiness['reasons']);
    }

    /**
     * Menguji Invalidation Guard: Unggahan galai baru membatalkan persetujuan aktif sebelumnya.
     */
    public function test_invalidation_guard_membatalkan_approval_saat_galai_baru_diunggah(): void
    {
        $layoutEditor = User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($layoutEditor);

        $submission = Submission::where('submission_number', 'SUB-2026-00109')->firstOrFail();
        $activeGalley = Galley::where('submission_id', $submission->id)->where('status', 'active')->firstOrFail();

        // Pastikan approval aktif ada sebelum mutasi
        $activeApprovalsCount = GalleyApproval::where('galley_id', $activeGalley->id)->whereNull('invalidated_at')->count();
        $this->assertGreaterThan(0, $activeApprovalsCount);

        // Unggah galai baru v3
        $pdfFilev3 = UploadedFile::fake()->create('SUB2026-00109_Galley_v3.pdf', 900, 'application/pdf');
        $this->post('/layouteditor/upload-galley', [
            'submission_id' => 'SUB-2026-00109',
            'galley_label' => 'PDF Full Text (v3 Darurat)',
            'format' => 'pdf',
            'file' => $pdfFilev3,
        ]);

        // Pastikan approval sebelumnya dibatalkan (invalidated_at diisi)
        $invalidatedCount = GalleyApproval::where('galley_id', $activeGalley->id)->whereNotNull('invalidated_at')->count();
        $this->assertSame($activeApprovalsCount, $invalidatedCount);
    }

    /**
     * Menguji eskalasi kendala tata letak ke Section Editor (XOR constraint: copyedit_id NULL & galley_id NOT NULL).
     */
    public function test_eskalasi_kendala_tata_letak_ke_section_editor(): void
    {
        $layoutEditor = User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->firstOrFail();
        $this->actingAs($layoutEditor);

        $escalateResp = $this->post('/layouteditor/escalate', [
            'submission_id' => 'SUB-2026-00107',
            'reason' => 'Perlu izin Section Editor untuk menampilkan diagram alur dalam 1 halaman lanskap penuh.',
        ]);
        $escalateResp->assertRedirect('/layouteditor/submissions/SUB-2026-00107');

        $escalation = ProductionEscalation::whereHas('submission', fn ($q) => $q->where('submission_number', 'SUB-2026-00107'))
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('open', $escalation->status);
        $this->assertNull($escalation->copyedit_id);
        $this->assertNotNull($escalation->galley_id);
        $this->assertSame('Perlu izin Section Editor untuk menampilkan diagram alur dalam 1 halaman lanskap penuh.', $escalation->reason);
    }
}
