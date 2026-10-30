<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EditorialDecision extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'review_round_id',
        'decided_by',
        'decision',
        'note_for_author',
        'internal_note',
        'status',
        'revision_due_at',
        'sent_at',
        'override_reason',
        'corrects_decision_id',
        'minimum_review_override_reason',
        'version',
    ];

    protected $casts = [
        'revision_due_at' => 'datetime',
        'sent_at' => 'datetime',
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
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
