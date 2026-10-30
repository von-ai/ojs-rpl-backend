<?php

declare(strict_types=1);

namespace App\Mail\SysAdmin;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SysAdminRoleAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?User $assignedBy = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SIRIDA] Penetapan Hak Akses: System Administrator'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.sysadmin.role-assigned',
            with: [
                'user' => $this->user,
                'assignedBy' => $this->assignedBy,
                'dashboardUrl' => route('sysadmin.dashboard'),
            ]
        );
    }
}
