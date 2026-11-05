<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Copyedit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CopyeditorSubmissionDetailViewModel
{
    private string $submissionId;

    public function __construct(string $submissionId = 'SUB-2026-00106')
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Data lengkap naskah untuk ruang kerja Copy Editor.
     * Mengonsumsi relasi copyedits, copyedit_outputs, copyedit_approvals,
     * copyedit_approved_files, production_escalations, dan layout_assignments.
     *
     * @return array{
     *     id: string,
     *     number: string,
     *     assignment_code: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, email: string, affiliation: string},
     *     section_editor: string,
     *     layout_editor: string,
     *     assigned_at: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     is_completed: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     source_file: array{id: string, name: string, size_formatted: string, download_url: string},
     *     output_files: array<int, array{id: string, name: string, type: string, size_formatted: string, sha256: string, uploaded_at: string, download_url: string}>,
     *     approval: array{status: string, status_label: string, is_approved: bool, is_waiting: bool, has_changes_requested: bool, author_note: ?string, approved_at: ?string, invalidated_at: ?string},
     *     escalation: array{has_active: bool, is_resolved: bool, ticket_id: ?string, category: ?string, reason: ?string, status: ?string, resolution: ?string, resolved_at: ?string, created_at: ?string},
     *     handoff: array{is_ready: bool, is_completed: bool, assigned_layout_editor: string, notes_placeholder: string, submitted_notes: ?string, completed_at: ?string},
     *     notes: ?string,
     *     activity_timeline: array<int, array{title: string, actor: string, date: string, description: string, badge_class: string}>
     * }|null
     */
    public function submission(): ?array
    {
        $fromDb = $this->queryCopyedit();
        if ($fromDb) {
            return $this->formatCopyeditDetail($fromDb);
        }

        return null;
    }

    private function queryCopyedit(): ?Copyedit
    {
        try {
            if (! class_exists(Copyedit::class) || ! Schema::hasTable('copyedits')) {
                return null;
            }

            return Copyedit::with([
                'submission.section',
                'submission.journal',
                'submission.author',
                'submission.contributors',
                'submission.activeLayoutAssignment.layoutEditor',
                'sourceFile',
                'outputs.file',
                'approvals.approvedFiles',
                'approvals.author',
                'escalations.openedBy',
            ])
                ->where('id', $this->submissionId)
                ->orWhereHas('submission', function ($q) {
                    $q->where('submission_number', $this->submissionId)
                        ->orWhere('id', $this->submissionId);
                })
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCopyeditDetail(Copyedit $copyedit): array
    {
        $submission = $copyedit->submission;
        $activeEscalation = $copyedit->escalations->first(fn ($e) => in_array($e->status, ['open', 'investigating'], true));
        $resolvedEscalation = $copyedit->escalations->first(fn ($e) => $e->status === 'resolved');
        $latestEscalation = $copyedit->escalations->first();

        $latestApproval = $copyedit->approvals->first(fn ($a) => is_null($a->invalidated_at))
            ?? $copyedit->approvals->first();

        $approvalStatus = 'not_requested';
        $approvalStatusLabel = 'Belum Diminta';

        if ($latestApproval) {
            if ($latestApproval->invalidated_at !== null) {
                $approvalStatus = 'invalidated';
                $approvalStatusLabel = 'Persetujuan Dibatalkan (Ada Berkas Baru)';
            } elseif ($latestApproval->decision === 'approved') {
                $approvalStatus = 'approved';
                $approvalStatusLabel = 'Telah Disetujui Penulis';
            } elseif ($latestApproval->decision === 'revisions_requested') {
                $approvalStatus = 'changes_requested';
                $approvalStatusLabel = 'Penulis Meminta Perbaikan';
            } elseif ($latestApproval->decision === 'requested') {
                $approvalStatus = 'requested';
                $approvalStatusLabel = 'Menunggu Respon Penulis';
            }
        }

        $isApproved = $approvalStatus === 'approved';
        $isWaiting = $approvalStatus === 'requested';
        $hasChangesRequested = $approvalStatus === 'changes_requested';

        $outputFiles = [];
        foreach ($copyedit->outputs as $output) {
            $file = $output->file;
            if (! $file) {
                continue;
            }
            $isSupplementary = str_contains(mb_strtolower($file->original_name), 'lampiran')
                || str_contains(mb_strtolower($file->original_name), 'supplementary');

            $byteSize = (int) ($file->byte_size ?? 0);
            if ($byteSize <= 0 && $file->storage_path) {
                try {
                    $disk = Storage::disk($file->storage_disk ?: 'azure');
                    if ($disk->exists($file->storage_path)) {
                        $byteSize = (int) $disk->size($file->storage_path);
                    }
                } catch (\Throwable) {
                }
            }

            $outputFiles[] = [
                'id' => $file->id,
                'name' => $file->original_name,
                'type' => $isSupplementary ? 'Lampiran Data' : 'Naskah Utama',
                'size_formatted' => $this->formatBytes($byteSize),
                'sha256' => $file->checksum_sha256 ?? '',
                'uploaded_at' => $file->created_at ? $file->created_at->format('d M Y H:i \W\I\T\A') : now()->format('d M Y H:i \W\I\T\A'),
                'download_url' => route('copyeditor.files.download', $file->id),
            ];
        }

        $handoffReady = $isApproved && ! $activeEscalation && count($outputFiles) > 0;

        $status = 'in_progress';
        $statusLabel = 'Tahap Copyediting Aktif';
        $statusBadgeClass = 'bg-sky-50 text-sky-800 border-sky-200';

        if ($copyedit->status === 'completed') {
            $status = 'completed';
            $statusLabel = 'Selesai — Telah Diserahkan ke Layout';
            $statusBadgeClass = 'bg-gray-100 text-gray-800 border-gray-300';
        } elseif ($activeEscalation) {
            $status = 'escalated';
            $statusLabel = 'Dalam Mediasi Redaksi';
            $statusBadgeClass = 'bg-red-50 text-red-700 border-red-200';
        } elseif ($resolvedEscalation) {
            $status = 'in_progress';
            $statusLabel = 'Mediasi Selesai — Butuh Penyesuaian';
            $statusBadgeClass = 'bg-indigo-50 text-indigo-800 border-indigo-200';
        } elseif ($isApproved) {
            $status = 'approved';
            $statusLabel = 'Disetujui Penulis';
            $statusBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        } elseif ($isWaiting) {
            $status = 'awaiting_author_approval';
            $statusLabel = 'Menunggu Persetujuan Penulis';
            $statusBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
        } elseif ($hasChangesRequested) {
            $status = 'in_progress';
            $statusLabel = 'Permintaan Revisi Penulis';
            $statusBadgeClass = 'bg-orange-50 text-orange-800 border-orange-200';
        }

        $corresponding = $submission?->contributors?->firstWhere('is_corresponding', true);
        $authorName = $corresponding?->full_name ?? $submission?->author?->name ?? 'Dr. Nurfadillah Bahar, M.Kes.';
        $authorEmail = $corresponding?->email ?? $submission?->author?->email ?? 'author@journal.org';
        $authorAffiliation = $corresponding?->affiliation ?? 'Universitas Hasanuddin';

        $layoutEditorName = $submission?->activeLayoutAssignment?->layoutEditor?->name ?? 'Rahmat Hidayat, S.Kom.';

        $timeline = [];
        if ($copyedit->status === 'completed') {
            $timeline[] = [
                'title' => 'Serah Terima Selesai',
                'actor' => $copyedit->copyEditor?->name ?? 'Copy Editor',
                'date' => $copyedit->updated_at ? $copyedit->updated_at->format('d M Y, H:i \W\I\T\A') : now()->format('d M Y, H:i \W\I\T\A'),
                'description' => "Naskah resmi diserahkan ke Layout Editor {$layoutEditorName}.",
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        }
        if ($isApproved) {
            $timeline[] = [
                'title' => 'Persetujuan Penulis Diterima',
                'actor' => $authorName,
                'date' => $latestApproval?->approved_at ? $latestApproval->approved_at->format('d M Y, H:i \W\I\T\A') : now()->format('d M Y, H:i \W\I\T\A'),
                'description' => 'Penulis menyetujui seluruh berkas hasil suntingan dan checksum SHA-256 dibekukan.',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        }
        if (count($outputFiles) > 0) {
            $timeline[] = [
                'title' => 'Berkas Suntingan Diunggah',
                'actor' => $copyedit->copyEditor?->name ?? 'Copy Editor',
                'date' => $copyedit->created_at ? $copyedit->created_at->format('d M Y, H:i \W\I\T\A') : now()->format('d M Y, H:i \W\I\T\A'),
                'description' => 'Mengunggah naskah hasil perbaikan tata bahasa dan sitasi.',
                'badge_class' => 'bg-sky-50 text-sky-700 border-sky-200',
            ];
        }

        $sourceByteSize = (int) ($copyedit->sourceFile?->byte_size ?? 0);
        if ($sourceByteSize <= 0 && $copyedit->sourceFile?->storage_path) {
            try {
                $disk = Storage::disk($copyedit->sourceFile->storage_disk ?: 'azure');
                if ($disk->exists($copyedit->sourceFile->storage_path)) {
                    $sourceByteSize = (int) $disk->size($copyedit->sourceFile->storage_path);
                }
            } catch (\Throwable) {
            }
        }

        return [
            'id' => $submission?->submission_number ?: $copyedit->id,
            'number' => $submission?->submission_number ?: $copyedit->id,
            'assignment_code' => '#CE-'.strtoupper(substr($copyedit->id, 0, 10)),
            'title' => $submission?->title ?? 'Naskah Ilmiah',
            'section_title' => $submission?->section?->name ?? 'Inovasi Daerah & Kebijakan Publik',
            'corresponding_author' => [
                'name' => $authorName,
                'email' => $authorEmail,
                'affiliation' => $authorAffiliation,
            ],
            'section_editor' => 'Prof. Hamka, Ph.D.',
            'layout_editor' => $layoutEditorName,
            'assigned_at' => $copyedit->created_at ? $copyedit->created_at->format('d M Y') : now()->format('d M Y'),
            'due_at' => $copyedit->due_at ? $copyedit->due_at->format('d M Y 23:59 \W\I\T\A') : now()->format('d M Y 23:59 \W\I\T\A'),
            'is_urgent' => $copyedit->due_at ? $copyedit->due_at->diffInDays(now()) <= 3 && $copyedit->status !== 'completed' : false,
            'is_completed' => $copyedit->status === 'completed',
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'source_file' => [
                'id' => $copyedit->sourceFile?->id ?? 'FILE-SRC-DEFAULT',
                'name' => $copyedit->sourceFile?->original_name ?? 'Source_Accepted.docx',
                'size_formatted' => $this->formatBytes($sourceByteSize),
                'download_url' => $copyedit->sourceFile ? route('copyeditor.files.download', $copyedit->sourceFile->id) : route('copyeditor.files.download', 'Source_Accepted.docx'),
            ],
            'output_files' => $outputFiles,
            'approval' => [
                'status' => $approvalStatus,
                'status_label' => $approvalStatusLabel,
                'is_approved' => $isApproved,
                'is_waiting' => $isWaiting,
                'has_changes_requested' => $hasChangesRequested,
                'author_note' => $latestApproval?->reason ?? $latestApproval?->note,
                'approved_at' => $latestApproval?->approved_at ? $latestApproval->approved_at->format('d M Y H:i \W\I\T\A') : null,
                'invalidated_at' => $latestApproval?->invalidated_at ? $latestApproval->invalidated_at->format('d M Y H:i \W\I\T\A') : null,
            ],
            'escalation' => [
                'has_active' => $activeEscalation !== null,
                'is_resolved' => $resolvedEscalation !== null,
                'ticket_id' => $latestEscalation ? (str_starts_with((string) $latestEscalation->id, 'ESC-') ? (string) $latestEscalation->id : 'ESC-'.strtoupper(substr((string) $latestEscalation->id, 0, 8))) : null,
                'category' => $latestEscalation ? 'Mediasi Sengketa Redaksi' : null,
                'reason' => $latestEscalation?->reason,
                'status' => $latestEscalation ? ($activeEscalation ? 'Menunggu Mediasi Section Editor' : 'Selesai Diputuskan') : null,
                'resolution' => $resolvedEscalation?->resolution,
                'resolved_at' => $resolvedEscalation?->updated_at ? $resolvedEscalation->updated_at->format('d M Y') : null,
                'created_at' => $latestEscalation?->created_at ? $latestEscalation->created_at->format('d M Y H:i \W\I\T\A') : null,
            ],
            'handoff' => [
                'is_ready' => $handoffReady,
                'is_completed' => $copyedit->status === 'completed',
                'assigned_layout_editor' => $layoutEditorName,
                'notes_placeholder' => 'Catatan khusus gaya selingkung atau instruksi penataan untuk Layout Editor...',
                'submitted_notes' => $copyedit->notes,
                'completed_at' => $copyedit->status === 'completed' && $copyedit->updated_at ? $copyedit->updated_at->format('d M Y, H:i \W\I\T\A') : null,
            ],
            'notes' => $copyedit->notes,
            'activity_timeline' => $timeline,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / pow(1024, $i), 2).' '.$units[$i];
    }
}
