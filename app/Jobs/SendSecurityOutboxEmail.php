<?php

namespace App\Jobs;

use App\Services\Outbox\OutboxProcessorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSecurityOutboxEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $outboxId)
    {
        $this->onConnection('database')->onQueue('identity')->beforeCommit();
    }

    public function handle(OutboxProcessorService $processor): void
    {
        $processor->processMessage($this->outboxId);
    }
}
