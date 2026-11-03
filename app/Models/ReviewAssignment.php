<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class ReviewAssignment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'response_reason',
        'review_round_id',
        'reviewer_id',
        'status',
        'response_due_at',
        'review_due_at',
        'responded_at',
        'completed_at',
        'deadline_version',
        'version',
        'invitation_message',
    ];

    protected $casts = [
        'response_due_at' => 'datetime',
        'review_due_at' => 'datetime',
        'responded_at' => 'datetime',
        'completed_at' => 'datetime',
        'deadline_version' => 'integer',
        'version' => 'integer',
    ];

    /**
     * @return BelongsTo<ReviewRound, $this>
     */
    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(ReviewRound::class, 'review_round_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * @return HasOne<Review, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'assignment_id');
    }

    /**
     * @return HasMany<ReviewExtension, $this>
     */
    public function extensions(): HasMany
    {
        return $this->hasMany(ReviewExtension::class, 'assignment_id')->latest();
    }

    /**
     * @return HasOne<ReviewExtension, $this>
     */
    public function pendingExtension(): HasOne
    {
        return $this->hasOne(ReviewExtension::class, 'assignment_id')->where('status', 'pending');
    }

    /**
     * @return HasMany<ReviewReminder, $this>
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(ReviewReminder::class, 'assignment_id')->latest();
    }

    /**
     * Mesin waktu review (normatif §use-cases.md):
     * Menghitung status efektif berdasarkan batas waktu tanpa bergantung semata pada cron worker.
     */
    public function getEffectiveStatusAttribute(): string
    {
        $now = Carbon::now('UTC');

        if ($this->status === 'invited' && $this->response_due_at && $now->greaterThanOrEqualTo($this->response_due_at)) {
            return 'no_response';
        }

        if ($this->status === 'accepted' && $this->review_due_at && $now->greaterThanOrEqualTo($this->review_due_at)) {
            return 'overdue';
        }

        return $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->effective_status) {
            'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'accepted' => 'bg-blue-50 text-blue-800 border-blue-200',
            'invited' => 'bg-amber-50 text-amber-800 border-amber-200',
            'overdue' => 'bg-rose-50 text-rose-800 border-rose-200',
            'no_response' => 'bg-orange-50 text-orange-800 border-orange-200',
            'declined' => 'bg-gray-100 text-gray-700 border-gray-200',
            'cancelled' => 'bg-gray-100 text-gray-600 border-gray-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->effective_status) {
            'invited' => 'Undangan Terkirim',
            'accepted' => 'Sedang Menelaah',
            'completed' => 'Telaah Selesai',
            'overdue' => 'Melewati Batas Waktu (Overdue)',
            'no_response' => 'Tidak Ada Respon',
            'declined' => 'Menolak Undangan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->effective_status),
        };
    }
}
