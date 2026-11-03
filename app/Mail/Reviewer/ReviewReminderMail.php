<?php

declare(strict_types=1);

namespace App\Mail\Reviewer;

use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReviewAssignment $assignment,
        public User $editor,
        public ?string $customMessage = null
    ) {}

    public function envelope(): Envelope
    {
        $submissionNumber = $this->assignment->reviewRound?->submission?->submission_number ?? 'Naskah';

        return new Envelope(
            subject: "[SIRIDA] Pengingat Penelaahan Naskah Ilmiah: {$submissionNumber}"
        );
    }

    public function content(): Content
    {
        $submission = $this->assignment->reviewRound?->submission;
        $isInvitedOnly = $this->assignment->status === 'invited';

        $dueAt = $isInvitedOnly
            ? ($this->assignment->response_due_at ? $this->assignment->response_due_at->translatedFormat('d F Y') : '-')
            : ($this->assignment->review_due_at ? $this->assignment->review_due_at->translatedFormat('d F Y') : '-');

        $statusLabel = match ($this->assignment->effective_status) {
            'invited' => 'Menunggu Konfirmasi Respon Undangan',
            'overdue' => 'Melewati Batas Waktu (Overdue)',
            'accepted' => 'Sedang Menelaah (Dalam Proses)',
            default => 'Penugasan Aktif',
        };

        return new Content(
            view: 'mail.reviewer.review-reminder',
            with: [
                'assignment' => $this->assignment,
                'submission' => $submission,
                'editor' => $this->editor,
                'reviewer' => $this->assignment->reviewer,
                'customMessage' => $this->customMessage,
                'dueAt' => $dueAt,
                'isInvitedOnly' => $isInvitedOnly,
                'statusLabel' => $statusLabel,
                'workspaceUrl' => route('reviewer.assignments.show', $this->assignment->id),
            ]
        );
    }
}
