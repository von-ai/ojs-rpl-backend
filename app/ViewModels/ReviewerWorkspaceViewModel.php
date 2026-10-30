<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\ReviewAssignment;
use App\Models\User;

class ReviewerWorkspaceViewModel
{
    /**
     * @param  array<string, array{label: string, weight: int, description: string}>  $rubricCriteria
     */
    public function __construct(
        public readonly ReviewAssignment $assignment,
        public readonly User $reviewer,
        public readonly array $rubricCriteria = []
    ) {}

    /**
     * Proyeksi judul aman tanpa identitas penulis (Double-Blind Projection).
     */
    public function title(): string
    {
        return $this->assignment->reviewRound->reviewer_title
            ?? $this->assignment->reviewRound->submission->title;
    }

    /**
     * Proyeksi abstrak aman tanpa identitas penulis (Double-Blind Projection).
     */
    public function abstract(): string
    {
        return $this->assignment->reviewRound->reviewer_abstract
            ?? $this->assignment->reviewRound->submission->abstract;
    }

    public function sectionName(): string
    {
        $submission = $this->assignment->reviewRound->submission;

        return $submission->section?->name ?? $submission->section?->title ?? 'Umum';
    }

    public function submissionNumber(): string
    {
        return $this->assignment->reviewRound->submission->submission_number
            ?? "SUB-{$this->assignment->reviewRound->submission_id}";
    }

    public function isInvited(): bool
    {
        return $this->assignment->status === 'invited';
    }

    public function isAccepted(): bool
    {
        return in_array($this->assignment->status, ['accepted', 'overdue'], true);
    }

    public function isCompleted(): bool
    {
        return $this->assignment->status === 'completed';
    }

    public function canSubmit(): bool
    {
        return in_array($this->assignment->status, ['accepted', 'overdue'], true)
            && $this->assignment->status !== 'completed';
    }

    public function existingReview()
    {
        return $this->assignment->review;
    }

    public function pendingExtension()
    {
        return $this->assignment->pendingExtension;
    }
}
