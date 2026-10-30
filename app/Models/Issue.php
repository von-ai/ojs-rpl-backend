<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'journal_id',
        'volume',
        'number',
        'year',
        'title',
        'description',
        'status',
        'is_current',
        'doi_prefix',
        'scheduled_at',
        'published_at',
        'publication_date',
        'version',
        'publication_failure_code',
        'publication_failed_at',
        'failure_diagnostics',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_current' => 'boolean',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'publication_date' => 'date',
        'publication_failed_at' => 'datetime',
        'failure_diagnostics' => 'array',
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
     * @return BelongsToMany<Article, $this>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'issue_articles')
            ->using(IssueArticlePivot::class)
            ->withPivot(['id', 'position', 'pages'])
            ->withTimestamps()
            ->orderByPivot('position', 'asc');
    }

    /**
     * @return HasMany<IssueArticle, $this>
     */
    public function issueArticles(): HasMany
    {
        return $this->hasMany(IssueArticle::class)->orderBy('position');
    }

    /**
     * @return BelongsToMany<Submission, $this>
     */
    public function submissions(): BelongsToMany
    {
        return $this->belongsToMany(Submission::class, 'issue_articles')
            ->withPivot(['id', 'position', 'pages', 'doi'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function canPublish(): bool
    {
        return ! $this->isPublished() && $this->issueArticles()->count() > 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => $this->is_current ? 'Terbit (Current Issue)' : 'Terbit (Arsip)',
            'scheduled' => 'Terjadwal Terbit',
            default => 'Draf Kurasi Naskah',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'published' => $this->is_current
                ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                : 'bg-gray-100 text-gray-700 border-gray-200',
            'scheduled' => 'bg-blue-50 text-blue-800 border-blue-200',
            default => 'bg-amber-50 text-amber-800 border-amber-200',
        };
    }
}
