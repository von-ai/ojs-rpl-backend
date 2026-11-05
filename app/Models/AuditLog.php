<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'audit_logs';

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException('Audit log is append-only');
        });

        static::deleting(function () {
            throw new \RuntimeException('Audit log is append-only');
        });
    }

    protected $fillable = [
        'id',
        'journal_id',
        'actor_id',
        'actor_type',
        'system_actor',
        'effective_role',
        'action',
        'resource_type',
        'resource_id',
        'before_after',
        'reason',
        'user_id',
        'details',
        'ip_address',
        'user_agent',
        'request_id',
        'occurred_at',
    ];

    protected $casts = [
        'before_after' => 'array',
        'details' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * Scope query untuk memfilter tipe aktor (user / system).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForActorType(Builder $query, string $actorType): Builder
    {
        if ($actorType === 'all' || $actorType === '') {
            return $query;
        }

        return $query->where('actor_type', $actorType);
    }

    /**
     * Label aksi yang ramah pengguna.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'system_settings.updated' => 'Pembaruan Pengaturan Platform',
            'system.cache_purged' => 'Pembersihan Cache Aplikasi',
            'user.sysadmin_assigned' => 'Pemberian Peran System Administrator',
            'user.sysadmin_revoked' => 'Pencabutan Peran System Administrator',
            'journal.created' => 'Pendaftaran Instansi Jurnal Baru',
            'journal.status_updated' => 'Pembaruan Status Publikasi Jurnal',
            'user_imports.processed' => 'Eksekusi Impor Pengguna CSV',
            'cron_worker.scheduled_scan' => 'Pemindaian Malware Berkas Unggahan',
            default => ucwords(str_replace(['.', '_'], ' ', $this->action)),
        };
    }

    /**
     * Helper praktis untuk mencatat mutasi data ke audit_logs (append-only).
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function record(array $attributes): self
    {
        $actorType = $attributes['actor_type'] ?? 'user';
        $requestId = $attributes['request_id']
            ?? (request()?->header('X-Request-ID') ?: 'req_'.Str::lower(Str::random(12)));
        $ip = $attributes['ip_address'] ?? request()?->ip();
        $userAgent = $attributes['user_agent'] ?? request()?->userAgent();

        $data = [
            'journal_id' => $attributes['journal_id'] ?? null,
            'actor_id' => $attributes['actor_id'] ?? null,
            'actor_type' => $actorType,
            'system_actor' => $attributes['system_actor'] ?? ($actorType === 'system' ? 'system_daemon' : null),
            'effective_role' => $attributes['effective_role'] ?? 'System Administrator',
            'action' => $attributes['action'],
            'resource_type' => $attributes['resource_type'] ?? null,
            'resource_id' => isset($attributes['resource_id']) ? (string) $attributes['resource_id'] : null,
            'before_after' => $attributes['before_after'] ?? null,
            'reason' => $attributes['reason'] ?? null,
            'user_id' => $attributes['user_id'] ?? null,
            'details' => $attributes['details'] ?? null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'request_id' => $requestId,
            'occurred_at' => $attributes['occurred_at'] ?? now(),
        ];

        if (isset($attributes['id'])) {
            $data['id'] = (string) $attributes['id'];
        }

        return static::create($data);
    }
}
