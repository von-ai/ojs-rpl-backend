<?php

declare(strict_types=1);

namespace App\Mail\SysAdmin;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JournalStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Journal $journal,
        public string $oldStatus,
        public string $newStatus,
        public ?User $changedBy = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Pembaruan Status Publikasi Jurnal: {$this->journal->name}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.sysadmin.journal-status-changed',
            with: [
                'journal' => $this->journal,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'changedBy' => $this->changedBy,
            ]
        );
    }
}
