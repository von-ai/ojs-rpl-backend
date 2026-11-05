<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ArticlePayment;
use App\Models\FileEntry;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticlePaymentSeeder extends Seeder
{
    /**
     * Menjalankan seeding komprehensif data pembayaran dan unggah bukti transfer naskah (APC).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seeder tidak boleh dijalankan pada production.');
        }

        // 1. Dapatkan atau aktifkan konfigurasi APC pada Jurnal SIRIDA Makassar
        $journal = Journal::where('slug', 'sirida-makassar')->first() ?? Journal::first();

        if (! $journal) {
            $this->command?->warn('Tidak ada jurnal yang ditemukan untuk seeding pembayaran.');

            return;
        }

        $journal->update([
            'apc_enabled' => true,
            'apc_amount' => 750000,
            'payment_bank_name' => 'Bank Sulselbar',
            'payment_account_number' => '130-003-00012345-6',
            'payment_account_holder' => 'BPP BRIDA Kota Makassar (Penerimaan APC)',
            'payment_instructions' => 'Mohon transfer sesuai nominal tagihan (Rp 750.000). Cantumkan nomor naskah (misal: SUB-2026-00106) pada berita transfer m-Banking / ATM. Unggah foto bukti transfer atau tangkapan layar transaksi resmi (JPG, PNG, atau PDF).',
        ]);

        // 2. Dapatkan pengguna inti
        $managerUser = User::where('email', 'yusuf.brida@makassar.go.id')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('role_code', 'manager'))->first()
            ?? User::first();

        $authorUser = User::where('email', 'andi.ilyas@unhas.ac.id')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('role_code', 'author'))->first()
            ?? User::first();

        // Binary representasi file PNG 1x1 piksel transparan untuk bukti transfer nyata
        $dummyReceiptPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        // =========================================================================
        // SKENARIO 1: Menunggu Verifikasi Pengelola (pending_verification)
        // Penulis telah mengunggah bukti transfer, menunggu aksi verifikasi Journal Manager / EIC.
        // =========================================================================
        $subPending = Submission::where('submission_number', 'SUB-2026-00106')->first()
            ?? Submission::where('status', 'in_production')->first();

        if ($subPending) {
            $path1 = "payments/{$subPending->id}/bukti_transfer_brimo_sub106.png";
            Storage::disk('azure')->put($path1, $dummyReceiptPng);

            $file1 = FileEntry::create([
                'storage_disk' => 'azure',
                'storage_path' => $path1,
                'original_name' => 'bukti_transfer_brimo_sub106.png',
                'mime_type' => 'image/png',
                'byte_size' => strlen($dummyReceiptPng),
                'checksum_sha256' => hash('sha256', $dummyReceiptPng),
                'virus_scan_status' => 'clean',
                'created_by' => $subPending->author_id ?? $authorUser->id,
            ]);

            $invoiceNumber1 = 'INV-'.($subPending->submission_number ? str_replace('-', '', $subPending->submission_number) : strtoupper(Str::substr((string) $subPending->id, -8)));

            ArticlePayment::updateOrCreate(
                ['submission_id' => $subPending->id],
                [
                    'user_id' => $subPending->author_id ?? $authorUser->id,
                    'invoice_number' => $invoiceNumber1,
                    'amount' => 750000,
                    'amount_paid' => 750000,
                    'status' => 'pending_verification',
                    'file_entry_id' => $file1->id,
                    'sender_bank_name' => 'Bank BRI',
                    'sender_account_name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
                    'transfer_date' => now()->subDay()->toDateString(),
                    'author_notes' => 'Telah ditransfer via BRImo sebesar Rp 750.000 dengan berita transfer '.$subPending->submission_number.'. Mohon verifikasinya agar naskah dapat dipublikasikan.',
                    'verification_notes' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                    'uploaded_at' => now()->subDay(),
                    'version' => 1,
                ]
            );
        }

        // =========================================================================
        // SKENARIO 2: Bukti Transfer Ditolak (rejected) - Perlu Unggah Ulang
        // Penulis mengunggah bukti yang tidak jelas, ditolak oleh Journal Manager dengan catatan.
        // =========================================================================
        $subRejected = Submission::where('submission_number', 'SUB-2026-00110')->first()
            ?? Submission::where('status', 'in_production')->skip(1)->first();

        if ($subRejected && $subRejected->id !== ($subPending?->id ?? null)) {
            $path2 = "payments/{$subRejected->id}/struk_atm_mandiri_sub110.png";
            Storage::disk('azure')->put($path2, $dummyReceiptPng);

            $file2 = FileEntry::create([
                'storage_disk' => 'azure',
                'storage_path' => $path2,
                'original_name' => 'struk_atm_mandiri_sub110.png',
                'mime_type' => 'image/png',
                'byte_size' => strlen($dummyReceiptPng),
                'checksum_sha256' => hash('sha256', $dummyReceiptPng),
                'virus_scan_status' => 'clean',
                'created_by' => $subRejected->author_id ?? $authorUser->id,
            ]);

            $invoiceNumber2 = 'INV-'.($subRejected->submission_number ? str_replace('-', '', $subRejected->submission_number) : strtoupper(Str::substr((string) $subRejected->id, -8)));

            ArticlePayment::updateOrCreate(
                ['submission_id' => $subRejected->id],
                [
                    'user_id' => $subRejected->author_id ?? $authorUser->id,
                    'invoice_number' => $invoiceNumber2,
                    'amount' => 750000,
                    'amount_paid' => 750000,
                    'status' => 'rejected',
                    'file_entry_id' => $file2->id,
                    'sender_bank_name' => 'Bank Mandiri',
                    'sender_account_name' => 'Andi M. Ilyas',
                    'transfer_date' => now()->subDays(3)->toDateString(),
                    'author_notes' => 'Transfer via mesin ATM Mandiri dekat kantor.',
                    'verification_notes' => 'Foto struk bukti transfer buram dan nomor referensi transaksi terpotong. Mohon unggah ulang bukti transfer yang jelas (PDF atau foto beresolusi tajam) agar validasi kas dapat disetujui.',
                    'verified_by' => $managerUser->id,
                    'verified_at' => now()->subDay(),
                    'uploaded_at' => now()->subDays(3),
                    'version' => 1,
                ]
            );
        }

        // =========================================================================
        // SKENARIO 3: Belum Dibayar / Tagihan Diterbitkan (pending_payment)
        // Naskah tahap produksi yang menunggu penulis untuk mengunggah bukti transfer.
        // =========================================================================
        $subUnpaid = Submission::where('submission_number', 'SUB-2026-00111')->first()
            ?? Submission::where('status', 'in_production')->skip(2)->first();

        if ($subUnpaid && $subUnpaid->id !== ($subPending?->id ?? null) && $subUnpaid->id !== ($subRejected?->id ?? null)) {
            $invoiceNumber3 = 'INV-'.($subUnpaid->submission_number ? str_replace('-', '', $subUnpaid->submission_number) : strtoupper(Str::substr((string) $subUnpaid->id, -8)));

            ArticlePayment::updateOrCreate(
                ['submission_id' => $subUnpaid->id],
                [
                    'user_id' => $subUnpaid->author_id ?? $authorUser->id,
                    'invoice_number' => $invoiceNumber3,
                    'amount' => 750000,
                    'amount_paid' => 0,
                    'status' => 'pending_payment',
                    'file_entry_id' => null,
                    'sender_bank_name' => null,
                    'sender_account_name' => null,
                    'transfer_date' => null,
                    'author_notes' => null,
                    'verification_notes' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                    'uploaded_at' => null,
                    'version' => 1,
                ]
            );
        }

        // =========================================================================
        // SKENARIO 4: Pembayaran Lunas & Terverifikasi (verified)
        // Naskah yang pembayarannya telah terverifikasi resmi oleh Journal Manager.
        // =========================================================================
        $subVerified = Submission::where('submission_number', 'SUB-2026-00107')->first()
            ?? Submission::where('status', 'published')->first();

        if ($subVerified) {
            $path3 = "payments/{$subVerified->id}/bukti_transfer_bni_sub107.png";
            Storage::disk('azure')->put($path3, $dummyReceiptPng);

            $file3 = FileEntry::create([
                'storage_disk' => 'azure',
                'storage_path' => $path3,
                'original_name' => 'bukti_transfer_bni_sub107.png',
                'mime_type' => 'image/png',
                'byte_size' => strlen($dummyReceiptPng),
                'checksum_sha256' => hash('sha256', $dummyReceiptPng),
                'virus_scan_status' => 'clean',
                'created_by' => $subVerified->author_id ?? $authorUser->id,
            ]);

            $invoiceNumber4 = 'INV-'.($subVerified->submission_number ? str_replace('-', '', $subVerified->submission_number) : strtoupper(Str::substr((string) $subVerified->id, -8)));

            ArticlePayment::updateOrCreate(
                ['submission_id' => $subVerified->id],
                [
                    'user_id' => $subVerified->author_id ?? $authorUser->id,
                    'invoice_number' => $invoiceNumber4,
                    'amount' => 750000,
                    'amount_paid' => 750000,
                    'status' => 'verified',
                    'file_entry_id' => $file3->id,
                    'sender_bank_name' => 'Bank BNI',
                    'sender_account_name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                    'transfer_date' => now()->subDays(10)->toDateString(),
                    'author_notes' => 'Pembayaran biaya publikasi naskah SUB-2026-00107.',
                    'verification_notes' => 'Mutasi kas masuk pada Bank Sulselbar cocok dan terkonfirmasi valid. Lunas.',
                    'verified_by' => $managerUser->id,
                    'verified_at' => now()->subDays(9),
                    'uploaded_at' => now()->subDays(10),
                    'version' => 1,
                ]
            );
        }

        // =========================================================================
        // SKENARIO 5: Dispensasi Biaya / Bebas Biaya Khusus (waived)
        // Penulis diberikan dispensasi pembebasan biaya oleh Redaksi.
        // =========================================================================
        $subWaived = Submission::where('submission_number', 'SUB-2026-SE-001')->first()
            ?? Submission::whereNotIn('id', array_filter([
                $subPending?->id,
                $subRejected?->id,
                $subUnpaid?->id,
                $subVerified?->id,
            ]))->first();

        if ($subWaived) {
            $invoiceNumber5 = 'INV-'.($subWaived->submission_number ? str_replace('-', '', $subWaived->submission_number) : strtoupper(Str::substr((string) $subWaived->id, -8)));

            ArticlePayment::updateOrCreate(
                ['submission_id' => $subWaived->id],
                [
                    'user_id' => $subWaived->author_id ?? $authorUser->id,
                    'invoice_number' => $invoiceNumber5,
                    'amount' => 750000,
                    'amount_paid' => 0,
                    'status' => 'waived',
                    'file_entry_id' => null,
                    'sender_bank_name' => null,
                    'sender_account_name' => null,
                    'transfer_date' => null,
                    'author_notes' => 'Permohonan dispensasi APC untuk program riset kerja sama antar-daerah.',
                    'verification_notes' => 'Dispensasi biaya (100% APC Waiver) disetujui oleh Redaksi dan Dewan Pengelola Jurnal.',
                    'verified_by' => $managerUser->id,
                    'verified_at' => now()->subDays(2),
                    'uploaded_at' => null,
                    'version' => 1,
                ]
            );
        }

        $this->command?->info('Seeding data pembayaran & unggah bukti transfer berhasil dijalankan.');
    }
}
