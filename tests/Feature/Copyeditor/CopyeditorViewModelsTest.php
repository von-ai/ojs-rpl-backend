<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\ProductionEscalation;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\ViewModels\CopyeditorSubmissionDetailViewModel;
use App\ViewModels\CopyeditorSubmissionsViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CopyeditorViewModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_models_read_and_format_from_database_correctly(): void
    {
        $author = User::factory()->create(['name' => 'Prof. Penulis Satu', 'email' => 'author1@test.com']);
        $copyEditor = User::factory()->create(['name' => 'Budi Copyeditor']);

        $journal = Journal::create([
            'slug' => 'jurnal-inovasi',
            'name' => 'Jurnal Inovasi',
            'issn' => '2222-3333',
            'status' => 'active',
        ]);

        $section = Section::create([
            'journal_id' => $journal->id,
            'name' => 'Inovasi Publik',
            'slug' => 'inovasi-publik',
            'position' => 1,
            'accepting_submissions' => true,
        ]);

        $sourceFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'submissions/manuscript_accepted.docx',
            'original_name' => 'manuscript_accepted.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 10240,
            'checksum_sha256' => hash('sha256', 'source content'),
            'created_by' => $author->id,
        ]);

        $submission = Submission::create([
            'submission_number' => 'SUB-2026-00888',
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $author->id,
            'title' => 'Studi Kasus Transportasi Berkelanjutan di Makassar',
            'abstract' => 'Abstrak naskah',
            'status' => 'accepted',
        ]);

        $copyedit = Copyedit::create([
            'submission_id' => $submission->id,
            'copy_editor_id' => $copyEditor->id,
            'source_file_id' => $sourceFile->id,
            'status' => 'in_progress',
            'due_at' => now()->addDays(5),
        ]);

        $outputFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'copyedits/output_final.docx',
            'original_name' => 'output_final.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 20480,
            'checksum_sha256' => hash('sha256', 'output final content'),
            'created_by' => $copyEditor->id,
        ]);

        CopyeditOutput::create([
            'copyedit_id' => $copyedit->id,
            'file_id' => $outputFile->id,
            'position' => 1,
        ]);

        $approval = CopyeditApproval::create([
            'copyedit_id' => $copyedit->id,
            'author_id' => $author->id,
            'decision' => 'approved',
            'output_version' => 1,
            'approved_at' => now(),
            'note' => 'Disetujui tanpa catatan perbaikan.',
        ]);

        CopyeditApprovedFile::create([
            'approval_id' => $approval->id,
            'file_id' => $outputFile->id,
            'checksum_sha256' => $outputFile->checksum_sha256,
        ]);

        // Test Index ViewModel
        $indexVm = new CopyeditorSubmissionsViewModel('all', 'Transportasi');
        $submissions = $indexVm->submissions();

        $this->assertCount(1, $submissions);
        $this->assertEquals('SUB-2026-00888', $submissions[0]['number']);
        $this->assertEquals('Disetujui Penulis', $submissions[0]['status_label']);
        $this->assertEquals(1, $submissions[0]['files_count']);

        // Test Detail ViewModel
        $detailVm = new CopyeditorSubmissionDetailViewModel('SUB-2026-00888');
        $detail = $detailVm->submission();

        $this->assertEquals('SUB-2026-00888', $detail['number']);
        $this->assertEquals('Studi Kasus Transportasi Berkelanjutan di Makassar', $detail['title']);
        $this->assertTrue($detail['approval']['is_approved']);
        $this->assertTrue($detail['handoff']['is_ready']);
        $this->assertCount(1, $detail['output_files']);
        $this->assertEquals('output_final.docx', $detail['output_files'][0]['name']);
    }

    public function test_view_model_fallback_works_when_database_is_empty(): void
    {
        $indexVm = new CopyeditorSubmissionsViewModel('all');
        $submissions = $indexVm->submissions();

        // Fallback returns mock submissions
        $this->assertNotEmpty($submissions);
        $this->assertEquals('SUB-2026-00106', $submissions[0]['number']);

        $detailVm = new CopyeditorSubmissionDetailViewModel('SUB-2026-00106');
        $detail = $detailVm->submission();

        $this->assertEquals('SUB-2026-00106', $detail['number']);
        $this->assertNotEmpty($detail['output_files']);
    }
}
