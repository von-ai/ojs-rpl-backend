<?php

declare(strict_types=1);

namespace App\Mail\SysAdmin;

use App\Models\UserImport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserImportCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserImport $userImport
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Laporan Hasil Impor Pengguna CSV: {$this->userImport->file_name}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.sysadmin.user-import-completed',
            with: [
                'userImport' => $this->userImport,
                'usersUrl' => route('sysadmin.users.index'),
            ]
        );
    }
}
