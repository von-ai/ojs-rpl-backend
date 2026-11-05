<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionEscalation extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'copyedit_id',
        'galley_id',
        'opened_by',
        'reason',
        'status',
        'resolution',
        'resume_stage',
        'version',
    ];

    protected $casts = [
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
     * @return BelongsTo<Copyedit, $this>
     */
    public function copyedit(): BelongsTo
    {
        return $this->belongsTo(Copyedit::class);
    }

    /**
     * @return BelongsTo<Galley, $this>
     */
    public function galley(): BelongsTo
    {
        return $this->belongsTo(Galley::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Alias for openedBy.
     *
     * @return BelongsTo<User, $this>
     */
    public function raisedBy(): BelongsTo
    {
        return $this->openedBy();
    }

    /**
     * Whether this escalation is still active (blocks workflow).
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'investigating'], true);
    }
}
