<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendReviewNotice implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public string $email, public string $subject, public string $body, public ?string $reminderId = null)
    {
        $this->onConnection('database')->onQueue('notifications')->beforeCommit();
    }

    public function handle(): void
    {
        Mail::raw($this->body, fn ($mail) => $mail->to($this->email)->subject($this->subject));
        if ($this->reminderId) {
            DB::table('review_reminders')->where('id', $this->reminderId)->update(['delivery_status' => 'sent', 'updated_at' => now()]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($this->reminderId) {
            DB::table('review_reminders')->where('id', $this->reminderId)->update(['delivery_status' => 'failed', 'updated_at' => now()]);
        }
    }
}
