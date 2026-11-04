<?php

declare(strict_types=1);

namespace App\Services\SectionEditor;

use App\Jobs\SendReviewNotice;
use App\Models\EditorialDecision;
use App\Models\FileEntry;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewReminder;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use App\Services\PortalAccess;
use App\Services\Reviewer\ReviewerService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SectionEditorService
{
    /**
     * Mendapatkan daftar naskah yang ditugaskan kepada Section Editor.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getAssignedSubmissions(User $editor, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Submission::query()
            ->whereHas('editorAssignments', function ($q) use ($editor) {
                $q->where('editor_id', $editor->id)
                    ->whereNull('revoked_at');
            })
            ->with([
                'author',
                'section',
                'currentReviewRound.assignments.reviewer',
                'currentReviewRound.assignments.review',
                'activeEditorAssignment.editor',
            ])
            ->latest('updated_at');

        // Filter status
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $status = $filters['status'];
            if ($status === 'in_review') {
                $query->whereIn('status', ['in_review', 'under_review']);
            } elseif ($status === 'reviewed') {
                $query->whereHas('currentReviewRound', function ($q) {
                    $q->whereHas('assignments', function ($qa) {
                        $qa->where('status', 'completed');
                    });
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Pencarian teks judul / nomor pengajuan / nama penulis
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('submission_number', 'like', "%{$search}%")
                    ->orWhereHas('author', function ($qa) use ($search) {
                        $qa->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Memastikan adanya putaran review (Review Round) aktif untuk naskah ini.
     */
    public function ensureActiveReviewRound(Submission $submission, ?string $note = null, ?FileEntry $anonymousFile = null, ?string $reviewerTitle = null, ?string $reviewerAbstract = null): ReviewRound
    {
        return DB::transaction(function () use ($submission, $note, $anonymousFile, $reviewerTitle, $reviewerAbstract) {
            $submission = PortalAccess::lockSubmission($submission, auth()->user(), 'section_editor');
            if (! in_array($submission->status, ['submitted', 'in_review', 'under_review', 'revisions_requested'], true)) {
                throw new InvalidArgumentException('Status naskah tidak mengizinkan putaran telaah baru.');
            }
            $active = $submission->reviewRounds()->where('status', 'active')->first();
            if (! $anonymousFile) {
                if ($active) {
                    return $active;
                }
                throw new InvalidArgumentException('Siapkan berkas, judul, dan abstrak anonim sebelum membuka putaran telaah.');
            }
            $replaceUnsafeRound = $active && ! $active->hasDistinctAnonymizedFile();
            if ($active && ! $replaceUnsafeRound && $active->activeAssignments()->exists()) {
                throw new InvalidArgumentException('Selesaikan penugasan pada putaran aktif sebelum membuka putaran baru.');
            }
            $primary = $submission->files()->with('file')->whereIn('genre', ['manuscript', 'revision'])->latest('created_at')->first()?->file;
            if (! $primary || ! $reviewerTitle || ! $reviewerAbstract) {
                throw new InvalidArgumentException('Naskah acuan, judul anonim, dan abstrak anonim wajib tersedia.');
            }
            $round = new ReviewRound([
                'submission_id' => $submission->id,
                'round' => (int) $submission->reviewRounds()->max('round') + 1,
                'manuscript_file_id' => $primary->id,
                'manuscript_checksum' => $primary->checksum_sha256,
                'anonymized_file_id' => $anonymousFile->id,
                'anonymized_checksum' => $anonymousFile->checksum_sha256,
                'reviewer_title' => $reviewerTitle,
                'reviewer_abstract' => $reviewerAbstract,
                'rubric_snapshot' => app(ReviewerService::class)->getDefaultRubricCriteria(),
                'minimum_completed_reviews' => 2,
                'status' => 'active', 'version' => 1, 'review_type' => 'double_blind', 'note' => $note,
            ]);
            $round->setRelation('manuscriptFile', $primary);
            $round->setRelation('anonymizedFile', $anonymousFile);
            if (! $round->hasDistinctAnonymizedFile()) {
                throw new InvalidArgumentException('Berkas anonim harus berbeda isinya dari naskah acuan.');
            }
            // Editorial confirmation plus a conservative identity check prevents copying raw metadata blindly.
            foreach ($submission->contributors as $contributor) {
                foreach ([$contributor->full_name, $contributor->email] as $identity) {
                    if ($identity && mb_strlen($identity) > 3 && (mb_stripos($reviewerTitle, $identity) !== false || mb_stripos($reviewerAbstract, $identity) !== false)) {
                        throw new InvalidArgumentException('Identitas kontributor masih tercantum dalam proyeksi anonim.');
                    }
                }
            }
            if ($active) {
                if ($replaceUnsafeRound) {
                    $active->activeAssignments()->update(['status' => 'cancelled', 'version' => DB::raw('version + 1')]);
                }
                $active->update(['status' => 'closed', 'closed_at' => now(), 'version' => $active->version + 1]);
            }

            $round->save();

            return $round;
        });
    }

    /**
     * Mengundang / menugaskan reviewer ke putaran review aktif (UC-11).
     *
     * @param  array{response_due_at: string|Carbon, review_due_at: string|Carbon, invitation_message?: ?string}  $data
     */
    public function assignReviewer(ReviewRound $round, User $reviewer, array $data, User $actor): ReviewAssignment
    {
        return DB::transaction(function () use ($round, $reviewer, $data, $actor) {
            PortalAccess::lockSubmission($round->submission, $actor, 'section_editor');
            $round = ReviewRound::query()->lockForUpdate()->findOrFail($round->id);
            if ($round->status !== 'active') {
                throw new InvalidArgumentException('Putaran telaah sudah ditutup.');
            }

            if (! $round->hasDistinctAnonymizedFile()) {
                throw new InvalidArgumentException('Siapkan putaran dengan berkas anonim yang berbeda dari naskah acuan.');
            }
            if ($round->anonymizedFile->virus_scan_status !== 'clean') {
                throw new InvalidArgumentException('Berkas anonim terpisah harus selesai dipindai sebelum reviewer diundang.');
            }

            // 1. Guard: Putaran review harus berstatus aktif
            if ($round->status !== 'active') {
                throw new InvalidArgumentException('Tidak dapat menugaskan reviewer pada putaran review yang telah ditutup atau dibatalkan.');
            }

            // 2. Guard: Pencegahan konflik kepentingan / self-review (Penulis tidak boleh menelaah naskahnya sendiri)
            $authorId = $round->submission->author_id;
            if ($reviewer->id === $authorId || $round->submission->contributors()->where(fn ($q) => $q->where('user_id', $reviewer->id)->orWhere('email', $reviewer->email))->exists()) {
                throw new InvalidArgumentException('Penulis naskah tidak dapat ditugaskan sebagai mitra bestari (konflik kepentingan/self-review).');
            }

            if (! $this->getAvailableReviewers($round->submission)->contains('id', $reviewer->id)) {
                throw new InvalidArgumentException('Reviewer harus aktif, terverifikasi, dan terdaftar pada jurnal ini.');
            }

            // 3. Guard: Pencegahan penugasan ganda aktif di putaran yang sama (live_slot check)
            $existingActive = $round->assignments()
                ->where('reviewer_id', $reviewer->id)
                ->whereIn('status', ['invited', 'accepted', 'overdue'])
                ->exists();

            if ($existingActive) {
                throw new InvalidArgumentException('Mitra bestari ini sudah memiliki penugasan aktif pada putaran telaah ini.');
            }

            // 4. Validasi tanggal deadline
            $responseDue = Carbon::parse($data['response_due_at'])->utc();
            $reviewDue = Carbon::parse($data['review_due_at'])->utc();

            if ($responseDue->lessThanOrEqualTo(now()) || $reviewDue->lessThanOrEqualTo($responseDue)) {
                throw new InvalidArgumentException('Batas waktu telaah (review due date) harus setelah batas waktu respon (response due date).');
            }

            return DB::transaction(function () use ($round, $reviewer, $responseDue, $reviewDue, $data): ReviewAssignment {
                $assignment = ReviewAssignment::create([
                    'review_round_id' => $round->id,
                    'reviewer_id' => $reviewer->id,
                    'status' => 'invited',
                    'response_due_at' => $responseDue,
                    'review_due_at' => $reviewDue,
                    'deadline_version' => 1,
                    'version' => 1,
                    'invitation_message' => $data['invitation_message'] ?? null,
                ]);

                SendReviewNotice::dispatch($reviewer->email, 'Undangan telaah naskah SIRIDA',
                    'Anda diundang menelaah naskah anonim: '.$round->reviewer_title."\n".route('reviewer.assignments.show', $assignment->id));

                // Mutasi status naskah menjadi under_review
                $submission = $round->submission;
                if ($submission->status !== 'under_review') {
                    $submission->update(['status' => 'under_review']);
                }

                return $assignment;
            });
        });
    }

    /**
     * Membatalkan undangan reviewer (UC-11: cancellation hanya untuk status 'invited').
     */
    public function cancelInvitation(ReviewAssignment $assignment, User $actor, ?string $reason = null): ReviewAssignment
    {
        return DB::transaction(function () use ($assignment, $actor) {
            $assignment = ReviewAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            PortalAccess::lockSubmission($assignment->reviewRound->submission, $actor, 'section_editor');

            if ($assignment->status !== 'invited') {
                throw new InvalidArgumentException('Pembatalan penugasan hanya diizinkan untuk undangan yang masih berstatus invited.');
            }

            return DB::transaction(function () use ($assignment): ReviewAssignment {
                $assignment->update([
                    'status' => 'cancelled',
                    'version' => $assignment->version + 1,
                ]);

                return $assignment;
            });
        });
    }

    /**
     * Mengambil keputusan atas permohonan perpanjangan waktu telaah reviewer (UC-08 & Mesin Waktu).
     */
    public function decideExtension(ReviewExtension $extension, string $decision, ?string $reason, User $editor): ReviewExtension
    {
        return DB::transaction(function () use ($extension, $decision, $reason, $editor) {
            $assignment = ReviewAssignment::findOrFail($extension->assignment_id);
            PortalAccess::lockSubmission($assignment->reviewRound->submission, $editor, 'section_editor');
            $assignment = ReviewAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            $extension = ReviewExtension::query()->lockForUpdate()->findOrFail($extension->id);
            if ($assignment->reviewRound->status !== 'active' || ! in_array($assignment->status, ['accepted', 'overdue'], true)) {
                throw new InvalidArgumentException('Penugasan telaah sudah ditutup.');
            }
            if ($decision === 'approved' && ($extension->requested_due_at->lte(now()) || $extension->requested_due_at->lte($assignment->review_due_at))) {
                throw new InvalidArgumentException('Tanggal perpanjangan sudah tidak berlaku.');
            }

            if ($extension->status !== 'pending') {
                throw new InvalidArgumentException('Permohonan perpanjangan waktu ini sudah pernah diputuskan sebelumnya.');
            }

            if (! in_array($decision, ['approved', 'rejected'], true)) {
                throw new InvalidArgumentException('Keputusan perpanjangan waktu harus approved atau rejected.');
            }

            return DB::transaction(function () use ($extension, $decision, $reason, $editor): ReviewExtension {
                $assignment = $extension->assignment;

                $extension->update([
                    'status' => $decision,
                    'decided_by' => $editor->id,
                    'decision_reason' => $reason,
                    'decided_at' => Carbon::now('UTC'),
                    'version' => $extension->version + 1,
                ]);

                if ($decision === 'approved') {
                    // Perpanjang deadline dan pulihkan status jika sebelumnya overdue
                    $newStatus = $assignment->status === 'overdue' ? 'accepted' : $assignment->status;

                    $assignment->update([
                        'review_due_at' => $extension->requested_due_at,
                        'deadline_version' => $assignment->deadline_version + 1,
                        'status' => $newStatus,
                        'version' => $assignment->version + 1,
                    ]);
                }

                return $extension;
            });
        });
    }

    /**
     * Mengirimkan surat pengingat (reminder) kepada reviewer.
     */
    public function sendReminder(ReviewAssignment $assignment, User $editor, ?string $message = null): ReviewReminder
    {
        return DB::transaction(function () use ($assignment, $editor, $message) {
            $assignment = ReviewAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            PortalAccess::lockSubmission($assignment->reviewRound->submission, $editor, 'section_editor');

            $reminder = ReviewReminder::create([
                'assignment_id' => $assignment->id,
                'sent_by' => $editor->id,
                'queued_at' => Carbon::now('UTC'),
                'delivery_status' => 'queued',
                'message' => $message ?? 'Pengingat batas waktu penelaahan naskah ilmiah.',
            ]);
            SendReviewNotice::dispatch($assignment->reviewer->email, 'Pengingat telaah SIRIDA', $reminder->message."\n".route('reviewer.assignments.show', $assignment->id), $reminder->id);

            return $reminder;
        });
    }

    /**
     * Mengirimkan rekomendasi editorial resmi Section Editor ke Pemimpin Redaksi (EIC).
     */
    public function submitRecommendation(ReviewRound $round, string $recommendation, string $note, User $editor): ReviewRound
    {
        return DB::transaction(function () use ($round, $recommendation, $note, $editor) {
            PortalAccess::lockSubmission($round->submission, $editor, 'section_editor');
            $round = ReviewRound::query()->lockForUpdate()->findOrFail($round->id);
            if ($round->status !== 'active') {
                throw new InvalidArgumentException('Putaran telaah sudah ditutup.');
            }

            if ($round->completedReviewCount() < $round->minimum_completed_reviews) {
                throw new InvalidArgumentException('Jumlah telaah selesai belum memenuhi kuorum.');
            }
            $allowed = ['accept', 'revisions_required', 'resubmit', 'decline'];
            if (! in_array($recommendation, $allowed, true)) {
                throw new InvalidArgumentException('Rekomendasi editorial tidak valid.');
            }

            return DB::transaction(function () use ($round, $recommendation, $note, $editor): ReviewRound {
                $round->update([
                    'se_recommendation' => $recommendation,
                    'se_recommendation_note' => $note,
                    'se_recommended_at' => Carbon::now('UTC'),
                ]);

                // Simpan draf keputusan editorial agar EIC dapat langsung menelaah dan menyetujui
                EditorialDecision::updateOrCreate(
                    [
                        'submission_id' => $round->submission_id,
                        'review_round_id' => $round->id,
                        'status' => 'draft',
                    ],
                    [
                        'decided_by' => $editor->id,
                        'decision' => $recommendation,
                        'note_for_author' => $note,
                        'internal_note' => "Rekomendasi dari Section Editor {$editor->name}: {$note}",
                        'version' => 1,
                    ]
                );

                return $round;
            });
        });
    }

    /**
     * Mengambil daftar calon reviewer yang memenuhi syarat untuk naskah ini.
     *
     * @return Collection<int, User>
     */
    public function getAvailableReviewers(Submission $submission)
    {
        return User::query()
            ->where('id', '!=', $submission->author_id)
            ->where('status', 'active')
            ->whereNotNull('email_verified_at')
            ->whereNotIn('id', DB::table('submission_contributors')->select('user_id')->where('submission_id', $submission->id)->whereNotNull('user_id'))
            ->whereNotIn('email', DB::table('submission_contributors')->select('email')->where('submission_id', $submission->id)->whereNotNull('email'))
            ->whereHas('userRoles', fn ($q) => $q->where('role_code', 'reviewer')->where('journal_id', $submission->journal_id))
            ->orderBy('full_name')
            ->get();
    }
}
