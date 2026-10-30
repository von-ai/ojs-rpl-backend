<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleContributor extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'article_id',
        'given_name',
        'family_name',
        'affiliation',
        'orcid',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->given_name} {$this->family_name}");
    }
}
