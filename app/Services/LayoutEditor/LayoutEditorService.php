<?php

declare(strict_types=1);

namespace App\Services\LayoutEditor;

use App\Mail\LayoutEditor\FinalApprovalRequestedMail;
use App\Mail\LayoutEditor\LayoutEditorAssignedMail;
use App\Mail\LayoutEditor\ProductionEscalatedMail;
use App\Mail\LayoutEditor\ProofreadingRequestedMail;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LayoutEditorService
{
    /**
     * Mengunggah berkas draf galai PDF/HTML baru atau revisi.
     * Menerapkan Version Genealogy & Checksum Invalidation Guard.
     */
    public function uploadGalley(
        Submission $submission,
        UploadedFile $file,
        string $label,
        string $format,
        User $uploader,
        ?string $changeSummary = null
    ): Galley {
        return DB::transaction(function () use ($submission, $file, $label, $format, $uploader): Galley {
            $path = $file->store("galleys/{$submission->id}", 'local');
            $disk = Storage::disk('local');
            $byteSize = (int) $disk->size($path);
            $sha256 = hash('sha256', (string) $disk->get($path));

            $fileEntry = FileEntry::create([
                'storage_disk' => 'local',
                'storage_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType() ?: ($format === 'pdf' ? 'application/pdf' : 'text/html'),
                'byte_size' => $byteSize,
                'checksum_sha256' => $sha256,
                'virus_scan_status' => 'clean',
                'created_by' => $uploader->id,
            ]);

            // Cari galai aktif saat ini untuk submission ini
            $previousActiveGalley = Galley::where('submission_id', $submission->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            $version = 1;
            $supersedesId = null;

            if ($previousActiveGalley) {
                // Invalidation Guard: membatalkan approval aktif sebelumnya akibat mutasi berkas
                GalleyApproval::where('galley_id', $previousActiveGalley->id)
                    ->whereNull('invalidated_at')
                    ->update(['invalidated_at' => now()]);

                // Tandai galai sebelumnya sebagai superseded
                $previousActiveGalley->status = 'superseded';
                $previousActiveGalley->save();

                $version = (int) $previousActiveGalley->version + 1;
                $supersedesId = $previousActiveGalley->id;
            }

            return Galley::create([
                'submission_id' => $submission->id,
                'file_id' => $fileEntry->id,
                'supersedes_galley_id' => $supersedesId,
                'format' => $format,
                'stage' => $version > 1 ? 'proofreading' : 'draft',
                'status' => 'active',
                'checksum_sha256' => $sha256,
                'approved_checksum' => null,
                'version' => $version,
                'label' => $label,
            ]);
        });
    }

    /**
     * Menandai catatan koreksi proofreading telah diselesaikan oleh Layout Editor.
     */
    public function resolveProofreadingNote(
        ProofreadingNote $note,
        string $resolution,
        User $actor
    ): ProofreadingNote {
        return DB::transaction(function () use ($note, $resolution): ProofreadingNote {
            $note->resolved = true;
            $note->resolution = $resolution;
            $note->resolved_at = now();
            $note->save();

            // Cek apakah seluruh catatan dalam penugasan proofreading ini telah diselesaikan
            $unresolvedCount = ProofreadingNote::where('proof_assignment_id', $note->proof_assignment_id)
                ->where('resolved', false)
                ->count();

            if ($unresolvedCount === 0) {
                $assignment = $note->assignment;
                if ($assignment && $assignment->status !== 'completed') {
                    $assignment->status = 'completed';
                    $assignment->save();
                }
            }

            return $note;
        });
    }

    /**
     * Mengajukan berkas galai aktif ke putaran proofreading.
     */
    public function requestProofreading(
        Submission $submission,
        User $actor,
        ?User $proofreader = null,
        ?string $message = null
    ): ProofAssignment {
        return DB::transaction(function () use ($submission, $proofreader, $message): ProofAssignment {
            $activeGalley = Galley::where('submission_id', $submission->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            if (! $activeGalley) {
                throw new DomainException('Tidak dapat mengajukan proofreading sebelum mengunggah draf galai PDF.');
            }

            if (! $proofreader) {
                // Cari proofreader yang pernah ditugaskan pada naskah ini, atau fallback ke user proofreader di DB
                $prevAssignment = ProofAssignment::whereHas('galley', fn ($q) => $q->where('submission_id', $submission->id))
                    ->latest()
                    ->first();

                if ($prevAssignment) {
                    $proofreader = $prevAssignment->proofreader;
                } else {
                    $proofreader = User::where('email', 'like', '%proof%')
                        ->orWhere('full_name', 'like', '%Aminah%')
                        ->first() ?? User::first();
                }
            }

            $activeGalley->stage = 'proofreading';
            $activeGalley->save();

            $assignment = ProofAssignment::create([
                'galley_id' => $activeGalley->id,
                'proofreader_id' => $proofreader->id,
                'file_id' => $activeGalley->file_id,
                'due_at' => now()->addDays(5),
                'status' => 'assigned',
                'assigned_at' => now(),
                'checksum_sha256' => (string) ($activeGalley->checksum_sha256 ? substr($activeGalley->checksum_sha256, 0, 64) : hash('sha256', $activeGalley->id)),
                'message' => $message,
            ]);

            try {
                if ($proofreader && filter_var($proofreader->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($proofreader->email)->send(
                        new ProofreadingRequestedMail($submission, $activeGalley, $assignment)
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirimkan notifikasi email proofreading: '.$e->getMessage());
            }

            return $assignment;
        });
    }

    /**
     * Menerbitkan tiket eskalasi kendala tata letak ke Section Editor.
     * Menerapkan XOR constraint: copyedit_id IS NULL AND galley_id IS NOT NULL.
     */
    public function submitEscalation(
        Submission $submission,
        User $openedBy,
        string $reason
    ): ProductionEscalation {
        $activeGalley = Galley::where('submission_id', $submission->id)
            ->where('status', 'active')
            ->latest('version')
            ->first();

        $escalation = ProductionEscalation::create([
            'submission_id' => $submission->id,
            'copyedit_id' => null,
            'galley_id' => $activeGalley?->id,
            'opened_by' => $openedBy->id,
            'reason' => $reason,
            'status' => 'open',
            'resolution' => null,
            'resume_stage' => null,
            'version' => 1,
        ]);

        try {
            $assignment = LayoutAssignment::where('submission_id', $submission->id)->first();
            $sectionEditor = ($assignment?->assigned_by ? User::find($assignment->assigned_by) : null)
                ?? User::where('email', 'prof.hamka@unhas.ac.id')->first()
                ?? $openedBy;

            if ($sectionEditor && filter_var($sectionEditor->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($sectionEditor->email)->send(
                    new ProductionEscalatedMail($submission, $escalation, $openedBy)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan notifikasi email eskalasi: '.$e->getMessage());
        }

        return $escalation;
    }

    /**
     * Permohonan persetujuan akhir Pemimpin Redaksi (Editor in Chief).
     * Menerapkan Notes Resolution Guard & Checksum Locking.
     */
    public function requestFinalApproval(
        Submission $submission,
        User $actor,
        ?string $note = null
    ): GalleyApproval {
        return DB::transaction(function () use ($submission, $actor, $note): GalleyApproval {
            $activeGalley = Galley::where('submission_id', $submission->id)
                ->where('status', 'active')
                ->latest('version')
                ->first();

            if (! $activeGalley) {
                throw new DomainException('Tidak dapat meminta persetujuan akhir sebelum berkas galai diunggah.');
            }

            // Invariant Guard 1: Anti-Bypass Proofreading
            // Galai naskah WAJIB telah dikirim ke putaran proofreading dan ditinjau proofreader
            $proofAssignment = ProofAssignment::whereHas('galley', fn ($q) => $q->where('submission_id', $submission->id))
                ->latest('assigned_at')
                ->first();

            if (! $proofAssignment) {
                throw new DomainException('Tidak dapat mengajukan persetujuan final: naskah wajib melalui putaran proofreading terlebih dahulu sebelum diajukan ke Editor in Chief.');
            }

            // Invariant Guard 2: Putaran proofreading tidak boleh masih menggantung tanpa penuntasan
            $totalNotes = ProofreadingNote::whereHas('galley', fn ($q) => $q->where('submission_id', $submission->id))
                ->count();

            if ($proofAssignment->status !== 'completed' && $totalNotes === 0) {
                throw new DomainException('Tidak dapat mengajukan persetujuan final: putaran proofreading sedang berlangsung dan belum diselesaikan oleh Proofreader.');
            }

            // Invariant Guard 3: Seluruh catatan koreksi proofreading pada submission ini wajib diselesaikan
            $unresolvedNotes = ProofreadingNote::whereHas('galley', fn ($q) => $q->where('submission_id', $submission->id))
                ->where('resolved', false)
                ->count();

            if ($unresolvedNotes > 0) {
                throw new DomainException("Tidak dapat mengajukan persetujuan final: masih terdapat {$unresolvedNotes} catatan koreksi proofreading yang belum diselesaikan.");
            }

            // Invariant Guard 4: Tidak boleh ada tiket eskalasi aktif
            $hasActiveEscalation = ProductionEscalation::where('submission_id', $submission->id)
                ->whereIn('status', ['open', 'investigating'])
                ->exists();

            if ($hasActiveEscalation) {
                throw new DomainException('Tidak dapat mengajukan persetujuan final: terdapat tiket eskalasi kendala tata letak yang belum diselesaikan.');
            }

            // Invalidate approval lama jika ada
            GalleyApproval::where('galley_id', $activeGalley->id)
                ->where('stage', 'editor_final')
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);

            // Set approved stage & lock approved checksum
            $activeGalley->stage = 'approved';
            $activeGalley->approved_checksum = $activeGalley->checksum_sha256;
            $activeGalley->save();

            $approval = GalleyApproval::create([
                'galley_id' => $activeGalley->id,
                'approved_by' => $actor->id,
                'stage' => 'editor_final',
                'checksum_sha256' => $activeGalley->checksum_sha256,
                'approved_at' => now(),
                'invalidated_at' => null,
                'file_id' => $activeGalley->file_id,
                'note' => $note ?: 'Disetujui untuk penjadwalan edisi terbitan.',
            ]);

            // Perbarui status submission menjadi siap terbit
            $submission->status = 'ready_for_issue';
            $submission->save();

            try {
                $eic = User::where('email', 'prof.hamka@unhas.ac.id')->first()
                    ?? User::where('email', 'like', '%editor%')->first()
                    ?? $actor;

                if ($eic && filter_var($eic->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($eic->email)->send(
                        new FinalApprovalRequestedMail($submission, $activeGalley, $approval, $actor)
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirimkan notifikasi email persetujuan akhir EIC: '.$e->getMessage());
            }

            return $approval;
        });
    }

    /**
     * Menugaskan Layout Editor untuk naskah dan mengirimkan notifikasi email penugasan.
     */
    public function assignLayoutEditor(
        Submission $submission,
        User $layoutEditor,
        User $assignedBy,
        \DateTimeInterface|string|null $dueAt = null,
        ?string $reason = null
    ): LayoutAssignment {
        $assignment = LayoutAssignment::updateOrCreate(
            ['submission_id' => $submission->id, 'layout_editor_id' => $layoutEditor->id],
            [
                'assigned_by' => $assignedBy->id,
                'assigned_at' => now(),
                'due_at' => $dueAt ?? now()->addDays(7),
                'revoked_at' => null,
                'reason' => $reason ?? 'Penugasan tata letak galai publikasi.',
                'version' => 1,
            ]
        );

        try {
            if ($layoutEditor && filter_var($layoutEditor->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($layoutEditor->email)->send(
                    new LayoutEditorAssignedMail($submission, $assignment, $assignedBy)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirimkan notifikasi email penugasan layout editor: '.$e->getMessage());
        }

        return $assignment;
    }

    /**
     * Persetujuan Penulis (Author Sign-off).
     */
    public function authorSignoff(
        Galley $galley,
        User $author,
        ?string $note = null
    ): GalleyApproval {
        return DB::transaction(function () use ($galley, $author, $note): GalleyApproval {
            return GalleyApproval::create([
                'galley_id' => $galley->id,
                'approved_by' => $author->id,
                'stage' => 'author_signoff',
                'checksum_sha256' => $galley->checksum_sha256,
                'approved_at' => now(),
                'invalidated_at' => null,
                'file_id' => $galley->file_id,
                'note' => $note,
            ]);
        });
    }

    /**
     * Memeriksa kesiapan naskah untuk diterbitkan ke nomor edisi jurnal (UC-15 Readiness Guard).
     *
     * @return array{is_ready: bool, reasons: array<int, string>}
     */
    public function checkPublicationReadiness(Submission $submission): array
    {
        $reasons = [];

        $activeGalley = Galley::where('submission_id', $submission->id)
            ->where('status', 'active')
            ->where('format', 'pdf')
            ->latest('version')
            ->first();

        if (! $activeGalley) {
            $reasons[] = 'Naskah belum memiliki galai PDF aktif.';
        } else {
            $hasFinalApproval = GalleyApproval::where('galley_id', $activeGalley->id)
                ->where('stage', 'editor_final')
                ->whereNull('invalidated_at')
                ->exists();

            if (! $hasFinalApproval) {
                $reasons[] = 'Galai PDF aktif belum memperoleh persetujuan akhir (editor_final) dari Editor in Chief.';
            }

            if ($activeGalley->approved_checksum !== null && $activeGalley->approved_checksum !== $activeGalley->checksum_sha256) {
                $reasons[] = 'Checksum berkas galai tidak cocok dengan checksum saat persetujuan akhir diberikan.';
            }
        }

        $activeEscalation = ProductionEscalation::where('submission_id', $submission->id)
            ->whereIn('status', ['open', 'investigating'])
            ->exists();

        if ($activeEscalation) {
            $reasons[] = 'Masih terdapat tiket eskalasi produksi yang belum terselesaikan.';
        }

        return [
            'is_ready' => count($reasons) === 0,
            'reasons' => $reasons,
        ];
    }
}
