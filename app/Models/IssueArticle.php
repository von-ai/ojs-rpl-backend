<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueArticle extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'issue_id',
        'submission_id',
        'position',
        'pages',
        'doi',
        'version',
    ];

    protected $casts = [
        'position' => 'integer',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<Issue, $this>
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
