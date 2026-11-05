<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewRound extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'round',
        'manuscript_file_id',
        'anonymized_file_id',
        'rubric_version_id',
        'minimum_completed_reviews',
        'status',
        'version',
        'review_type',
        'note',
        'manuscript_checksum',
        'anonymized_checksum',
        'rubric_snapshot',
        'reviewer_title',
        'reviewer_abstract',
        'se_recommendation',
        'se_recommendation_note',
        'se_recommended_at',
        'closed_at',
    ];

    protected $casts = [
        'round' => 'integer',
        'minimum_completed_reviews' => 'integer',
        'version' => 'integer',
        'rubric_snapshot' => 'array',
        'se_recommended_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function completedReviewCount(): int
    {
        return $this->assignments()->where('status', 'completed')->whereHas('review', fn ($q) => $q->where('status', 'submitted'))->distinct()->count('reviewer_id');
    }

    public function hasDistinctAnonymizedFile(): bool
    {
        if ($this->review_type !== 'double_blind' || ! $this->manuscript_file_id || $this->manuscript_file_id === $this->anonymized_file_id) {
            return false;
        }

        $original = strtolower((string) $this->manuscriptFile?->checksum_sha256);
        $anonymous = strtolower((string) $this->anonymizedFile?->checksum_sha256);

        return preg_match('/\A[0-9a-f]{64}\z/', $original) === 1
            && preg_match('/\A[0-9a-f]{64}\z/', $anonymous) === 1
            && hash_equals(strtolower((string) ($this->manuscript_checksum ?? $original)), $original)
            && hash_equals(strtolower((string) $this->anonymized_checksum), $anonymous)
            && ! hash_equals($original, $anonymous);
    }

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
    public function anonymizedFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'anonymized_file_id');
    }

    /**
     * @return BelongsTo<FileEntry, $this>
     */
    public function manuscriptFile(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'manuscript_file_id');
    }

    /**
     * @return BelongsTo<ReviewRubricVersion, $this>
     */
    public function rubricVersion(): BelongsTo
    {
        return $this->belongsTo(ReviewRubricVersion::class, 'rubric_version_id');
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'review_round_id');
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'review_round_id')
            ->whereIn('status', ['invited', 'accepted', 'overdue']);
    }

    /**
     * @return HasMany<ReviewAssignment, $this>
     */
    public function completedAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'review_round_id')
            ->where('status', 'completed');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Aktif Berjalan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }
}
