<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_number',
        'journal_id',
        'section_id',
        'author_id',
        'title',
        'abstract',
        'keywords',
        'references',
        'subject_area',
        'comments_for_editor',
        'status',
        'guideline_acceptance',
        'submitted_at',
        'version',
    ];

    protected $casts = [
        'keywords' => 'array',
        'guideline_acceptance' => 'array',
        'submitted_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return HasMany<SubmissionContributor, $this>
     */
    public function contributors(): HasMany
    {
        return $this->hasMany(SubmissionContributor::class)->orderBy('position');
    }

    /**
     * @return HasOne<SubmissionContributor, $this>
     */
    public function correspondingAuthor(): HasOne
    {
        return $this->hasOne(SubmissionContributor::class)->where('is_corresponding', true);
    }

    /**
     * @return HasMany<SubmissionFile, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    /**
     * Primary manuscript file
     *
     * @return HasOne<SubmissionFile, $this>
     */
    public function primaryManuscriptFile(): HasOne
    {
        return $this->hasOne(SubmissionFile::class)->where('genre', 'manuscript');
    }
}
