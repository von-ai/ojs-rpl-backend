<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
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
use Illuminate\Support\Facades\Schema;

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
    protected ?string $rawRememberToken = null;

    public function setRememberToken($value): void
    {
        $this->rawRememberToken = $value;
        $this->attributes[$this->getRememberTokenName()] = $value ? hash_hmac('sha256', $value, (string) config('app.key')) : null;
    }

    public function getRememberToken(): ?string
    {
        return $this->rawRememberToken ?? $this->getAttribute($this->getRememberTokenName());
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    /**
     * Get the password string for authentication.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

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
     * @return HasMany<EditorAssignment, $this>
     */
    public function editorAssignments(): HasMany
    {
        return $this->hasMany(EditorAssignment::class, 'editor_id');
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    /**
     * Alias for userRoles relationship.
     *
     * @return HasMany<UserRole, $this>
     */
    public function roles(): HasMany
    {
        return $this->userRoles();
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'reviewer_id');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    /**
     * Memeriksa apakah pengguna memiliki hak akses System Administrator global.
     */
    public function isSysAdmin(): bool
    {
        if ($this->relationLoaded('userRoles')) {
            return $this->userRoles->contains(fn ($role) => $role->role_code === 'sysadmin' && $role->journal_id === null);
        }

        return $this->userRoles()->where('role_code', 'sysadmin')->whereNull('journal_id')->exists();
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
                ->where(fn ($q) => $q->where('role_code', '!=', 'sysadmin')->orWhereNull('journal_id'))
                ->pluck('role_code')
                ->filter(fn ($code) => is_string($code) && isset(UserRole::roleCatalog()[$code]))
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable) {
            $dbRoles = [];
        }

        $dbRoles[] = 'author';
        $dbRoles = array_unique($dbRoles);

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
     * Mengambil daftar metadata seluruh jurnal yang dapat diakses pengguna.
     * Sesuai standar OJS: Pengguna dapat berpindah konteks jurnal aktif untuk seluruh peran.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     initials: string,
     *     slug: string,
     *     roles: array<int, string>,
     *     roles_summary: string,
     *     has_current_role: bool
     * }>
     */
    public function availableJournalsMeta(?string $currentRole = null): array
    {
        try {
            if (! class_exists(Journal::class) || ! Schema::hasTable('journals')) {
                return [];
            }

            $allJournals = Journal::withoutGlobalScope('portal_access')
                ->where('status', '!=', 'archived')
                ->orderBy('name')
                ->get();

            if ($allJournals->isEmpty()) {
                return [];
            }

            $userRolesByJournal = $this->userRoles()
                ->whereNotNull('journal_id')
                ->get()
                ->groupBy('journal_id');

            $isSysAdmin = $this->isSysAdmin();

            return $allJournals->map(function (Journal $journal) use ($userRolesByJournal, $isSysAdmin, $currentRole) {
                $roles = $userRolesByJournal->get($journal->id, collect());
                $roleCodes = $roles->pluck('role_code')->unique()->all();

                $labels = array_map(fn ($c) => UserRole::roleLabel($c), $roleCodes);
                if ($isSysAdmin && ! in_array('System Administrator', $labels, true)) {
                    array_unshift($labels, 'System Administrator');
                }

                if (empty($labels)) {
                    $labels[] = 'Penulis';
                }

                $hasCurrentRole = $currentRole ? (
                    $isSysAdmin
                    || $currentRole === 'author'
                    || in_array($currentRole, $roleCodes, true)
                ) : false;

                return [
                    'id' => (string) $journal->id,
                    'name' => (string) $journal->name,
                    'initials' => (string) ($journal->initials ?: strtoupper(substr($journal->slug, 0, 6))),
                    'slug' => (string) $journal->slug,
                    'roles' => $labels,
                    'roles_summary' => implode(', ', array_unique($labels)),
                    'has_current_role' => $hasCurrentRole,
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Menentukan model Jurnal yang sedang aktif berdasarkan sesi atau peran pengguna saat ini.
     */
    public function activeJournal(?string $roleCode = null): ?Journal
    {
        try {
            if (! class_exists(Journal::class) || ! Schema::hasTable('journals')) {
                return null;
            }

            $activeId = null;
            try {
                if (session()->has('active_journal_id')) {
                    $activeId = session('active_journal_id');
                }
            } catch (\Throwable) {
                // Sesi belum terinisialisasi
            }

            if (is_string($activeId) && $activeId !== '') {
                $journal = Journal::withoutGlobalScope('portal_access')->find($activeId);
                if ($journal) {
                    // Sysadmin atau peran author selalu valid pada setiap jurnal aktif
                    if ($this->isSysAdmin() || $roleCode === 'author' || $roleCode === null) {
                        return $journal;
                    }

                    // Periksa apakah pengguna memiliki peran terkait di jurnal ini
                    if ($this->userRoles()->where('journal_id', $journal->id)->where('role_code', $roleCode)->exists()) {
                        return $journal;
                    }
                }
            }

            // Fallback 1: Cari jurnal pertama tempat user memegang peran ini
            if ($roleCode !== null && $roleCode !== 'author' && ! $this->isSysAdmin()) {
                $firstJournalId = $this->userRoles()
                    ->where('role_code', $roleCode)
                    ->whereNotNull('journal_id')
                    ->orderBy('journal_id')
                    ->value('journal_id');

                if ($firstJournalId) {
                    $found = Journal::withoutGlobalScope('portal_access')->find($firstJournalId);
                    if ($found) {
                        return $found;
                    }
                }
            }

            // Fallback 2: Cari jurnal pertama tempat user memegang peran apa pun
            $userFirstJournalId = $this->userRoles()
                ->whereNotNull('journal_id')
                ->orderBy('journal_id')
                ->value('journal_id');

            if ($userFirstJournalId) {
                $found = Journal::withoutGlobalScope('portal_access')->find($userFirstJournalId);
                if ($found) {
                    return $found;
                }
            }

            // Fallback 3: Jurnal pertama sistem
            return Journal::withoutGlobalScope('portal_access')->first();
        } catch (\Throwable) {
            return null;
        }
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
        return collect($available)->first(fn ($role) => $role !== 'author') ?? 'author';
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

        $primaryRole = collect($available)->first(fn ($role) => $role !== 'author') ?? 'author';

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
            'remember_expires_at' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'version' => 'integer',
            'failed_login_count' => 'integer',
        ];
    }
}
