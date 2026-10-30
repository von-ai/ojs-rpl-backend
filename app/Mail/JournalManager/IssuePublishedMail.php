<?php

declare(strict_types=1);

namespace App\Mail\JournalManager;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IssuePublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Issue $issue
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIRIDA] Publikasi Resmi Terbitan Vol. {$this->issue->volume} No. {$this->issue->number} ({$this->issue->year})"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.journalmanager.issue-published',
            with: [
                'issue' => $this->issue,
                'articlesCount' => $this->issue->issueArticles()->count(),
                'publishedAt' => $this->issue->published_at ? $this->issue->published_at->translatedFormat('d F Y H:i').' WITA' : now()->translatedFormat('d F Y H:i').' WITA',
                'issueUrl' => route('journalmanager.issues.show', $this->issue->id),
            ]
        );
    }
}
