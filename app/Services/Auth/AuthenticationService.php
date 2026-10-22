<?php

namespace App\Services\Auth;

use App\Exceptions\ApiException;
use App\Models\AuthSession;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(
        protected TokenService $tokenService
    ) {}

    /**
     * Autentikasi untuk REST API Mode (mengembalikan array AuthTokenResponse).
     *
     * @throws ApiException
     */
    public function authenticateApi(string $login, string $password, ?string $ip = null, ?string $requestId = null): array
    {
        $user = $this->attemptLogin($login, $password, isApi: true);

        // Buat AuthSession (ERD Modul 01 Tabel 6)
        $session = AuthSession::create([
            'user_id' => $user->id,
            'active_membership_id' => null,
            'active_role' => null,
            'absolute_expires_at' => now()->addHours(24),
            'revoked_at' => null,
            'version' => 1,
        ]);

        // Buat Token Pasangan (JWT Access Token + Opaque Refresh Token)
        $tokenData = $this->tokenService->createTokenPair($user, $session->id);

        // Simpan Refresh Token (ERD Modul 01 Tabel 7)
        RefreshToken::create([
            'session_id' => $session->id,
            'token_hash' => hash('sha256', $tokenData['refresh_token']),
            'parent_token_id' => null,
            'used_at' => null,
            'expires_at' => $tokenData['refresh_token_expires_at'],
            'revoked_at' => null,
        ]);

        // Catat Audit Log Login
        $this->recordAudit($user->id, 'user.login.success', [
            'transport' => 'bearer',
            'session_id' => $session->id,
        ], $ip, $requestId);

        $user->loadMissing('profile');

        return [
            'data' => [
                'tokens' => $tokenData,
                'user' => [
                    'id' => $user->id,
                    'type' => 'user',
                    'attributes' => [
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'affiliation' => $user->profile?->affiliation ?? '',
                        'status' => $user->status,
                        'email_verified' => $user->email_verified_at !== null,
                        'version' => (int) $user->version,
                    ],
                ],
                'memberships' => [],
                'system_roles' => [],
            ],
            'meta' => [
                'request_id' => $requestId ?? (string) Str::ulid(),
            ],
        ];
    }

    /**
     * Autentikasi untuk Web Session Mode (mengembalikan model User).
     *
     * @throws ValidationException
     */
    public function authenticateWeb(string $login, string $password, ?string $ip = null): User
    {
        $user = $this->attemptLogin($login, $password, isApi: false);

        $this->recordAudit($user->id, 'user.login.success', [
            'transport' => 'web_session',
        ], $ip);

        return $user;
    }

    /**
     * Inti verifikasi kredensial, row-lock database, dan penegakan aturan lockout SIRIDA.
     */
    protected function attemptLogin(string $login, string $password, bool $isApi): User
    {
        $identifier = trim(strtolower($login));

        $result = DB::transaction(function () use ($identifier, $password) {
            // Cari pengguna berdasarkan email atau username (case-insensitive) dengan baris terkunci
            $user = User::whereRaw('LOWER(email) = ?', [$identifier])
                ->orWhereRaw('LOWER(username) = ?', [$identifier])
                ->lockForUpdate()
                ->first();

            // Jika akun tidak ditemukan
            if (! $user) {
                // Mitigasi timing attack
                Hash::check($password, '$argon2id$v=19$m=65536,t=4,p=1$ZHVtbXlzYWx0MTIzNDU2Nw$4q1mE9r3O9B8U6t2W1v0xZ5y8r7s9t0v2w4x6y8z0a1');

                return ['status' => 'not_found'];
            }

            // Evaluasi lockout akun (ERD Aturan 01 • Users dan Lockout)
            if ($user->locked_until !== null) {
                if (now()->lt($user->locked_until)) {
                    $retryAfter = (int) ceil(now()->diffInSeconds($user->locked_until));

                    return [
                        'status' => 'locked',
                        'retry_after' => $retryAfter,
                        'locked_until' => $user->locked_until,
                    ];
                }

                // Waktu lockout 15 menit sudah berakhir, reset status
                $user->locked_until = null;
                $user->failed_login_count = 0;
                $user->last_failed_login_at = null;
                if ($user->status === 'locked') {
                    $user->status = 'active';
                }
                $user->version += 1;
                $user->save();
            }

            // Periksa kata sandi (Mendukung Argon2id dan Bcrypt sesuai ERD)
            $passwordMatches = false;
            try {
                $passwordMatches = Hash::check($password, $user->password_hash);
            } catch (\RuntimeException) {
                $passwordMatches = password_verify($password, $user->password_hash);
            }

            if (! $passwordMatches) {
                $user->failed_login_count += 1;
                $user->last_failed_login_at = now();

                // 5 kegagalan berturut-turut mengunci akun 15 menit (ERD Aturan 01)
                if ($user->failed_login_count >= 5) {
                    $user->locked_until = now()->addMinutes(15);
                    $user->status = 'locked';
                    $user->version += 1;
                    $user->save();

                    $this->queueSecurityEmail($user);

                    return [
                        'status' => 'locked',
                        'retry_after' => 900,
                        'locked_until' => $user->locked_until,
                    ];
                }

                $user->save();

                return ['status' => 'invalid_credentials'];
            }

            // Syarat Akun: Hanya akun aktif & email terverifikasi yang boleh masuk (OpenAPI baris 8270)
            if ($user->email_verified_at === null) {
                return ['status' => 'not_verified'];
            }

            if ($user->status !== 'active') {
                return ['status' => 'inactive'];
            }

            // Login Berhasil: Reset counter kegagalan ke 0
            if ($user->failed_login_count > 0 || $user->locked_until !== null || $user->last_failed_login_at !== null) {
                $user->failed_login_count = 0;
                $user->locked_until = null;
                $user->last_failed_login_at = null;
                $user->save();
            }

            return ['status' => 'success', 'user' => $user];
        });

        // Tangani hasil evaluasi di luar transaksi database agar mutasi counter dan lockout tetap ter-commit
        match ($result['status']) {
            'not_found', 'invalid_credentials' => $this->throwInvalidCredentials($isApi),
            'locked' => $this->throwAccountLocked($isApi, $result['retry_after'], $result['locked_until']),
            'not_verified' => $this->throwNotVerified($isApi),
            'inactive' => $this->throwInactiveAccount($isApi),
            'success' => null,
        };

        return $result['user'];
    }

    protected function throwInvalidCredentials(bool $isApi): never
    {
        $message = 'Kombinasi email/username dan kata sandi yang Anda masukkan tidak cocok.';
        if ($isApi) {
            throw new ApiException(401, 'INVALID_CREDENTIALS', $message);
        }
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function throwAccountLocked(bool $isApi, int $retryAfter, CarbonInterface $lockedUntil): never
    {
        $message = "Akun Anda sementara terkunci karena 5 kali percobaan gagal berturut-turut. Silakan coba lagi dalam {$retryAfter} detik.";
        if ($isApi) {
            throw (new ApiException(423, 'ACCOUNT_LOCKED', $message))
                ->withHeaders(['Retry-After' => (string) $retryAfter]);
        }
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function throwNotVerified(bool $isApi): never
    {
        $message = 'Akun belum diverifikasi melalui email. Silakan periksa kotak masuk email Anda.';
        if ($isApi) {
            throw new ApiException(401, 'EMAIL_NOT_VERIFIED', $message);
        }
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function throwInactiveAccount(bool $isApi): never
    {
        $message = 'Akun tidak aktif atau telah dinonaktifkan oleh administrator.';
        if ($isApi) {
            throw new ApiException(401, 'ACCOUNT_INACTIVE', $message);
        }
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function queueSecurityEmail(User $user): void
    {
        DB::table('outbox_messages')->insert([
            'id' => (string) Str::ulid(),
            'type' => 'security.account_locked_email',
            'payload' => json_encode([
                'user_id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'locked_until' => $user->locked_until?->toIso8601String(),
                'occurred_at' => now()->toIso8601String(),
            ]),
            'status' => 'pending',
            'created_at' => now(),
        ]);
    }

    protected function recordAudit(string $userId, string $action, array $details, ?string $ip = null, ?string $requestId = null): void
    {
        DB::table('audit_logs')->insert([
            'id' => (string) Str::ulid(),
            'user_id' => $userId,
            'actor_id' => $userId,
            'actor_type' => 'user',
            'resource_type' => 'user',
            'resource_id' => $userId,
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'request_id' => $requestId,
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
