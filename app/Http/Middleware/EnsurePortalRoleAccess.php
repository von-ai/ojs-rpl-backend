<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalRoleAccess
{
    /**
     * Memastikan pengguna yang memiliki daftar peran di user_roles hanya dapat mengakses portal yang sesuai dengan perannya.
     */
    public function handle(Request $request, Closure $next, string $roleCode): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }

            return redirect()->guest(route('login'));
        }

        if (! $user->hasRole($roleCode)) {
            $targetMeta = UserRole::roleMeta($roleCode);
            $message = "Anda tidak memiliki hak akses ke {$targetMeta['portal_label']}.";

            if ($request->expectsJson()) {
                abort(403, $message);
            }

            return redirect()
                ->route($user->defaultPortalRoute())
                ->with('error', $message);
        }

        if ($request->hasSession()) {
            $request->session()->put('active_role', $roleCode);
        }

        return $next($request);
    }
}
