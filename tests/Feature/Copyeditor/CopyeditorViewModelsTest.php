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
use Illuminate\Support\Facades\Storage;
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

        // Tab count parity on fallback mock
        $tabs = ['all', 'editing', 'awaiting_approval', 'escalated', 'completed'];
        foreach ($tabs as $tabKey) {
            $tabVm = new CopyeditorSubmissionsViewModel($tabKey);
            $tabCount = $tabVm->tabs()['items'][$tabKey]['count'];
            $submissionsList = $tabVm->submissions();
            $this->assertCount(
                $tabCount,
                $submissionsList,
                "Tab [{$tabKey}] count ({$tabCount}) does not match submissions count (".count($submissionsList).') in fallback mode.'
            );
        }
    }

    public function test_tab_counts_and_submissions_list_have_strict_parity_with_database(): void
    {
        $author = User::factory()->create(['name' => 'Penulis Uji']);
        $copyEditor = User::factory()->create(['name' => 'Editor Uji']);

        $journal = Journal::create([
            'slug' => 'jurnal-uji',
            'name' => 'Jurnal Uji',
            'issn' => '1111-2222',
            'status' => 'active',
        ]);

        $section = Section::create([
            'journal_id' => $journal->id,
            'name' => 'Seksi Uji',
            'slug' => 'seksi-uji',
            'position' => 1,
            'accepting_submissions' => true,
        ]);

        $sourceFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'submissions/src.docx',
            'original_name' => 'src.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'src'),
            'created_by' => $author->id,
        ]);

        // 1. In Progress
        $sub1 = Submission::create(['submission_number' => 'SUB-TEST-1', 'journal_id' => $journal->id, 'section_id' => $section->id, 'author_id' => $author->id, 'title' => 'Naskah In Progress', 'abstract' => 'Abstrak 1', 'status' => 'in_progress']);
        Copyedit::create(['submission_id' => $sub1->id, 'copy_editor_id' => $copyEditor->id, 'source_file_id' => $sourceFile->id, 'status' => 'in_progress', 'due_at' => now()->addDays(5)]);

        // 2. Approved (belongs to editing tab)
        $sub2 = Submission::create(['submission_number' => 'SUB-TEST-2', 'journal_id' => $journal->id, 'section_id' => $section->id, 'author_id' => $author->id, 'title' => 'Naskah Approved', 'abstract' => 'Abstrak 2', 'status' => 'in_progress']);
        $ce2 = Copyedit::create(['submission_id' => $sub2->id, 'copy_editor_id' => $copyEditor->id, 'source_file_id' => $sourceFile->id, 'status' => 'in_progress', 'due_at' => now()->addDays(5)]);
        CopyeditApproval::create(['copyedit_id' => $ce2->id, 'author_id' => $author->id, 'decision' => 'approved', 'output_version' => 1, 'approved_at' => now()]);

        // 3. Awaiting Approval
        $sub3 = Submission::create(['submission_number' => 'SUB-TEST-3', 'journal_id' => $journal->id, 'section_id' => $section->id, 'author_id' => $author->id, 'title' => 'Naskah Awaiting', 'abstract' => 'Abstrak 3', 'status' => 'in_progress']);
        $ce3 = Copyedit::create(['submission_id' => $sub3->id, 'copy_editor_id' => $copyEditor->id, 'source_file_id' => $sourceFile->id, 'status' => 'in_progress', 'due_at' => now()->addDays(5)]);
        CopyeditApproval::create(['copyedit_id' => $ce3->id, 'author_id' => $author->id, 'decision' => 'requested', 'output_version' => 1, 'approved_at' => now()]);

        // 4. Escalated
        $sub4 = Submission::create(['submission_number' => 'SUB-TEST-4', 'journal_id' => $journal->id, 'section_id' => $section->id, 'author_id' => $author->id, 'title' => 'Naskah Escalated', 'abstract' => 'Abstrak 4', 'status' => 'in_progress']);
        $ce4 = Copyedit::create(['submission_id' => $sub4->id, 'copy_editor_id' => $copyEditor->id, 'source_file_id' => $sourceFile->id, 'status' => 'in_progress', 'due_at' => now()->addDays(5)]);
        ProductionEscalation::create(['submission_id' => $sub4->id, 'copyedit_id' => $ce4->id, 'opened_by' => $copyEditor->id, 'reason' => 'Sengketa', 'status' => 'open']);

        // 5. Completed
        $sub5 = Submission::create(['submission_number' => 'SUB-TEST-5', 'journal_id' => $journal->id, 'section_id' => $section->id, 'author_id' => $author->id, 'title' => 'Naskah Completed', 'abstract' => 'Abstrak 5', 'status' => 'completed']);
        Copyedit::create(['submission_id' => $sub5->id, 'copy_editor_id' => $copyEditor->id, 'source_file_id' => $sourceFile->id, 'status' => 'completed', 'due_at' => now()->addDays(5)]);

        // Test each tab: count must strictly equal count($submissions)
        $tabs = ['all', 'editing', 'awaiting_approval', 'escalated', 'completed'];
        foreach ($tabs as $tabKey) {
            $tabVm = new CopyeditorSubmissionsViewModel($tabKey);
            $tabCount = $tabVm->tabs()['items'][$tabKey]['count'];
            $submissionsList = $tabVm->submissions();

            $this->assertCount(
                $tabCount,
                $submissionsList,
                "Tab [{$tabKey}] badge count ({$tabCount}) does not match list count (".count($submissionsList).') in database mode.'
            );
        }

        // Specifically check 'editing' tab: should have 2 items (in_progress + approved)
        $editingVm = new CopyeditorSubmissionsViewModel('editing');
        $this->assertEquals(2, $editingVm->tabs()['items']['editing']['count']);
        $editingSubmissions = $editingVm->submissions();
        $this->assertCount(2, $editingSubmissions);
        $numbers = array_column($editingSubmissions, 'number');
        $this->assertContains('SUB-TEST-1', $numbers);
        $this->assertContains('SUB-TEST-2', $numbers);
    }

    public function test_detail_view_model_falls_back_to_disk_size_when_byte_size_is_zero_or_missing(): void
    {
        Storage::fake('local');
        $disk = Storage::disk('local');

        $author = User::factory()->create();
        $copyEditor = User::factory()->create();
        $journal = Journal::create(['slug' => 'jurnal-disk', 'name' => 'Jurnal Disk', 'issn' => '4444-5555', 'status' => 'active']);
        $section = Section::create(['journal_id' => $journal->id, 'name' => 'Disk Section', 'slug' => 'disk-sec', 'position' => 1, 'accepting_submissions' => true]);

        // Create physical file with 2,244,000 bytes (2.14 MB)
        $diskPath = 'submissions/source_zero_byte.docx';
        $disk->put($diskPath, str_repeat('A', 2244000));

        $sourceFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => $diskPath,
            'original_name' => 'source_zero_byte.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 0, // In DB it is 0
            'checksum_sha256' => hash('sha256', 'content'),
            'created_by' => $author->id,
        ]);

        $submission = Submission::create([
            'submission_number' => 'SUB-2026-00999',
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $author->id,
            'title' => 'Uji Coba Fallback Ukuran Berkas',
            'abstract' => 'Abstrak',
            'status' => 'accepted',
        ]);

        $copyedit = Copyedit::create([
            'submission_id' => $submission->id,
            'copy_editor_id' => $copyEditor->id,
            'source_file_id' => $sourceFile->id,
            'status' => 'in_progress',
            'due_at' => now()->addDays(3),
        ]);

        // Output file with 2,149,580 bytes (2.05 MB)
        $outputPath = 'copyedits/output_zero_byte.docx';
        $disk->put($outputPath, str_repeat('B', 2149580));

        $outputFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => $outputPath,
            'original_name' => 'output_zero_byte.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 0, // In DB it is 0
            'checksum_sha256' => hash('sha256', 'output'),
            'created_by' => $copyEditor->id,
        ]);

        CopyeditOutput::create([
            'copyedit_id' => $copyedit->id,
            'file_id' => $outputFile->id,
            'position' => 1,
        ]);

        $vm = new CopyeditorSubmissionDetailViewModel('SUB-2026-00999');
        $detail = $vm->submission();

        // Source file must fallback to disk size and format as 2.14 MB
        $this->assertSame('2.14 MB', $detail['source_file']['size_formatted']);

        // Output file must fallback to disk size and format as 2.05 MB
        $this->assertCount(1, $detail['output_files']);
        $this->assertSame('2.05 MB', $detail['output_files'][0]['size_formatted']);
    }
}
