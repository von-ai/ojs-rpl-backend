<?php

declare(strict_types=1);

namespace App\Services\Outbox;

use App\Mail\SecurityAccountLockedMail;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class OutboxProcessorService
{
    /**
     * @return array{processed_count: int, success_count: int, failed_count: int}
     */
    public function processPendingMessages(int $limit = 50): array
    {
        $ids = $this->pending()->orderBy('created_at')->orderBy('id')->limit($limit)->pluck('id');
        $result = ['processed_count' => 0, 'success_count' => 0, 'failed_count' => 0];

        foreach ($ids as $id) {
            $status = $this->processMessage($id);
            if ($status === null) {
                continue;
            }

            $result['processed_count']++;
            $result[$status === 'sent' ? 'success_count' : 'failed_count']++;
        }

        return $result;
    }

    public function processMessage(string $id): ?string
    {
        return DB::transaction(function () use ($id): ?string {
            $message = $this->pending()->where('id', $id)->lockForUpdate()->first();
            if (! $message) {
                return null;
            }

            $attempt = (int) $message->attempt_count + 1;
            $row = DB::table('outbox_messages')->where('id', $id);
            $row->update(['status' => 'processing']);
            $status = 'sent';
            $availableAt = null;

            try {
                $payload = json_decode($message->payload, true, flags: JSON_THROW_ON_ERROR);
                if (! is_array($payload)) {
                    throw new InvalidArgumentException('Payload outbox harus berupa objek.');
                }
                $this->handleMessage($message->type, $payload);
            } catch (InvalidArgumentException|JsonException $e) {
                $status = 'failed';
                Log::warning('Pesan outbox tidak valid.', ['id' => $id, 'type' => $message->type]);
            } catch (TransportExceptionInterface $e) {
                $status = $attempt >= 3 ? 'failed' : 'pending';
                $availableAt = $status === 'pending' ? now()->addMinutes($attempt * 5) : null;
                Log::warning('Pengiriman pesan outbox gagal.', ['id' => $id, 'attempt' => $attempt]);
            }

            $row->update(['status' => $status, 'attempt_count' => $attempt, 'available_at' => $availableAt]);

            return $status;
        });
    }

    private function pending(): Builder
    {
        return DB::table('outbox_messages')->where('status', 'pending')
            ->where(fn (Builder $query) => $query->whereNull('available_at')->orWhere('available_at', '<=', now()));
    }

    /** @param array<string, mixed> $payload */
    protected function handleMessage(string $type, array $payload): void
    {
        match ($type) {
            'security.account_locked_email' => $this->handleAccountLockedEmail($payload),
            default => throw new InvalidArgumentException('Tipe pesan outbox tidak didukung.'),
        };
    }

    /** @param array<string, mixed> $payload */
    private function handleAccountLockedEmail(array $payload): void
    {
        $email = $payload['email'] ?? null;
        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Alamat email tujuan tidak valid.');
        }

        $username = $payload['username'] ?? 'Pengguna';
        $lockedUntil = $payload['locked_until'] ?? '15 menit';
        $occurredAt = $payload['occurred_at'] ?? now()->toIso8601String();
        if (! is_string($username) || ! is_string($lockedUntil) || ! is_string($occurredAt)) {
            throw new InvalidArgumentException('Data notifikasi keamanan tidak valid.');
        }

        Mail::to($email)->send(new SecurityAccountLockedMail($username, $lockedUntil, $occurredAt));
    }
}
