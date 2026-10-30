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
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

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

    /**
     * @return HasMany<Copyedit, $this>
     */
    public function copyedits(): HasMany
    {
        return $this->hasMany(Copyedit::class);
    }

    /**
     * Latest copyedit assignment
     *
     * @return HasOne<Copyedit, $this>
     */
    public function copyedit(): HasOne
    {
        return $this->hasOne(Copyedit::class)->latestOfMany('created_at');
    }

    /**
     * @return HasMany<ProductionEscalation, $this>
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(ProductionEscalation::class);
    }

    /**
     * @return HasMany<LayoutAssignment, $this>
     */
    public function layoutAssignments(): HasMany
    {
        return $this->hasMany(LayoutAssignment::class);
    }

    /**
     * @return HasOne<LayoutAssignment, $this>
     */
    public function activeLayoutAssignment(): HasOne
    {
        return $this->hasOne(LayoutAssignment::class)->whereNull('revoked_at')->latestOfMany('created_at');
    }

    /**
     * @return HasMany<Galley, $this>
     */
    public function galleys(): HasMany
    {
        return $this->hasMany(Galley::class);
    }

    /**
     * @return HasOne<Galley, $this>
     */
    public function activeGalley(): HasOne
    {
        return $this->hasOne(Galley::class)->where('status', 'active')->latestOfMany('created_at');
    }

    /**
     * @return HasOne<IssueArticle, $this>
     */
    public function issueArticle(): HasOne
    {
        return $this->hasOne(IssueArticle::class);
    }

    /**
     * @return HasOneThrough<Issue, IssueArticle, $this>
     */
    public function issue(): HasOneThrough
    {
        return $this->hasOneThrough(Issue::class, IssueArticle::class, 'submission_id', 'id', 'id', 'issue_id');
    }
}
