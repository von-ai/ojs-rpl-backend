<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ArticlePaymentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticlePayment extends Model
{
    /** @use HasFactory<ArticlePaymentFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'submission_id',
        'user_id',
        'invoice_number',
        'amount',
        'amount_paid',
        'status',
        'file_entry_id',
        'sender_bank_name',
        'sender_account_name',
        'transfer_date',
        'author_notes',
        'verification_notes',
        'verified_by',
        'verified_at',
        'uploaded_at',
        'version',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'transfer_date' => 'date',
        'verified_at' => 'datetime',
        'uploaded_at' => 'datetime',
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
     * Penulis yang mengajukan atau membayar
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Berkas bukti transfer pembayaran yang diunggah
     *
     * @return BelongsTo<FileEntry, $this>
     */
    public function fileEntry(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class, 'file_entry_id');
    }

    /**
     * Pengelola / Editor yang memverifikasi atau menolak bukti pembayaran
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification';
    }

    public function isPendingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Menunggu Pembayaran',
            'pending_verification' => 'Menunggu Verifikasi Redaksi',
            'verified' => 'Lunas / Terverifikasi',
            'rejected' => 'Bukti Ditolak (Perlu Perbaikan)',
            'waived' => 'Bebas Biaya (Diberikan Pembebasan/Waiver)',
            default => 'Status Tidak Diketahui',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending_payment' => 'bg-amber-50 text-amber-800 border-amber-200',
            'pending_verification' => 'bg-sky-50 text-sky-800 border-sky-200',
            'verified' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
            'waived' => 'bg-purple-50 text-purple-800 border-purple-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}
