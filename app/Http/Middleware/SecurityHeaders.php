<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'");
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }
        $isNoCacheRoute = $request->user() !== null
            || $request->is(
                'login',
                'register',
                'password/*',
                'email/*',
                'dashboard',
                'auth/*',
                'sysadmin',
                'sysadmin/*',
                'journalmanager',
                'journalmanager/*',
                'eic',
                'eic/*',
                'section-editor',
                'section-editor/*',
                'reviewer',
                'reviewer/*',
                'copyeditor',
                'copyeditor/*',
                'layouteditor',
                'layouteditor/*',
                'proofreader',
                'proofreader/*',
                'author',
                'author/*'
            );

        if ($isNoCacheRoute) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('CDN-Cache-Control', 'no-store');
            $response->headers->set('Surrogate-Control', 'no-store');
        }

        return $response;
    }
}
