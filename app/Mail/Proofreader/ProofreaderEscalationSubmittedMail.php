<?php

declare(strict_types=1);

namespace App\Mail\Proofreader;

use App\Models\ProductionEscalation;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProofreaderEscalationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public ProductionEscalation $escalation,
        public User $proofreader
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Eskalasi Proofreader Terhadap Naskah {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.proofreader.escalation-submitted',
            with: [
                'submission' => $this->submission,
                'escalation' => $this->escalation,
                'proofreader' => $this->proofreader,
                'workspaceUrl' => route('proofreader.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
