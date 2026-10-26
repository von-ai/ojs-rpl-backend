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
