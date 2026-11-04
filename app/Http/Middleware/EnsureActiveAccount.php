<?php

namespace App\Http\Middleware;

use App\Services\Auth\SessionRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($authenticated = $request->user()) {
            $user = $authenticated->fresh();
            $registry = app(SessionRegistry::class);
            if ($user && ! $request->session()->has('auth_session_id') && Auth::viaRemember()) {
                $request->session()->put($registry->start($user, true));
            }
            $entry = DB::table('auth_sessions')->where('id', $request->session()->get('auth_session_id'))->first();
            $fingerprint = hash_hmac('sha256', $user?->getAuthPassword() ?? '', (string) config('app.key'));
            if (! $user || $user->status !== 'active' || $user->email_verified_at === null || $user->trashed()
                || ! $entry || $entry->user_id !== $user->id || $entry->revoked_at !== null
                || now()->greaterThanOrEqualTo($entry->absolute_expires_at)
                || ! hash_equals($entry->password_fingerprint, $fingerprint)) {
                $registry->revoke($request->session()->get('auth_session_id'));
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $request->expectsJson() ? response()->json(['message' => 'Sesi berakhir. Silakan masuk kembali.'], 401) : redirect()->route('login')->with('error', 'Sesi berakhir. Silakan masuk kembali.');
            }
            Auth::setUser($user);

        }

        return $next($request);
    }
}
