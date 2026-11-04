<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SecurityAccountLockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $username,
        public string $lockedUntil,
        public string $occurredAt
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIRIDA] Peringatan Keamanan: Akun Anda Dikunci Sementara'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.security.account-locked',
            with: [
                'username' => $this->username,
                'lockedUntil' => $this->lockedUntil,
                'occurredAt' => $this->occurredAt,
            ]
        );
    }
}
