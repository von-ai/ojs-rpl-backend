<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

class SectionEditorWorkspaceViewModel
{
    /**
     * @param  Collection<int, User>  $availableReviewers
     */
    public function __construct(
        public readonly Submission $submission,
        public readonly ReviewRound $activeRound,
        public readonly User $editor,
        public readonly Collection $availableReviewers
    ) {}

    public function submissionNumber(): string
    {
        return $this->submission->submission_number ?? "SUB-{$this->submission->id}";
    }

    public function title(): string
    {
        return $this->submission->title;
    }

    public function abstract(): string
    {
        return $this->submission->abstract;
    }

    public function sectionName(): string
    {
        return $this->submission->section?->name ?? $this->submission->section?->title ?? 'Umum';
    }

    public function authorName(): string
    {
        return $this->submission->author?->name ?? 'Anonim';
    }

    public function authorEmail(): string
    {
        return $this->submission->author?->email ?? '-';
    }

    public function currentStatus(): string
    {
        return $this->submission->status;
    }

    public function completedReviewsCount(): int
    {
        return $this->activeRound->assignments->where('status', 'completed')->count();
    }

    public function minimumCompletedReviews(): int
    {
        return $this->activeRound->minimum_completed_reviews;
    }

    public function canRecommendToEic(): bool
    {
        return $this->completedReviewsCount() >= $this->minimumCompletedReviews();
    }

    public function hasSeRecommendation(): bool
    {
        return ! empty($this->activeRound->se_recommendation);
    }
}
