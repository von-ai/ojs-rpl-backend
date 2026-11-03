<?php

declare(strict_types=1);

namespace App\Services\Copyeditor;

use App\Jobs\SendReviewNotice;
use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\FileEntry;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\User;
use App\Services\EditorialMediationService;
use App\Services\PortalAccess;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CopyeditorService
{
    /**
     * Mengunggah berkas hasil suntingan baru (Output Set).
     * Menerapkan Invalidation Guard: membatalkan approval aktif sebelumnya secara otomatis.
     */
    public function uploadOutputFile(Copyedit $copyedit, UploadedFile $file, User $uploader): FileEntry
    {
        return DB::transaction(function () use ($copyedit, $file, $uploader) {
            PortalAccess::lockSubmission($copyedit->submission, $uploader, 'copyeditor');
            $copyedit = Copyedit::query()->lockForUpdate()->findOrFail($copyedit->id);
            if ($copyedit->copy_editor_id !== $uploader->id || $copyedit->status === 'completed') {
                throw new DomainException('Penugasan copyediting sudah selesai atau bukan milik Anda.');
            }

            return DB::transaction(function () use ($copyedit, $file, $uploader): FileEntry {
                $storageDisk = config('filesecurity.disk', 'local');
                $path = $file->store("copyedits/{$copyedit->id}", $storageDisk);
                $disk = Storage::disk($storageDisk);
                $byteSize = (int) $disk->size($path);
                $sha256 = hash('sha256', (string) $disk->get($path));

                $fileEntry = FileEntry::create([
                    'storage_disk' => $storageDisk,
                    'storage_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'byte_size' => $byteSize,
                    'checksum_sha256' => $sha256,
                    'virus_scan_status' => 'pending',
                    'created_by' => $uploader->id,
                ]);

                $nextPosition = (int) ($copyedit->outputs()->max('position') ?? 0) + 1;

                CopyeditOutput::create([
                    'copyedit_id' => $copyedit->id,
                    'file_id' => $fileEntry->id,
                    'position' => $nextPosition,
                ]);

                // Invalidation Guard: setiap penambahan berkas baru otomatis membatalkan approval aktif sebelumnya
                CopyeditApproval::where('copyedit_id', $copyedit->id)
                    ->whereNull('invalidated_at')
                    ->update(['invalidated_at' => now()]);

                if ($copyedit->status === 'assigned') {
                    $copyedit->status = 'in_progress';
                }
                $copyedit->version = (int) $copyedit->version + 1;
                $copyedit->save();

                return $fileEntry;
            });
        });
    }

    /**
     * Meminta persetujuan penulis atas Output Set terkini.
     */
    public function requestAuthorApproval(Copyedit $copyedit, ?string $note = null, ?User $actor = null): CopyeditApproval
    {
        $actor ??= auth()->user();
        abort_unless($actor, 403);

        return DB::transaction(function () use ($copyedit, $note, $actor): CopyeditApproval {
            PortalAccess::lockSubmission($copyedit->submission, $actor, 'copyeditor');
            $copyedit = Copyedit::query()->lockForUpdate()->findOrFail($copyedit->id);
            if ($copyedit->copy_editor_id !== $actor->id || $copyedit->status === 'completed') {
                throw new DomainException('Penugasan tidak lagi dapat diubah.');
            }
            if ($copyedit->outputs()->count() === 0) {
                throw new DomainException('Tidak dapat meminta persetujuan penulis sebelum mengunggah berkas hasil suntingan.');
            }

            $active = $copyedit->approvals()->whereNull('invalidated_at')->first();
            if ($active && in_array($active->decision, ['requested', 'approved'], true)) {
                return $active;
            }
            if ($active) {
                throw new DomainException('Unggah versi hasil suntingan baru sebelum meminta peninjauan ulang.');
            }

            // Invalidate existing pending/active approval
            CopyeditApproval::where('copyedit_id', $copyedit->id)
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);

            $authorId = $copyedit->submission->author_id;
            if (! $authorId) {
                $corresponding = $copyedit->submission->correspondingAuthor;
                $authorId = $corresponding?->user_id;
                if (! $authorId) {
                    throw new DomainException('Penulis korespondensi belum memiliki akun yang terhubung.');
                }
            }

            if ($copyedit->outputs()->whereHas('file', fn ($q) => $q->where('virus_scan_status', '!=', 'clean'))->exists()) {
                throw new DomainException('Berkas suntingan belum lulus pemindaian keamanan.');
            }

            $nextVersion = (int) ($copyedit->approvals()->max('output_version') ?? 0) + 1;

            $approval = CopyeditApproval::create([
                'copyedit_id' => $copyedit->id,
                'author_id' => $authorId,
                'decision' => 'requested',
                'reason' => null,
                'output_version' => $nextVersion,
                'approved_at' => null,
                'requested_at' => now(),
                'invalidated_at' => null,
                'note' => $note,
            ]);
            $author = User::findOrFail($authorId);
            SendReviewNotice::dispatch($author->email, 'Tinjau hasil copyediting SIRIDA', 'Hasil suntingan menunggu persetujuan Anda. '.route('author.submissions.show', $copyedit->submission_id));

            return $approval;
        });
    }

    /**
     * Penulis menyetujui hasil suntingan (Author Approval).
     * Menerapkan Checksum Freeze: membekukan hash SHA-256 seluruh berkas output saat ini.
     */
    public function approveByAuthor(CopyeditApproval $approval, User $author, ?string $note = null): CopyeditApproval
    {
        return DB::transaction(function () use ($approval, $author, $note) {
            PortalAccess::lockSubmission($approval->copyedit->submission, $author, 'author');
            $approval = CopyeditApproval::query()->lockForUpdate()->findOrFail($approval->id);
            if ($approval->author_id !== $author->id || $approval->invalidated_at !== null || $approval->decision !== 'requested') {
                throw new DomainException('Permintaan persetujuan tidak lagi berlaku atau bukan milik Anda.');
            }

            return DB::transaction(function () use ($approval, $author, $note): CopyeditApproval {
                $approval->decision = 'approved';
                $approval->author_id = $author->id;
                $approval->approved_at = now();
                $approval->invalidated_at = null;
                $approval->note = $note;
                $approval->save();

                // Checksum Freeze: kunci seluruh hash SHA-256 berkas output saat ini
                $copyedit = $approval->copyedit;
                foreach ($copyedit->outputs as $output) {
                    $file = $output->file;
                    CopyeditApprovedFile::updateOrCreate(
                        [
                            'approval_id' => $approval->id,
                            'file_id' => $output->file_id,
                        ],
                        [
                            'checksum_sha256' => $file->checksum_sha256 ?? hash('sha256', $file->original_name),
                        ]
                    );
                }

                return $approval;
            });
        });
    }

    /**
     * Penulis mengajukan keberatan / permintaan perubahan naskah.
     */
    public function requestChangesByAuthor(CopyeditApproval $approval, User $author, string $reason): CopyeditApproval
    {
        return DB::transaction(function () use ($approval, $author, $reason) {
            PortalAccess::lockSubmission($approval->copyedit->submission, $author, 'author');
            $approval = CopyeditApproval::query()->lockForUpdate()->findOrFail($approval->id);
            if ($approval->author_id !== $author->id || $approval->invalidated_at !== null || $approval->decision !== 'requested') {
                throw new DomainException('Permintaan persetujuan tidak lagi berlaku atau bukan milik Anda.');
            }

            $approval->decision = 'revisions_requested';
            $approval->author_id = $author->id;
            $approval->reason = $reason;
            $approval->save();

            return $approval;
        });
    }

    /**
     * Pengajuan eskalasi mediasi sengketa redaksi ke Section Editor / Editor-in-Chief.
     */
    public function submitEscalation(
        Copyedit $copyedit,
        User $openedBy,
        string $reason,
        ?string $category = null
    ): ProductionEscalation {
        return DB::transaction(function () use ($copyedit, $openedBy, $reason, $category) {
            PortalAccess::lockSubmission($copyedit->submission, $openedBy, 'copyeditor');
            $copyedit = Copyedit::query()->lockForUpdate()->findOrFail($copyedit->id);
            if ($copyedit->copy_editor_id !== $openedBy->id || $copyedit->status === 'completed') {
                throw new DomainException('Penugasan copyediting sudah selesai atau bukan milik Anda.');
            }

            $formattedReason = $category ? "[{$category}] {$reason}" : $reason;

            return ProductionEscalation::create([
                'submission_id' => $copyedit->submission_id,
                'copyedit_id' => $copyedit->id,
                'galley_id' => null,
                'opened_by' => $openedBy->id,
                'reason' => $formattedReason,
                'status' => 'open',
                'resolution' => null,
                'resume_stage' => null,
                'version' => 1,
            ]);
        });
    }

    /**
     * Resolusi mediasi oleh Section Editor / EIC.
     */
    public function resolveEscalation(ProductionEscalation $escalation, string $resolution, string $resumeStage = 'in_progress', ?User $actor = null): ProductionEscalation
    {
        $actor ??= auth()->user();
        abort_unless($actor, 403);
        $role = $actor->hasRole('editor_in_chief') ? 'editor_in_chief' : 'section_editor';

        return app(EditorialMediationService::class)->resolve($escalation, $actor, $role, $resolution);
    }

    /**
     * Verifikasi kesiapan serah terima ke Layout Editor (Handoff Readiness Guard).
     *
     * @return array{is_ready: bool, reasons: array<int, string>}
     */
    public function checkHandoffReadiness(Copyedit $copyedit): array
    {
        $reasons = [];

        // 1. Harus memiliki berkas suntingan
        if ($copyedit->outputs()->count() === 0) {
            $reasons[] = 'Belum ada berkas suntingan (Output Set) yang diunggah.';
        }

        // 2. Harus memiliki approval aktif berstatus 'approved'
        $latestApproval = $copyedit->approvals()
            ->whereNull('invalidated_at')
            ->first();

        if (! $latestApproval || $latestApproval->decision !== 'approved') {
            $reasons[] = 'Persetujuan penulis (Author Approval) belum disetujui atau telah dibatalkan.';
        } else {
            // 3. Checksum Freeze check: verifikasi integritas hash byte seluruh berkas
            $approvedFiles = $latestApproval->approvedFiles->keyBy('file_id');
            foreach ($copyedit->outputs as $output) {
                $approved = $approvedFiles->get($output->file_id);
                if (! $approved) {
                    $reasons[] = "Berkas '{$output->file->original_name}' belum dibekukan dalam persetujuan penulis.";
                } elseif ($approved->checksum_sha256 !== $output->file->checksum_sha256) {
                    $reasons[] = "Checksum berkas '{$output->file->original_name}' tidak cocok dengan berkas yang disetujui penulis.";
                }
            }
        }

        if ($copyedit->outputs()->whereHas('file', fn ($q) => $q->where('virus_scan_status', '!=', 'clean'))->exists()) {
            $reasons[] = 'Berkas suntingan belum lulus pemindaian keamanan.';
        }

        // 4. Tidak boleh ada tiket eskalasi mediasi aktif
        $activeEscalation = $copyedit->escalations()
            ->whereIn('status', ['open', 'investigating'])
            ->exists();

        if ($activeEscalation) {
            $reasons[] = 'Terdapat tiket eskalasi mediasi redaksi yang masih aktif / belum diselesaikan.';
        }

        return [
            'is_ready' => count($reasons) === 0,
            'reasons' => $reasons,
        ];
    }

    /**
     * Menyelesaikan tahap copyediting dan serah terima naskah ke Layout Editor.
     */
    public function completeHandoff(
        Copyedit $copyedit,
        User $actor,
        string $layoutEditorId,
        ?string $notes = null
    ): LayoutAssignment {
        return DB::transaction(function () use ($copyedit, $actor, $layoutEditorId, $notes) {
            PortalAccess::lockSubmission($copyedit->submission, $actor, 'copyeditor');
            $copyedit = Copyedit::query()->lockForUpdate()->findOrFail($copyedit->id);
            if ($copyedit->copy_editor_id !== $actor->id || $copyedit->status === 'completed') {
                throw new DomainException('Penugasan copyediting sudah selesai atau bukan milik Anda.');
            }

            return DB::transaction(function () use ($copyedit, $actor, $layoutEditorId, $notes): LayoutAssignment {
                $readiness = $this->checkHandoffReadiness($copyedit);
                if (! $readiness['is_ready']) {
                    throw new DomainException(implode(' ', $readiness['reasons']));
                }

                // Update status Copyedit
                $copyedit->status = 'completed';
                if ($notes) {
                    $copyedit->notes = $notes;
                }
                $copyedit->save();

                // Update status Submission
                $submission = $copyedit->submission;
                $submission->status = 'in_production';
                $submission->save();

                if (! User::whereKey($layoutEditorId)->where('status', 'active')->whereNotNull('email_verified_at')->whereHas('userRoles', fn ($q) => $q->where('journal_id', $submission->journal_id)->where('role_code', 'layout_editor'))->exists()) {
                    throw new DomainException('Layout Editor tidak memiliki peran aktif pada jurnal ini.');
                }
                LayoutAssignment::where('submission_id', $submission->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);

                // Buat penugasan Layout Editor
                return LayoutAssignment::create([
                    'submission_id' => $submission->id,
                    'layout_editor_id' => $layoutEditorId,
                    'assigned_by' => $actor->id,
                    'assigned_at' => now(),
                    'due_at' => now()->addDays(7),
                    'reason' => $notes ?: 'Serah terima berkas final dari tahap copyediting.',
                ]);
            });
        });
    }
}
