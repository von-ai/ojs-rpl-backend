<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Galley extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'file_id',
        'supersedes_galley_id',
        'format',
        'stage',
        'status',
        'checksum_sha256',
        'approved_checksum',
        'version',
        'label',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'file_id');
    }

    /**
     * @return BelongsTo<Galley, $this>
     */
    public function supersedesGalley(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_galley_id');
    }

    /**
     * @return HasMany<Galley, $this>
     */
    public function supersededByGalleys(): HasMany
    {
        return $this->hasMany(self::class, 'supersedes_galley_id');
    }

    /**
     * @return HasMany<ProofAssignment, $this>
     */
    public function proofAssignments(): HasMany
    {
        return $this->hasMany(ProofAssignment::class, 'galley_id');
    }

    /**
     * @return HasMany<ProofreadingNote, $this>
     */
    public function proofreadingNotes(): HasMany
    {
        return $this->hasMany(ProofreadingNote::class, 'galley_id');
    }

    /**
     * @return HasMany<GalleyApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(GalleyApproval::class, 'galley_id');
    }

    /**
     * @return HasMany<ProductionEscalation, $this>
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(ProductionEscalation::class, 'galley_id');
    }

    public function readyForAuthorSignoff(): bool
    {
        return $this->isActive() && $this->stage === 'proofread'
            && $this->file?->virus_scan_status === 'clean'
            && $this->checksum_sha256 && hash_equals($this->checksum_sha256, (string) $this->file->checksum_sha256)
            && $this->proofAssignments()->where('status', 'completed')
                ->where('file_id', $this->file_id)->where('checksum_sha256', $this->checksum_sha256)->exists();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
