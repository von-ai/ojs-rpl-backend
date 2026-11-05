<?php

declare(strict_types=1);

namespace App\Services\Eic;

use App\Jobs\SendReviewNotice;
use App\Models\Copyedit;
use App\Models\DeskReview;
use App\Models\EditorAssignment;
use App\Models\EditorialDecision;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\ReviewAssignment;
use App\Models\Submission;
use App\Models\User;
use App\Services\PortalAccess;
use App\Services\PrivateFileService;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EditorInChiefService
{
    /**
     * Melakukan evaluasi awal kelayakan naskah (Desk Review / UC-10).
     *
     * @param array{
     *     scope_fit: bool,
     *     files_complete: bool,
     *     guidelines_compliant: bool,
     *     similarity_score?: ?float,
     *     notes?: ?string,
     *     status: string,
     *     section_editor_id?: ?string
     * } $data
     */
    public function performDeskReview(
        Submission $submission,
        User $actor,
        array $data
    ): DeskReview {
        return DB::transaction(function () use ($submission, $actor, $data) {
            $submission = PortalAccess::lockSubmission($submission, $actor, 'editor_in_chief');

            if ($submission->status !== 'submitted') {
                throw new DomainException('Tahap skrining awal telah selesai dan tidak dapat diubah lagi karena naskah sudah berada pada tahapan berikutnya.');
            }

            $allowedStatuses = ['passed', 'rejected', 'revision_required'];
            if (! in_array($data['status'], $allowedStatuses, true)) {
                throw new DomainException("Status hasil desk review tidak valid: {$data['status']}");
            }

            return DB::transaction(function () use ($submission, $actor, $data): DeskReview {
                $deskReview = DeskReview::create([
                    'submission_id' => $submission->id,
                    'assessed_by' => $actor->id,
                    'scope_fit' => (bool) $data['scope_fit'],
                    'files_complete' => (bool) $data['files_complete'],
                    'guidelines_compliant' => (bool) $data['guidelines_compliant'],
                    'similarity_score' => isset($data['similarity_score']) ? (float) $data['similarity_score'] : null,
                    'notes' => $data['notes'] ?? null,
                    'status' => $data['status'],
                    'version' => 1,
                ]);

                // Update status naskah berdasarkan hasil penilaian awal
                if ($data['status'] === 'passed') {
                    $submission->status = 'in_review';

                    // Jika ada Section Editor yang ditugaskan langsung
                    if (! empty($data['section_editor_id'])) {
                        $sectionEditor = User::find($data['section_editor_id']);
                        if ($sectionEditor) {
                            $this->assignSectionEditor(
                                $submission,
                                $actor,
                                $sectionEditor,
                                'Penugasan Section Editor langsung saat lolos desk review.'
                            );
                        }
                    }
                } elseif ($data['status'] === 'rejected') {
                    $submission->status = 'declined';
                } elseif ($data['status'] === 'revision_required') {
                    $submission->status = 'revisions_requested';
                }

                $submission->version = (int) $submission->version + 1;
                $submission->save();

                return $deskReview;
            });
        });
    }

    /** @return Builder<User> */
    public function eligibleSectionEditors(Submission $submission): Builder
    {
        return User::query()
            ->where('status', 'active')
            ->whereNotNull('email_verified_at')
            ->when($submission->author_id, fn ($query) => $query->where('id', '!=', $submission->author_id))
            ->whereNotIn('id', $submission->contributors()->select('user_id')->whereNotNull('user_id'))
            ->whereNotIn('email', $submission->contributors()->select('email')->whereNotNull('email'))
            ->whereHas('userRoles', fn ($query) => $query
                ->where('role_code', 'section_editor')
                ->where('journal_id', $submission->journal_id));
    }

    /**
     * Menugaskan Section Editor untuk memimpin penelaahan naskah (UC-11).
     */
    public function assignSectionEditor(
        Submission $submission,
        User $eic,
        User $sectionEditor,
        ?string $note = null
    ): EditorAssignment {
        return DB::transaction(function () use ($submission, $eic, $sectionEditor, $note) {
            $submission = PortalAccess::lockSubmission($submission, $eic, 'editor_in_chief');
            if (! $this->eligibleSectionEditors($submission)->whereKey($sectionEditor->id)->exists()) {
                throw new DomainException('Editor Bagian harus aktif, terverifikasi, bebas konflik, dan memiliki peran pada jurnal ini.');
            }

            if (in_array($submission->status, ['accepted', 'in_production', 'published', 'declined', 'archived', 'withdrawn'], true)) {
                throw new DomainException('Penugasan Editor Bagian tidak dapat diubah karena naskah telah melewati tahap penelaahan.');
            }

            return DB::transaction(function () use ($submission, $sectionEditor, $note): EditorAssignment {
                // Cabut penugasan Section Editor aktif sebelumnya jika ada
                EditorAssignment::where('submission_id', $submission->id)
                    ->where('role', 'section_editor')
                    ->whereNull('revoked_at')
                    ->update(['revoked_at' => Carbon::now('UTC')]);

                // Terbitkan penugasan baru
                $assignment = EditorAssignment::create([
                    'submission_id' => $submission->id,
                    'editor_id' => $sectionEditor->id,
                    'role' => 'section_editor',
                    'assigned_at' => Carbon::now('UTC'),
                    'note' => $note ? trim($note) : null,
                ]);

                if ($submission->status === 'submitted') {
                    $submission->status = 'in_review';
                    $submission->version = (int) $submission->version + 1;
                    $submission->save();
                }

                return $assignment;
            });
        });
    }

    /**
     * Menerbitkan keputusan editorial resmi atas naskah (UC-12).
     *
     * @param array{
     *     decision: string,
     *     note_for_author: string,
     *     internal_note?: ?string,
     *     revision_due_at?: ?string
     * } $data
     */
    public function makeEditorialDecision(
        Submission $submission,
        User $eic,
        array $data
    ): EditorialDecision {
        return DB::transaction(function () use ($submission, $eic, $data) {
            $submission = PortalAccess::lockSubmission($submission, $eic, 'editor_in_chief');

            if (! in_array($submission->status, ['submitted', 'in_review', 'under_review'], true)) {
                throw new DomainException('Keputusan editorial akhir telah ditetapkan secara permanen dan tidak dapat diubah lagi.');
            }

            $allowedDecisions = ['accept', 'revisions_required', 'resubmit', 'decline'];
            if (! in_array($data['decision'], $allowedDecisions, true)) {
                throw new DomainException("Jenis keputusan editorial tidak valid: {$data['decision']}");
            }

            if ($data['decision'] === 'accept') {
                $round = $submission->currentReviewRound;
                if (! $round || $round->completedReviewCount() < $round->minimum_completed_reviews) {
                    throw new DomainException('Kuorum telaah selesai belum terpenuhi untuk menerima naskah.');
                }
            }

            // Aturan domain UC-12: Keputusan revisi wajib menyertakan batas waktu (due date)
            $isRevision = in_array($data['decision'], ['revisions_required', 'resubmit'], true);
            if ($isRevision && empty($data['revision_due_at'])) {
                throw new DomainException('Batas waktu revisi (due date) wajib ditentukan untuk keputusan permintaan revisi naskah.');
            }

            return DB::transaction(function () use ($submission, $eic, $data, $isRevision): EditorialDecision {
                $dueAt = $isRevision && ! empty($data['revision_due_at'])
                    ? Carbon::parse($data['revision_due_at'])
                    : null;

                $decision = EditorialDecision::create([
                    'submission_id' => $submission->id,
                    'review_round_id' => $submission->currentReviewRound?->id,
                    'decided_by' => $eic->id,
                    'decision' => $data['decision'],
                    'note_for_author' => trim($data['note_for_author']),
                    'internal_note' => isset($data['internal_note']) ? trim($data['internal_note']) : null,
                    'status' => 'sent',
                    'revision_due_at' => $dueAt,
                    'sent_at' => Carbon::now('UTC'),
                    'version' => 1,
                ]);

                // Transisi status naskah
                $submission->status = match ($data['decision']) {
                    'accept' => 'accepted',
                    'revisions_required', 'resubmit' => 'revisions_requested',
                    'decline' => 'declined',
                    default => 'in_review',
                };

                $submission->version = (int) $submission->version + 1;
                $submission->save();

                if ($data['decision'] === 'accept') {
                    $round = $submission->currentReviewRound;
                    $source = $round?->manuscriptFile;
                    if (! $source || $source->virus_scan_status !== 'clean' || ! $round->manuscript_checksum || ! hash_equals($round->manuscript_checksum, (string) $source->checksum_sha256)) {
                        throw new DomainException('Naskah hasil telaah belum terverifikasi untuk copyediting.');
                    }
                    app(PrivateFileService::class)->stream($source);
                    $copyeditor = User::where('status', 'active')->whereNotNull('email_verified_at')
                        ->where('id', '!=', $submission->author_id)
                        ->whereNotIn('id', DB::table('submission_contributors')->select('user_id')->where('submission_id', $submission->id)->whereNotNull('user_id'))
                        ->whereHas('userRoles', fn ($q) => $q->where('role_code', 'copyeditor')->where('journal_id', $submission->journal_id))->orderBy('id')->first();
                    if (! $copyeditor) {
                        throw new DomainException('Belum ada Copyeditor aktif dan terverifikasi pada jurnal ini.');
                    }
                    Copyedit::create(['submission_id' => $submission->id, 'copy_editor_id' => $copyeditor->id, 'source_file_id' => $source->id, 'status' => 'assigned', 'due_at' => now()->addDays(7), 'version' => 1]);
                    SendReviewNotice::dispatch($copyeditor->email, 'Penugasan copyediting SIRIDA', 'Naskah siap disunting. '.route('copyeditor.submissions.show', $submission->id));
                }

                $roundIds = $submission->reviewRounds()->where('status', 'active')->pluck('id');
                ReviewAssignment::whereIn('review_round_id', $roundIds)->whereIn('status', ['invited', 'accepted', 'overdue'])->update(['status' => 'cancelled', 'version' => DB::raw('version + 1')]);
                $submission->reviewRounds()->whereIn('id', $roundIds)->update(['status' => 'closed', 'closed_at' => now(), 'version' => DB::raw('version + 1')]);

                return $decision;
            });
        });
    }

    /**
     * Menerbitkan persetujuan akhir galai publikasi resmi oleh Pemimpin Redaksi (EIC) (UC-14 & UC-15).
     * Secara otomatis memindahkan status naskah ke 'ready_for_issue'.
     */
    public function approveFinalGalley(
        Submission $submission,
        User $eic,
        ?string $note = null
    ): GalleyApproval {
        return DB::transaction(function () use ($submission, $eic, $note) {
            $submission = PortalAccess::lockSubmission($submission, $eic, 'editor_in_chief');

            if (! in_array($submission->status, ['accepted', 'in_production', 'ready_for_issue'], true)) {
                throw new DomainException('Naskah belum memasuki tahap produksi yang dapat disetujui.');
            }

            // Cari galai aktif naskah (utamakan PDF)
            $activeGalley = $submission->activeGalley
                ?? Galley::where('submission_id', $submission->id)
                    ->where('status', 'active')
                    ->latest('version')
                    ->first();

            if (! $activeGalley) {
                throw new DomainException('Tidak dapat menerbitkan persetujuan akhir: naskah belum memiliki berkas galai aktif.');
            }

            if ($activeGalley->file?->virus_scan_status !== 'clean') {
                throw new DomainException('Galai belum lulus pemindaian keamanan.');
            }

            // Invariant Guard 1: Putaran proofreading wajib telah dilakukan
            $hasProof = ProofAssignment::where('galley_id', $activeGalley->id)->where('file_id', $activeGalley->file_id)->where('checksum_sha256', $activeGalley->checksum_sha256)->where('status', 'completed')->exists();

            if (! $hasProof) {
                throw new DomainException('Tidak dapat menerbitkan persetujuan akhir: galai naskah wajib melalui putaran proofreading terlebih dahulu.');
            }

            // Invariant Guard 2: Seluruh catatan koreksi proofreading wajib diselesaikan
            $unresolvedNotes = ProofreadingNote::where('galley_id', $activeGalley->id)
                ->where(fn ($q) => $q->where('resolved', false)->orWhere('verified', false))
                ->count();

            if ($unresolvedNotes > 0) {
                throw new DomainException("Tidak dapat menerbitkan persetujuan akhir: masih terdapat {$unresolvedNotes} catatan koreksi proofreading yang belum diselesaikan.");
            }

            // Invariant Guard 3: Tidak boleh ada tiket eskalasi aktif
            $hasActiveEscalation = ProductionEscalation::where('submission_id', $submission->id)
                ->whereIn('status', ['open', 'investigating'])
                ->exists();

            if ($hasActiveEscalation) {
                throw new DomainException('Tidak dapat menerbitkan persetujuan akhir: terdapat tiket eskalasi kendala tata letak yang belum diselesaikan.');
            }

            return DB::transaction(function () use ($submission, $activeGalley, $eic, $note): GalleyApproval {
                // Invalidate approval editor_final sebelumnya jika ada
                GalleyApproval::where('galley_id', $activeGalley->id)
                    ->where('stage', 'editor_final')
                    ->whereNull('invalidated_at')
                    ->update(['invalidated_at' => Carbon::now('UTC')]);

                // Kunci stage galai menjadi approved dan kunci approved_checksum
                $activeGalley->stage = 'approved';
                $activeGalley->approved_checksum = $activeGalley->checksum_sha256;
                $activeGalley->save();

                // Terbitkan rekod persetujuan akhir resmi (GalleyApproval)
                $approval = GalleyApproval::create([
                    'galley_id' => $activeGalley->id,
                    'approved_by' => $eic->id,
                    'stage' => 'editor_final',
                    'checksum_sha256' => $activeGalley->checksum_sha256,
                    'approved_at' => Carbon::now('UTC'),
                    'invalidated_at' => null,
                    'file_id' => $activeGalley->file_id,
                    'note' => $note ? trim($note) : 'Persetujuan akhir galai resmi disahkan oleh Pemimpin Redaksi. Naskah siap dijadwalkan ke edisi terbitan.',
                ]);

                // OTOMASI: Pemindahan status naskah ke ready_for_issue
                $submission->status = 'ready_for_issue';
                $submission->version = (int) $submission->version + 1;
                $submission->save();

                return $approval;
            });
        });
    }

    /**
     * Mengambil daftar naskah terstruktur untuk ruang kerja Editor-in-Chief.
     */
    public function getSubmissionsQueue(
        ?string $statusTab = 'all',
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = Submission::with([
            'section',
            'author',
            'activeEditorAssignment.editor',
            'deskReviews',
            'editorialDecisions',
            'currentReviewRound.assignments',
            'reviewRounds.assignments',
            'revisions',
        ])->where('status', '!=', 'draft');

        if ($statusTab === 'submitted') {
            $query->where('status', 'submitted');
        } elseif ($statusTab === 'in_review') {
            $query->where('status', 'in_review');
        } elseif ($statusTab === 'revisions') {
            $query->where('status', 'revisions_requested');
        } elseif ($statusTab === 'decided') {
            $query->whereIn('status', ['accepted', 'declined', 'in_production', 'ready_for_issue', 'published']);
        }

        if (! empty($search)) {
            $kw = trim($search);
            $query->where(function ($q) use ($kw): void {
                $q->where('title', 'like', "%{$kw}%")
                    ->orWhere('submission_number', 'like', "%{$kw}%")
                    ->orWhereHas('author', function ($aq) use ($kw): void {
                        $aq->where('full_name', 'like', "%{$kw}%")
                            ->orWhere('email', 'like', "%{$kw}%");
                    });
            });
        }

        return $query->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
