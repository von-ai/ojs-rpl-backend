<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Outbox\OutboxProcessorService;
use Illuminate\Console\Command;

class ProcessOutboxCommand extends Command
{
    protected $signature = 'outbox:process {--limit=50 : Batas jumlah pesan yang diproses per iterasi}';

    protected $description = 'Memproses pesan transactional outbox yang berstatus pending (email keamanan, notifikasi, integrasi)';

    public function handle(OutboxProcessorService $processor): int
    {
        $limit = (int) $this->option('limit');
        $this->info("Memproses hingga {$limit} pesan outbox pending...");

        $result = $processor->processPendingMessages($limit);

        $this->info("✓ Berhasil memproses {$result['success_count']} pesan.");
        if ($result['failed_count'] > 0) {
            $this->warn("! {$result['failed_count']} pesan gagal diproses.");
        }

        return self::SUCCESS;
    }
}
