<?php

namespace App\Services\Auth;

use App\Models\User;

class TokenService
{
    /**
     * Membuat pasangan access token (JWT, maks 24 jam) dan refresh token opaque.
     */
    public function createTokenPair(User $user, string $sessionId): array
    {
        $accessExpiresAt = now()->addHours(24);
        $refreshExpiresAt = now()->addDays(7);

        $payload = [
            'iss' => config('app.url', 'http://localhost'),
            'sub' => $user->id,
            'session_id' => $sessionId,
            'iat' => now()->timestamp,
            'exp' => $accessExpiresAt->timestamp,
        ];

        $accessToken = $this->encodeJwt($payload);
        $refreshToken = bin2hex(random_bytes(32));

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'access_token_expires_at' => $accessExpiresAt->toIso8601String(),
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => $refreshExpiresAt->toIso8601String(),
        ];
    }

    /**
     * Encode JWT HS256 standar RFC 7519.
     */
    protected function encodeJwt(array $payload): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $headerB64 = $this->base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadB64 = $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
        $secret = (string) (config('app.key') ?? 'base64:99999999999999999999999999999999');
        $signature = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $secret, true);
        $signatureB64 = $this->base64UrlEncode($signature);

        return "{$headerB64}.{$payloadB64}.{$signatureB64}";
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
