<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\DeskReview;
use App\Models\EditorialDecision;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\Journal;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EicSubmissionDetailViewModel
{
    public function __construct(
        public readonly Submission $submission
    ) {}

    /**
     * Apakah tahap Skrining Awal (Tahap 1) masih dapat diisi atau diubah.
     * Hanya aktif ketika naskah berstatus submitted dan belum berpindah ke tahap berikutnya.
     */
    public function canEditDeskReview(): bool
    {
        return $this->submission->status === 'submitted';
    }

    /**
     * Apakah tahap lanjutan sedang menunggu penyelesaian Skrining Awal terlebih dahulu.
     */
    public function isWaitingDeskReview(): bool
    {
        return $this->submission->status === 'submitted' && $this->submission->reviewRounds->isEmpty();
    }

    /**
     * Apakah alur editorial naskah telah mencapai keputusan final (diterima, produksi, terbit, ditolak, diarsipkan).
     */
    public function isWorkflowFinalized(): bool
    {
        return in_array($this->submission->status, [
            'accepted',
            'in_production',
            'ready_for_issue',
            'published',
            'declined',
            'archived',
            'withdrawn',
        ], true);
    }

    /**
     * Mengambil berkas galai aktif naskah saat ini jika ada.
     */
    public function activeGalley(): ?Galley
    {
        return $this->submission->activeGalley
            ?? $this->submission->galleys->firstWhere('status', 'active');
    }

    /**
     * Memeriksa apakah naskah memiliki persetujuan akhir galai resmi (editor_final).
     */
    public function hasFinalGalleyApproval(): bool
    {
        $galley = $this->activeGalley();
        if (! $galley) {
            return false;
        }

        return $galley->approvals
            ->where('stage', 'editor_final')
            ->whereNull('invalidated_at')
            ->isNotEmpty();
    }

    /**
     * Mengambil objek persetujuan akhir galai resmi jika telah disahkan.
     */
    public function finalGalleyApproval(): ?GalleyApproval
    {
        $galley = $this->activeGalley();
        if (! $galley) {
            return null;
        }

        return $galley->approvals
            ->where('stage', 'editor_final')
            ->whereNull('invalidated_at')
            ->sortByDesc('approved_at')
            ->first();
    }

    /**
     * Memeriksa apakah Pemimpin Redaksi dapat menerbitkan persetujuan akhir galai.
     * Naskah harus memiliki galai aktif, belum disahkan final, dan belum terbit.
     */
    public function canApproveFinalGalley(): bool
    {
        if (in_array($this->submission->status, ['published', 'declined', 'archived', 'withdrawn', 'ready_for_issue'], true)) {
            return false;
        }

        $galley = $this->activeGalley();
        if (! $galley) {
            return false;
        }

        if ($this->hasFinalGalleyApproval()) {
            return false;
        }

        return in_array($this->submission->status, ['accepted', 'in_production'], true);
    }

    /**
     * Apakah penugasan Section Editor (Tahap 2) dapat dilakukan atau diperbarui.
     */
    public function canAssignSectionEditor(): bool
    {
        if ($this->isWorkflowFinalized()) {
            return false;
        }

        return in_array($this->submission->status, ['in_review', 'under_review', 'revisions_requested'], true)
            || ($this->submission->status === 'submitted' && $this->submission->reviewRounds->isNotEmpty());
    }

    /**
     * Apakah penetapan Keputusan Editorial Resmi (Tahap 3) dapat dilakukan.
     */
    public function canMakeEditorialDecision(): bool
    {
        if ($this->isWorkflowFinalized()) {
            return false;
        }

        return in_array($this->submission->status, ['in_review', 'under_review', 'revisions_requested'], true)
            || ($this->submission->status === 'submitted' && $this->submission->reviewRounds->isNotEmpty());
    }

    /**
     * Mengambil keputusan editorial resmi terakhir jika sudah diterbitkan.
     */
    public function latestEditorialDecision(): ?EditorialDecision
    {
        return $this->submission->editorialDecisions
            ->where('status', '!=', 'draft')
            ->whereNotNull('sent_at')
            ->sortByDesc('sent_at')
            ->first();
    }

    /**
     * Alasan dispensasi kuorum jika diajukan oleh Section Editor.
     */
    public function minimumReviewOverrideReason(): ?string
    {
        $round = $this->latestReviewRound();
        if (! $round) {
            return null;
        }

        return $this->submission->editorialDecisions
            ->where('review_round_id', $round->id)
            ->first()
            ?->minimum_review_override_reason;
    }

    /**
     * Mengambil daftar calon Section Editor yang dapat ditugaskan untuk inisialisasi awal.
     * Dibatasi agar rendering SSR tetap ringan meski database memiliki ribuan editor.
     * Pencarian interaktif selanjutnya dilayani via asynchronous combobox.
     *
     * @return Collection<int, User>
     */
    public function availableSectionEditors(?int $limit = 20): Collection
    {
        $authorIds = array_filter(array_merge(
            [$this->submission->author_id],
            $this->submission->contributors()->pluck('user_id')->all()
        ));
        $authorEmails = array_filter(array_merge(
            [$this->submission->author?->email],
            $this->submission->contributors()->pluck('email')->all()
        ));

        $roleEditorsQuery = User::whereNotIn('id', $authorIds)
            ->whereNotIn('email', $authorEmails)
            ->where('status', 'active')
            ->whereHas('userRoles', function ($q): void {
                $q->whereIn('role_code', ['section_editor', 'editor_in_chief'])
                    ->where(function ($qj): void {
                        $qj->where('journal_id', $this->submission->journal_id)
                            ->orWhereNull('journal_id');
                    });
            })
            ->withCount([
                'editorAssignments as active_assignments_count' => function ($q): void {
                    $q->whereNull('revoked_at');
                },
            ])
            ->with([
                'userRoles' => function ($q): void {
                    $q->whereIn('role_code', ['section_editor', 'editor_in_chief'])
                        ->where(function ($qj): void {
                            $qj->where('journal_id', $this->submission->journal_id)
                                ->orWhereNull('journal_id');
                        });
                },
            ])
            ->orderBy('full_name', 'asc');

        if ($limit !== null) {
            $roleEditors = (clone $roleEditorsQuery)->limit($limit)->get();
            $assignedId = $this->submission->activeEditorAssignment?->editor_id;
            if ($assignedId && ! $roleEditors->contains('id', $assignedId)) {
                $assignedUser = User::find($assignedId);
                if ($assignedUser) {
                    $roleEditors->prepend($assignedUser);
                }
            }
        } else {
            $roleEditors = $roleEditorsQuery->get();
        }

        if ($roleEditors->isNotEmpty()) {
            return $roleEditors;
        }

        $fallbackQuery = User::whereNotIn('id', $authorIds)
            ->whereNotIn('email', $authorEmails)
            ->where('status', 'active')
            ->withCount([
                'editorAssignments as active_assignments_count' => function ($q): void {
                    $q->whereNull('revoked_at');
                },
            ])
            ->orderBy('full_name', 'asc');

        return $limit !== null ? $fallbackQuery->limit($limit)->get() : $fallbackQuery->get();
    }

    /**
     * Informasi ringkas status alur kerja naskah sesuai 9 status kanonis.
     *
     * @return array{label: string, badge_class: string, stage_name: string}
     */
    public function statusInfo(): array
    {
        $label = match ($this->submission->status) {
            'draft' => 'Draf Belum Diajukan',
            'submitted' => 'Menunggu Skrining Awal',
            'in_review' => 'Dalam Penelaahan',
            'revisions_requested' => 'Perlu Revisi Penulis',
            'accepted' => 'Diterima untuk Publikasi',
            'in_production' => 'Tahap Produksi',
            'ready_for_issue' => 'Siap Masuk Terbitan',
            'published' => 'Telah Terbit Resmi',
            'declined' => 'Naskah Ditolak',
            'archived', 'withdrawn' => 'Naskah Diarsipkan',
            default => 'Dalam Proses Editorial',
        };

        $badgeClass = match ($this->submission->status) {
            'submitted' => 'bg-amber-50 text-amber-800 border-amber-200',
            'in_review' => 'bg-sky-50 text-sky-800 border-sky-200',
            'revisions_requested' => 'bg-orange-50 text-orange-800 border-orange-200',
            'accepted' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'in_production' => 'bg-purple-50 text-purple-800 border-purple-200',
            'ready_for_issue' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'published' => 'bg-green-50 text-green-800 border-green-200',
            'declined' => 'bg-red-50 text-red-800 border-red-200',
            'archived', 'withdrawn' => 'bg-zinc-100 text-zinc-700 border-zinc-300',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };

        $stageName = match ($this->submission->status) {
            'submitted' => 'Tahap 2 dari 6',
            'in_review' => 'Tahap 3 dari 6',
            'revisions_requested' => 'Tahap 4 dari 6',
            'accepted', 'in_production' => 'Tahap 5 dari 6',
            'ready_for_issue' => 'Tahap 5 dari 6 (Siap Terbit)',
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
     * Mengambil penilaian skrining awal terakhir jika sudah dilakukan.
     */
    public function latestDeskReview(): ?DeskReview
    {
        return $this->submission->deskReviews->sortByDesc('created_at')->first();
    }

    /**
     * Mengambil putaran review terakhir pada naskah.
     */
    public function latestReviewRound(): ?ReviewRound
    {
        return $this->submission->reviewRounds->sortByDesc('round')->first();
    }

    /**
     * Ringkasan metrik progres telaah mitra bestari dan rekomendasi Editor Bagian.
     *
     * @return array{
     *     has_round: bool,
     *     round_number: int,
     *     invited_count: int,
     *     completed_count: int,
     *     minimum_required: int,
     *     threshold_met: bool,
     *     se_recommendation: ?string,
     *     se_recommendation_label: string,
     *     se_recommendation_badge: string,
     *     se_recommendation_note: ?string,
     *     se_recommended_at: ?string
     * }
     */
    public function reviewSummary(): array
    {
        $round = $this->latestReviewRound();

        if (! $round) {
            return [
                'has_round' => false,
                'round_number' => 0,
                'invited_count' => 0,
                'completed_count' => 0,
                'minimum_required' => 2,
                'threshold_met' => false,
                'se_recommendation' => null,
                'se_recommendation_label' => 'Belum Ada Rekomendasi Editor Bagian',
                'se_recommendation_badge' => 'bg-gray-100 text-gray-600 border-gray-200',
                'se_recommendation_note' => null,
                'se_recommended_at' => null,
            ];
        }

        $invitedCount = $round->assignments->count();
        $completedCount = $round->assignments->where('status', 'completed')->count();
        $minRequired = max(1, (int) $round->minimum_completed_reviews);
        $rec = $round->se_recommendation;

        $recLabel = match ($rec) {
            'accept' => 'Rekomendasi Editor Bagian: Terima Naskah',
            'revisions_required' => 'Rekomendasi Editor Bagian: Perlu Revisi Penulis',
            'resubmit' => 'Rekomendasi Editor Bagian: Kirim Ulang Telaah',
            'decline' => 'Rekomendasi Editor Bagian: Tolak Naskah',
            default => 'Menunggu Rekomendasi Editor Bagian',
        };

        $recBadge = match ($rec) {
            'accept' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'revisions_required' => 'bg-amber-50 text-amber-800 border-amber-200',
            'resubmit' => 'bg-orange-50 text-orange-800 border-orange-200',
            'decline' => 'bg-rose-50 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };

        return [
            'has_round' => true,
            'round_number' => (int) $round->round,
            'invited_count' => $invitedCount,
            'completed_count' => $completedCount,
            'minimum_required' => $minRequired,
            'threshold_met' => $completedCount >= $minRequired,
            'se_recommendation' => $rec,
            'se_recommendation_label' => $recLabel,
            'se_recommendation_badge' => $recBadge,
            'se_recommendation_note' => $round->se_recommendation_note,
            'se_recommended_at' => $round->se_recommended_at ? $round->se_recommended_at->format('d M Y, H:i') : null,
        ];
    }

    /**
     * Daftar 5 langkah visual stepper progres alur kerja editorial.
     *
     * @return array<int, array{step: int, title: string, subtitle: string, state: string}>
     */
    public function workflowSteps(): array
    {
        $status = $this->submission->status;
        $isDeskRevisionRequested = ($status === 'revisions_requested' && $this->submission->reviewRounds->isEmpty());
        $hasDeskPassed = $this->submission->deskReviews->where('status', 'passed')->isNotEmpty()
            || in_array($status, ['in_review', 'under_review', 'accepted', 'in_production', 'ready_for_issue', 'published'], true)
            || ($status === 'revisions_requested' && ! $isDeskRevisionRequested);
        $hasSe = $this->submission->activeEditorAssignment !== null
            || in_array($status, ['accepted', 'in_production', 'ready_for_issue', 'published'], true)
            || ($status === 'revisions_requested' && ! $isDeskRevisionRequested);
        $hasDecision = $this->submission->editorialDecisions->where('status', '!=', 'draft')->whereNotNull('sent_at')->isNotEmpty()
            || in_array($status, ['accepted', 'in_production', 'ready_for_issue', 'published', 'declined'], true);
        $inProdOrPub = in_array($status, ['accepted', 'in_production', 'ready_for_issue', 'published'], true);

        if ($status === 'declined') {
            return [
                ['step' => 1, 'title' => 'Skrining Awal', 'subtitle' => 'Pemeriksaan Redaksi', 'state' => 'completed'],
                ['step' => 2, 'title' => 'Penugasan Editor', 'subtitle' => 'Delegasi Editor Bagian', 'state' => $hasSe ? 'completed' : 'terminated'],
                ['step' => 3, 'title' => 'Telaah Sejawat', 'subtitle' => 'Evaluasi Mitra Bestari', 'state' => $this->submission->reviewRounds->isNotEmpty() ? 'completed' : 'terminated'],
                ['step' => 4, 'title' => 'Keputusan Redaksi', 'subtitle' => 'Naskah Ditolak Resmi', 'state' => 'terminated'],
                ['step' => 5, 'title' => 'Produksi & Terbit', 'subtitle' => 'Alur Dihentikan', 'state' => 'upcoming'],
            ];
        }

        return [
            [
                'step' => 1,
                'title' => 'Skrining Awal',
                'subtitle' => $isDeskRevisionRequested ? 'Menunggu Revisi Format Penulis' : 'Pemeriksaan Kelayakan',
                'state' => $hasDeskPassed ? 'completed' : (in_array($status, ['submitted', 'revisions_requested'], true) ? 'current' : 'upcoming'),
            ],
            [
                'step' => 2,
                'title' => 'Penugasan Editor',
                'subtitle' => $this->submission->activeEditorAssignment?->editor?->name ?? 'Pilih Editor Bagian',
                'state' => $hasSe ? 'completed' : ($hasDeskPassed ? 'current' : 'upcoming'),
            ],
            [
                'step' => 3,
                'title' => 'Telaah & Revisi',
                'subtitle' => ($status === 'revisions_requested' && ! $isDeskRevisionRequested) ? 'Menunggu Revisi Penulis' : 'Evaluasi Mitra Bestari',
                'state' => $inProdOrPub ? 'completed' : ((in_array($status, ['in_review', 'under_review'], true) || ($status === 'revisions_requested' && ! $isDeskRevisionRequested)) ? 'current' : 'upcoming'),
            ],
            [
                'step' => 4,
                'title' => 'Keputusan Redaksi',
                'subtitle' => $hasDecision ? 'Keputusan Diterbitkan' : 'Penetapan Keputusan',
                'state' => $inProdOrPub ? 'completed' : ($this->reviewSummary()['se_recommendation'] ? 'current' : ($hasDecision ? 'completed' : 'upcoming')),
            ],
            [
                'step' => 5,
                'title' => 'Produksi & Terbit',
                'subtitle' => $status === 'published' ? 'Telah Terbit Resmi' : ($status === 'ready_for_issue' ? 'Siap Terbit (Galai Disetujui)' : 'Penyuntingan & Galai'),
                'state' => in_array($status, ['published', 'ready_for_issue'], true) ? 'completed' : ($inProdOrPub ? 'current' : 'upcoming'),
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
            ];
        }

        $hasProof = (bool) ($payment && $payment->file_entry_id);

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
            'proof_url' => $hasProof ? route('eic.submissions.payment.proof', $sub->id) : null,
            'can_verify' => (bool) ($payment && $payment->isPendingVerification()),
        ];
    }
}
