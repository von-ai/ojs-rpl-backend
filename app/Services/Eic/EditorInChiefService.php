<?php

declare(strict_types=1);

namespace App\Services\Eic;

use App\Models\DeskReview;
use App\Models\EditorAssignment;
use App\Models\EditorialDecision;
use App\Models\Submission;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        $allowedDecisions = ['accept', 'revisions_required', 'resubmit', 'decline'];
        if (! in_array($data['decision'], $allowedDecisions, true)) {
            throw new DomainException("Jenis keputusan editorial tidak valid: {$data['decision']}");
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
                'review_round_id' => null,
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

            return $decision;
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
        ]);

        if ($statusTab === 'submitted') {
            $query->where('status', 'submitted');
        } elseif ($statusTab === 'in_review') {
            $query->where('status', 'in_review');
        } elseif ($statusTab === 'revisions') {
            $query->where('status', 'revisions_requested');
        } elseif ($statusTab === 'decided') {
            $query->whereIn('status', ['accepted', 'declined', 'in_production', 'published']);
        }

        if (! empty($search)) {
            $kw = trim($search);
            $query->where(function ($q) use ($kw): void {
                $q->where('title', 'like', "%{$kw}%")
                    ->orWhere('submission_number', 'like', "%{$kw}%")
                    ->orWhereHas('author', function ($aq) use ($kw): void {
                        $aq->where('name', 'like', "%{$kw}%")
                            ->orWhere('email', 'like', "%{$kw}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
