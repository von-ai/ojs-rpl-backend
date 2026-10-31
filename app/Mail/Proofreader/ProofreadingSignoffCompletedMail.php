<?php

declare(strict_types=1);

namespace App\Mail\Proofreader;

use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProofreadingSignoffCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public Galley $galley,
        public ProofAssignment $assignment,
        public User $proofreader
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Persetujuan Bukti Baca (Sign-off) Selesai: Naskah {$this->submission->submission_number}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.proofreader.signoff-completed',
            with: [
                'submission' => $this->submission,
                'galley' => $this->galley,
                'assignment' => $this->assignment,
                'proofreader' => $this->proofreader,
                'workspaceUrl' => route('layouteditor.submissions.show', $this->submission->submission_number),
            ]
        );
    }
}
