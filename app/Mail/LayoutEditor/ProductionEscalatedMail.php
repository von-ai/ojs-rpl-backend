<?php

declare(strict_types=1);

namespace App\Mail\LayoutEditor;

use App\Models\ProductionEscalation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductionEscalatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public ProductionEscalation $escalation,
        public User $openedBy
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Tiket Eskalasi Tata Letak Galai: {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.layouteditor.production-escalated',
            with: [
                'submission' => $this->submission,
                'escalation' => $this->escalation,
                'openedBy' => $this->openedBy,
                'workspaceUrl' => route('layouteditor.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
