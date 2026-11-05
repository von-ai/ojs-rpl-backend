<?php

declare(strict_types=1);

namespace App\Services\Outbox;

use App\Mail\SecurityAccountLockedMail;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OutboxProcessorService
{
    /**
     * Memproses batch pesan outbox yang berstatus 'pending'.
     *
     * @return array{
     *     processed_count: int,
     *     success_count: int,
     *     failed_count: int
     * }
     */
    public function processPendingMessages(int $limit = 50): array
    {
        $messages = DB::table('outbox_messages')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        foreach ($messages as $msg) {
            $processedCount++;
            try {
                $payload = json_decode((string) $msg->payload, true) ?: [];

                $this->handleMessage($msg->type, $payload);

                DB::table('outbox_messages')
                    ->where('id', $msg->id)
                    ->update(['status' => 'sent']);

                $successCount++;
                Log::info("Outbox message {$msg->id} [{$msg->type}] berhasil diproses.");
            } catch (Exception $e) {
                $failedCount++;
                DB::table('outbox_messages')
                    ->where('id', $msg->id)
                    ->update(['status' => 'failed']);

                Log::error("Gagal memproses outbox message {$msg->id} [{$msg->type}]: {$e->getMessage()}", [
                    'exception' => $e,
                ]);
            }
        }

        return [
            'processed_count' => $processedCount,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ];
    }

    /**
     * Menjalankan handler sesuai tipe pesan outbox.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handleMessage(string $type, array $payload): void
    {
        match ($type) {
            'security.account_locked_email' => $this->handleAccountLockedEmail($payload),
            default => Log::warning("Tipe outbox message '{$type}' tidak memiliki handler terdaftar."),
        };
    }

    /**
     * Mengirimkan email notifikasi akun terkunci ke pengguna.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handleAccountLockedEmail(array $payload): void
    {
        $email = $payload['email'] ?? null;
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Alamat email tujuan tidak valid dalam payload outbox.');
        }

        $username = (string) ($payload['username'] ?? 'Pengguna');
        $lockedUntil = (string) ($payload['locked_until'] ?? '15 menit');
        $occurredAt = (string) ($payload['occurred_at'] ?? now()->toIso8601String());

        Mail::to($email)->send(new SecurityAccountLockedMail($username, $lockedUntil, $occurredAt));
    }
}
