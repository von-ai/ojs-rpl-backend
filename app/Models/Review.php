<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assignment_id',
        'scores',
        'comments_for_author',
        'confidential_comments_for_editor',
        'recommendation',
        'certify_no_conflict',
        'status',
        'submitted_at',
        'version',
    ];

    protected $casts = [
        'scores' => 'array',
        'certify_no_conflict' => 'boolean',
        'submitted_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<ReviewAssignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class, 'assignment_id');
    }

    /**
     * @return HasMany<ReviewAnnotation, $this>
     */
    public function annotations(): HasMany
    {
        return $this->hasMany(ReviewAnnotation::class, 'review_id');
    }

    public function getRecommendationBadgeClassAttribute(): string
    {
        return match ($this->recommendation) {
            'accept' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'revisions_required' => 'bg-amber-50 text-amber-800 border-amber-200',
            'resubmit' => 'bg-orange-50 text-orange-800 border-orange-200',
            'decline' => 'bg-rose-50 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getRecommendationLabelAttribute(): string
    {
        return match ($this->recommendation) {
            'accept' => 'Diterima (Accept Submission)',
            'revisions_required' => 'Perlu Revisi (Revisions Required)',
            'resubmit' => 'Kirim Ulang untuk Ditelaah (Resubmit for Review)',
            'decline' => 'Ditolak (Decline Submission)',
            default => ucfirst($this->recommendation),
        };
    }
}
