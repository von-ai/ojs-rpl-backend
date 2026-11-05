<?php

namespace Tests\Feature\Security;

use App\Models\Article;
use App\Models\Galley;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorialPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_role_forms_publish_a_real_article_with_the_approved_file(): void
    {
        Storage::fake('local');
        Mail::fake();
        Notification::fake();
        $journal = Journal::create(['name' => 'Jurnal Uji Integrasi', 'slug' => 'sirida-makassar', 'status' => 'active', 'apc_enabled' => false, 'apc_fee' => 'Gratis']);
        $section = Section::create(['journal_id' => $journal->id, 'name' => 'Riset', 'slug' => 'riset', 'accepting_submissions' => true]);
        $people = [];
        foreach (['author', 'editor_in_chief', 'section_editor', 'reviewer', 'copyeditor', 'layout_editor', 'proofreader', 'manager'] as $role) {
            $people[$role] = User::factory()->create();
            $this->grantRole($people[$role], $role, $journal->id);
        }
        $secondReviewer = User::factory()->create();
        $this->grantRole($secondReviewer, 'reviewer', $journal->id);
        $author = $people['author'];
        $this->actingAs($author);
        $this->post(route('author.submissions.store'), [
            'action' => 'submit', 'section_id' => $section->id, 'author_guidelines_accepted' => 1,
            'title' => 'Pengujian integrasi tata kelola publikasi ilmiah',
            'abstract' => str_repeat('Penelitian ini menguji integrasi sistem dan verifikasi proses publikasi ilmiah. ', 3),
            'keywords' => ['Integrasi', 'Publikasi', 'Riset'],
            'manuscript_file' => UploadedFile::fake()->create('identitas-penulis.pdf', 10, 'application/pdf'),
            'contributors' => [['given_name' => 'Penulis', 'family_name' => 'Uji', 'email' => $author->email, 'affiliation' => 'Universitas Uji', 'corresponding' => true]],
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $submission = Submission::firstOrFail();
        $id = $submission->id;
        $number = $submission->submission_number;
        $this->scanTestUploads();
        $this->actingAs($people['editor_in_chief']);
        $this->post(route('eic.submissions.desk-review', $id), [
            'scope_fit' => 1, 'files_complete' => 1, 'guidelines_compliant' => 1, 'similarity_score' => 5,
            'status' => 'passed', 'section_editor_id' => $people['section_editor']->id,
        ])->assertSessionHas('success')->assertSessionMissing('error');
        $this->actingAs($people['section_editor']);
        $this->post(route('section-editor.rounds.store', $id), [
            'anonymous_file' => UploadedFile::fake()->createWithContent('anonim.pdf', "%PDF-1.4\n% Manuskrip anonim\n%%EOF"),
            'reviewer_title' => 'Riset integrasi sistem ilmiah', 'reviewer_abstract' => 'Abstrak anonim tanpa identitas penulis.', 'anonymity_confirmed' => 1,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $round = ReviewRound::where('submission_id', $id)->where('status', 'active')->firstOrFail();
        $this->scanTestUploads();
        foreach ([$people['reviewer'], $secondReviewer] as $reviewer) {
            $this->actingAs($people['section_editor']);
            $this->post(route('section-editor.reviewers.assign', $round->id), [
                'reviewer_id' => $reviewer->id, 'response_due_at' => now()->addDays(3)->toDateString(), 'review_due_at' => now()->addDays(14)->toDateString(),
            ])->assertSessionHasNoErrors()->assertSessionHas('success');
            $assignment = ReviewAssignment::where('review_round_id', $round->id)->where('reviewer_id', $reviewer->id)->firstOrFail();
            $this->actingAs($reviewer);
            $this->post(route('reviewer.assignments.respond', $assignment->id), ['response' => 'accepted'])->assertSessionHasNoErrors();
            $this->get(route('reviewer.files.download', [$assignment->id, $round->manuscript_file_id]))->assertNotFound();
            $this->get(route('reviewer.files.download', [$assignment->id, $round->anonymized_file_id]))->assertOk()->assertDownload('Manuskrip-Anonim.pdf');
            $this->post(route('reviewer.assignments.submit', $assignment->id), [
                'scores' => ['originality' => 90, 'methodology' => 85, 'presentation' => 88, 'policy_relevance' => 90],
                'comments_for_author' => 'Metodologi jelas dan hasil penelitian sesuai dengan ruang lingkup jurnal.',
                'recommendation' => 'accept', 'certify_no_conflict' => 1,
            ])->assertSessionHasNoErrors()->assertSessionHas('success');
        }
        $this->actingAs($people['section_editor']);
        $this->post(route('section-editor.rounds.recommendation', $round->id), ['recommendation' => 'accept', 'note' => 'Dua telaah independen telah selesai dan merekomendasikan penerimaan.'])->assertSessionHasNoErrors();
        $this->actingAs($people['editor_in_chief']);
        $this->post(route('eic.submissions.decision', $id), ['decision' => 'accept', 'note_for_author' => 'Naskah diterima setelah dua telaah independen.'])->assertSessionHas('success')->assertSessionMissing('error');
        $this->assertDatabaseHas('copyedits', ['submission_id' => $id, 'copy_editor_id' => $people['copyeditor']->id]);
        $this->actingAs($people['copyeditor']);
        $this->post('/copyeditor/upload', ['submission_id' => $number, 'copyedit_file' => UploadedFile::fake()->create('hasil-sunting.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors();
        $this->scanTestUploads();
        $this->post('/copyeditor/request-approval', ['submission_id' => $number])->assertSessionMissing('error');
        $this->actingAs($author);
        $this->get(route('author.submissions.show', $id))->assertOk()->assertSee('Tinjau hasil copyediting');
        $this->post('/copyeditor/author-decision', ['submission_id' => $number, 'decision' => 'approved', 'note' => 'Hasil suntingan disetujui.'])->assertSessionHasNoErrors()->assertSessionMissing('error');
        $this->actingAs($people['copyeditor']);
        $this->post('/copyeditor/complete-handoff', ['submission_id' => $number])->assertSessionHasNoErrors()->assertSessionMissing('error');
        $this->assertDatabaseHas('layout_assignments', ['submission_id' => $id, 'layout_editor_id' => $people['layout_editor']->id]);
        $this->actingAs($people['layout_editor']);
        $this->post('/layouteditor/upload-galley', ['submission_id' => $number, 'file' => UploadedFile::fake()->create('galai.pdf', 10, 'application/pdf'), 'galley_label' => 'PDF Full Text', 'format' => 'pdf'])->assertSessionHasNoErrors();
        $this->scanTestUploads();
        $this->post('/layouteditor/request-proofread', ['submission_id' => $number])->assertSessionHasNoErrors();
        $galley = Galley::where('submission_id', $id)->where('status', 'active')->firstOrFail();
        $this->assertDatabaseHas('proof_assignments', ['galley_id' => $galley->id, 'proofreader_id' => $people['proofreader']->id]);
        $this->actingAs($people['proofreader']);
        $this->post('/proofreader/complete', ['submission_id' => $number])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('galleys', ['id' => $galley->id, 'stage' => 'proofread']);
        $this->actingAs($people['layout_editor']);
        $this->post('/layouteditor/request-final-approval', ['submission_id' => $number])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('galley_approvals', ['galley_id' => $galley->id, 'stage' => 'editor_final']);
        $this->actingAs($people['editor_in_chief']);
        $this->post(route('eic.submissions.approve-galley', $id))->assertSessionHas('success')->assertSessionMissing('error');
        $this->actingAs($people['manager']);
        $this->post('/journalmanager/issues', ['volume' => '1', 'number' => '1', 'year' => 2026, 'title' => 'Edisi integrasi'])->assertSessionHasNoErrors();
        $issue = Issue::firstOrFail();
        $this->post(route('journalmanager.issues.add-article', $issue->id), ['article_id' => $id, 'pages' => '1-10'])->assertSessionHasNoErrors();
        $this->post(route('journalmanager.issues.publish', $issue->id))->assertSessionHasNoErrors();
        $article = Article::where('submission_id', $id)->firstOrFail();
        $this->assertSame('published', $article->status);
        $this->assertDatabaseHas('issue_articles', ['issue_id' => $issue->id, 'article_id' => $article->id]);
        Auth::logout();
        $this->get(route('journal.issues.show', $issue->id))->assertOk()->assertSee($submission->title);
        $this->get(route('journal.articles.show', $article->slug))->assertOk()->assertSee($submission->title);
        $this->get(route('journal.articles.galleys.download', [$article->slug, $galley->id]))->assertOk();
    }
}
