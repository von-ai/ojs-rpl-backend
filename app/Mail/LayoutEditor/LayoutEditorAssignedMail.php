<?php

declare(strict_types=1);

namespace App\Mail\LayoutEditor;

use App\Models\LayoutAssignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LayoutEditorAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public LayoutAssignment $assignment,
        public ?User $assignedBy = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Penugasan Tata Letak Galai Publikasi: {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.layouteditor.layout-editor-assigned',
            with: [
                'submission' => $this->submission,
                'assignment' => $this->assignment,
                'assignedBy' => $this->assignedBy,
                'dueAt' => $this->assignment->due_at ? $this->assignment->due_at->translatedFormat('d F Y H:i').' WITA' : 'TBD',
                'workspaceUrl' => route('layouteditor.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
