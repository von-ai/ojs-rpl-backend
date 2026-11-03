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

    /**
     * Daftar kata kunci naskah telaah.
     *
     * @return array<int, string>
     */
    public function keywords(): array
    {
        $keywords = $this->assignment->reviewRound?->submission?->keywords;

        if (is_array($keywords)) {
            return array_values(array_filter(array_map('trim', $keywords)));
        }

        if (is_string($keywords) && trim($keywords) !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $keywords))));
        }

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

    public function isDeclined(): bool
    {
        return $this->assignment->status === 'declined';
    }

    public function isCancelled(): bool
    {
        return $this->assignment->status === 'cancelled';
    }

    public function isSubmissionFinalized(): bool
    {
        $submission = $this->assignment->reviewRound?->submission;

        return $submission && in_array($submission->status, ['declined', 'published', 'accepted', 'in_production', 'ready_for_issue', 'archived', 'withdrawn'], true);
    }

    public function finalizedSubmissionMessage(): ?string
    {
        $submission = $this->assignment->reviewRound?->submission;
        if (! $submission) {
            return null;
        }

        return match ($submission->status) {
            'declined' => 'Naskah ini telah diputuskan ditolak oleh Pemimpin Redaksi (EIC), sehingga proses penelaahan telaah telah dihentikan.',
            'published' => 'Naskah ini telah selesai diproses dan diterbitkan secara resmi, sehingga proses penelaahan telah ditutup.',
            'accepted', 'in_production', 'ready_for_issue' => 'Naskah ini telah diterima oleh Pemimpin Redaksi dan memasuki tahap produksi.',
            default => 'Proses penelaahan untuk naskah ini telah ditutup oleh Dewan Redaksi.',
        };
    }

    public function isNoResponse(): bool
    {
        return $this->assignment->effective_status === 'no_response';
    }

    public function canSubmit(): bool
    {
        return in_array($this->assignment->status, ['accepted', 'overdue'], true)
            && $this->assignment->status !== 'completed';
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
