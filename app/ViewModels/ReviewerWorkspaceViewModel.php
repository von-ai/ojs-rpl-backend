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
            ?? 'Judul anonim belum disiapkan';
    }

    /**
     * Proyeksi abstrak aman tanpa identitas penulis (Double-Blind Projection).
     */
    public function abstract(): string
    {
        return $this->assignment->reviewRound->reviewer_abstract
            ?? 'Abstrak anonim belum disiapkan';
    }

    /**
     * Daftar kata kunci naskah telaah.
     *
     * @return array<int, string>
     */
    public function keywords(): array
    {
        // Keywords on the submission are not an anonymized snapshot.
        return [];
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
        return $this->assignment->effective_status === 'accepted' && $this->assignment->reviewRound->status === 'active';
    }

    public function canDownloadManuscript(): bool
    {
        return in_array($this->assignment->status, ['accepted', 'overdue', 'completed'], true);
    }

    public function existingReview()
    {
        return $this->assignment->review;
    }

    public function pendingExtension()
    {
        return $this->assignment->pendingExtension;
    }

    public function daysRemaining(): ?int
    {
        if (! $this->assignment->review_due_at) {
            return null;
        }

        return (int) now()->diffInDays($this->assignment->review_due_at, false);
    }

    /**
     * @return array{label: string, badge_class: string}
     */
    public function statusInfo(): array
    {
        return match ($this->assignment->status) {
            'invited' => [
                'label' => 'Undangan Masuk',
                'badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
            ],
            'accepted' => [
                'label' => 'Sedang Ditelaah',
                'badge_class' => 'bg-blue-50 text-blue-800 border-blue-200',
            ],
            'overdue' => [
                'label' => 'Melewati Batas Waktu',
                'badge_class' => 'bg-rose-50 text-rose-800 border-rose-200',
            ],
            'completed' => [
                'label' => 'Telaah Selesai',
                'badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            ],
            'declined' => [
                'label' => 'Undangan Ditolak',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-300',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-300',
            ],
            default => [
                'label' => ucfirst($this->assignment->status),
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
        };
    }
}
