<?php

declare(strict_types=1);

namespace App\Mail\LayoutEditor;

use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FinalApprovalRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public Galley $galley,
        public GalleyApproval $approval,
        public User $actor
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Permohonan Persetujuan Akhir Galai EIC: {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.layouteditor.final-approval-requested',
            with: [
                'submission' => $this->submission,
                'galley' => $this->galley,
                'approval' => $this->approval,
                'actor' => $this->actor,
                'workspaceUrl' => route('layouteditor.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
