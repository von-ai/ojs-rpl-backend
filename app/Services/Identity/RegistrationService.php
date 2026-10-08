<?php

namespace App\Services\Identity;

use App\Exceptions\IdentityException;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationService
{
    public function register(array $input): User
    {
        try {
            return DB::transaction(function () use ($input) {
                if (User::whereRaw('LOWER(email) = ?', [$input['email']])
                    ->orWhereRaw('LOWER(username) = ?', [$input['username']])->exists()) {
                    throw new IdentityException(409, 'ACCOUNT_ALREADY_EXISTS', 'Email atau username sudah terdaftar.');
                }
                $user = new User;
                $user->forceFill([
                    'name' => $input['full_name'],
                    'email' => $input['email'],
                    'username' => $input['username'],
                    'password' => Hash::make($input['password']),
                    'status' => 'pending',
                    'version' => 1,
                ])->save();
                DB::table('user_profiles')->insert([
                    'user_id' => $user->id, 'affiliation' => $input['affiliation'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->issueChallenge($user);
                $this->audit($user, 'account_registered');

                return $user;
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw new IdentityException(409, 'ACCOUNT_ALREADY_EXISTS', 'Email atau username sudah terdaftar.');
        }
    }

    public function verify(string $token): User
    {
        return DB::transaction(function () use ($token) {
            // All identity mutations lock the user first, then its challenges.
            $challenge = DB::table('identity_challenges')->where('token_hash', hash('sha256', $token))->first();
            if (! $challenge) {
                throw $this->expired();
            }
            $user = User::whereKey($challenge->user_id)->lockForUpdate()->firstOrFail();
            $challenge = DB::table('identity_challenges')->where('id', $challenge->id)->lockForUpdate()->first();
            $clock = $this->databaseNow();
            if ($challenge->purpose !== 'email_verification' || $challenge->consumed_at !== null
                || strtotime($challenge->expires_at) <= strtotime($clock) || $user->status !== 'pending') {
                throw $this->expired();
            }
            DB::table('identity_challenges')->where('user_id', $user->id)->whereNull('consumed_at')->update(['consumed_at' => $clock]);
            $user->forceFill(['email_verified_at' => $clock, 'status' => 'active', 'version' => $user->version + 1])->save();
            $this->audit($user, 'email_verified');

            return $user;
        });
    }

    public function resend(string $email): void
    {
        DB::transaction(function () use ($email) {
            $user = User::where('email', $email)->lockForUpdate()->first();
            if (! $user || $user->status !== 'pending' || $user->email_verified_at !== null) {
                return;
            }
            $latest = DB::table('identity_challenges')->where('user_id', $user->id)->latest('created_at')->first();
            $clock = $this->databaseNow();
            if ($latest && strtotime($latest->created_at) > strtotime($clock) - 60) {
                return;
            }
            DB::table('identity_challenges')->where('user_id', $user->id)->whereNull('consumed_at')->update(['consumed_at' => $clock]);
            $this->issueChallenge($user);
            $this->audit($user, 'verification_resent');
        });
    }

    private function issueChallenge(User $user): void
    {
        $token = bin2hex(random_bytes(32));
        $id = (string) Str::ulid();
        $clock = $this->databaseNow();
        DB::table('identity_challenges')->insert([
            'id' => $id, 'user_id' => $user->id, 'purpose' => 'email_verification',
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', strtotime($clock) + 86400),
            'consumed_at' => null, 'created_at' => $clock, 'updated_at' => $clock,
        ]);
        // This database queue shares the identity transaction: no commit/enqueue gap.
        SendVerificationEmail::dispatch($id, $token)->onConnection('identity')->beforeCommit();
    }

    private function databaseNow(): string
    {
        $sql = DB::getDriverName() === 'pgsql'
            ? "SELECT clock_timestamp() AT TIME ZONE 'UTC' AS instant"
            : 'SELECT CURRENT_TIMESTAMP AS instant';

        return DB::selectOne($sql)->instant;
    }

    private function audit(User $user, string $action): void
    {
        DB::table('audit_logs')->insert([
            'id' => (string) Str::ulid(), 'actor_id' => $user->id, 'actor_type' => 'human',
            'resource_type' => 'user', 'resource_id' => $user->id,
            'action' => $action, 'occurred_at' => $this->databaseNow(),
            'request_id' => request()->attributes->get('request_id'),
        ]);
    }

    private function expired(): IdentityException
    {
        return new IdentityException(410, 'VERIFICATION_TOKEN_EXPIRED', 'Tautan verifikasi tidak valid, kedaluwarsa, atau sudah digunakan.');
    }
}
