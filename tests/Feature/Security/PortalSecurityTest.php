<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\EditorAssignment;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Models\UserRole;
use App\Notifications\ResetPasswordNotification;
use App\Services\Auth\AuthenticationService;
use App\Services\Reviewer\ReviewerService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function submission(User $author, ?Journal $journal = null): Submission
    {
        $journal ??= Journal::create(['name' => 'Jurnal Uji', 'slug' => Str::ulid(), 'status' => 'active']);
        $section = Section::create(['name' => 'Riset', 'slug' => Str::ulid(), 'journal_id' => $journal->id, 'accepting_submissions' => true]);

        return Submission::create(['submission_number' => 'SEC-'.Str::ulid(), 'journal_id' => $journal->id, 'section_id' => $section->id, 'author_id' => $author->id, 'title' => 'Judul riset', 'abstract' => 'Abstrak penelitian', 'status' => 'in_review']);
    }

    private function role(User $user, string $role, ?string $journalId = null): void
    {
        UserRole::create(['user_id' => $user->id, 'role_code' => $role, 'journal_id' => $journalId]);
    }

    private function file(string $status = 'clean'): FileEntry
    {
        $path = 'security/'.Str::ulid().'.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test document');

        return FileEntry::create(['storage_disk' => 'local', 'storage_path' => $path, 'original_name' => 'identitas-penulis.pdf', 'mime_type' => 'application/pdf', 'byte_size' => 22, 'checksum_sha256' => hash('sha256', '%PDF-1.4 test document'), 'virus_scan_status' => $status]);
    }

    public function test_lockout_never_reactivates_a_disabled_account(): void
    {
        $user = User::factory()->create(['status' => 'disabled', 'password' => 'Correct-password-123']);
        $service = app(AuthenticationService::class);
        for ($i = 0; $i < 5; $i++) {
            try {
                $service->authenticateWeb($user->email, 'incorrect');
            } catch (ValidationException) {
            }
        }
        $this->assertSame('disabled', $user->fresh()->status);
        $this->travel(16)->minutes();
        try {
            $service->authenticateWeb($user->email, 'Correct-password-123');
            $this->fail('Disabled account authenticated');
        } catch (ValidationException) {
        }
        $this->assertSame('disabled', $user->fresh()->status);
    }

    public function test_eic_cannot_access_another_journal(): void
    {
        $author = User::factory()->create();
        $mine = $this->submission($author);
        $other = $this->submission($author);
        $editor = User::factory()->create();
        $this->role($editor, 'editor_in_chief', $mine->journal_id);
        $this->actingAs($editor)->get(route('eic.submissions.show', $other->id))->assertNotFound();
        $this->post(route('eic.submissions.decision', $other->id), ['decision' => 'decline', 'note_for_author' => 'Tidak dapat diproses'])->assertNotFound();
        $this->assertDatabaseMissing('editorial_decisions', ['submission_id' => $other->id]);
    }

    public function test_section_editor_requires_a_current_assignment(): void
    {
        $submission = $this->submission(User::factory()->create());
        $editor = User::factory()->create();
        $this->role($editor, 'section_editor', $submission->journal_id);
        $this->actingAs($editor)->get(route('section-editor.submissions.show', $submission->id))->assertNotFound();
        EditorAssignment::create(['submission_id' => $submission->id, 'editor_id' => $editor->id, 'role' => 'section_editor', 'assigned_at' => now()]);
        $this->get(route('section-editor.submissions.show', $submission->id))->assertOk();
        $this->assertDatabaseCount('review_rounds', 0); // Reading a workspace must not create a round.
    }

    public function test_author_cannot_substitute_a_file_from_another_submission(): void
    {
        Storage::fake('local');
        $author = User::factory()->create();
        $own = $this->submission($author);
        $other = $this->submission(User::factory()->create());
        $file = $this->file();
        SubmissionFile::create(['submission_id' => $other->id, 'file_id' => $file->id]);
        $this->actingAs($author)->get(route('author.submissions.files.download', [$own->id, $file->id]))->assertNotFound();
    }

    public function test_copyeditor_file_route_checks_ownership_even_without_role_middleware(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $submission = $this->submission($owner);
        $file = $this->file();
        SubmissionFile::create(['submission_id' => $submission->id, 'file_id' => $file->id]);
        $this->actingAs(User::factory()->create())->get(route('copyeditor.files.download', $file->id))->assertNotFound();
    }

    public function test_quarantined_and_tampered_files_cannot_be_downloaded(): void
    {
        Storage::fake('local');
        $author = User::factory()->create();
        $submission = $this->submission($author);
        $file = $this->file('pending');
        SubmissionFile::create(['submission_id' => $submission->id, 'file_id' => $file->id]);
        $url = route('author.submissions.files.download', [$submission->id, $file->id]);
        $this->actingAs($author)->get($url)->assertStatus(423);
        $file->update(['virus_scan_status' => 'clean']);
        Storage::disk('local')->put($file->storage_path, 'tampered content');
        $this->get($url)->assertStatus(409);
    }

    public function test_missing_files_do_not_generate_placeholder_documents(): void
    {
        Storage::fake('local');
        $author = User::factory()->create();
        $submission = $this->submission($author);
        $file = $this->file();
        SubmissionFile::create(['submission_id' => $submission->id, 'file_id' => $file->id]);
        Storage::disk('local')->delete($file->storage_path);
        $this->actingAs($author)->get(route('author.submissions.files.download', [$submission->id, $file->id]))->assertNotFound();
        Storage::disk('local')->assertMissing($file->storage_path);
    }

    public function test_reviewer_cannot_download_the_original_manuscript(): void
    {
        Storage::fake('local');
        $reviewer = User::factory()->create();
        $submission = $this->submission(User::factory()->create());
        $this->role($reviewer, 'reviewer', $submission->journal_id);
        $original = $this->file();
        $anonymous = $this->file();
        $round = ReviewRound::create(['submission_id' => $submission->id, 'round' => 1, 'status' => 'active', 'manuscript_file_id' => $original->id, 'anonymized_file_id' => $anonymous->id, 'anonymized_checksum' => $anonymous->checksum_sha256, 'reviewer_title' => 'Anonim', 'reviewer_abstract' => 'Anonim']);
        $assignment = ReviewAssignment::create(['review_round_id' => $round->id, 'reviewer_id' => $reviewer->id, 'status' => 'accepted', 'response_due_at' => now()->addDay(), 'review_due_at' => now()->addWeek()]);
        $this->actingAs($reviewer)->get(route('reviewer.files.download', [$assignment->id, $original->id]))->assertNotFound();
        $this->get(route('reviewer.files.download', [$assignment->id, $anonymous->id]))->assertOk()->assertDownload('Manuskrip-Anonim.pdf');
    }

    public function test_declined_reviewer_cannot_write_a_draft(): void
    {
        $reviewer = User::factory()->create();
        $submission = $this->submission(User::factory()->create());
        $this->role($reviewer, 'reviewer', $submission->journal_id);
        $round = ReviewRound::create(['submission_id' => $submission->id, 'round' => 1, 'status' => 'active']);
        $assignment = ReviewAssignment::create(['review_round_id' => $round->id, 'reviewer_id' => $reviewer->id, 'status' => 'declined', 'response_due_at' => now()->addDay(), 'review_due_at' => now()->addWeek()]);
        try {
            app(ReviewerService::class)->saveDraft($assignment, ['comments_for_author' => 'unauthorized'], $reviewer);
            $this->fail('Declined assignment was writable');
        } catch (\InvalidArgumentException) {
        }
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_revoked_session_is_rejected_on_the_next_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertRedirect();
        DB::table('auth_sessions')->where('user_id', $user->id)->update(['revoked_at' => now()]);
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_remember_tokens_are_hashed_and_have_a_server_side_expiry(): void
    {
        $user = User::factory()->create(['password' => 'Correct-password-123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'Correct-password-123', 'remember' => 'on'])->assertRedirect();
        $fresh = $user->fresh();
        $this->assertNotNull($fresh->remember_expires_at);
        $this->assertSame(64, strlen($fresh->getRawOriginal('remember_token')));
        $this->assertTrue($fresh->remember_expires_at->lte(now()->addDay()));
    }

    public function test_password_reset_is_single_use_and_does_not_activate_disabled_accounts(): void
    {
        Notification::fake();
        $user = User::factory()->create(['status' => 'disabled']);
        $token = null;
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notice) use (&$token) {
            $token = $notice->token;

            return true;
        });
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'A-new-secure-password-123', 'password_confirmation' => 'A-new-secure-password-123'];
        $this->post(route('password.update'), $data)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password_hash));
        $this->assertSame('disabled', $user->fresh()->status);
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');
    }

    public function test_journal_scoped_sysadmin_grant_does_not_grant_global_administration(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create(['name' => 'Jurnal', 'slug' => Str::ulid()]);
        $this->role($user, 'sysadmin', $journal->id);
        $this->assertFalse($user->isSysAdmin());
        $this->assertFalse($user->hasRole('sysadmin'));
    }

    public function test_database_rejects_a_section_from_a_different_journal(): void
    {
        $first = $this->submission(User::factory()->create());
        $other = $this->submission(User::factory()->create());
        $this->expectException(QueryException::class);
        DB::table('submissions')->where('id', $first->id)->update(['section_id' => $other->section_id]);
    }

    public function test_untrusted_host_cannot_poison_password_recovery_links(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->withServerVariables(['HTTP_HOST' => 'attacker.invalid', 'SERVER_NAME' => 'attacker.invalid'])
            ->post('http://attacker.invalid/password/forgot', ['email' => $user->email])->assertStatus(400);
        Notification::assertNothingSent();
    }

    public function test_granting_a_reviewer_role_preserves_own_author_access(): void
    {
        $author = User::factory()->create();
        $submission = $this->submission($author);
        $this->grantRole($author, 'reviewer', $submission->journal_id);
        $this->assertTrue($author->fresh()->hasRole('author'));
        $this->actingAs($author)->get(route('author.submissions.show', $submission->id))->assertOk();
    }

    public function test_cache_maintenance_preserves_security_rate_limits(): void
    {
        $admin = User::factory()->create();
        $this->role($admin, 'sysadmin');
        RateLimiter::hit('security-regression', 600);
        $this->actingAs($admin)->post(route('sysadmin.settings.clear-cache'))->assertSessionHas('status');
        $this->assertSame(1, RateLimiter::attempts('security-regression'));
    }

    public function test_audit_history_cannot_be_updated_or_deleted(): void
    {
        $log = AuditLog::record(['actor_type' => 'system', 'system_actor' => 'security_test', 'action' => 'test.append_only']);
        foreach (['update', 'delete'] as $operation) {
            try {
                DB::transaction(function () use ($log, $operation) {
                    $query = DB::table('audit_logs')->where('id', $log->id);
                    $operation === 'update' ? $query->update(['action' => 'tampered']) : $query->delete();
                });
                $this->fail('Audit mutation was accepted');
            } catch (QueryException) {
                $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'action' => 'test.append_only']);
            }
        }
    }
}
