<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SubmissionContributorFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionContributor extends Model
{
    /** @use HasFactory<SubmissionContributorFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'user_id',
        'given_name',
        'family_name',
        'email',
        'affiliation',
        'orcid',
        'is_corresponding',
        'position',
    ];

    protected $casts = [
        'is_corresponding' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->given_name ? $this->given_name.' ' : '').$this->family_name);
    }
}
