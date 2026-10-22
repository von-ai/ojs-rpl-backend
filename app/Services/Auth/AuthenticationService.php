<?php

namespace App\Services\Auth;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Autentikasi untuk Web Session Mode (mengembalikan model User).
     *
     * @throws ValidationException
     */
    public function authenticateWeb(string $login, string $password, ?string $ip = null): User
    {
        $user = $this->attemptLogin($login, $password);

        $this->recordAudit($user->id, 'user.login.success', [
            'transport' => 'web_session',
        ], $ip);

        return $user;
    }

    /**
     * Inti verifikasi kredensial, row-lock database, dan penegakan aturan lockout SIRIDA.
     */
    protected function attemptLogin(string $login, string $password): User
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

            // Syarat Akun: Hanya akun aktif & email terverifikasi yang boleh masuk
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
            'not_found', 'invalid_credentials' => $this->throwInvalidCredentials(),
            'locked' => $this->throwAccountLocked($result['retry_after'], $result['locked_until']),
            'not_verified' => $this->throwNotVerified(),
            'inactive' => $this->throwInactiveAccount(),
            'success' => null,
        };

        return $result['user'];
    }

    protected function throwInvalidCredentials(): never
    {
        $message = 'Kombinasi email/username dan kata sandi yang Anda masukkan tidak cocok.';
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function throwAccountLocked(int $retryAfter, CarbonInterface $lockedUntil): never
    {
        $message = "Akun Anda sementara terkunci karena 5 kali percobaan gagal berturut-turut. Silakan coba lagi dalam {$retryAfter} detik.";
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function throwNotVerified(): never
    {
        $message = 'Akun belum diverifikasi melalui email. Silakan periksa kotak masuk email Anda.';
        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    protected function throwInactiveAccount(): never
    {
        $message = 'Akun tidak aktif atau telah dinonaktifkan oleh administrator.';
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
