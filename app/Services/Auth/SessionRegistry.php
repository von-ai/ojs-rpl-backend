<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionRegistry
{
    public function start(User $user, bool $fromRemember = false): array
    {
        $id = (string) Str::ulid();
        $expires = now()->addDay();
        if ($fromRemember && $user->remember_expires_at?->lt($expires)) {
            $expires = $user->remember_expires_at;
        }
        $fingerprint = hash_hmac('sha256', $user->getAuthPassword(), (string) config('app.key'));
        DB::table('auth_sessions')->insert([
            'id' => $id, 'user_id' => $user->id, 'password_fingerprint' => $fingerprint,
            'absolute_expires_at' => $expires, 'version' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return ['auth_session_id' => $id];
    }

    public function revoke(?string $id): void
    {
        if ($id) {
            DB::table('auth_sessions')->where('id', $id)->whereNull('revoked_at')->update(['revoked_at' => now(), 'version' => DB::raw('version + 1'), 'updated_at' => now()]);
        }
    }
}
