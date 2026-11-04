<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\ArticlePayment;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use App\Services\PortalAccess;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticlePaymentService
{
    /**
     * Mengambil atau menginisialisasi catatan pembayaran untuk naskah jika jurnal berbayar.
     */
    public function resolveOrCreatePayment(Submission $submission, ?User $actor = null): ?ArticlePayment
    {
        $journal = $submission->journal ?? Journal::first();
        if (! $journal || ! $journal->isPaidApc()) {
            return $submission->latestPayment;
        }

        // Cari transaksi pembayaran aktif
        $payment = $submission->latestPayment;
        if ($payment) {
            return $payment;
        }

        return DB::transaction(function () use ($submission, $journal, $actor): ArticlePayment {
            $year = date('Y');
            $cleanNum = preg_replace('/[^0-9]/', '', (string) $submission->submission_number) ?: (string) rand(1000, 9999);
            $invoiceNumber = 'INV-'.$year.'-APC'.str_pad($cleanNum, 5, '0', STR_PAD_LEFT);

            // Cek keunikan invoice number
            if (ArticlePayment::where('invoice_number', $invoiceNumber)->exists()) {
                $invoiceNumber .= '-'.Str::upper(Str::random(4));
            }

            $amount = $this->determineApcAmount($journal);

            return ArticlePayment::create([
                'submission_id' => $submission->id,
                'user_id' => $submission->author_id ?? ($actor?->id ?? User::first()?->id),
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'status' => 'pending_payment',
                'version' => 1,
            ]);
        });
    }

    /**
     * Memproses pengunggahan bukti transfer pembayaran oleh Penulis.
     *
     * @param  array{
     *     amount_paid?: numeric|string|null,
     *     sender_bank_name?: ?string,
     *     sender_account_name?: ?string,
     *     transfer_date?: ?string,
     *     author_notes?: ?string
     * }  $data
     */
    public function uploadPaymentProof(
        Submission $submission,
        User $author,
        UploadedFile $file,
        array $data = []
    ): ArticlePayment {
        // Invariant 1: Naskah harus dalam tahapan menjelang terbit (accepted, in_production, ready_for_issue)
        $allowedStatuses = ['accepted', 'in_production', 'ready_for_issue', 'scheduled', 'published'];
        if (! in_array($submission->status, $allowedStatuses, true)) {
            throw new DomainException('Pembayaran hanya dapat dilakukan setelah naskah dinyatakan diterima untuk publikasi oleh dewan redaksi.');
        }

        // Invariant 2: Jurnal harus merupakan jurnal berbayar (memiliki biaya APC)
        $journal = $submission->journal ?? Journal::first();
        if (! $journal || ! $journal->isPaidApc()) {
            throw new DomainException('Jurnal ini tidak memungut biaya publikasi (Gratis / Diamond Open Access). Anda tidak perlu mengunggah bukti pembayaran.');
        }

        return DB::transaction(function () use ($submission, $author, $file, $data): ArticlePayment {
            $payment = $submission->latestPayment;
            if (! $payment) {
                $payment = $this->resolveOrCreatePayment($submission, $author);
            }

            if (! $payment) {
                throw new DomainException('Gagal memproses data tagihan pembayaran naskah.');
            }

            // Simpan berkas fisik ke disk penyimpanan lokal
            $path = $file->store("payments/{$submission->id}", 'local');
            $sha256 = hash_file('sha256', $file->getRealPath());

            $fileEntry = FileEntry::create([
                'storage_disk' => 'local',
                'storage_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'byte_size' => (int) Storage::disk('local')->size($path),
                'checksum_sha256' => $sha256,
                'virus_scan_status' => 'pending',
                'created_by' => $author->id,
            ]);

            $amountPaid = isset($data['amount_paid']) && is_numeric($data['amount_paid'])
                ? (float) $data['amount_paid']
                : (float) $payment->amount;

            $payment->update([
                'file_entry_id' => $fileEntry->id,
                'amount_paid' => $amountPaid,
                'sender_bank_name' => ! empty($data['sender_bank_name']) ? trim((string) $data['sender_bank_name']) : null,
                'sender_account_name' => ! empty($data['sender_account_name']) ? trim((string) $data['sender_account_name']) : null,
                'transfer_date' => ! empty($data['transfer_date']) ? $data['transfer_date'] : now()->toDateString(),
                'author_notes' => ! empty($data['author_notes']) ? trim((string) $data['author_notes']) : null,
                'status' => 'pending_verification',
                'uploaded_at' => now(),
                'version' => $payment->version + 1,
            ]);

            return $payment->fresh(['fileEntry', 'submission', 'user']);
        });
    }

    /**
     * Memverifikasi dan menyetujui pembayaran (Lunas).
     */
    public function verifyPayment(ArticlePayment $payment, User $verifier, ?string $notes = null): ArticlePayment
    {
        return DB::transaction(function () use ($payment, $verifier, $notes) {
            $role = $verifier->hasRole('manager')
                ? 'manager'
                : ($verifier->hasRole('section_editor') ? 'section_editor' : 'editor_in_chief');
            PortalAccess::lockSubmission($payment->submission, $verifier, $role);
            $payment = ArticlePayment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($payment->status !== 'pending_verification') {
                throw new DomainException('Pembayaran tidak sedang menunggu verifikasi.');
            }
            if ($payment->fileEntry?->virus_scan_status !== 'clean') {
                throw new DomainException('Bukti pembayaran belum lulus pemindaian keamanan.');
            }
            if ((int) str_replace('.', '', (string) $payment->amount_paid) < (int) str_replace('.', '', (string) $payment->amount)) {
                throw new DomainException('Nominal pembayaran belum memenuhi tagihan APC.');
            }

            return DB::transaction(function () use ($payment, $verifier, $notes): ArticlePayment {
                $payment->update([
                    'status' => 'verified',
                    'verified_by' => $verifier->id,
                    'verified_at' => now(),
                    'verification_notes' => $notes ? trim($notes) : 'Pembayaran sah dan telah diverifikasi oleh dewan redaksi/pengelola jurnal.',
                    'version' => $payment->version + 1,
                ]);

                return $payment->fresh();
            });
        });
    }

    /**
     * Menolak bukti pembayaran yang tidak sesuai (misal buram, nominal salah).
     */
    public function rejectPayment(ArticlePayment $payment, User $verifier, string $reason): ArticlePayment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason) {
            $role = $verifier->hasRole('manager')
                ? 'manager'
                : ($verifier->hasRole('section_editor') ? 'section_editor' : 'editor_in_chief');
            PortalAccess::lockSubmission($payment->submission, $verifier, $role);
            $payment = ArticlePayment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($payment->status !== 'pending_verification') {
                throw new DomainException('Pembayaran tidak sedang menunggu verifikasi.');
            }

            if (trim($reason) === '') {
                throw new DomainException('Alasan penolakan bukti pembayaran wajib diisi.');
            }

            return DB::transaction(function () use ($payment, $verifier, $reason): ArticlePayment {
                $payment->update([
                    'status' => 'rejected',
                    'verified_by' => $verifier->id,
                    'verified_at' => now(),
                    'verification_notes' => trim($reason),
                    'version' => $payment->version + 1,
                ]);

                return $payment->fresh();
            });
        });
    }

    /**
     * Memberikan pembebasan biaya (waiver / keringanan) kepada naskah.
     */
    public function waivePayment(ArticlePayment $payment, User $verifier, string $reason): ArticlePayment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason) {
            $role = $verifier->hasRole('manager')
                ? 'manager'
                : ($verifier->hasRole('section_editor') ? 'section_editor' : 'editor_in_chief');
            PortalAccess::lockSubmission($payment->submission, $verifier, $role);
            $payment = ArticlePayment::query()->lockForUpdate()->findOrFail($payment->id);

            if (trim($reason) === '') {
                throw new DomainException('Alasan pembebasan biaya (waiver) wajib diisi.');
            }

            return DB::transaction(function () use ($payment, $verifier, $reason): ArticlePayment {
                $payment->update([
                    'status' => 'waived',
                    'verified_by' => $verifier->id,
                    'verified_at' => now(),
                    'verification_notes' => trim($reason),
                    'version' => $payment->version + 1,
                ]);

                return $payment->fresh();
            });
        });
    }

    /**
     * Menentukan nominal APC default jurnal dalam format numeric.
     */
    public function determineApcAmount(Journal $journal): float
    {
        if (! empty($journal->apc_amount) && (float) $journal->apc_amount > 0) {
            return (float) $journal->apc_amount;
        }

        if (! empty($journal->apc_fee)) {
            $cleaned = preg_replace('/[^0-9]/', '', $journal->apc_fee);
            if (! empty($cleaned) && (float) $cleaned > 0) {
                return (float) $cleaned;
            }
        }

        return 500000.00; // Default nominal APC standar jika berbayar tapi tidak ditentukan
    }
}
