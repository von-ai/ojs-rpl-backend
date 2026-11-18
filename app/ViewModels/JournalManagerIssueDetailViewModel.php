<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Issue;
use App\Models\Submission;
use Illuminate\Support\Facades\Schema;

class JournalManagerIssueDetailViewModel
{
    private string $issueId;

    public function __construct(string $issueId = 'ISSUE-01JARK960A')
    {
        $this->issueId = $issueId;
    }

    /**
     * Data lengkap edisi terbitan untuk ruang kerja penyusunan daftar isi (Table of Contents).
     *
     * @return array{
     *     id: string,
     *     volume: string,
     *     number: string,
     *     year: int,
     *     title: string,
     *     description: ?string,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     is_current: bool,
     *     scheduled_at: ?string,
     *     publication_date: ?string,
     *     published_at: ?string,
     *     doi_prefix: string,
     *     can_publish: bool,
     *     empty_guard_violated: bool,
     *     articles: array<int, array{
     *         id: string,
     *         submission_number: string,
     *         position: int,
     *         title: string,
     *         section_title: string,
     *         authors_string: string,
     *         pages: string,
     *         galley_pdf_url: string,
     *         doi: string
     *     }>,
     *     ready_articles: array<int, array{
     *         id: string,
     *         number: string,
     *         title: string,
     *         section_title: string,
     *         author_name: string,
     *         galley_format: string,
     *         approved_at: string
     *     }>,
     *     activity_timeline: array<int, array{
     *         title: string,
     *         actor: string,
     *         date: string,
     *         description: string,
     *         badge_class: string
     *     }>
     * }
     */
    public function issue(): ?array
    {
        if (Schema::hasTable('issues')) {
            $issue = Issue::with([
                'journal',
                'issueArticles.submission.section',
                'issueArticles.submission.author',
                'issueArticles.submission.activeGalley',
                'issueArticles.submission.latestPayment.fileEntry',
            ])->where('id', $this->issueId)->first();

            if ($issue) {
                return $this->formatIssueData($issue);
            }
        }

        return null;
    }

    private function formatIssueData(Issue $issue): array
    {
        $isJournalPaid = (bool) ($issue->journal?->isPaidApc() ?? false);

        $articles = $issue->issueArticles->map(function ($ia) use ($issue, $isJournalPaid) {
            $sub = $ia->submission;

            $galleyDownloadUrl = '#';
            if ($sub && $sub->activeGalley) {
                try {
                    $galleyDownloadUrl = route('layouteditor.galleys.download', $sub->activeGalley->id);
                } catch (\Throwable) {
                    $galleyDownloadUrl = '#';
                }
            }

            $payment = $sub?->latestPayment;
            $paymentStatus = ! $isJournalPaid ? 'free' : ($payment ? $payment->status : 'pending_payment');
            $paymentLabel = ! $isJournalPaid ? 'Bebas Biaya' : ($payment ? $payment->statusLabel() : 'Belum Dibayar');
            $paymentBadge = ! $isJournalPaid ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($payment ? $payment->statusBadgeClass() : 'bg-amber-50 text-amber-800 border-amber-200');
            $hasProof = (bool) ($payment && $payment->file_entry_id);
            $proofUrl = ($sub && $hasProof) ? route('journalmanager.submissions.payment.proof', $sub->id) : null;
            $amountFormatted = $payment && $payment->amount_paid ? 'Rp '.number_format((float) $payment->amount_paid, 0, ',', '.') : ($payment ? 'Rp '.number_format((float) $payment->amount, 0, ',', '.') : ($isJournalPaid ? 'Rp '.number_format((float) ($issue->journal?->apc_amount ?? 500000), 0, ',', '.') : 'Gratis'));

            return [
                'id' => (string) $ia->id,
                'submission_id' => (string) ($sub?->id ?? ''),
                'submission_number' => $sub?->submission_number ?? 'SUB-N/A',
                'position' => (int) $ia->position,
                'title' => $sub?->title ?? 'Naskah Artikel',
                'section_title' => $sub?->section?->name ?? 'Bidang Riset',
                'authors_string' => $sub?->author?->name ?? 'Penulis Utama',
                'pages' => $ia->pages ?? '1-14',
                'galley_pdf_url' => $galleyDownloadUrl,
                'doi' => $ia->doi ?? ($issue->doi_prefix ? "{$issue->doi_prefix}.".str_pad((string) $ia->position, 2, '0', STR_PAD_LEFT) : '-'),
                'is_paid_journal' => $isJournalPaid,
                'payment_status' => $paymentStatus,
                'payment_label' => $paymentLabel,
                'payment_badge' => $paymentBadge,
                'has_proof' => $hasProof,
                'proof_url' => $proofUrl,
                'amount_formatted' => $amountFormatted,
                'invoice_number' => $payment?->invoice_number,
                'sender_bank_name' => $payment?->sender_bank_name,
                'sender_account_name' => $payment?->sender_account_name,
                'transfer_date' => $payment?->transfer_date?->format('d M Y'),
                'author_notes' => $payment?->author_notes,
                'verification_notes' => $payment?->verification_notes,
                'uploaded_at' => $payment?->uploaded_at?->format('d M Y H:i'),
                'verified_at' => $payment?->verified_at?->format('d M Y H:i'),
            ];
        })->values()->all();

        // Antrean Naskah Siap Terbit (belum masuk edisi)
        $readyArticles = [];
        if (Schema::hasTable('submissions')) {
            $readySubmissions = Submission::where('journal_id', $issue->journal_id)
                ->whereDoesntHave('issueArticle')
                ->whereIn('status', ['accepted', 'in_production', 'ready_for_issue', 'approved'])
                ->whereNotIn('status', ['declined', 'rejected', 'archived', 'withdrawn', 'in_review', 'under_review', 'revisions_requested', 'draft', 'submitted', 'published'])
                ->with(['section', 'author', 'activeGalley', 'latestPayment.fileEntry', 'journal'])
                ->get();

            $readyArticles = $readySubmissions->map(function (Submission $sub) use ($isJournalPaid, $issue) {
                if ($sub->activeGalley) {
                    $cleanLabel = preg_replace('/\s*\([^)]*\)/', '', $sub->activeGalley->label);
                    $cleanLabel = str_ireplace('PDF Full Text', 'PDF Teks Lengkap', $cleanLabel);
                    $cleanLabel = trim((string) $cleanLabel) ?: 'PDF Teks Lengkap';
                    $formatLabel = $cleanLabel;
                    $galleyVersion = $sub->activeGalley->version ?? 1;
                } else {
                    $formatLabel = 'PDF Teks Lengkap';
                    $galleyVersion = 1;
                }

                $payment = $sub->latestPayment;
                $paymentStatus = ! $isJournalPaid ? 'free' : ($payment ? $payment->status : 'pending_payment');
                $paymentLabel = ! $isJournalPaid ? 'Bebas Biaya' : ($payment ? $payment->statusLabel() : 'Belum Dibayar');
                $paymentBadge = ! $isJournalPaid ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($payment ? $payment->statusBadgeClass() : 'bg-amber-50 text-amber-800 border-amber-200');
                $hasProof = (bool) ($payment && $payment->file_entry_id);
                $proofUrl = $hasProof ? route('journalmanager.submissions.payment.proof', $sub->id) : null;
                $amountFormatted = $payment && $payment->amount_paid ? 'Rp '.number_format((float) $payment->amount_paid, 0, ',', '.') : ($payment ? 'Rp '.number_format((float) $payment->amount, 0, ',', '.') : ($isJournalPaid ? 'Rp '.number_format((float) ($issue->journal?->apc_amount ?? 500000), 0, ',', '.') : 'Gratis'));

                $isPaymentCleared = ! $isJournalPaid || ($payment && ($payment->isVerified() || $payment->isWaived()));
                $isIssuePublished = $issue->isPublished();

                $canAddToIssue = false;
                $cantAddReason = '';

                if ($isIssuePublished) {
                    $canAddToIssue = false;
                    $cantAddReason = 'Edisi ini telah resmi terbit. Penambahan naskah baru ke daftar isi hanya dapat dilakukan pada edisi Draf atau Terjadwal.';
                } elseif (! $isPaymentCleared) {
                    $canAddToIssue = false;
                    if ($paymentStatus === 'pending_verification') {
                        $cantAddReason = 'Bukti pembayaran menunggu verifikasi redaksi. Verifikasi atau bebaskan biaya terlebih dahulu.';
                    } elseif ($paymentStatus === 'rejected') {
                        $cantAddReason = 'Bukti pembayaran ditolak. Perlu perbaikan bukti atau berikan pembebasan biaya terlebih dahulu.';
                    } else {
                        $cantAddReason = 'Pembayaran APC belum lunas. Naskah harus dibayar atau dibebaskan terlebih dahulu sebelum masuk edisi.';
                    }
                } else {
                    $canAddToIssue = true;
                    $cantAddReason = 'Masukkan ke Edisi Terbitan';
                }

                return [
                    'id' => (string) $sub->id,
                    'number' => (string) $sub->submission_number,
                    'title' => (string) $sub->title,
                    'section_title' => (string) ($sub->section?->name ?? 'Bidang Riset'),
                    'author_name' => (string) ($sub->author?->name ?? 'Penulis Naskah'),
                    'galley_format' => $formatLabel,
                    'galley_version' => $galleyVersion,
                    'approved_at' => $sub->updated_at ? $sub->updated_at->translatedFormat('d M Y H:i').' WITA' : 'Tersedia',
                    'is_paid_journal' => $isJournalPaid,
                    'payment_status' => $paymentStatus,
                    'payment_label' => $paymentLabel,
                    'payment_badge' => $paymentBadge,
                    'has_proof' => $hasProof,
                    'proof_url' => $proofUrl,
                    'amount_formatted' => $amountFormatted,
                    'invoice_number' => $payment?->invoice_number,
                    'sender_bank_name' => $payment?->sender_bank_name,
                    'sender_account_name' => $payment?->sender_account_name,
                    'transfer_date' => $payment?->transfer_date?->format('d M Y'),
                    'author_notes' => $payment?->author_notes,
                    'verification_notes' => $payment?->verification_notes,
                    'uploaded_at' => $payment?->uploaded_at?->format('d M Y H:i'),
                    'verified_at' => $payment?->verified_at?->format('d M Y H:i'),
                    'can_add_to_issue' => $canAddToIssue,
                    'cant_add_reason' => $cantAddReason,
                    'is_payment_cleared' => $isPaymentCleared,
                ];
            })->values()->all();
        }

        $canPublish = $issue->canPublish();
        $emptyGuardViolated = count($articles) === 0;

        // Susun Linimasa Aktivitas Dinamis
        $timeline = [];
        if ($issue->isPublished() && $issue->published_at) {
            $timeline[] = [
                'title' => 'Edisi Resmi Dipublikasikan',
                'actor' => 'Journal Manager',
                'date' => $issue->published_at->translatedFormat('d M Y H:i').' WITA',
                'description' => "Edisi {$issue->title} telah resmi dipublikasikan dan terbuka untuk pembaca.",
                'badge_class' => 'bg-brand-maroon',
            ];
        }

        if (count($articles) > 0) {
            $lastArticle = end($articles);
            $timeline[] = [
                'title' => 'Artikel '.count($articles).' Ditambahkan ke Daftar Isi',
                'actor' => 'Journal Manager',
                'date' => $issue->updated_at ? $issue->updated_at->translatedFormat('d M Y H:i').' WITA' : 'Hari ini',
                'description' => "Artikel {$lastArticle['submission_number']} dimasukkan ke posisi nomor {$lastArticle['position']} dengan halaman {$lastArticle['pages']}.",
                'badge_class' => 'bg-brand-maroon',
            ];
        }

        $timeline[] = [
            'title' => 'Draf Edisi Dibuat',
            'actor' => 'Journal Manager',
            'date' => $issue->created_at ? $issue->created_at->translatedFormat('d M Y H:i').' WITA' : 'Bulan ini',
            'description' => "Nomor terbitan Vol. {$issue->volume} No. {$issue->number} ({$issue->year}) dibuka untuk kurasi naskah.",
            'badge_class' => 'bg-brand-maroon',
        ];

        return [
            'id' => (string) $issue->id,
            'volume' => (string) $issue->volume,
            'number' => (string) $issue->number,
            'year' => (int) $issue->year,
            'title' => (string) $issue->title,
            'description' => $issue->description,
            'status' => (string) $issue->status,
            'status_label' => $issue->status_label,
            'status_badge_class' => $issue->status_badge_class,
            'is_current' => (bool) $issue->is_current,
            'scheduled_at' => $issue->scheduled_at ? $issue->scheduled_at->translatedFormat('d M Y') : null,
            'publication_date' => $issue->published_at ? $issue->published_at->translatedFormat('d M Y') : null,
            'published_at' => $issue->published_at ? $issue->published_at->translatedFormat('d M Y H:i').' WITA' : null,
            'doi_prefix' => (string) ($issue->doi_prefix ?? "10.61234/sirida.v{$issue->volume}i{$issue->number}"),
            'can_publish' => $canPublish,
            'empty_guard_violated' => $emptyGuardViolated,
            'articles' => $articles,
            'ready_articles' => $readyArticles,
            'activity_timeline' => $timeline,
        ];
    }
}
