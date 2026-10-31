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
        'open_access_policy',
        'peer_review_policy',
        'license',
        'status',
        'current_issue_id',
        'version',
    ];

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
