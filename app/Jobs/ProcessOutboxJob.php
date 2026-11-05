<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Outbox\OutboxProcessorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessOutboxJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $limit = 50
    ) {}

    public function handle(OutboxProcessorService $processor): void
    {
        $result = $processor->processPendingMessages($this->limit);

        if ($result['processed_count'] > 0) {
            Log::info("ProcessOutboxJob selesai: {$result['success_count']} berhasil, {$result['failed_count']} gagal dari {$result['processed_count']} pesan.");
        }
    }
}
