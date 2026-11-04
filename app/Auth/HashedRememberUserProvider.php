<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class HashedRememberUserProvider extends EloquentUserProvider
{
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        $user = $this->retrieveById($identifier);
        if (! $user || ! $user->remember_expires_at || now()->greaterThanOrEqualTo($user->remember_expires_at)) {
            return null;
        }
        $digest = $user->getRawOriginal($user->getRememberTokenName());

        return $digest && hash_equals($digest, hash_hmac('sha256', $token, (string) config('app.key'))) ? $user : null;
    }
}
