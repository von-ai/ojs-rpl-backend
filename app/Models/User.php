<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

#[Fillable([
    'name',
    'email',
    'username',
    'full_name',
    'password',
    'password_hash',
    'status',
    'email_verified_at',
    'locked_until',
    'version',
    'failed_login_count',
    'last_failed_login_at',
])]
#[Hidden(['password_hash', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    /**
     * Get the password string for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    /**
     * Backward-compatibility accessor for name attribute.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name ?? '';
    }

    /**
     * Backward-compatibility mutator for name attribute.
     */
    public function setNameAttribute(?string $value): void
    {
        $this->attributes['full_name'] = $value;
    }

    /**
     * Backward-compatibility mutator for password attribute.
     */
    public function setPasswordAttribute(?string $value): void
    {
        if ($value !== null) {
            $info = password_get_info($value);
            $this->attributes['password_hash'] = (! empty($info['algo']))
                ? $value
                : Hash::make($value);
        }
    }

    /**
     * Mutator for password_hash attribute.
     */
    public function setPasswordHashAttribute(?string $value): void
    {
        if ($value !== null) {
            $info = password_get_info($value);
            $this->attributes['password_hash'] = (! empty($info['algo']))
                ? $value
                : Hash::make($value);
        }
    }

    /**
     * Backward-compatibility accessor for password attribute.
     */
    public function getPasswordAttribute(): ?string
    {
        return $this->password_hash;
    }

    /**
     * Relasi profil akademik pengguna.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    /**
     * @return HasMany<Copyedit, $this>
     */
    public function copyedits(): HasMany
    {
        return $this->hasMany(Copyedit::class, 'copy_editor_id');
    }

    /**
     * @return HasMany<LayoutAssignment, $this>
     */
    public function layoutAssignments(): HasMany
    {
        return $this->hasMany(LayoutAssignment::class, 'layout_editor_id');
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'reviewer_id');
    }

    /**
     * Mengambil daftar kode peran unik yang dimiliki pengguna, diurutkan sesuai katalog.
     * Setiap pengguna terautentikasi minimal memiliki akses 'author' sebagai fallback dasar.
     *
     * @return array<int, string>
     */
    public function availableRoleCodes(): array
    {
        $catalogKeys = array_keys(UserRole::roleCatalog());

        try {
            $dbRoles = $this->userRoles()
                ->pluck('role_code')
                ->filter(fn ($code) => is_string($code) && isset(UserRole::roleCatalog()[$code]))
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable) {
            $dbRoles = [];
        }

        if (empty($dbRoles)) {
            return ['author'];
        }

        // Urutkan sesuai urutan prioritas di roleCatalog()
        return array_values(array_intersect($catalogKeys, $dbRoles));
    }

    /**
     * Mengambil daftar metadata peran lengkap yang dimiliki pengguna.
     *
     * @return array<int, array{
     *     code: string,
     *     label: string,
     *     portal_label: string,
     *     description: string,
     *     route: string,
     *     prefix: string,
     *     badge_class: string
     * }>
     */
    public function availableRolesMeta(): array
    {
        return array_map(
            fn (string $code): array => UserRole::roleMeta($code),
            $this->availableRoleCodes()
        );
    }

    /**
     * Memeriksa apakah pengguna memiliki peran tertentu.
     */
    public function hasRole(string $roleCode): bool
    {
        return in_array($roleCode, $this->availableRoleCodes(), true);
    }

    /**
     * Menentukan kode peran yang sedang aktif berdasarkan sesi atau path URL saat ini.
     */
    public function activeRoleCode(?string $currentPath = null): string
    {
        $available = $this->availableRoleCodes();

        // 1. Jika URL saat ini berada di dalam prefix salah satu portal yang dimiliki pengguna, prioritaskan portal tersebut
        $path = $currentPath ?? (app()->bound('request') ? request()->path() : '');
        $firstSegment = strtolower((string) strtok(trim($path, '/'), '/'));

        foreach (UserRole::roleCatalog() as $code => $meta) {
            if ($firstSegment !== '' && $firstSegment === $meta['prefix'] && in_array($code, $available, true)) {
                return $code;
            }
        }

        // 2. Gunakan active_role dari session apabila masih valid dimiliki oleh pengguna
        try {
            $sessionRole = session('active_role');
            if (is_string($sessionRole) && in_array($sessionRole, $available, true)) {
                return $sessionRole;
            }
        } catch (\Throwable) {
            // Abaikan jika session belum terinisialisasi
        }

        // 3. Fallback ke peran pertama yang dimiliki pengguna
        return $available[0] ?? 'author';
    }

    /**
     * Menentukan nama rute beranda portal default saat pengguna masuk atau mengakses /dashboard.
     */
    public function defaultPortalRoute(): string
    {
        $available = $this->availableRoleCodes();

        try {
            $sessionRole = session('active_role');
            if (is_string($sessionRole) && in_array($sessionRole, $available, true)) {
                return UserRole::roleMeta($sessionRole)['route'];
            }
        } catch (\Throwable) {
            // Abaikan
        }

        $primaryRole = $available[0] ?? 'author';

        return UserRole::roleMeta($primaryRole)['route'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'version' => 'integer',
            'failed_login_count' => 'integer',
        ];
    }
}
