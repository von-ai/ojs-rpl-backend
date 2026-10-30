<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'scheduled_at',
        'publication_date',
        'failure_diagnostics',
        'version',
        'publication_failure_code',
        'publication_failed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'version' => 'integer',
        'scheduled_at' => 'datetime',
        'publication_date' => 'date',
        'publication_failed_at' => 'datetime',
        'failure_diagnostics' => 'array',
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
            ->withPivot(['position', 'pages'])
            ->withTimestamps()
            ->orderByPivot('position', 'asc');
    }
}
