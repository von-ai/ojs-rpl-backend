<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService
    ) {}

    /**
     * Memproses autentikasi REST API mode.
     *
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $requestId = $request->attributes->get('request_id') ?? (string) Str::ulid();

        // 1. Independent IP Rate Limiting (OpenAPI line 8273: rate-limit both account and source independently)
        $ip = $request->ip() ?? '127.0.0.1';
        $ipThrottleKey = 'api-login-ip:'.$ip;
        if (RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            throw (new ApiException(429, 'RATE_LIMITED', "Terlalu banyak permintaan login dari alamat IP ini. Silakan coba lagi dalam {$seconds} detik."))
                ->withHeaders(['Retry-After' => (string) $seconds]);
        }
        RateLimiter::hit($ipThrottleKey, 60);

        // 2. Validasi input payload JSON (OpenAPI: LoginRequest required [login, password])
        $login = $request->input('login') ?? $request->input('email');
        $password = $request->input('password');

        $errors = [];
        if (empty($login)) {
            $errors[] = [
                'field' => 'login',
                'code' => 'REQUIRED',
                'message' => 'Alamat email atau username wajib diisi.',
            ];
        }
        if (empty($password)) {
            $errors[] = [
                'field' => 'password',
                'code' => 'REQUIRED',
                'message' => 'Kata sandi wajib diisi.',
            ];
        }

        if (! empty($errors)) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Data masukan tidak valid.', $errors);
        }

        // 3. Eksekusi autentikasi & penerbitan token
        $response = $this->authService->authenticateApi((string) $login, (string) $password, $ip, $requestId);

        return response()->json($response, 200, [
            'Content-Type' => 'application/json',
            'X-Request-ID' => $requestId,
            'Cache-Control' => 'no-store',
        ]);
    }
}
