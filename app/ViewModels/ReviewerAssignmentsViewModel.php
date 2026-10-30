<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewerAssignmentsViewModel
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly User $reviewer,
        public readonly LengthAwarePaginator $assignments,
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

    public function invitedCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->where('status', 'invited')
            ->count();
    }

    public function activeCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->whereIn('status', ['accepted', 'overdue'])
            ->count();
    }

    public function completedCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->where('status', 'completed')
            ->count();
    }

    public function totalCount(): int
    {
        return ReviewAssignment::query()
            ->where('reviewer_id', $this->reviewer->id)
            ->count();
    }
}
