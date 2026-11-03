<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendSecurityOutboxEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $outboxId)
    {
        $this->onConnection('database')->onQueue('identity')->beforeCommit();
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $entry = DB::table('outbox_messages')->where('id', $this->outboxId)->lockForUpdate()->first();
            if (! $entry || $entry->status === 'sent' || $entry->type !== 'security.account_locked_email') {
                return;
            }
            $data = json_decode($entry->payload, true, flags: JSON_THROW_ON_ERROR);
            Mail::raw('Akun SIRIDA Anda mengalami lima percobaan masuk gagal dan dikunci sementara selama 15 menit. Jika bukan Anda, minta pengaturan ulang kata sandi melalui portal resmi.', fn ($mail) => $mail->to($data['email'])->subject('Pemberitahuan keamanan akun SIRIDA'));
            DB::table('outbox_messages')->where('id', $entry->id)->update(['status' => 'sent']);
        });
    }
}
