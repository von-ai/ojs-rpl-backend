<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ReviewAssignment;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckReviewDeadlinesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        $now = Carbon::now('UTC');

        // 1. Tandai penugasan telaah yang melewati deadline review sebagai 'overdue'
        $overdueCount = ReviewAssignment::query()
            ->where('status', 'accepted')
            ->whereNotNull('review_due_at')
            ->where('review_due_at', '<=', $now)
            ->update(['status' => 'overdue']);

        // 2. Tandai undangan telaah yang tidak direspon hingga batas waktu sebagai 'no_response'
        $noResponseCount = ReviewAssignment::query()
            ->where('status', 'invited')
            ->whereNotNull('response_due_at')
            ->where('response_due_at', '<=', $now)
            ->update(['status' => 'no_response']);

        if ($overdueCount > 0 || $noResponseCount > 0) {
            Log::info("CheckReviewDeadlinesJob: {$overdueCount} penugasan telaah ditandai overdue, {$noResponseCount} undangan ditandai no_response.");
        }
    }
}
