<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\EditorialDecision;
use App\Models\Journal;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use App\Services\Payment\ArticlePaymentService;
use Illuminate\Support\Collection;

class SectionEditorWorkspaceViewModel
{
    /**
     * @param  Collection<int, User>  $availableReviewers
     */
    public function __construct(
        public readonly Submission $submission,
        public readonly ReviewRound $activeRound,
        public readonly User $editor,
        public readonly Collection $availableReviewers,
        private readonly ArticlePaymentService $paymentService
    ) {}

    public function submissionNumber(): string
    {
        return $this->submission->submission_number ?? "SUB-{$this->submission->id}";
    }

    public function title(): string
    {
        return $this->submission->title;
    }

    public function abstract(): string
    {
        return $this->submission->abstract ?? '';
    }

    public function sectionName(): string
    {
        return $this->submission->section?->name ?? $this->submission->section?->title ?? 'Umum';
    }

    public function authorName(): string
    {
        return $this->submission->author?->name ?? 'Penulis Naskah';
    }

    public function authorEmail(): string
    {
        return $this->submission->author?->email ?? '-';
    }

    public function currentStatus(): string
    {
        return $this->submission->status;
    }

    public function completedReviewsCount(): int
    {
        return $this->activeRound->completedReviewCount();
    }

    public function minimumCompletedReviews(): int
    {
        return max(1, (int) $this->activeRound->minimum_completed_reviews);
    }

    public function canRecommendToEic(): bool
    {
        return $this->completedReviewsCount() >= $this->minimumCompletedReviews();
    }

    public function hasSeRecommendation(): bool
    {
        return ! empty($this->activeRound->se_recommendation);
    }

    public function currentEditorialDecision(): ?EditorialDecision
    {
        return $this->submission->editorialDecisions
            ->where('review_round_id', $this->activeRound->id)
            ->first();
    }

    public function minimumReviewOverrideReason(): ?string
    {
        return $this->currentEditorialDecision()?->minimum_review_override_reason;
    }

    public function hasOverrideRecommendation(): bool
    {
        return $this->hasSeRecommendation() && ! empty($this->minimumReviewOverrideReason());
    }

    /**
     * Apakah alur kerja editorial naskah sudah diputuskan secara final oleh EIC.
     */
    public function isWorkflowFinalized(): bool
    {
        return in_array($this->submission->status, [
            'accepted',
            'in_production',
            'published',
            'declined',
            'archived',
            'withdrawn',
        ], true);
    }

    /**
     * Informasi ringkas status alur kerja naskah.
     *
     * @return array{label: string, badge_class: string, stage_name: string}
     */
    public function statusInfo(): array
    {
        $label = match ($this->submission->status) {
            'draft' => 'Draf Belum Diajukan',
            'submitted' => 'Menunggu Skrining Awal',
            'in_review', 'under_review' => 'Dalam Penelaahan',
            'revisions_requested' => 'Perlu Revisi Penulis',
            'accepted' => 'Diterima untuk Publikasi',
            'in_production' => 'Tahap Produksi',
            'published' => 'Telah Terbit Resmi',
            'declined' => 'Naskah Ditolak',
            'archived', 'withdrawn' => 'Naskah Diarsipkan',
            default => 'Dalam Proses Editorial',
        };

        $badgeClass = match ($this->submission->status) {
            'submitted' => 'bg-amber-50 text-amber-800 border-amber-200',
            'in_review', 'under_review' => 'bg-sky-50 text-sky-800 border-sky-200',
            'revisions_requested' => 'bg-orange-50 text-orange-800 border-orange-200',
            'accepted' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'in_production' => 'bg-purple-50 text-purple-800 border-purple-200',
            'published' => 'bg-green-50 text-green-800 border-green-200',
            'declined' => 'bg-red-50 text-red-800 border-red-200',
            'archived', 'withdrawn' => 'bg-zinc-100 text-zinc-700 border-zinc-300',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };

        $stageName = match ($this->submission->status) {
            'submitted' => 'Tahap 2 dari 6',
            'in_review', 'under_review' => 'Tahap 3 dari 6',
            'revisions_requested' => 'Tahap 4 dari 6',
            'accepted', 'in_production' => 'Tahap 5 dari 6',
            'published' => 'Tahap 6 dari 6',
            'declined', 'archived', 'withdrawn' => 'Alur Dihentikan',
            default => 'Tahap 1 dari 6',
        };

        return [
            'label' => $label,
            'badge_class' => $badgeClass,
            'stage_name' => $stageName,
        ];
    }

    /**
     * Stepper visual 5 langkah progres alur kerja editorial naskah.
     *
     * @return array<int, array{step: int, title: string, subtitle: string, state: string}>
     */
    public function workflowSteps(): array
    {
        $status = $this->submission->status;
        $hasDeskPassed = $this->submission->deskReviews->where('status', 'passed')->isNotEmpty()
            || in_array($status, ['in_review', 'under_review', 'revisions_requested', 'accepted', 'in_production', 'published'], true);
        $hasSe = true; // Section Editor sudah ditugaskan
        $hasDecision = $this->submission->editorialDecisions->where('status', '!=', 'draft')->whereNotNull('sent_at')->isNotEmpty()
            || in_array($status, ['accepted', 'in_production', 'published', 'declined'], true);
        $inProdOrPub = in_array($status, ['accepted', 'in_production', 'published'], true);

        if ($status === 'declined') {
            return [
                ['step' => 1, 'title' => 'Skrining Awal', 'subtitle' => 'Pemeriksaan Redaksi', 'state' => 'completed'],
                ['step' => 2, 'title' => 'Penugasan Editor', 'subtitle' => $this->editor->name, 'state' => 'completed'],
                ['step' => 3, 'title' => 'Telaah Sejawat', 'subtitle' => 'Evaluasi Mitra Bestari', 'state' => 'completed'],
                ['step' => 4, 'title' => 'Keputusan Redaksi', 'subtitle' => 'Naskah Ditolak Resmi', 'state' => 'terminated'],
                ['step' => 5, 'title' => 'Produksi & Terbit', 'subtitle' => 'Alur Dihentikan', 'state' => 'upcoming'],
            ];
        }

        return [
            [
                'step' => 1,
                'title' => 'Skrining Awal',
                'subtitle' => 'Pemeriksaan Kelayakan',
                'state' => $hasDeskPassed ? 'completed' : 'current',
            ],
            [
                'step' => 2,
                'title' => 'Penugasan Editor',
                'subtitle' => $this->editor->name,
                'state' => 'completed',
            ],
            [
                'step' => 3,
                'title' => 'Telaah & Revisi',
                'subtitle' => $status === 'revisions_requested' ? 'Menunggu Revisi Penulis' : 'Evaluasi Mitra Bestari',
                'state' => $inProdOrPub ? 'completed' : (in_array($status, ['in_review', 'under_review', 'revisions_requested'], true) ? 'current' : 'upcoming'),
            ],
            [
                'step' => 4,
                'title' => 'Keputusan Redaksi',
                'subtitle' => $hasDecision ? 'Keputusan Diterbitkan' : 'Penetapan oleh EIC',
                'state' => $inProdOrPub ? 'completed' : ($this->hasSeRecommendation() ? 'current' : ($hasDecision ? 'completed' : 'upcoming')),
            ],
            [
                'step' => 5,
                'title' => 'Produksi & Terbit',
                'subtitle' => $status === 'published' ? 'Telah Terbit Resmi' : 'Penyuntingan & Galai',
                'state' => $status === 'published' ? 'completed' : ($inProdOrPub ? 'current' : 'upcoming'),
            ],
        ];
    }

    /**
     * Rincian status dan berkas bukti pembayaran APC naskah.
     *
     * @return array<string, mixed>
     */
    public function paymentInfo(): array
    {
        $sub = $this->submission;
        $journal = $sub->journal ?? Journal::first();
        $isPaid = (bool) ($journal?->isPaidApc() ?? false);
        $payment = $sub->latestPayment;

        if (! $isPaid) {
            return [
                'is_free' => true,
                'status' => 'free',
                'status_label' => 'Bebas Biaya (Diamond Open Access)',
                'status_badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'apc_fee' => (string) ($journal?->apc_fee ?? 'Rp 0,-'),
                'has_proof' => false,
                'proof_url' => null,
                'can_verify' => false,
                'can_reject' => false,
                'can_waive' => false,
            ];
        }

        $hasProof = (bool) ($payment && $payment->file_entry_id);
        $actions = $this->paymentService->availableActions($sub, $payment);

        return [
            'is_free' => false,
            'status' => $payment ? $payment->status : 'pending_payment',
            'status_label' => $payment ? $payment->statusLabel() : 'Menunggu Pembayaran',
            'status_badge' => $payment ? $payment->statusBadgeClass() : 'bg-amber-50 text-amber-800 border-amber-200',
            'invoice_number' => $payment?->invoice_number ?? '-',
            'amount_formatted' => $payment && $payment->amount_paid ? 'Rp '.number_format((float) $payment->amount_paid, 0, ',', '.') : ($payment ? 'Rp '.number_format((float) $payment->amount, 0, ',', '.') : 'Rp '.number_format((float) ($journal?->apc_amount ?? 500000), 0, ',', '.')),
            'sender_bank_name' => $payment?->sender_bank_name,
            'sender_account_name' => $payment?->sender_account_name,
            'transfer_date' => $payment?->transfer_date?->format('d M Y'),
            'author_notes' => $payment?->author_notes,
            'verification_notes' => $payment?->verification_notes,
            'uploaded_at' => $payment?->uploaded_at?->format('d M Y H:i'),
            'verified_at' => $payment?->verified_at?->format('d M Y H:i'),
            'has_proof' => $hasProof,
            'proof_url' => $hasProof ? route('section-editor.submissions.payment.proof', $sub->id) : null,
            'can_verify' => $actions['can_verify'],
            'can_reject' => $actions['can_reject'],
            'can_waive' => $actions['can_waive'],
        ];
    }
}
