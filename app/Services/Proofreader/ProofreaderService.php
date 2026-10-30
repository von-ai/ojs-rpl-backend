<?php

declare(strict_types=1);

namespace App\Services\Proofreader;

use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ProofreaderService
{
    /**
     * Menambahkan catatan koreksi tipografi/layout baru pada galai aktif.
     * Menerapkan Checksum & File Binding Guard (UC-18).
     *
     * @param array{
     *     category: string,
     *     page?: int|null,
     *     severity: string,
     *     description: string
     * } $data
     */
    public function addNote(
        Submission $submission,
        User $actor,
        array $data
    ): ProofreadingNote {
        return DB::transaction(function () use ($submission, $actor, $data): ProofreadingNote {
            $activeGalley = Galley::where('submission_id', $submission->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            if (! $activeGalley) {
                throw new DomainException('Tidak ditemukan berkas galai aktif untuk naskah ini.');
            }

            // Ambil penugasan proofreading aktif atau buat penugasan jika belum ada
            $assignment = ProofAssignment::where('galley_id', $activeGalley->id)
                ->latest()
                ->first();

            if (! $assignment) {
                $assignment = ProofAssignment::create([
                    'galley_id' => $activeGalley->id,
                    'proofreader_id' => $actor->id,
                    'file_id' => $activeGalley->file_id,
                    'due_at' => now()->addDays(5),
                    'status' => 'in_progress',
                    'assigned_at' => now(),
                    'checksum_sha256' => (string) ($activeGalley->checksum_sha256 ? substr($activeGalley->checksum_sha256, 0, 64) : hash('sha256', $activeGalley->id)),
                    'message' => 'Penugasan bukti baca aktif',
                ]);
            } elseif ($assignment->status === 'assigned') {
                $assignment->status = 'in_progress';
                $assignment->save();
            }

            $validCategories = ['typo', 'layout', 'metadata', 'reference'];
            $category = in_array($data['category'], $validCategories, true) ? $data['category'] : 'typo';

            $validSeverities = ['minor', 'major', 'critical'];
            $severity = in_array($data['severity'], $validSeverities, true) ? $data['severity'] : 'minor';

            $page = isset($data['page']) && (int) $data['page'] >= 1 ? (int) $data['page'] : null;

            return ProofreadingNote::create([
                'proof_assignment_id' => $assignment->id,
                'galley_id' => $activeGalley->id,
                'file_id' => $activeGalley->file_id,
                'checksum_sha256' => $activeGalley->checksum_sha256,
                'category' => $category,
                'page' => $page,
                'severity' => $severity,
                'description' => trim($data['description']),
                'resolved' => false,
                'resolution' => null,
                'resolved_at' => null,
                'version' => 1,
            ]);
        });
    }

    /**
     * Memverifikasi resolusi perbaikan catatan koreksi dari Layout Editor.
     */
    public function verifyNote(
        ProofreadingNote $note,
        User $actor
    ): ProofreadingNote {
        return DB::transaction(function () use ($note): ProofreadingNote {
            $note->resolved = true;
            if (! $note->resolved_at) {
                $note->resolved_at = now();
            }
            if (! $note->resolution) {
                $note->resolution = 'Telah diverifikasi dan disetujui oleh Proofreader.';
            }
            $note->save();

            return $note;
        });
    }

    /**
     * Memberikan persetujuan akhir (Sign-off) oleh Proofreader.
     * Menerapkan Sign-off Guard: DILARANG selesai jika ada catatan kritis/mayor yang belum resolved.
     */
    public function completeProofread(
        Submission $submission,
        User $actor,
        ?string $note = null
    ): ProofAssignment {
        return DB::transaction(function () use ($submission, $actor, $note): ProofAssignment {
            $activeGalley = Galley::where('submission_id', $submission->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            if (! $activeGalley) {
                throw new DomainException('Tidak ditemukan berkas galai aktif untuk naskah ini.');
            }

            $assignment = ProofAssignment::where('galley_id', $activeGalley->id)
                ->latest()
                ->first();

            if (! $assignment) {
                throw new DomainException('Tidak ditemukan penugasan proofreading aktif untuk galai ini.');
            }

            // Sign-off Guard: Periksa apakah masih ada catatan kritis atau mayor yang belum diselesaikan
            $blockingNotes = ProofreadingNote::where('proof_assignment_id', $assignment->id)
                ->where('resolved', false)
                ->whereIn('severity', ['critical', 'major'])
                ->count();

            if ($blockingNotes > 0) {
                throw new DomainException(
                    "Tidak dapat menyelesaikan proofreading: terdapat {$blockingNotes} catatan koreksi berstatus kritis/mayor yang belum diselesaikan."
                );
            }

            $assignment->status = 'completed';
            $assignment->save();

            // Catat persetujuan formal galai oleh Proofreader
            GalleyApproval::create([
                'galley_id' => $activeGalley->id,
                'approved_by' => $actor->id,
                'stage' => 'proofreader_signoff',
                'checksum_sha256' => $activeGalley->checksum_sha256,
                'approved_at' => now(),
                'file_id' => $activeGalley->file_id,
                'note' => $note ?: 'Disetujui oleh Proofreader (Sign-off lengkap).',
            ]);

            return $assignment;
        });
    }

    /**
     * Mengajukan eskalasi ketidaksesuaian naskah kepada Editor in Chief.
     * Menerapkan constraint XOR: galley_id IS NOT NULL AND copyedit_id IS NULL.
     */
    public function submitEscalation(
        Submission $submission,
        User $actor,
        string $reason
    ): ProductionEscalation {
        return DB::transaction(function () use ($submission, $actor, $reason): ProductionEscalation {
            $activeGalley = Galley::where('submission_id', $submission->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            if (! $activeGalley) {
                throw new DomainException('Tidak ditemukan galai aktif untuk diajukan eskalasi.');
            }

            return ProductionEscalation::create([
                'submission_id' => $submission->id,
                'galley_id' => $activeGalley->id,
                'copyedit_id' => null,
                'status' => 'open',
                'opened_by' => $actor->id,
                'reason' => trim($reason),
            ]);
        });
    }
}
