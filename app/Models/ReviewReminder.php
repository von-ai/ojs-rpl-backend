<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReminder extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assignment_id',
        'sent_by',
        'queued_at',
        'delivery_status',
        'message',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
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
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
