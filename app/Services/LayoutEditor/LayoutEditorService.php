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
use App\Services\PortalAccess;
use App\Services\PrivateFileService;
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
        return DB::transaction(function () use ($submission, $file, $label, $format, $uploader) {
            $submission = PortalAccess::lockSubmission($submission, $uploader, 'layout_editor');

            if (in_array($submission->status, ['published', 'withdrawn', 'declined', 'archived'], true)) {
                throw new DomainException('Galai naskah final tidak dapat diubah melalui alur produksi biasa.');
            }
            if ($submission->status === 'ready_for_issue') {
                $submission->update(['status' => 'in_production']);
            }

            return DB::transaction(function () use ($submission, $file, $label, $format, $uploader): Galley {
                $storageDisk = (string) config('filesecurity.disk', 'azure');
                $path = $file->store("galleys/{$submission->id}", $storageDisk);
                $disk = Storage::disk($storageDisk);
                $byteSize = (int) $disk->size($path);
                $sha256 = hash('sha256', (string) $disk->get($path));

                $fileEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: ($format === 'pdf' ? 'application/pdf' : 'text/html'),
                    'byte_size' => $byteSize,
                    'checksum_sha256' => $sha256,
                    'virus_scan_status' => 'pending',
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
                    'stage' => 'draft',
                    'status' => 'active',
                    'checksum_sha256' => $sha256,
                    'approved_checksum' => null,
                    'version' => $version,
                    'label' => $label,
                ]);
            });
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
        return DB::transaction(function () use ($note, $resolution, $actor) {
            PortalAccess::lockSubmission($note->galley->submission, $actor, 'layout_editor');
            $note = ProofreadingNote::query()->lockForUpdate()->findOrFail($note->id);
            if ($note->galley->status !== 'active' || $note->galley->stage === 'approved') {
                throw new DomainException('Catatan versi historis atau galai final tidak dapat diubah.');
            }

            return DB::transaction(function () use ($note, $resolution): ProofreadingNote {
                $note->resolved = true;
                $note->resolution = $resolution;
                $note->resolved_at = now();
                $note->save();

                $note->update(['verified' => false, 'verified_at' => null, 'verified_by' => null]);

                return $note;
            });
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
        return DB::transaction(function () use ($submission, $actor, $proofreader, $message) {
            $submission = PortalAccess::lockSubmission($submission, $actor, 'layout_editor');

            return DB::transaction(function () use ($submission, $proofreader, $message): ProofAssignment {
                $activeGalley = Galley::where('submission_id', $submission->id)
                    ->where('status', 'active')
                    ->latest('version')
                    ->first();

                if (! $activeGalley) {
                    throw new DomainException('Tidak dapat mengajukan proofreading sebelum mengunggah draf galai PDF.');
                }

                if (! $proofreader) {
                    $proofreader = User::where('status', 'active')->whereNotNull('email_verified_at')->whereHas('userRoles', fn ($q) => $q->where('role_code', 'proofreader')->where('journal_id', $submission->journal_id))->orderBy('id')->first();
                }
                if (! $proofreader || ! $proofreader->userRoles()->where('journal_id', $submission->journal_id)->where('role_code', 'proofreader')->exists() || $proofreader->status !== 'active' || ! $proofreader->email_verified_at) {
                    throw new DomainException('Belum ada Proofreader aktif dan terverifikasi pada jurnal ini.');
                }
                if ($activeGalley->file?->virus_scan_status !== 'clean') {
                    throw new DomainException('Galai belum lulus pemindaian keamanan.');
                }

                $existing = ProofAssignment::where('galley_id', $activeGalley->id)->where('proofreader_id', $proofreader->id)->first();
                if ($existing) {
                    return $existing;
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

                if ($activeGalley->supersedes_galley_id) {
                    $carry = ProofreadingNote::where('galley_id', $activeGalley->supersedes_galley_id)->where(fn ($q) => $q->where('resolved', false)->orWhere('verified', false))->get();
                    foreach ($carry as $oldNote) {
                        ProofreadingNote::create(['proof_assignment_id' => $assignment->id, 'galley_id' => $activeGalley->id, 'file_id' => $activeGalley->file_id, 'checksum_sha256' => $activeGalley->checksum_sha256, 'category' => $oldNote->category, 'severity' => $oldNote->severity, 'page' => null, 'description' => 'Periksa kembali koreksi versi sebelumnya: '.$oldNote->description, 'resolved' => false, 'verified' => false, 'version' => 1]);
                    }
                }

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
        return DB::transaction(function () use ($submission, $openedBy, $reason) {
            $submission = PortalAccess::lockSubmission($submission, $openedBy, 'layout_editor');

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
        });
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
        return DB::transaction(function () use ($submission, $actor, $note) {
            $submission = PortalAccess::lockSubmission($submission, $actor, 'layout_editor');

            return DB::transaction(function () use ($submission, $actor, $note): GalleyApproval {
                $activeGalley = Galley::where('submission_id', $submission->id)
                    ->where('status', 'active')
                    ->latest('version')
                    ->first();

                if (! $activeGalley) {
                    throw new DomainException('Tidak dapat meminta persetujuan akhir sebelum berkas galai diunggah.');
                }

                if (in_array($submission->status, ['published', 'withdrawn', 'declined', 'archived'], true)) {
                    throw new DomainException('Status naskah tidak memungkinkan permohonan persetujuan produksi.');
                }
                $existing = GalleyApproval::where('galley_id', $activeGalley->id)
                    ->where('file_id', $activeGalley->file_id)->where('checksum_sha256', $activeGalley->checksum_sha256)
                    ->whereIn('stage', ['editor_requested', 'editor_final'])->whereNull('invalidated_at')->first();
                if ($existing) {
                    return $existing;
                }

                // Invariant Guard 1: Anti-Bypass Proofreading
                // Galai naskah WAJIB telah dikirim ke putaran proofreading dan ditinjau proofreader
                $proofAssignment = ProofAssignment::where('galley_id', $activeGalley->id)->where('file_id', $activeGalley->file_id)->where('checksum_sha256', $activeGalley->checksum_sha256)
                    ->latest('assigned_at')
                    ->first();

                if (! $proofAssignment) {
                    throw new DomainException('Tidak dapat mengajukan persetujuan final: naskah wajib melalui putaran proofreading terlebih dahulu sebelum diajukan ke Editor in Chief.');
                }

                // Invariant Guard 2: Putaran proofreading tidak boleh masih menggantung tanpa penuntasan
                $totalNotes = ProofreadingNote::where('galley_id', $activeGalley->id)
                    ->count();

                if ($proofAssignment->status !== 'completed') {
                    throw new DomainException('Tidak dapat mengajukan persetujuan final: putaran proofreading sedang berlangsung dan belum diselesaikan oleh Proofreader.');
                }

                // Invariant Guard 3: Seluruh catatan koreksi proofreading pada submission ini wajib diselesaikan
                $unresolvedNotes = ProofreadingNote::where('galley_id', $activeGalley->id)
                    ->where(fn ($q) => $q->where('resolved', false)->orWhere('verified', false))
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

                $approval = GalleyApproval::create([
                    'galley_id' => $activeGalley->id,
                    'approved_by' => $actor->id,
                    'stage' => 'editor_requested',
                    'checksum_sha256' => $activeGalley->checksum_sha256,
                    'approved_at' => null,
                    'requested_at' => now(),
                    'invalidated_at' => null,
                    'file_id' => $activeGalley->file_id,
                    'note' => $note ?: 'Diajukan untuk diperiksa Pemimpin Redaksi.',
                ]);

                // Perbarui status submission menjadi siap terbit
                $submission->status = 'in_production';
                $submission->save();

                try {
                    $eic = User::where('status', 'active')->whereNotNull('email_verified_at')->whereHas('userRoles', fn ($q) => $q->where('journal_id', $submission->journal_id)->where('role_code', 'editor_in_chief'))->first();

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
        return DB::transaction(function () use ($submission, $layoutEditor, $assignedBy, $dueAt, $reason) {
            $role = $assignedBy->hasRole('editor_in_chief') ? 'editor_in_chief' : ($assignedBy->hasRole('manager') ? 'manager' : 'copyeditor');
            $submission = PortalAccess::lockSubmission($submission, $assignedBy, $role);
            if (! User::whereKey($layoutEditor->id)->where('status', 'active')->whereNotNull('email_verified_at')->whereHas('userRoles', fn ($q) => $q->where('role_code', 'layout_editor')->where('journal_id', $submission->journal_id))->exists()) {
                throw new DomainException('Layout Editor tidak memiliki peran aktif pada jurnal ini.');
            }
            LayoutAssignment::where('submission_id', $submission->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);

            $assignment = LayoutAssignment::create([
                'submission_id' => $submission->id, 'layout_editor_id' => $layoutEditor->id,
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
        });
    }

    /**
     * Persetujuan Penulis (Author Sign-off).
     */
    public function authorSignoff(
        Galley $galley,
        User $author,
        ?string $note = null
    ): GalleyApproval {
        return DB::transaction(function () use ($galley, $author, $note) {
            $submission = PortalAccess::lockSubmission($galley->submission, $author, 'author');
            $galley = Galley::query()->lockForUpdate()->findOrFail($galley->id);
            abort_unless($submission->author_id === $author->id, 404);
            if (! in_array($submission->status, ['accepted', 'in_production'], true)
                || $submission->activeGalley?->id !== $galley->id || ! $galley->readyForAuthorSignoff()) {
                throw new DomainException('Galai ini belum siap disetujui atau sudah diganti.');
            }

            $existing = $galley->approvals()->where('stage', 'author_signoff')
                ->where('approved_by', $author->id)->whereNull('invalidated_at')
                ->where('file_id', $galley->file_id)->where('checksum_sha256', $galley->checksum_sha256)->first();
            if ($existing) {
                return $existing;
            }

            app(PrivateFileService::class)->stream($galley->file);

            return $galley->approvals()->create([
                'approved_by' => $author->id,
                'stage' => 'author_signoff',
                'checksum_sha256' => $galley->checksum_sha256,
                'approved_at' => now(),
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
        if (! in_array($submission->status, ['ready_for_issue', 'scheduled'], true)) {
            $reasons[] = 'Naskah belum dinyatakan siap dijadwalkan terbit oleh EIC.';
        }

        $activeGalley = Galley::where('submission_id', $submission->id)
            ->where('status', 'active')
            ->where('format', 'pdf')
            ->latest('version')
            ->first();

        if (! $activeGalley) {
            $reasons[] = 'Naskah belum memiliki galai PDF aktif.';
        } else {
            $hasFinalApproval = GalleyApproval::where('galley_id', $activeGalley->id)
                ->where('stage', 'editor_final')->where('file_id', $activeGalley->file_id)->where('checksum_sha256', $activeGalley->checksum_sha256)
                ->whereNull('invalidated_at')->whereHas('approvedBy.userRoles', fn ($q) => $q->where('journal_id', $submission->journal_id)->where('role_code', 'editor_in_chief'))->exists();

            if (! $hasFinalApproval) {
                $reasons[] = 'Galai PDF aktif belum memperoleh persetujuan akhir (editor_final) dari Editor in Chief.';
            }

            if ($activeGalley->approved_checksum !== null && $activeGalley->approved_checksum !== $activeGalley->checksum_sha256) {
                $reasons[] = 'Checksum berkas galai tidak cocok dengan checksum saat persetujuan akhir diberikan.';
            }
        }

        if (! $submission->isPaymentCleared()) {
            $reasons[] = 'Pembayaran APC belum diselesaikan.';
        }
        if ($activeGalley && ($activeGalley->file?->virus_scan_status !== 'clean' || $activeGalley->approved_checksum !== $activeGalley->checksum_sha256)) {
            $reasons[] = 'Berkas galai belum terverifikasi dan disetujui.';
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
