<?php

declare(strict_types=1);

namespace App\Services\SectionEditor;

use App\Models\EditorialDecision;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewReminder;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
                        $qa->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Memastikan adanya putaran review (Review Round) aktif untuk naskah ini.
     */
    public function ensureActiveReviewRound(Submission $submission, ?string $note = null): ReviewRound
    {
        $activeRound = $submission->reviewRounds()
            ->where('status', 'active')
            ->latest('round')
            ->first();

        if ($activeRound) {
            return $activeRound;
        }

        $nextRoundNumber = ((int) $submission->reviewRounds()->max('round')) + 1;

        return DB::transaction(function () use ($submission, $nextRoundNumber, $note): ReviewRound {
            $primaryFile = $submission->files()->first();

            $round = ReviewRound::create([
                'submission_id' => $submission->id,
                'round' => $nextRoundNumber,
                'manuscript_file_id' => $primaryFile?->file_id,
                'anonymized_file_id' => $primaryFile?->file_id,
                'minimum_completed_reviews' => 2,
                'status' => 'active',
                'version' => 1,
                'review_type' => 'double_blind',
                'note' => $note,
                'reviewer_title' => $submission->title,
                'reviewer_abstract' => $submission->abstract,
            ]);

            if ($submission->status === 'submitted') {
                $submission->update(['status' => 'in_review']);
            }

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
        // 1. Guard: Putaran review harus berstatus aktif
        if ($round->status !== 'active') {
            throw new InvalidArgumentException('Tidak dapat menugaskan reviewer pada putaran review yang telah ditutup atau dibatalkan.');
        }

        // 2. Guard: Pencegahan konflik kepentingan / self-review (Penulis tidak boleh menelaah naskahnya sendiri)
        $authorId = $round->submission->author_id;
        if ($reviewer->id === $authorId) {
            throw new InvalidArgumentException('Penulis naskah tidak dapat ditugaskan sebagai mitra bestari (konflik kepentingan/self-review).');
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

        if ($reviewDue->lessThanOrEqualTo($responseDue)) {
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

            // Mutasi status naskah menjadi under_review
            $submission = $round->submission;
            if ($submission->status !== 'under_review') {
                $submission->update(['status' => 'under_review']);
            }

            return $assignment;
        });
    }

    /**
     * Membatalkan undangan reviewer (UC-11: cancellation hanya untuk status 'invited').
     */
    public function cancelInvitation(ReviewAssignment $assignment, User $actor, ?string $reason = null): ReviewAssignment
    {
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
    }

    /**
     * Mengambil keputusan atas permohonan perpanjangan waktu telaah reviewer (UC-08 & Mesin Waktu).
     */
    public function decideExtension(ReviewExtension $extension, string $decision, ?string $reason, User $editor): ReviewExtension
    {
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
    }

    /**
     * Mengirimkan surat pengingat (reminder) kepada reviewer.
     */
    public function sendReminder(ReviewAssignment $assignment, User $editor, ?string $message = null): ReviewReminder
    {
        return ReviewReminder::create([
            'assignment_id' => $assignment->id,
            'sent_by' => $editor->id,
            'queued_at' => Carbon::now('UTC'),
            'delivery_status' => 'sent',
            'message' => $message ?? 'Pengingat batas waktu penelaahan naskah ilmiah.',
        ]);
    }

    /**
     * Mengirimkan rekomendasi editorial resmi Section Editor ke Pemimpin Redaksi (EIC).
     */
    public function submitRecommendation(ReviewRound $round, string $recommendation, string $note, User $editor): ReviewRound
    {
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
            ->where(function ($q) {
                if (Schema::hasTable('user_roles')) {
                    $q->whereHas('userRoles', function ($qr) {
                        $qr->where('role_code', 'reviewer');
                    })->orWhereDoesntHave('userRoles');
                }
            })
            ->orderBy('full_name')
            ->get();
    }
}
