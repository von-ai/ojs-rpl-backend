<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CopyeditApproval extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'copyedit_id',
        'author_id',
        'decision',
        'reason',
        'output_version',
        'approved_at',
        'invalidated_at',
        'note',
    ];

    protected $casts = [
        'output_version' => 'integer',
        'approved_at' => 'datetime',
        'invalidated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Copyedit, $this>
     */
    public function copyedit(): BelongsTo
    {
        return $this->belongsTo(Copyedit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return HasMany<CopyeditApprovedFile, $this>
     */
    public function approvedFiles(): HasMany
    {
        return $this->hasMany(CopyeditApprovedFile::class, 'approval_id');
    }

    /**
     * Helper to check if approval is still valid.
     */
    public function isValid(): bool
    {
        return $this->invalidated_at === null && $this->decision === 'approved';
    }
}
