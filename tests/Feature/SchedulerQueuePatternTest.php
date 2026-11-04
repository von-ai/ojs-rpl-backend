<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\BackupDatabaseToAzureJob;
use App\Jobs\CheckReviewDeadlinesJob;
use App\Jobs\ProcessOutboxJob;
use App\Jobs\PruneExpiredChallengesJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SchedulerQueuePatternTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_jobs_implement_should_queue_interface(): void
    {
        $this->assertTrue(is_subclass_of(CheckReviewDeadlinesJob::class, ShouldQueue::class));
        $this->assertTrue(is_subclass_of(PruneExpiredChallengesJob::class, ShouldQueue::class));
        $this->assertTrue(is_subclass_of(BackupDatabaseToAzureJob::class, ShouldQueue::class));
        $this->assertTrue(is_subclass_of(ProcessOutboxJob::class, ShouldQueue::class));
    }

    public function test_scheduler_registers_queued_jobs(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $hasDeadlineJob = $events->contains(function ($event) {
            return str_contains($event->description ?? '', CheckReviewDeadlinesJob::class)
                || str_contains((string) $event->command, CheckReviewDeadlinesJob::class);
        });

        $hasPruneJob = $events->contains(function ($event) {
            return str_contains($event->description ?? '', PruneExpiredChallengesJob::class)
                || str_contains((string) $event->command, PruneExpiredChallengesJob::class);
        });

        $hasBackupJob = $events->contains(function ($event) {
            return str_contains($event->description ?? '', BackupDatabaseToAzureJob::class)
                || str_contains((string) $event->command, BackupDatabaseToAzureJob::class);
        });

        $hasOutboxJob = $events->contains(function ($event) {
            return str_contains($event->description ?? '', ProcessOutboxJob::class)
                || str_contains((string) $event->command, ProcessOutboxJob::class);
        });

        $this->assertTrue($hasDeadlineJob, 'CheckReviewDeadlinesJob harus terdaftar di scheduler.');
        $this->assertTrue($hasPruneJob, 'PruneExpiredChallengesJob harus terdaftar di scheduler.');
        $this->assertTrue($hasBackupJob, 'BackupDatabaseToAzureJob harus terdaftar di scheduler.');
        $this->assertTrue($hasOutboxJob, 'ProcessOutboxJob harus terdaftar di scheduler.');
    }

    public function test_prune_expired_challenges_job_cleans_up_stale_records(): void
    {
        $now = Carbon::now('UTC');

        $user = User::factory()->create();

        // Challenge kedaluwarsa 2 hari lalu
        DB::table('identity_challenges')->insert([
            'id' => (string) Str::ulid(),
            'user_id' => $user->id,
            'purpose' => 'email_verification',
            'token_hash' => hash('sha256', 'expired-token'),
            'expires_at' => $now->copy()->subDays(2),
            'consumed_at' => null,
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(2),
        ]);

        // Challenge yang masih aktif
        $activeId = (string) Str::ulid();
        DB::table('identity_challenges')->insert([
            'id' => $activeId,
            'user_id' => $user->id,
            'purpose' => 'email_verification',
            'token_hash' => hash('sha256', 'active-token'),
            'expires_at' => $now->copy()->addHour(),
            'consumed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Jalankan job
        (new PruneExpiredChallengesJob)->handle();

        // Pastikan record kedaluwarsa dihapus dan record aktif dipertahankan
        $this->assertEquals(1, DB::table('identity_challenges')->count());
        $this->assertDatabaseHas('identity_challenges', ['id' => $activeId]);
    }
}
