<?php

namespace Tests\Feature;

use App\Mail\VerifyRegistrationEmail;
use App\Models\User;
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

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Penulis SIRIDA', 'email' => 'author@example.org',
            'username' => 'penulis', 'password' => 'StrongPassword123!',
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
        $response = $this->postJson('/api/v1/auth/register', $this->payload([
            'email' => 'AUTHOR@EXAMPLE.ORG', 'username' => 'Penulis',
        ]))->assertCreated()->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Location', '/register/check-email')
            ->assertJsonPath('data.attributes.status', 'pending')
            ->assertJsonPath('data.attributes.email_verified', false)
            ->assertJsonPath('data.attributes.affiliation', 'Universitas Hasanuddin');
        $user = User::firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $response->json('data.id'));
        $this->assertSame($user->id, $response->json('data.id'));
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('identity_challenges', ['user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $user->id, 'resource_id' => $user->id]);
        $this->assertSame('author@example.org', $user->email);
        $this->assertSame('penulis', $user->username);
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));
        $this->assertStringNotContainsString('StrongPassword123!', $response->getContent());
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
        foreach (['users' => 'id', 'user_profiles' => 'user_id', 'identity_challenges' => 'user_id',
            'audit_logs' => 'actor_id', 'sessions' => 'user_id'] as $table => $column) {
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
        $this->postJson('/api/v1/auth/register', $this->payload())->assertCreated();
        $this->postJson('/api/v1/auth/register', $this->payload(['email' => 'AUTHOR@EXAMPLE.ORG', 'username' => 'other']))
            ->assertConflict()->assertJsonPath('code', 'ACCOUNT_ALREADY_EXISTS');
        $this->postJson('/api/v1/auth/register', $this->payload(['email' => 'other@example.org', 'username' => 'PENULIS']))->assertConflict();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_registration_rejects_privilege_fields_and_incomplete_or_invalid_input(): void
    {
        foreach ([['requested_role' => 'reviewer'], ['journal_id' => '123'], ['status' => 'active'],
            ['password' => 'short'], ['username' => 'not valid'], ['affiliation' => 'x'], ['full_name' => 'x']] as $invalid) {
            $this->postJson('/api/v1/auth/register', $this->payload($invalid))->assertUnprocessable()
                ->assertHeader('Content-Type', 'application/problem+json');
        }
        $this->call('POST', '/api/v1/auth/register', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{}')->assertUnprocessable();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_long_unicode_passwords_follow_the_contract_without_bcrypt_truncation(): void
    {
        $password = str_repeat('密', 128);
        $this->postJson('/api/v1/auth/register', $this->payload(['password' => $password]))->assertCreated();
        $this->assertTrue(Hash::check($password, User::first()->password));
        $this->assertFalse(Hash::check(str_repeat('密', 127).'別', User::first()->password));
    }

    public function test_verification_activates_account_once_and_records_audit(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload())->assertCreated();
        $token = $this->queuedToken();
        $this->postJson('/api/v1/auth/email/verify', ['token' => $token])->assertOk()
            ->assertJsonPath('data.attributes.status', 'active')
            ->assertJsonPath('data.attributes.version', 2)
            ->assertJsonPath('data.attributes.email_verified', true);
        $this->postJson('/api/v1/auth/email/verify', ['token' => $token])->assertGone();
        $this->assertDatabaseHas('audit_logs', ['action' => 'email_verified']);
        $this->assertDatabaseCount('audit_logs', 2);
        $this->assertGuest();
    }

    public function test_expired_unknown_and_exact_deadline_tokens_are_rejected(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload())->assertCreated();
        $token = $this->queuedToken();
        DB::table('identity_challenges')->update(['expires_at' => DB::raw('CURRENT_TIMESTAMP')]);
        $this->postJson('/api/v1/auth/email/verify', ['token' => $token])->assertGone();
        $this->postJson('/api/v1/auth/email/verify', ['token' => str_repeat('a', 64)])->assertGone();
        $this->assertSame('pending', User::first()->status);
    }

    public function test_resend_has_generic_response_cooldown_and_invalidates_old_token(): void
    {
        $this->postJson('/api/v1/auth/register', $this->payload())->assertCreated();
        $old = $this->queuedToken();
        $this->postJson('/api/v1/auth/email/resend', ['email' => 'author@example.org'])->assertAccepted()->assertContent('');
        $this->assertDatabaseCount('jobs', 1);
        DB::table('identity_challenges')->update(['created_at' => now()->subMinutes(2)]);
        $this->postJson('/api/v1/auth/email/resend', ['email' => 'AUTHOR@EXAMPLE.ORG'])->assertAccepted()->assertContent('');
        $this->assertDatabaseCount('jobs', 2);
        $this->postJson('/api/v1/auth/email/verify', ['token' => $old])->assertGone();
        $this->postJson('/api/v1/auth/email/verify', ['token' => $this->queuedToken()])->assertOk();
        $this->postJson('/api/v1/auth/email/resend', ['email' => 'unknown@example.org'])->assertAccepted()->assertContent('');
        $this->postJson('/api/v1/auth/email/resend', ['email' => 'author@example.org'])->assertAccepted()->assertContent('');
        $this->assertDatabaseCount('jobs', 2);
    }

    public function test_queue_worker_sends_functional_verification_email(): void
    {
        Mail::fake();
        $this->postJson('/api/v1/auth/register', $this->payload())->assertCreated();
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

    public function test_api_rejects_non_json_malformed_json_and_rate_limits(): void
    {
        $this->post('/api/v1/auth/register', $this->payload())->assertStatus(415);
        $this->call('POST', '/api/v1/auth/register', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{broken')->assertBadRequest();
        for ($i = 0; $i < 8; $i++) {
            $this->postJson('/api/v1/auth/email/resend', ['email' => 'unknown@example.org'])->assertAccepted();
        }
        $this->postJson('/api/v1/auth/email/resend', ['email' => 'unknown@example.org'])->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_queue_failure_rolls_back_the_entire_registration(): void
    {
        DB::statement('DROP TABLE jobs');
        $this->postJson('/api/v1/auth/register', $this->payload())->assertStatus(500);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('user_profiles', 0);
        $this->assertDatabaseCount('identity_challenges', 0);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
