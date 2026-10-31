<?php

declare(strict_types=1);

namespace App\Mail\Proofreader;

use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorrectionNotesAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public ProofreadingNote $note,
        public User $proofreader
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Catatan Koreksi Proofreader: Naskah {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.proofreader.correction-notes-added',
            with: [
                'submission' => $this->submission,
                'note' => $this->note,
                'proofreader' => $this->proofreader,
                'workspaceUrl' => route('layouteditor.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
