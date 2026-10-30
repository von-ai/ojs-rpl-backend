<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SectionEditorSubmissionsViewModel
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly User $editor,
        public readonly LengthAwarePaginator $submissions,
        public readonly array $filters = []
    ) {}

    public function currentStatus(): string
    {
        return $this->filters['status'] ?? 'all';
    }

    public function searchQuery(): string
    {
        return $this->filters['search'] ?? '';
    }

    public function inReviewCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->whereIn('status', ['in_review', 'under_review'])
            ->count();
    }

    public function revisionsRequestedCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->where('status', 'revisions_requested')
            ->count();
    }

    public function reviewedCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->whereHas('currentReviewRound', function ($q) {
                $q->whereHas('assignments', function ($qa) {
                    $qa->where('status', 'completed');
                });
            })
            ->count();
    }

    public function totalCount(): int
    {
        return Submission::query()
            ->whereHas('editorAssignments', function ($q) {
                $q->where('editor_id', $this->editor->id)->whereNull('revoked_at');
            })
            ->count();
    }
}
