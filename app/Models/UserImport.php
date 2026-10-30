<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserImport extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'user_imports';

    protected $fillable = [
        'journal_id',
        'file_name',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'status',
        'error_log',
        'imported_by',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'failed_rows' => 'integer',
        'error_log' => 'array',
    ];

    /**
     * @return BelongsTo<Journal, $this>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Label status impor yang ramah pengguna.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'completed') {
            return $this->failed_rows > 0
                ? "Selesai ({$this->failed_rows} Gagal)"
                : 'Selesai Sukses';
        }

        return match ($this->status) {
            'processing' => 'Sedang Diproses',
            'failed' => 'Gagal Diproses',
            'pending' => 'Menunggu Antrean',
            default => ucfirst($this->status),
        };
    }

    /**
     * Kelas styling badge status impor.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->status === 'completed') {
            return $this->failed_rows > 0
                ? 'bg-amber-50 text-amber-800 border-amber-200'
                : 'bg-emerald-50 text-emerald-800 border-emerald-200';
        }

        return match ($this->status) {
            'failed' => 'bg-red-50 text-red-800 border-red-200',
            'processing' => 'bg-blue-50 text-blue-800 border-blue-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}
