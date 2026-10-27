<?php

declare(strict_types=1);

namespace App\Services\Copyeditor;

use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\CopyeditApprovedFile;
use App\Models\CopyeditOutput;
use App\Models\FileEntry;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\User;
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
        return DB::transaction(function () use ($copyedit, $file, $uploader): FileEntry {
            $path = $file->store("copyedits/{$copyedit->id}", 'local');
            $disk = Storage::disk('local');
            $byteSize = (int) $disk->size($path);
            $sha256 = hash('sha256', (string) $disk->get($path));

            $fileEntry = FileEntry::create([
                'storage_disk' => 'local',
                'storage_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                'byte_size' => $byteSize,
                'checksum_sha256' => $sha256,
                'virus_scan_status' => 'clean',
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
    }

    /**
     * Meminta persetujuan penulis atas Output Set terkini.
     */
    public function requestAuthorApproval(Copyedit $copyedit, ?string $note = null): CopyeditApproval
    {
        return DB::transaction(function () use ($copyedit, $note): CopyeditApproval {
            if ($copyedit->outputs()->count() === 0) {
                throw new DomainException('Tidak dapat meminta persetujuan penulis sebelum mengunggah berkas hasil suntingan.');
            }

            // Invalidate existing pending/active approval
            CopyeditApproval::where('copyedit_id', $copyedit->id)
                ->whereNull('invalidated_at')
                ->update(['invalidated_at' => now()]);

            $authorId = $copyedit->submission->author_id;
            if (! $authorId) {
                $corresponding = $copyedit->submission->correspondingAuthor;
                $authorId = $corresponding?->user_id ?? $copyedit->copy_editor_id;
            }

            $nextVersion = (int) ($copyedit->approvals()->max('output_version') ?? 0) + 1;

            return CopyeditApproval::create([
                'copyedit_id' => $copyedit->id,
                'author_id' => $authorId,
                'decision' => 'requested',
                'reason' => null,
                'output_version' => $nextVersion,
                'approved_at' => now(),
                'invalidated_at' => null,
                'note' => $note,
            ]);
        });
    }

    /**
     * Penulis menyetujui hasil suntingan (Author Approval).
     * Menerapkan Checksum Freeze: membekukan hash SHA-256 seluruh berkas output saat ini.
     */
    public function approveByAuthor(CopyeditApproval $approval, User $author, ?string $note = null): CopyeditApproval
    {
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
    }

    /**
     * Penulis mengajukan keberatan / permintaan perubahan naskah.
     */
    public function requestChangesByAuthor(CopyeditApproval $approval, User $author, string $reason): CopyeditApproval
    {
        $approval->decision = 'revisions_requested';
        $approval->author_id = $author->id;
        $approval->reason = $reason;
        $approval->save();

        return $approval;
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
    }

    /**
     * Resolusi mediasi oleh Section Editor / EIC.
     */
    public function resolveEscalation(
        ProductionEscalation $escalation,
        string $resolution,
        string $resumeStage = 'in_progress'
    ): ProductionEscalation {
        $escalation->status = 'resolved';
        $escalation->resolution = $resolution;
        $escalation->resume_stage = $resumeStage;
        $escalation->save();

        return $escalation;
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
            $submission->status = 'layout';
            $submission->save();

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
    }
}
