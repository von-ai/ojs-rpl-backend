<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopyeditingDatabaseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_copyediting_models_and_tables_can_be_created_and_queried(): void
    {
        $userAuthor = User::factory()->create(['name' => 'Penulis Satu']);
        $userCopyEditor = User::factory()->create(['name' => 'Editor Bahasa']);
        $userLayoutEditor = User::factory()->create(['name' => 'Editor Tata Letak']);

        $journal = Journal::create([
            'slug' => 'test-journal',
            'name' => 'Test Jurnal',
            'issn' => '1234-5678',
            'status' => 'active',
        ]);

        $section = Section::create([
            'journal_id' => $journal->id,
            'name' => 'Artikel Riset',
            'slug' => 'artikel-riset',
            'position' => 1,
            'accepting_submissions' => true,
        ]);

        $sourceFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'submissions/test_source.docx',
            'original_name' => 'test_source.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'source content'),
            'created_by' => $userAuthor->id,
        ]);

        $submission = Submission::create([
            'submission_number' => 'SUB-2026-00999',
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $userAuthor->id,
            'title' => 'Pengujian Model Copyediting',
            'abstract' => 'Abstrak pengujian',
            'status' => 'accepted',
        ]);

        // 1. Test Copyedit creation
        $copyedit = Copyedit::create([
            'submission_id' => $submission->id,
            'copy_editor_id' => $userCopyEditor->id,
            'source_file_id' => $sourceFile->id,
            'status' => 'in_progress',
            'notes' => 'Catatan copyedit',
            'due_at' => now()->addDays(7),
        ]);

        $this->assertDatabaseHas('copyedits', [
            'id' => $copyedit->id,
            'status' => 'in_progress',
        ]);
        $this->assertEquals($submission->id, $copyedit->submission->id);
        $this->assertEquals($userCopyEditor->id, $copyedit->copyEditor->id);
        $this->assertEquals($sourceFile->id, $copyedit->sourceFile->id);

        // 2. Test Output Set creation
        $outputFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'copyedits/test_output.docx',
            'original_name' => 'test_output.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 2048,
            'checksum_sha256' => hash('sha256', 'output content'),
            'created_by' => $userCopyEditor->id,
        ]);

        $copyeditOutput = CopyeditOutput::create([
            'copyedit_id' => $copyedit->id,
            'file_id' => $outputFile->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('copyedit_outputs', [
            'copyedit_id' => $copyedit->id,
            'file_id' => $outputFile->id,
        ]);
        $this->assertCount(1, $copyedit->fresh()->outputs);
        $this->assertEquals($outputFile->id, $copyedit->fresh()->outputs->first()->file->id);

        // 3. Test Copyedit Approval creation & Checksum Freeze
        $approval = CopyeditApproval::create([
            'copyedit_id' => $copyedit->id,
            'author_id' => $userAuthor->id,
            'decision' => 'approved',
            'reason' => 'Naskah sudah rapi',
            'output_version' => 1,
            'approved_at' => now(),
            'note' => 'Disetujui tanpa catatan',
        ]);

        $this->assertTrue($approval->isValid());

        $approvedFile = CopyeditApprovedFile::create([
            'approval_id' => $approval->id,
            'file_id' => $outputFile->id,
            'checksum_sha256' => $outputFile->checksum_sha256,
        ]);

        $this->assertDatabaseHas('copyedit_approved_files', [
            'approval_id' => $approval->id,
            'file_id' => $outputFile->id,
        ]);
        $this->assertCount(1, $approval->fresh()->approvedFiles);

        // 4. Test Production Escalation creation
        $escalation = ProductionEscalation::create([
            'submission_id' => $submission->id,
            'copyedit_id' => $copyedit->id,
            'opened_by' => $userCopyEditor->id,
            'reason' => 'Perbedaan istilah teknis',
            'status' => 'open',
        ]);

        $this->assertTrue($escalation->isActive());
        $this->assertDatabaseHas('production_escalations', [
            'id' => $escalation->id,
            'status' => 'open',
        ]);

        // 5. Test Layout Assignment creation
        $layoutAssignment = LayoutAssignment::create([
            'submission_id' => $submission->id,
            'layout_editor_id' => $userLayoutEditor->id,
            'assigned_by' => $userCopyEditor->id,
            'assigned_at' => now(),
            'due_at' => now()->addDays(5),
            'reason' => 'Serah terima copyedit final',
        ]);

        $this->assertDatabaseHas('layout_assignments', [
            'id' => $layoutAssignment->id,
            'submission_id' => $submission->id,
            'layout_editor_id' => $userLayoutEditor->id,
        ]);
        $this->assertEquals($submission->id, $layoutAssignment->submission->id);
    }
}
