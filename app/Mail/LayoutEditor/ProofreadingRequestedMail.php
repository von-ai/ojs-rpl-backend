<?php

declare(strict_types=1);

namespace App\Mail\LayoutEditor;

use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProofreadingRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public Galley $galley,
        public ProofAssignment $assignment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Permohonan Proofreading Galai Naskah {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.layouteditor.proofreading-requested',
            with: [
                'submission' => $this->submission,
                'galley' => $this->galley,
                'assignment' => $this->assignment,
                'dueAt' => $this->assignment->due_at ? $this->assignment->due_at->translatedFormat('d F Y H:i').' WITA' : '5 hari ke depan',
                'workspaceUrl' => route('layouteditor.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
