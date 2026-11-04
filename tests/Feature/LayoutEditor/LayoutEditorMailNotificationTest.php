<?php

declare(strict_types=1);

namespace Tests\Feature\LayoutEditor;

use App\Mail\LayoutEditor\FinalApprovalRequestedMail;
use App\Mail\LayoutEditor\LayoutEditorAssignedMail;
use App\Mail\LayoutEditor\ProductionEscalatedMail;
use App\Mail\LayoutEditor\ProofreadingRequestedMail;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\Submission;
use App\Models\User;
use App\Services\LayoutEditor\LayoutEditorService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LayoutEditorMailNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $layoutEditor;

    private User $proofreader;

    private User $sectionEditor;

    private Submission $submission;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        $this->layoutEditor = User::where('email', 'rahmat.hidayat@brida.makassar.go.id')->firstOrFail();
        $this->proofreader = User::where('email', 'sitti.aminah@brida.makassar.go.id')->firstOrFail();
        $this->sectionEditor = User::where('email', 'prof.hamka@unhas.ac.id')->firstOrFail();
        $this->submission = Submission::where('submission_number', 'SUB-2026-00106')->firstOrFail();
    }

    /**
     * Memastikan email penugasan layout editor terkirim saat ditugaskan oleh Section Editor.
     */
    public function test_email_penugasan_layout_editor_terkirim(): void
    {
        Mail::fake();

        $service = app(LayoutEditorService::class);
        $this->grantRole($this->sectionEditor, 'editor_in_chief', $this->submission->journal_id);
        $service->assignLayoutEditor(
            $this->submission,
            $this->layoutEditor,
            $this->sectionEditor,
            now()->addDays(7),
            'Susun tata letak format PDF 2-kolom.'
        );

        Mail::assertSent(LayoutEditorAssignedMail::class, function ($mail) {
            return $mail->hasTo('rahmat.hidayat@brida.makassar.go.id')
                && $mail->submission->submission_number === 'SUB-2026-00106';
        });
    }

    /**
     * Memastikan email permohonan proofreading terkirim saat galai dikirim ke proofreader.
     */
    public function test_email_permohonan_proofreading_terkirim(): void
    {
        Mail::fake();

        $file = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => "galleys/{$this->submission->id}/test.pdf",
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'test'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->layoutEditor->id,
        ]);

        Galley::create([
            'submission_id' => $this->submission->id,
            'file_id' => $file->id,
            'format' => 'pdf',
            'stage' => 'draft',
            'status' => 'active',
            'checksum_sha256' => $file->checksum_sha256,
            'version' => 1,
            'label' => 'PDF Full Text (v1)',
        ]);

        $service = app(LayoutEditorService::class);
        $service->requestProofreading(
            $this->submission,
            $this->layoutEditor,
            $this->proofreader,
            'Mohon periksa tipografi halaman 1-4.'
        );

        Mail::assertSent(ProofreadingRequestedMail::class, function ($mail) {
            return $mail->hasTo('sitti.aminah@brida.makassar.go.id')
                && $mail->submission->submission_number === 'SUB-2026-00106';
        });
    }

    /**
     * Memastikan email eskalasi terkirim ke Section Editor saat ada kendala tata letak.
     */
    public function test_email_eskalasi_tata_letak_terkirim_ke_section_editor(): void
    {
        Mail::fake();

        $service = app(LayoutEditorService::class);
        $service->submitEscalation(
            $this->submission,
            $this->layoutEditor,
            'Tabel kontur batimetri memerlukan penataan lanskap khusus.'
        );

        Mail::assertSent(ProductionEscalatedMail::class, function ($mail) {
            return $mail->hasTo('prof.hamka@unhas.ac.id')
                && $mail->submission->submission_number === 'SUB-2026-00106';
        });
    }

    /**
     * Memastikan email permohonan persetujuan akhir terkirim ke EIC saat diajukan.
     */
    public function test_email_permohonan_persetujuan_akhir_terkirim_ke_eic(): void
    {
        Mail::fake();

        $file = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => "galleys/{$this->submission->id}/test_final.pdf",
            'original_name' => 'test_final.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 2048,
            'checksum_sha256' => hash('sha256', 'test_final'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->layoutEditor->id,
        ]);

        $galley = Galley::create([
            'submission_id' => $this->submission->id,
            'file_id' => $file->id,
            'format' => 'pdf',
            'stage' => 'draft',
            'status' => 'active',
            'checksum_sha256' => $file->checksum_sha256,
            'version' => 1,
            'label' => 'PDF Full Text (v1 Final)',
        ]);

        ProofAssignment::create([
            'galley_id' => $galley->id,
            'proofreader_id' => $this->proofreader->id,
            'file_id' => $file->id,
            'due_at' => now()->addDays(2),
            'status' => 'completed',
            'assigned_at' => now()->subDay(),
            'checksum_sha256' => substr($file->checksum_sha256, 0, 64),
        ]);

        $service = app(LayoutEditorService::class);
        $service->requestFinalApproval(
            $this->submission,
            $this->layoutEditor,
            'Galai final siap diterbitkan.'
        );

        Mail::assertSent(FinalApprovalRequestedMail::class, function ($mail) {
            return $mail->hasTo(User::where('status', 'active')->whereNotNull('email_verified_at')->whereHas('userRoles', fn ($q) => $q->where('journal_id', $this->submission->journal_id)->where('role_code', 'editor_in_chief'))->firstOrFail()->email)
                && $mail->submission->submission_number === 'SUB-2026-00106';
        });
    }
}
