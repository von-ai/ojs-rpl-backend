<?php

declare(strict_types=1);

namespace App\Mail\JournalManager;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIRIDA] Permohonan Reset Kata Sandi Akun'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.journalmanager.password-reset',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ]
        );
    }
}
