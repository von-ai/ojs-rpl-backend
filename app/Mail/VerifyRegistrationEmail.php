<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyRegistrationEmail extends Mailable
{
    public function __construct(private string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verifikasi akun SIRIDA');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.verify-registration', with: [
            'verificationUrl' => route('verification.form', ['token' => $this->token]),
        ]);
    }
}
