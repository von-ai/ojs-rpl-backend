<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeskReview extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'assessed_by',
        'scope_fit',
        'files_complete',
        'guidelines_compliant',
        'similarity_score',
        'notes',
        'status',
        'version',
    ];

    protected $casts = [
        'scope_fit' => 'boolean',
        'files_complete' => 'boolean',
        'guidelines_compliant' => 'boolean',
        'similarity_score' => 'float',
        'version' => 'integer',
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
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
