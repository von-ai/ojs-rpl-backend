<?php

declare(strict_types=1);

namespace Tests\Unit\Copyeditor;

use App\Models\Copyedit;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Services\Copyeditor\CopyeditorService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CopyeditorServiceTest extends TestCase
{
    use RefreshDatabase;

    private CopyeditorService $service;

    private User $author;

    private User $copyEditor;

    private User $layoutEditor;

    private Submission $submission;

    private FileEntry $sourceFile;

    private Copyedit $copyedit;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('azure');
        $this->service = new CopyeditorService;

        $this->author = User::factory()->create(['name' => 'Dr. Penulis Korespondensi']);
        $this->copyEditor = User::factory()->create(['name' => 'Editor Bahasa Handal']);
        $this->layoutEditor = User::factory()->create(['name' => 'Editor Tata Letak']);

        $journal = Journal::create([
            'slug' => 'jurnal-makassar',
            'name' => 'Jurnal Makassar',
            'issn' => '1111-2222',
            'status' => 'active',
        ]);

        $this->grantRole($this->copyEditor, 'copyeditor', $journal->id);
        $this->grantRole($this->layoutEditor, 'layout_editor', $journal->id);
        $this->actingAs($this->copyEditor);

        $section = Section::create([
            'journal_id' => $journal->id,
            'name' => 'Inovasi Kebijakan',
            'slug' => 'inovasi-kebijakan',
            'position' => 1,
            'accepting_submissions' => true,
        ]);

        $this->sourceFile = FileEntry::create([
            'storage_disk' => 'azure',
            'storage_path' => 'manuscripts/source_001.docx',
            'original_name' => 'source_001.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'source content'),
            'created_by' => $this->author->id,
        ]);

        $this->submission = Submission::create([
            'submission_number' => 'SUB-2026-00106',
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'title' => 'Analisis Kebijakan Daerah Makassar',
            'abstract' => 'Abstrak naskah uji',
            'status' => 'accepted',
        ]);

        $this->copyedit = Copyedit::create([
            'submission_id' => $this->submission->id,
            'copy_editor_id' => $this->copyEditor->id,
            'source_file_id' => $this->sourceFile->id,
            'status' => 'assigned',
            'due_at' => now()->addDays(7),
        ]);
    }

    public function test_upload_output_file_stores_file_and_updates_status(): void
    {
        $fakeFile = UploadedFile::fake()->create('clean_manuscript.docx', 500);

        $fileEntry = $this->service->uploadOutputFile($this->copyedit, $fakeFile, $this->copyEditor);

        $this->assertDatabaseHas('files', ['id' => $fileEntry->id]);
        $this->assertDatabaseHas('copyedit_outputs', [
            'copyedit_id' => $this->copyedit->id,
            'file_id' => $fileEntry->id,
            'position' => 1,
        ]);

        $this->assertEquals('in_progress', $this->copyedit->fresh()->status);
        $this->assertNotEmpty($fileEntry->checksum_sha256);
        $this->assertSame((int) Storage::disk('azure')->size($fileEntry->storage_path), $fileEntry->byte_size);
        $this->assertSame(hash('sha256', (string) Storage::disk('azure')->get($fileEntry->storage_path)), $fileEntry->checksum_sha256);
    }

    public function test_invalidation_guard_triggers_when_new_output_is_uploaded(): void
    {
        $fakeFile1 = UploadedFile::fake()->create('v1.docx', 300);
        $this->service->uploadOutputFile($this->copyedit, $fakeFile1, $this->copyEditor);

        $this->scanTestUploads();
        $approval = $this->service->requestAuthorApproval($this->copyedit, 'Mohon ditinjau');
        $this->assertNull($approval->invalidated_at);

        // Uploading a new file must automatically invalidate previous approval
        $fakeFile2 = UploadedFile::fake()->create('v2.docx', 350);
        $this->service->uploadOutputFile($this->copyedit, $fakeFile2, $this->copyEditor);

        $this->assertNotNull($approval->fresh()->invalidated_at);
        $this->assertFalse($approval->fresh()->isValid());
    }

    public function test_request_approval_fails_if_output_set_is_empty(): void
    {
        $this->expectException(DomainException::class);
        $this->service->requestAuthorApproval($this->copyedit);
    }

    public function test_author_approval_creates_checksum_freeze(): void
    {
        $fakeFile = UploadedFile::fake()->create('final_copyedit.docx', 400);
        $fileEntry = $this->service->uploadOutputFile($this->copyedit, $fakeFile, $this->copyEditor);

        $this->scanTestUploads();
        $approval = $this->service->requestAuthorApproval($this->copyedit);
        $this->service->approveByAuthor($approval, $this->author, 'Sangat baik');

        $this->assertTrue($approval->fresh()->isValid());
        $this->assertEquals('approved', $approval->fresh()->decision);

        // Verify Checksum Freeze
        $this->assertDatabaseHas('copyedit_approved_files', [
            'approval_id' => $approval->id,
            'file_id' => $fileEntry->id,
            'checksum_sha256' => $fileEntry->checksum_sha256,
        ]);
    }

    public function test_author_can_request_revisions(): void
    {
        $fakeFile = UploadedFile::fake()->create('v1.docx', 400);
        $this->service->uploadOutputFile($this->copyedit, $fakeFile, $this->copyEditor);
        $this->scanTestUploads();
        $approval = $this->service->requestAuthorApproval($this->copyedit);

        $this->service->requestChangesByAuthor($approval, $this->author, 'Perbaiki penulisan istilah pada tabel 3');

        $this->assertEquals('revisions_requested', $approval->fresh()->decision);
        $this->assertEquals('Perbaiki penulisan istilah pada tabel 3', $approval->fresh()->reason);
    }

    public function test_escalation_lifecycle_blocks_and_resumes(): void
    {
        $escalation = $this->service->submitEscalation(
            $this->copyedit,
            $this->copyEditor,
            'Penulis menolak standardisasi istilah baku',
            'Ketidaksesuaian Terminologi'
        );

        $this->assertTrue($escalation->isActive());
        $this->assertEquals('open', $escalation->status);
        $this->assertStringContainsString('Ketidaksesuaian Terminologi', $escalation->reason);

        // Resolve escalation
        $mediator = User::factory()->create();
        $this->grantRole($mediator, 'editor_in_chief', $this->submission->journal_id);
        $this->service->resolveEscalation($escalation, 'Gunakan istilah baku sesuai KBBI V', 'in_progress', $mediator);

        $this->assertFalse($escalation->fresh()->isActive());
        $this->assertEquals('resolved', $escalation->fresh()->status);
    }

    public function test_handoff_readiness_and_completion_guard(): void
    {
        // 1. Initially not ready
        $readiness = $this->service->checkHandoffReadiness($this->copyedit);
        $this->assertFalse($readiness['is_ready']);

        // 2. Upload output
        $fakeFile = UploadedFile::fake()->create('approved_manuscript.docx', 500);
        $this->service->uploadOutputFile($this->copyedit, $fakeFile, $this->copyEditor);

        // 3. Request and approve
        $this->scanTestUploads();
        $approval = $this->service->requestAuthorApproval($this->copyedit);
        $this->service->approveByAuthor($approval, $this->author);

        // 4. Now ready
        $readiness = $this->service->checkHandoffReadiness($this->copyedit);
        $this->assertTrue($readiness['is_ready']);

        // 5. If active escalation exists, readiness becomes false
        $escalation = $this->service->submitEscalation($this->copyedit, $this->copyEditor, 'Catatan tambahan');
        $readiness = $this->service->checkHandoffReadiness($this->copyedit);
        $this->assertFalse($readiness['is_ready']);

        // Resolve escalation
        $mediator = User::factory()->create();
        $this->grantRole($mediator, 'editor_in_chief', $this->submission->journal_id);
        $this->service->resolveEscalation($escalation, 'Selesai', 'in_progress', $mediator);
        $this->assertTrue($this->service->checkHandoffReadiness($this->copyedit)['is_ready']);

        // 6. Complete Handoff
        $assignment = $this->service->completeHandoff(
            $this->copyedit,
            $this->copyEditor,
            $this->layoutEditor->id,
            'Serah terima ke layout editor untuk penerbitan'
        );

        $this->assertDatabaseHas('layout_assignments', ['id' => $assignment->id]);
        $this->assertEquals('completed', $this->copyedit->fresh()->status);
        $this->assertEquals('in_production', $this->submission->fresh()->status);
    }
}
