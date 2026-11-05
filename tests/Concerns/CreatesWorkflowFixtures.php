<?php

namespace Tests\Concerns;

use App\Jobs\ScanFile;
use App\Models\ArticlePayment;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserRole;
use App\Services\LayoutEditor\LayoutEditorService;
use App\Services\Reviewer\ReviewerService;
use App\Services\Security\MalwareScannerInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait CreatesWorkflowFixtures
{
    protected function grantRole(User $user, string $role, string $journalId): void
    {
        UserRole::firstOrCreate(['user_id' => $user->id, 'journal_id' => $journalId, 'role_code' => $role]);
    }

    /** Explicitly simulate a successful external scan between workflow steps. */
    protected function scanTestUploads(): void
    {
        $scanner = new class implements MalwareScannerInterface
        {
            public function scan(FileEntry $file): string
            {
                return 'clean';
            }
        };
        foreach (FileEntry::where('virus_scan_status', 'pending')->get() as $file) {
            if (Storage::disk($file->storage_disk)->exists($file->storage_path)) {
                (new ScanFile($file->id))->handle($scanner);
            }
        }
    }

    protected function cleanTestFile(string $name = 'galai-uji.pdf'): FileEntry
    {
        $content = '%PDF-1.4 valid test fixture '.$name;
        $path = 'test-fixtures/'.Str::ulid().'.pdf';
        Storage::disk('azure')->put($path, $content);

        return FileEntry::create(['storage_disk' => 'azure', 'storage_path' => $path, 'original_name' => $name, 'mime_type' => 'application/pdf', 'byte_size' => strlen($content), 'checksum_sha256' => hash('sha256', $content), 'virus_scan_status' => 'clean']);
    }

    /** Construct an editorially approved fixture for issue-management tests. */
    protected function preparePublication(Submission $submission): Galley
    {
        $submission->update(['status' => 'ready_for_issue']);
        $editor = User::factory()->create();
        $this->grantRole($editor, 'editor_in_chief', $submission->journal_id);
        $file = $this->cleanTestFile();
        $submission->galleys()->where('status', 'active')->update(['status' => 'superseded']);
        $galley = Galley::create(['submission_id' => $submission->id, 'file_id' => $file->id, 'format' => 'pdf', 'label' => 'PDF uji siap terbit', 'stage' => 'approved', 'status' => 'active', 'checksum_sha256' => $file->checksum_sha256, 'approved_checksum' => $file->checksum_sha256, 'version' => (int) $submission->galleys()->max('version') + 1]);
        GalleyApproval::create(['galley_id' => $galley->id, 'file_id' => $file->id, 'checksum_sha256' => $file->checksum_sha256, 'approved_by' => $editor->id, 'approved_at' => now(), 'stage' => 'editor_final']);
        $submission->escalations()->whereIn('status', ['open', 'investigating'])->update(['status' => 'resolved', 'resolution' => 'Mediasi diselesaikan dalam fixture siap terbit.']);
        if ($submission->requiresPayment()) {
            ArticlePayment::create(['submission_id' => $submission->id, 'user_id' => $submission->author_id ?? User::factory()->create()->id, 'invoice_number' => 'TEST-'.Str::ulid(), 'amount' => '500000.00', 'amount_paid' => '0.00', 'status' => 'waived', 'verified_by' => $editor->id, 'verified_at' => now(), 'verification_notes' => 'Pembebasan biaya dalam fixture publikasi.']);
        }
        $readiness = app(LayoutEditorService::class)->checkPublicationReadiness($submission->fresh());
        $this->assertTrue($readiness['is_ready'], implode(' ', $readiness['reasons']));

        return $galley;
    }

    protected function completeReviewRound(Submission $submission, ?ReviewRound $round = null): ReviewRound
    {
        $round ??= ReviewRound::create(['submission_id' => $submission->id, 'round' => 1, 'status' => 'active', 'minimum_completed_reviews' => 2]);
        $original = $this->cleanTestFile('asli.pdf');
        $anonymous = $this->cleanTestFile('anonim.pdf');
        $round->update(['manuscript_file_id' => $original->id, 'manuscript_checksum' => $original->checksum_sha256, 'anonymized_file_id' => $anonymous->id, 'anonymized_checksum' => $anonymous->checksum_sha256, 'rubric_snapshot' => app(ReviewerService::class)->getDefaultRubricCriteria(), 'reviewer_title' => 'Judul anonim untuk uji', 'reviewer_abstract' => 'Abstrak anonim untuk uji']);
        for ($i = 0; $i < 2; $i++) {
            $reviewer = User::factory()->create();
            $this->grantRole($reviewer, 'reviewer', $submission->journal_id);
            $assignment = ReviewAssignment::create(['review_round_id' => $round->id, 'reviewer_id' => $reviewer->id, 'status' => 'completed', 'response_due_at' => now()->addDay(), 'review_due_at' => now()->addWeek(), 'responded_at' => now(), 'completed_at' => now()]);
            Review::create(['assignment_id' => $assignment->id, 'scores' => ['originality' => 80, 'methodology' => 80, 'presentation' => 80, 'policy_relevance' => 80], 'comments_for_author' => 'Hasil telaah untuk pengujian alur editorial.', 'recommendation' => 'accept', 'certify_no_conflict' => true, 'status' => 'submitted', 'submitted_at' => now()]);
        }

        return $round;
    }
}
