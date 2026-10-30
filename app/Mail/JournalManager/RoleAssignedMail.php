<?php

declare(strict_types=1);

namespace App\Mail\JournalManager;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RoleAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public UserRole $userRole
    ) {}

    public function envelope(): Envelope
    {
        $roleLabel = UserRole::roleLabel($this->userRole->role_code);

        return new Envelope(
            subject: "[SIRIDA] Penetapan Peran Baru: {$roleLabel}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.journalmanager.role-assigned',
            with: [
                'user' => $this->user,
                'userRole' => $this->userRole,
                'roleLabel' => UserRole::roleLabel($this->userRole->role_code),
                'loginUrl' => route('login'),
            ]
        );
    }
}
