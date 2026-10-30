<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IssueArticlePivot extends Pivot
{
    use HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'issue_articles';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function ($pivot) {
            if (empty($pivot->submission_id) && ! empty($pivot->article_id)) {
                $article = Article::find($pivot->article_id);
                if ($article && ! empty($article->submission_id)) {
                    $pivot->submission_id = $article->submission_id;
                }
            }
        });
    }
}
