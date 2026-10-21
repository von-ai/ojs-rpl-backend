<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuthSession extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'active_membership_id',
        'active_role',
        'absolute_expires_at',
        'revoked_at',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'absolute_expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class, 'session_id');
    }
}
