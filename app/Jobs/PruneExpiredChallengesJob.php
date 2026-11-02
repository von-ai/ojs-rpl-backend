<?php

declare(strict_types=1);

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneExpiredChallengesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        $pruned = DB::table('identity_challenges')
            ->where('expires_at', '<', Carbon::now('UTC')->subDay())
            ->delete();

        if ($pruned > 0) {
            Log::info("PruneExpiredChallengesJob: Berhasil membersihkan {$pruned} identity challenge kedaluwarsa.");
        }
    }
}
