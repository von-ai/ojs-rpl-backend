<?php

declare(strict_types=1);

namespace Tests\Feature\Outbox;

use App\Jobs\ProcessOutboxJob;
use App\Mail\SecurityAccountLockedMail;
use App\Services\Outbox\OutboxProcessorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
