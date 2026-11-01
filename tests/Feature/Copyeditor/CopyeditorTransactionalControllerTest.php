<?php

declare(strict_types=1);

namespace Tests\Feature\Copyeditor;

use App\Models\Copyedit;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CopyeditorTransactionalControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    private User $copyEditor;

    private User $layoutEditor;

    private Submission $submission;

    private Copyedit $copyedit;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->author = User::factory()->create(['name' => 'Prof. Penulis Korespondensi']);
        $this->copyEditor = User::factory()->create(['name' => 'Budi Copyeditor']);
        \App\Models\UserRole::create([
            'user_id' => $this->copyEditor->id,
            'role_code' => 'copyeditor',
        ]);
        $this->layoutEditor = User::factory()->create(['name' => 'Rudi Layout Editor']);

        $journal = Journal::create([
            'slug' => 'sirida-jurnal',
            'name' => 'Jurnal Riset Daerah',
            'issn' => '1212-3434',
            'status' => 'active',
        ]);

        $section = Section::create([
            'journal_id' => $journal->id,
            'name' => 'Kajian Kebijakan',
            'slug' => 'kajian-kebijakan',
            'position' => 1,
            'accepting_submissions' => true,
        ]);

        $sourceFile = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'submissions/accepted_manuscript.docx',
            'original_name' => 'accepted_manuscript.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'manuscript content'),
            'created_by' => $this->author->id,
        ]);

        $this->submission = Submission::create([
            'submission_number' => 'SUB-2026-00555',
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'author_id' => $this->author->id,
            'title' => 'Inovasi Pelayanan Publik Terpadu',
            'abstract' => 'Abstrak naskah',
            'status' => 'accepted',
        ]);

        $this->copyedit = Copyedit::create([
            'submission_id' => $this->submission->id,
            'copy_editor_id' => $this->copyEditor->id,
            'source_file_id' => $sourceFile->id,
            'status' => 'assigned',
            'due_at' => now()->addDays(7),
        ]);
    }

    public function test_controller_upload_file_persists_to_database_and_sets_in_progress(): void
    {
        $this->actingAs($this->copyEditor);

        $uploadedFile = UploadedFile::fake()->create('edited_clean.docx', 500);

        $response = $this->post('/copyeditor/upload', [
            'submission_id' => 'SUB-2026-00555',
            'copyedit_file' => $uploadedFile,
        ]);

        $response->assertRedirect('/copyeditor/submissions/SUB-2026-00555');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('files', [
            'original_name' => 'edited_clean.docx',
        ]);

        $this->assertDatabaseHas('copyedit_outputs', [
            'copyedit_id' => $this->copyedit->id,
        ]);

        $this->assertEquals('in_progress', $this->copyedit->fresh()->status);
    }

    public function test_controller_request_approval_and_author_decision(): void
    {
        $this->actingAs($this->copyEditor);

        // Upload first
        $this->post('/copyeditor/upload', [
            'submission_id' => 'SUB-2026-00555',
            'copyedit_file' => UploadedFile::fake()->create('edited_v1.docx', 400),
        ]);

        // Request approval
        $response = $this->post('/copyeditor/request-approval', [
            'submission_id' => 'SUB-2026-00555',
            'note' => 'Silakan ditinjau hasil suntingan tata bahasa kami.',
        ]);

        $response->assertRedirect('/copyeditor/submissions/SUB-2026-00555');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('copyedit_approvals', [
            'copyedit_id' => $this->copyedit->id,
            'decision' => 'requested',
            'note' => 'Silakan ditinjau hasil suntingan tata bahasa kami.',
        ]);

        // Author decision (approve)
        $this->actingAs($this->author);
        $decisionResponse = $this->post('/copyeditor/author-decision', [
            'submission_id' => 'SUB-2026-00555',
            'decision' => 'approved',
            'note' => 'Sudah sesuai format jurnal.',
        ]);

        $decisionResponse->assertRedirect('/copyeditor/submissions/SUB-2026-00555');

        $this->assertDatabaseHas('copyedit_approvals', [
            'copyedit_id' => $this->copyedit->id,
            'decision' => 'approved',
        ]);

        $this->assertDatabaseHas('copyedit_approved_files', [
            'approval_id' => $this->copyedit->fresh()->approvals->first()->id,
        ]);
    }

    public function test_controller_escalation_and_resolution(): void
    {
        $this->actingAs($this->copyEditor);

        $response = $this->post('/copyeditor/escalate', [
            'submission_id' => 'SUB-2026-00555',
            'category' => 'Ketidaksesuaian Terminologi Teknis',
            'reason' => 'Perbedaan istilah pada bagian metodologi.',
        ]);

        $response->assertRedirect('/copyeditor/submissions/SUB-2026-00555');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('production_escalations', [
            'copyedit_id' => $this->copyedit->id,
            'status' => 'open',
        ]);

        // Resolve escalation
        $resolveResponse = $this->post('/copyeditor/resolve-escalation', [
            'submission_id' => 'SUB-2026-00555',
            'resolution' => 'Gunakan istilah baku SNI.',
        ]);

        $resolveResponse->assertRedirect('/copyeditor/submissions/SUB-2026-00555');
        $this->assertDatabaseHas('production_escalations', [
            'copyedit_id' => $this->copyedit->id,
            'status' => 'resolved',
            'resolution' => 'Gunakan istilah baku SNI.',
        ]);
    }

    public function test_controller_complete_handoff_after_approval(): void
    {
        $this->actingAs($this->copyEditor);

        // Upload and approve
        $this->post('/copyeditor/upload', [
            'submission_id' => 'SUB-2026-00555',
            'copyedit_file' => UploadedFile::fake()->create('edited_final.docx', 400),
        ]);
        $this->post('/copyeditor/request-approval', ['submission_id' => 'SUB-2026-00555']);
        $this->post('/copyeditor/author-decision', ['submission_id' => 'SUB-2026-00555', 'decision' => 'approved']);

        // Complete handoff
        $response = $this->post('/copyeditor/complete-handoff', [
            'submission_id' => 'SUB-2026-00555',
            'notes' => 'Siap masuk tahap layout 2 kolom.',
        ]);

        $response->assertRedirect('/copyeditor/submissions/SUB-2026-00555');
        $response->assertSessionHas('status');

        $this->assertEquals('completed', $this->copyedit->fresh()->status);
        $this->assertEquals('layout', $this->submission->fresh()->status);
        $this->assertDatabaseHas('layout_assignments', [
            'submission_id' => $this->submission->id,
        ]);
    }

    public function test_controller_download_file_returns_streamed_download(): void
    {
        $this->actingAs($this->copyEditor);

        $fakeFile = UploadedFile::fake()->create('manuscript_to_download.docx', 300);
        $path = $fakeFile->store('copyedits/test', 'local');

        $fileEntry = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => $path,
            'original_name' => 'manuscript_to_download.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 300 * 1024,
            'checksum_sha256' => hash('sha256', 'dummy'),
            'created_by' => $this->copyEditor->id,
        ]);

        $response = $this->get(route('copyeditor.files.download', $fileEntry->id));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=manuscript_to_download.docx');
    }

    public function test_controller_download_file_aborts_404_when_missing_on_disk(): void
    {
        $this->actingAs($this->copyEditor);

        $fileEntry = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => 'submissions/missing_file_on_disk.docx',
            'original_name' => 'missing_file_on_disk.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 1024,
            'checksum_sha256' => hash('sha256', 'missing'),
            'created_by' => $this->copyEditor->id,
        ]);

        $response = $this->get(route('copyeditor.files.download', $fileEntry->id));

        $response->assertStatus(404);
        $this->assertFalse(Storage::disk('local')->exists('submissions/missing_file_on_disk.docx'));
    }

    public function test_controller_download_file_aborts_404_when_not_in_database(): void
    {
        $this->actingAs($this->copyEditor);

        $response = $this->get(route('copyeditor.files.download', 'non_existent_file_id_or_name.docx'));

        $response->assertStatus(404);
    }

    public function test_controller_download_file_supports_lookup_by_original_name_when_exists_on_disk(): void
    {
        $this->actingAs($this->copyEditor);

        $fakeFile = UploadedFile::fake()->create('SUB2026-00106_Source_Accepted.docx', 200);
        $path = $fakeFile->storeAs('submissions', 'SUB2026-00106_Source_Accepted.docx', 'local');

        FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => $path,
            'original_name' => 'SUB2026-00106_Source_Accepted.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'byte_size' => 200 * 1024,
            'checksum_sha256' => hash('sha256', 'source_accepted'),
            'created_by' => $this->copyEditor->id,
        ]);

        $response = $this->get(route('copyeditor.files.download', 'SUB2026-00106_Source_Accepted.docx'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=SUB2026-00106_Source_Accepted.docx');
    }

    public function test_controller_upload_file_validation_rejects_disallowed_mime(): void
    {
        $this->actingAs($this->copyEditor);

        $exeFile = UploadedFile::fake()->create('malicious.exe', 100);

        $response = $this->post('/copyeditor/upload', [
            'submission_id' => 'SUB-2026-00555',
            'copyedit_file' => $exeFile,
        ]);

        $response->assertSessionHasErrors(['copyedit_file']);
    }

    public function test_controller_complete_handoff_fails_when_not_ready(): void
    {
        $this->actingAs($this->copyEditor);

        // Handoff before author approval must fail with error message
        $response = $this->post('/copyeditor/complete-handoff', [
            'submission_id' => 'SUB-2026-00555',
        ]);

        $response->assertRedirect('/copyeditor/submissions/SUB-2026-00555');
        $response->assertSessionHas('error');
        $this->assertNotEquals('completed', $this->copyedit->fresh()->status);
    }
}
