<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->attributes->has('request_id')) {
            $requestId = $request->header('X-Request-ID') ?? (string) Str::ulid();
            $request->attributes->set('request_id', $requestId);
        }

        $response = $next($request);

        $response->headers->set('X-Request-ID', $request->attributes->get('request_id'));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
