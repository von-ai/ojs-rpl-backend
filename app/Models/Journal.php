<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\JournalFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    /** @use HasFactory<JournalFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'slug',
        'name',
        'initials',
        'issn',
        'eissn',
        'publisher',
        'editorial_email',
        'contact_phone',
        'focus_scope',
        'frequency',
        'apc_fee',
        'apc_enabled',
        'apc_amount',
        'payment_bank_name',
        'payment_account_number',
        'payment_account_holder',
        'payment_instructions',
        'open_access_policy',
        'peer_review_policy',
        'license',
        'status',
        'current_issue_id',
        'version',
    ];

    protected $casts = [
        'apc_enabled' => 'boolean',
        'apc_amount' => 'decimal:2',
    ];

    /**
     * Memeriksa apakah jurnal memungut biaya pemrosesan naskah (APC / Berbayar)
     */
    public function isPaidApc(): bool
    {
        if ($this->apc_enabled) {
            return true;
        }

        if (empty($this->apc_fee)) {
            return false;
        }

        $fee = strtolower($this->apc_fee);
        if (str_contains($fee, 'gratis') || str_contains($fee, 'bebas') || str_contains($fee, 'free') || str_contains($fee, 'rp 0')) {
            return false;
        }

        preg_match_all('/\d+/', str_replace(['.', ','], '', $this->apc_fee), $matches);
        if (! empty($matches[0])) {
            foreach ($matches[0] as $num) {
                if ((int) $num > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return HasMany<Section, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('position');
    }

    /**
     * @return HasMany<Submission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * @return HasMany<Issue, $this>
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class)->orderByDesc('year')->orderByDesc('volume')->orderByDesc('number');
    }

    /**
     * @return HasMany<Announcement, $this>
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class)->latest('published_at');
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * @return HasMany<UserImport, $this>
     */
    public function userImports(): HasMany
    {
        return $this->hasMany(UserImport::class);
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * @return BelongsTo<Issue, $this>
     */
    public function currentIssue(): BelongsTo
    {
        return $this->belongsTo(Issue::class, 'current_issue_id');
    }
}
