<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Request-scoped filtering complements role checks with current journal/assignment ownership. */
class PortalAccess
{
    public static function apply(Model $model, Builder $query): void
    {
        $role = request()->attributes->get('portal.role');
        $user = request()->user();
        if (! $role || ! $user || $role === 'sysadmin') {
            return;
        }

        $table = $model->getTable();
        if ($table === 'submissions') {
            self::submissions($query, $user, $role);
        } elseif (in_array($table, ['journals', 'sections', 'issues'], true)) {
            if (in_array($role, ['manager', 'editor_in_chief', 'section_editor'], true)) {
                $query->whereIn($table.'.'.($table === 'journals' ? 'id' : 'journal_id'), self::journalIds($user, $role));
            }
        } elseif (in_array($table, ['copyedits', 'galleys', 'layout_assignments', 'review_rounds', 'editor_assignments', 'revisions', 'article_payments', 'production_escalations'], true)) {
            $query->whereIn($table.'.submission_id', self::visibleSubmissions($user, $role));
            if ($table === 'copyedits' && $role === 'copyeditor') {
                $query->where('copy_editor_id', $user->id);
            }
            if ($table === 'galleys' && $role === 'proofreader') {
                $query->whereIn('galleys.id', DB::table('proof_assignments')->select('galley_id')->where('proofreader_id', $user->id));
            }
        } elseif ($table === 'review_assignments') {
            $query->whereIn('review_round_id', DB::table('review_rounds')->select('id')->whereIn('submission_id', self::visibleSubmissions($user, $role)));
            if ($role === 'reviewer') {
                $query->where('reviewer_id', $user->id);
            }
        } elseif (in_array($table, ['review_extensions', 'review_reminders', 'reviews'], true)) {
            $query->whereIn('assignment_id', DB::table('review_assignments')->select('id')->whereIn('review_round_id', DB::table('review_rounds')->select('id')->whereIn('submission_id', self::visibleSubmissions($user, $role))));
            if ($role === 'reviewer') {
                $query->whereIn('assignment_id', DB::table('review_assignments')->select('id')->where('reviewer_id', $user->id));
            }
        } elseif (in_array($table, ['proofreading_notes', 'galley_approvals'], true)) {
            $query->whereIn('galley_id', DB::table('galleys')->select('id')->whereIn('submission_id', self::visibleSubmissions($user, $role)));
            if ($role === 'proofreader' && $table === 'proofreading_notes') {
                $query->whereIn('proof_assignment_id', DB::table('proof_assignments')->select('id')->where('proofreader_id', $user->id));
            }
        } elseif ($table === 'copyedit_approvals') {
            $query->whereIn('copyedit_id', DB::table('copyedits')->select('id')->whereIn('submission_id', self::visibleSubmissions($user, $role)));
        } elseif ($table === 'proof_assignments') {
            $query->whereIn('galley_id', DB::table('galleys')->select('id')->whereIn('submission_id', self::visibleSubmissions($user, $role)));
            if ($role === 'proofreader') {
                $query->where('proofreader_id', $user->id);
            }
        }
    }

    public static function lockSubmission(Submission $submission, User $user, string $role): Submission
    {
        abort_unless($user->hasRole($role) && User::whereKey($user->id)->where('status', 'active')->whereNotNull('email_verified_at')->exists(), 403);
        $query = self::visibleSubmissions($user, $role)->whereKey($submission->id)->select('submissions.*');
        if ($role === 'proofreader') {
            $query->whereHas('activeGalley.proofAssignments', fn ($q) => $q->where('proofreader_id', $user->id));
        }

        return $query->lockForUpdate()->firstOrFail();
    }

    public static function journalIds(User $user, string $role): \Illuminate\Database\Query\Builder
    {
        return DB::table('user_roles')->select('journal_id')->where('user_id', $user->id)->where('role_code', $role)->whereNotNull('journal_id');
    }

    public static function visibleSubmissions(User $user, string $role): Builder
    {
        $query = Submission::withoutGlobalScope('portal_access')->select('submissions.id');
        self::submissions($query, $user, $role);

        return $query;
    }

    public static function submissions(Builder $query, User $user, string $role): void
    {
        if (in_array($role, ['manager', 'editor_in_chief', 'section_editor', 'reviewer', 'copyeditor', 'layout_editor', 'proofreader'], true)) {
            $query->whereIn('submissions.journal_id', self::journalIds($user, $role));
        }

        if (in_array($role, ['editor_in_chief', 'section_editor'], true)) {
            $query->where(fn ($q) => $q->whereNull('submissions.author_id')->orWhere('submissions.author_id', '!=', $user->id))
                ->whereNotIn('submissions.id', DB::table('submission_contributors')->select('submission_id')->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('email', $user->email)));
        }

        match ($role) {
            'author' => $query->where(function (Builder $q) use ($user) {
                $q->where('submissions.author_id', $user->id)->orWhereIn('submissions.id', DB::table('submission_contributors')->select('submission_id')->where('user_id', $user->id));
            }),
            'section_editor' => $query->whereIn('submissions.id', DB::table('editor_assignments')->select('submission_id')->where('editor_id', $user->id)->whereNull('revoked_at')),
            'copyeditor' => $query->whereIn('submissions.id', DB::table('copyedits')->select('submission_id')->where('copy_editor_id', $user->id)),
            'layout_editor' => $query->whereIn('submissions.id', DB::table('layout_assignments')->select('submission_id')->where('layout_editor_id', $user->id)->whereNull('revoked_at')),
            'proofreader' => $query->whereIn('submissions.id', DB::table('galleys')->select('submission_id')->whereIn('id', DB::table('proof_assignments')->select('galley_id')->where('proofreader_id', $user->id))),
            'reviewer' => $query->whereIn('submissions.id', DB::table('review_rounds')->select('submission_id')->whereIn('id', DB::table('review_assignments')->select('review_round_id')->where('reviewer_id', $user->id))),
            'editor_in_chief', 'manager', 'sysadmin' => null,
            default => $query->whereRaw('1 = 0'),
        };
    }
}
