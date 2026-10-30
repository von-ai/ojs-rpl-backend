<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewRubricVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'journal_id',
        'version',
        'criteria',
        'reviewer_guidelines',
    ];

    protected $casts = [
        'version' => 'integer',
        'criteria' => 'array',
    ];

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * @return HasMany<ReviewRound, $this>
     */
    public function reviewRounds(): HasMany
    {
        return $this->hasMany(ReviewRound::class, 'rubric_version_id');
    }
}
