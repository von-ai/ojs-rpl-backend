<?php

namespace App\Jobs;

use App\Mail\VerifyRegistrationEmail;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public string $challengeId, private string $token) {}

    public function backoff(): array
    {
        return [30, 120, 300, 600];
    }

    public function handle(): void
    {
        $challenge = DB::table('identity_challenges')->where('id', $this->challengeId)->first();
        if (! $challenge || $challenge->consumed_at !== null || strtotime($challenge->expires_at) <= time()) {
            return;
        }
        $user = DB::table('users')->where('id', $challenge->user_id)->first();
        if ($user && $user->status === 'pending') {
            Mail::to($user->email)->send(new VerifyRegistrationEmail($this->token));
        }
    }
}
