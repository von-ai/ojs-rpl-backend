<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofreadingNote extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'proof_assignment_id',
        'galley_id',
        'file_id',
        'checksum_sha256',
        'category',
        'page',
        'severity',
        'description',
        'resolved',
        'resolution',
        'resolved_at',
        'verified',
        'verified_at',
        'verified_by',
        'version',
    ];

    protected $casts = [
        'page' => 'integer',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<ProofAssignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ProofAssignment::class, 'proof_assignment_id');
    }

    /**
     * @return BelongsTo<Galley, $this>
     */
    public function galley(): BelongsTo
    {
        return $this->belongsTo(Galley::class);
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'file_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isBlocking(): bool
    {
        return ! $this->resolved && in_array($this->severity, ['critical', 'major'], true);
    }

    public function isVerified(): bool
    {
        return (bool) $this->verified;
    }
}
