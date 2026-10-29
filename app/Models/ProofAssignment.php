<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProofAssignment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'galley_id',
        'proofreader_id',
        'file_id',
        'due_at',
        'status',
        'assigned_at',
        'checksum_sha256',
        'message',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Galley, $this>
     */
    public function galley(): BelongsTo
    {
        return $this->belongsTo(Galley::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function proofreader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proofreader_id');
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'file_id');
    }

    /**
     * @return HasMany<ProofreadingNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ProofreadingNote::class, 'proof_assignment_id');
    }
}
