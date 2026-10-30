<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'journal_id',
        'section_id',
        'slug',
        'title',
        'abstract',
        'keywords',
        'references',
        'license',
        'status',
        'published_at',
        'version',
    ];

    protected $casts = [
        'keywords' => 'array',
        'version' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

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
     * @return HasMany<ArticleContributor, $this>
     */
    public function contributors(): HasMany
    {
        return $this->hasMany(ArticleContributor::class)->orderBy('position', 'asc');
    }

    /**
     * @return BelongsToMany<Galley, $this>
     */
    public function galleys(): BelongsToMany
    {
        return $this->belongsToMany(Galley::class, 'article_galleys')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position', 'asc');
    }

    /**
     * @return BelongsToMany<Issue, $this>
     */
    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'issue_articles')
            ->withPivot(['position', 'pages'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<ArticleMetric, $this>
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ArticleMetric::class);
    }
}
