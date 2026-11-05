<?php

declare(strict_types=1);

namespace Tests\Feature\Outbox;

use App\Jobs\ProcessOutboxJob;
use App\Jobs\SendSecurityOutboxEmail;
use App\Mail\SecurityAccountLockedMail;
use App\Services\Outbox\OutboxProcessorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class OutboxProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_processor_sends_email_and_marks_message_sent(): void
    {
        Mail::fake();

        $messageId = (string) Str::ulid();
        DB::table('outbox_messages')->insert([
            'id' => $messageId,
            'type' => 'security.account_locked_email',
            'payload' => json_encode([
                'user_id' => (string) Str::ulid(),
                'email' => 'author@makassar.go.id',
                'username' => 'author_test',
                'locked_until' => Carbon::now('UTC')->addMinutes(15)->toIso8601String(),
                'occurred_at' => Carbon::now('UTC')->toIso8601String(),
            ]),
            'status' => 'pending',
            'created_at' => Carbon::now('UTC'),
        ]);

        $service = new OutboxProcessorService;
        $result = $service->processPendingMessages();

        $this->assertEquals(1, $result['processed_count']);
        $this->assertEquals(1, $result['success_count']);
        $this->assertEquals(0, $result['failed_count']);

        $this->assertDatabaseHas('outbox_messages', [
            'id' => $messageId,
            'status' => 'sent',
        ]);

        Mail::assertSent(SecurityAccountLockedMail::class, function ($mail) {
            return $mail->hasTo('author@makassar.go.id')
                && $mail->username === 'author_test';
        });
    }

    public function test_outbox_processor_marks_failed_on_invalid_payload(): void
    {
        $messageId = (string) Str::ulid();
        DB::table('outbox_messages')->insert([
            'id' => $messageId,
            'type' => 'security.account_locked_email',
            'payload' => json_encode([
                'email' => 'invalid-email-format',
            ]),
            'status' => 'pending',
            'created_at' => Carbon::now('UTC'),
        ]);

        $service = new OutboxProcessorService;
        $result = $service->processPendingMessages();

        $this->assertEquals(1, $result['processed_count']);
        $this->assertEquals(0, $result['success_count']);
        $this->assertEquals(1, $result['failed_count']);

        $this->assertDatabaseHas('outbox_messages', [
            'id' => $messageId,
            'status' => 'failed',
        ]);
    }

    public function test_outbox_worker_and_direct_job_cannot_send_the_same_message_twice(): void
    {
        Mail::fake();
        $id = (string) Str::ulid();
        DB::table('outbox_messages')->insert([
            'id' => $id, 'type' => 'security.account_locked_email', 'payload' => '{"email":"audit@example.test"}',
            'status' => 'pending', 'created_at' => now(),
        ]);
        $processor = new class($id) extends OutboxProcessorService
        {
            public function __construct(private string $id) {}

            protected function handleMessage(string $type, array $payload): void
            {
                (new SendSecurityOutboxEmail($this->id))->handle(new OutboxProcessorService);
                parent::handleMessage($type, $payload);
            }
        };

        $processor->processPendingMessages();
        (new SendSecurityOutboxEmail($id))->handle(new OutboxProcessorService);

        Mail::assertSentCount(1);
        $this->assertDatabaseHas('outbox_messages', ['id' => $id, 'status' => 'sent', 'attempt_count' => 1]);
    }

    public function test_unknown_messages_fail_and_transport_errors_retry_later(): void
    {
        $unsupported = (string) Str::ulid();
        $retry = (string) Str::ulid();
        DB::table('outbox_messages')->insert([
            ['id' => $unsupported, 'type' => 'audit.unsupported', 'payload' => '{}', 'status' => 'pending', 'created_at' => now()],
            ['id' => $retry, 'type' => 'security.account_locked_email', 'payload' => '{"email":"audit@example.test"}', 'status' => 'pending', 'created_at' => now()],
        ]);

        $this->assertSame('failed', (new OutboxProcessorService)->processMessage($unsupported));
        $failing = new class extends OutboxProcessorService
        {
            protected function handleMessage(string $type, array $payload): void
            {
                throw new TransportException('Test transport failure');
            }
        };
        $this->assertSame('pending', $failing->processMessage($retry));
        $this->assertNull($failing->processMessage($retry));
        $this->assertDatabaseHas('outbox_messages', ['id' => $retry, 'status' => 'pending', 'attempt_count' => 1]);

        $this->travel(6)->minutes();
        Mail::fake();
        $this->assertSame('sent', (new OutboxProcessorService)->processMessage($retry));
        Mail::assertSentCount(1);
        $this->assertDatabaseHas('outbox_messages', ['id' => $retry, 'status' => 'sent', 'attempt_count' => 2]);
    }

    public function test_outbox_job_runs_processor(): void
    {
        Mail::fake();

        DB::table('outbox_messages')->insert([
            'id' => (string) Str::ulid(),
            'type' => 'security.account_locked_email',
            'payload' => json_encode([
                'email' => 'reviewer@makassar.go.id',
                'username' => 'reviewer_test',
                'locked_until' => Carbon::now('UTC')->addMinutes(15)->toIso8601String(),
                'occurred_at' => Carbon::now('UTC')->toIso8601String(),
            ]),
            'status' => 'pending',
            'created_at' => Carbon::now('UTC'),
        ]);

        $job = new ProcessOutboxJob;
        $job->handle(new OutboxProcessorService);

        Mail::assertSent(SecurityAccountLockedMail::class);
        $this->assertDatabaseMissing('outbox_messages', ['status' => 'pending']);
    }

    public function test_artisan_outbox_process_command(): void
    {
        Mail::fake();

        DB::table('outbox_messages')->insert([
            'id' => (string) Str::ulid(),
            'type' => 'security.account_locked_email',
            'payload' => json_encode([
                'email' => 'editor@makassar.go.id',
                'username' => 'editor_test',
                'locked_until' => Carbon::now('UTC')->addMinutes(15)->toIso8601String(),
                'occurred_at' => Carbon::now('UTC')->toIso8601String(),
            ]),
            'status' => 'pending',
            'created_at' => Carbon::now('UTC'),
        ]);

        $this->artisan('outbox:process', ['--limit' => 10])
            ->expectsOutputToContain('Memproses hingga 10 pesan outbox pending...')
            ->expectsOutputToContain('Berhasil memproses 1 pesan.')
            ->assertExitCode(0);

        Mail::assertSent(SecurityAccountLockedMail::class);
    }
}
