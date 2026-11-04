<?php

namespace Tests\Feature;

use App\Mail\VerifyRegistrationEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Penulis SIRIDA',
            'email' => 'author@example.org',
            'username' => 'penulis',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'affiliation' => 'Universitas Hasanuddin',
        ], $overrides);
    }

    private function queuedToken(): string
    {
        $payload = json_decode(DB::table('jobs')->latest('id')->value('payload'), true);
        $job = unserialize(Crypt::decrypt($payload['data']['command']));
        $property = new \ReflectionProperty($job, 'token');

        return $property->getValue($job);
    }

    public function test_registration_persists_pending_identity_profile_hashed_password_and_encrypted_email_atomically(): void
    {
        $response = $this->post('/register', $this->payload([
            'email' => 'AUTHOR@EXAMPLE.ORG',
            'username' => 'Penulis',
        ]))->assertRedirect('/register/check-email')
            ->assertSessionHas('registered', true);

        $user = User::firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $user->id);
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('identity_challenges', ['user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $user->id, 'resource_id' => $user->id]);
        $this->assertSame('author@example.org', $user->email);
        $this->assertSame('penulis', $user->username);
        $this->assertSame('pending', $user->status);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));

        $token = $this->queuedToken();
        $this->assertDatabaseHas('identity_challenges', ['token_hash' => hash('sha256', $token)]);
        $this->assertStringNotContainsString($token, DB::table('jobs')->value('payload'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'account_registered']);
        $this->assertGuest();
    }

    public function test_erd_user_primary_key_and_all_user_foreign_keys_are_ulids(): void
    {
        $user = User::factory()->create();
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $user->id);
        $this->assertSame('string', $user->getKeyType());
        $this->assertFalse($user->getIncrementing());
        $this->assertFalse(Schema::hasColumn('users', 'public_id'));

        foreach ([
            'users' => 'id',
            'user_profiles' => 'user_id',
            'identity_challenges' => 'user_id',
            'audit_logs' => 'actor_id',
            'sessions' => 'user_id',
        ] as $table => $column) {
            $definition = collect(Schema::getColumns($table))->firstWhere('name', $column);
            $this->assertContains($definition['type_name'], ['char', 'bpchar', 'varchar'], $table.'.'.$column);
            if ($table !== 'users') {
                $foreign = collect(Schema::getForeignKeys($table))->first(fn ($key) => $key['columns'] === [$column]);
                $this->assertNotNull($foreign, $table.'.'.$column);
                $this->assertSame('users', $foreign['foreign_table']);
                $this->assertSame(['id'], $foreign['foreign_columns']);
            }
        }
    }

    public function test_duplicate_email_and_username_are_case_insensitive_conflicts(): void
    {
        $this->post('/register', $this->payload())->assertRedirect('/register/check-email');
        $this->post('/register', $this->payload(['email' => 'AUTHOR@EXAMPLE.ORG', 'username' => 'other']))
            ->assertSessionHasErrors('email');
        $this->post('/register', $this->payload(['email' => 'other@example.org', 'username' => 'PENULIS']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_registration_rejects_privilege_fields_and_incomplete_or_invalid_input(): void
    {
        foreach ([
            ['requested_role' => 'reviewer'],
            ['journal_id' => '123'],
            ['status' => 'active'],
            ['password' => 'short', 'password_confirmation' => 'short'],
            ['username' => 'not valid'],
            ['affiliation' => 'x'],
            ['full_name' => 'x'],
            ['password_confirmation' => 'mismatch'],
        ] as $invalid) {
            $this->post('/register', $this->payload($invalid))
                ->assertSessionHasErrors();
        }

        $this->post('/register', [])->assertSessionHasErrors();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_long_unicode_passwords_follow_the_contract_without_bcrypt_truncation(): void
    {
        $password = str_repeat('密', 128);
        $this->post('/register', $this->payload(['password' => $password, 'password_confirmation' => $password]))
            ->assertRedirect('/register/check-email');
        $this->assertTrue(Hash::check($password, User::first()->password));
        $this->assertFalse(Hash::check(str_repeat('密', 127).'別', User::first()->password));
    }

    public function test_verification_activates_account_once_and_records_audit(): void
    {
        $this->post('/register', $this->payload())->assertRedirect('/register/check-email');
        $token = $this->queuedToken();

        $this->post('/email/verify', ['token' => $token])
            ->assertRedirect('/register/check-email')
            ->assertSessionHas('verified', true);

        $user = User::first();
        $this->assertSame('active', $user->status);
        $this->assertSame(2, $user->version);
        $this->assertNotNull($user->email_verified_at);

        $this->post('/email/verify', ['token' => $token])->assertStatus(410);
        $this->assertDatabaseHas('audit_logs', ['action' => 'email_verified']);
        $this->assertDatabaseCount('audit_logs', 2);
        $this->assertGuest();
    }

    public function test_expired_unknown_and_exact_deadline_tokens_are_rejected(): void
    {
        $this->post('/register', $this->payload())->assertRedirect('/register/check-email');
        $token = $this->queuedToken();
        DB::table('identity_challenges')->update(['expires_at' => DB::raw('CURRENT_TIMESTAMP')]);

        $this->post('/email/verify', ['token' => $token])->assertStatus(410);
        $this->post('/email/verify', ['token' => str_repeat('a', 64)])->assertStatus(410);
        $this->assertSame('pending', User::first()->status);
    }

    public function test_resend_has_generic_response_cooldown_and_invalidates_old_token(): void
    {
        $this->post('/register', $this->payload())->assertRedirect('/register/check-email');
        $old = $this->queuedToken();

        $this->post('/email/resend', ['email' => 'author@example.org'])
            ->assertRedirect('/register/check-email')
            ->assertSessionHas('resent', true);
        $this->assertDatabaseCount('jobs', 1);

        DB::table('identity_challenges')->update(['created_at' => now()->subMinutes(2)]);
        $this->post('/email/resend', ['email' => 'AUTHOR@EXAMPLE.ORG'])
            ->assertRedirect('/register/check-email')
            ->assertSessionHas('resent', true);
        $this->assertDatabaseCount('jobs', 2);

        $this->post('/email/verify', ['token' => $old])->assertStatus(410);
        $this->post('/email/verify', ['token' => $this->queuedToken()])
            ->assertRedirect('/register/check-email')
            ->assertSessionHas('verified', true);

        $this->post('/email/resend', ['email' => 'unknown@example.org'])
            ->assertRedirect('/register/check-email')
            ->assertSessionHas('resent', true);
        $this->post('/email/resend', ['email' => 'author@example.org'])
            ->assertRedirect('/register/check-email')
            ->assertSessionHas('resent', true);
        $this->assertDatabaseCount('jobs', 2);
    }

    public function test_queue_worker_sends_functional_verification_email(): void
    {
        Mail::fake();
        $this->post('/register', $this->payload())->assertRedirect('/register/check-email');
        $token = $this->queuedToken();

        $this->artisan('queue:work', ['connection' => 'identity', '--queue' => 'identity', '--once' => true])->assertSuccessful();
        Mail::assertSent(VerifyRegistrationEmail::class, function ($mail) use ($token) {
            return $mail->hasTo('author@example.org') && str_contains($mail->render(), '/email/verify?token='.$token);
        });
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_web_mvp_keeps_safe_input_and_supports_register_verify_and_resend(): void
    {
        $this->get('/register')->assertOk()->assertSee('name="full_name"', false)->assertSee('name="_token"', false);
        $this->post('/register', $this->payload(['password_confirmation' => 'mismatch']))
            ->assertSessionHasErrors('password')->assertSessionMissing('_old_input.password');
        $this->post('/register', $this->payload(['password_confirmation' => 'StrongPassword123!']))
            ->assertRedirect('/register/check-email');

        $token = $this->queuedToken();
        $this->get('/email/verify?token='.$token)->assertOk()->assertSee($token)->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertSame('pending', User::first()->status);

        $this->post('/email/verify', ['token' => $token])->assertRedirect('/register/check-email');
        $this->get('/register/check-email')->assertOk()->assertSee('Akun Anda aktif');
        $this->post('/email/resend', ['email' => 'unknown@example.org'])->assertRedirect('/register/check-email');
    }

    public function test_resend_rate_limits(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/email/resend', ['email' => 'unknown@example.org'])->assertRedirect('/register/check-email');
        }
        $this->post('/email/resend', ['email' => 'unknown@example.org'])->assertStatus(429);
    }

    public function test_queue_failure_rolls_back_the_entire_registration(): void
    {
        $queue = \Mockery::mock(Queue::class);
        $queue->shouldReceive('push')->andThrow(new \RuntimeException('Simulated durable queue failure'));
        \Illuminate\Support\Facades\Queue::partialMock()->shouldReceive('connection')->with('identity')->andReturn($queue);
        try {
            $this->post('/register', $this->payload());
        } catch (\Throwable) {
            // Queue table missing throws exception
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('user_profiles', 0);
        $this->assertDatabaseCount('identity_challenges', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
