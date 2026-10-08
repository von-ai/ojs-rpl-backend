<?php

namespace App\Http\Middleware;

use App\Exceptions\IdentityException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IdentityApi
{
    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('request_id', (string) Str::uuid());
        if (! $request->isJson()) {
            throw new IdentityException(415, 'UNSUPPORTED_MEDIA_TYPE', 'Gunakan Content-Type: application/json.');
        }
        $body = json_decode($request->getContent());
        if (json_last_error() !== JSON_ERROR_NONE || ! is_object($body)) {
            throw new IdentityException(400, 'INVALID_JSON', 'Body harus berupa objek JSON yang valid.');
        }
        $response = $next($request);

        return $response->header('Cache-Control', 'no-store')
            ->header('X-Request-ID', $request->attributes->get('request_id'));
    }
}
