<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewExtension extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assignment_id',
        'requested_due_at',
        'reason',
        'status',
        'decided_by',
        'decision_reason',
        'decided_at',
        'version',
    ];

    protected $casts = [
        'requested_due_at' => 'datetime',
        'decided_at' => 'datetime',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<ReviewAssignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class, 'assignment_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
