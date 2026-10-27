<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayoutAssignment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'layout_editor_id',
        'assigned_by',
        'assigned_at',
        'due_at',
        'revoked_at',
        'reason',
        'version',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'revoked_at' => 'datetime',
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
    public function layoutEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'layout_editor_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
