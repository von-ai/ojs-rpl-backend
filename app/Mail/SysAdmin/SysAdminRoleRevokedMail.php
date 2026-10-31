<?php

declare(strict_types=1);

namespace App\Mail\SysAdmin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SysAdminRoleRevokedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?User $revokedBy = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIRIDA] Pemberitahuan Pencabutan Hak Akses: System Administrator'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.sysadmin.role-revoked',
            with: [
                'user' => $this->user,
                'revokedBy' => $this->revokedBy,
            ]
        );
    }
}
