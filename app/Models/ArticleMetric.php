<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleMetric extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'article_id',
        'galley_id',
        'event_type',
        'visit_hash',
        'window_start',
        'count',
    ];

    protected $casts = [
        'count' => 'integer',
        'window_start' => 'datetime',
    ];

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * @return BelongsTo<Galley, $this>
     */
    public function galley(): BelongsTo
    {
        return $this->belongsTo(Galley::class);
    }
}
