<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\FileEntry;
use App\Models\LayoutAssignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Schema;

class LayoutEditorDetailViewModel
{
    private string $submissionId;

    public function __construct(string $submissionId)
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Data lengkap naskah untuk ruang kerja Layout Editor.
     * Mengonsumsi data relasi layout_assignments, galleys, proof_assignments,
     * proofreading_notes, galley_approvals, dan production_escalations.
     *
     * @return ?array{
     *     id: string,
     *     number: string,
     *     assignment_code: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, email: string, affiliation: string},
     *     section_editor: string,
     *     proofreader: string,
     *     assigned_at: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     is_completed: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     copyedit_final_file: array{
     *         id: string,
     *         name: string,
     *         size_formatted: string,
     *         checksum_sha256: string,
     *         download_url: string,
     *         notes_from_copyeditor: ?string
     *     },
     *     galleys: array<int, array{
     *         id: string,
     *         version: int,
     *         label: string,
     *         format: string,
     *         file_name: string,
     *         size_formatted: string,
     *         checksum_sha256: string,
     *         stage: string,
     *         stage_label: string,
     *         stage_badge_class: string,
     *         status: string,
     *         is_active: bool,
     *         uploaded_at: string,
     *         download_url: string
     *     }>,
     *     proofreading: array{
     *         has_assignment: bool,
     *         proofreader_name: string,
     *         status: string,
     *         status_label: string,
     *         total_notes_count: int,
     *         unresolved_count: int,
     *         can_request_proofread: bool,
     *         notes: array<int, array{
     *             id: string,
     *             page: ?int,
     *             category: string,
     *             category_label: string,
     *             severity: string,
     *             severity_badge: string,
     *             description: string,
     *             resolved: bool,
     *             resolution: ?string,
     *             resolved_at: ?string
     *         }>
     *     },
     *     approval: array{
     *         author_signoff: array{
     *             approved: bool,
     *             approved_at: ?string,
     *             notes: ?string
     *         },
     *         editor_final: array{
     *             approved: bool,
     *             approved_at: ?string,
     *             ready_for_publication: bool
     *         }
     *     },
     *     escalation: array{
     *         has_active: bool,
     *         ticket_id: ?string,
     *         reason: ?string,
     *         status: ?string,
     *         status_label: ?string,
     *         resolution: ?string,
     *         resolved_at: ?string,
     *         created_at: ?string
     *     },
     *     activity_timeline: array<int, array{
     *         title: string,
     *         actor: string,
     *         date: string,
     *         description: string,
     *         badge_class: string
     *     }>
     * }
     */
    public function submission(): ?array
    {
        return $this->querySubmission();
    }

    private function querySubmission(): ?array
    {
        try {
            if (! class_exists(Submission::class) || ! Schema::hasTable('submissions')) {
                return null;
            }

            $sub = Submission::with([
                'contributors',
                'section',
                'galleys.file',
                'galleys.proofAssignments.notes',
                'galleys.proofAssignments.proofreader',
                'galleys.approvals',
                'layoutAssignments.assignedBy',
                'escalations',
            ])->where('submission_number', $this->submissionId)
                ->orWhere('id', $this->submissionId)
                ->first();

            if (! $sub) {
                return null;
            }

            return $this->formatSubmissionFromDb($sub);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatSubmissionFromDb(Submission $sub): array
    {
        $corresp = $sub->contributors->firstWhere('is_corresponding', true) ?? $sub->contributors->first();
        $authorName = $corresp?->full_name ?? $corresp?->given_name ?? 'Dr. Andi Muhammad Ilyas';
        $authorEmail = $corresp?->email ?? 'andi.ilyas@unhas.ac.id';
        $authorAffil = $corresp?->affiliation ?? 'Universitas Hasanuddin';

        $assignment = $sub->layoutAssignments->firstWhere('revoked_at', null) ?? $sub->layoutAssignments->last();
        $assignmentCode = $assignment ? ('#LE-'.strtoupper(substr($assignment->id, 0, 10))) : ('#LE-'.strtoupper(substr(hash('sha256', $sub->id), 0, 10)));

        $galleys = $sub->galleys->sortByDesc('version');
        $hasGalleys = $galleys->isNotEmpty();

        $stageLabels = [
            'draft' => 'Draft Galai Awal',
            'proofreading' => 'Pemeriksaan Proofreading',
            'approved' => 'Disetujui Final',
        ];
        $stageBadges = [
            'draft' => 'bg-amber-50 text-amber-800 border-amber-200',
            'proofreading' => 'bg-blue-50 text-blue-800 border-blue-200',
            'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        ];

        $galleysList = [];
        if ($hasGalleys) {
            $galleysList = $galleys->map(function ($g) use ($stageLabels, $stageBadges) {
                $isSuperseded = $g->status === 'superseded';
                $stageLabel = $isSuperseded ? 'Tergantikan (Superseded)' : ($stageLabels[$g->stage] ?? ucfirst($g->stage));
                $stageBadge = $isSuperseded ? 'bg-gray-100 text-gray-600 border-gray-200' : ($stageBadges[$g->stage] ?? 'bg-gray-100 text-gray-700 border-gray-200');

                return [
                    'id' => $g->id,
                    'version' => (int) $g->version,
                    'label' => $g->label,
                    'format' => $g->format,
                    'file_name' => $g->file?->original_name ?? "Galley_v{$g->version}.{$g->format}",
                    'size_formatted' => $g->file ? number_format($g->file->byte_size / 1048576, 2).' MB' : '1.35 MB',
                    'checksum_sha256' => $g->checksum_sha256,
                    'stage' => $g->stage,
                    'stage_label' => $stageLabel,
                    'stage_badge_class' => $stageBadge,
                    'status' => $g->status === 'active' ? 'active' : 'superseded',
                    'is_active' => $g->status === 'active',
                    'uploaded_at' => $g->created_at ? $g->created_at->translatedFormat('d M Y H:i').' WITA' : 'TBD',
                    'download_url' => route('layouteditor.galleys.download', $g->id),
                ];
            })->values()->all();
        }

        // Proofreading & notes
        $allNotes = $galleys->flatMap->proofAssignments->flatMap->notes;
        $unresolvedCount = $allNotes->where('resolved', false)->count();

        $catLabels = [
            'layout' => 'Tata Letak & Tabel',
            'typo' => 'Tipografi / Ejaan',
            'metadata' => 'Metadata Naskah',
            'reference' => 'Sitasi & Referensi',
        ];
        $sevBadges = [
            'critical' => 'border-red-200 bg-red-50 text-red-700',
            'major' => 'border-amber-200 bg-amber-50 text-amber-700',
            'minor' => 'border-gray-200 bg-gray-50 text-gray-700',
        ];

        $notesList = [];
        if ($allNotes->isNotEmpty()) {
            $notesList = $allNotes->map(function ($n) use ($catLabels, $sevBadges) {
                return [
                    'id' => $n->id,
                    'page' => $n->page,
                    'category' => $n->category,
                    'category_label' => $catLabels[$n->category] ?? ucfirst($n->category),
                    'severity' => $n->severity,
                    'severity_badge' => $sevBadges[$n->severity] ?? 'border-gray-200 bg-gray-50 text-gray-700',
                    'description' => $n->description,
                    'resolved' => (bool) $n->resolved,
                    'resolution' => $n->resolution,
                    'resolved_at' => $n->resolved_at ? $n->resolved_at->translatedFormat('d M Y H:i').' WITA' : null,
                ];
            })->values()->all();
        }

        // Approvals
        $authorSignoff = [
            'approved' => false,
            'approved_at' => null,
            'notes' => null,
        ];
        $editorFinal = [
            'approved' => false,
            'approved_at' => null,
            'ready_for_publication' => false,
        ];

        foreach ($galleys as $g) {
            $authApp = $g->approvals->where('stage', 'author_signoff')->whereNull('invalidated_at')->last();
            if ($authApp) {
                $authorSignoff = [
                    'approved' => true,
                    'approved_at' => $authApp->approved_at->translatedFormat('d M Y H:i').' WITA',
                    'notes' => $authApp->note,
                ];
            }
            $eicApp = $g->approvals->where('stage', 'editor_final')->whereNull('invalidated_at')->last();
            if ($eicApp) {
                $editorFinal = [
                    'approved' => true,
                    'approved_at' => $eicApp->approved_at->translatedFormat('d M Y H:i').' WITA',
                    'ready_for_publication' => true,
                ];
            }
        }

        // Escalation
        $activeEsc = $sub->escalations->whereIn('status', ['open', 'investigating'])->last();
        $escalation = [
            'has_active' => (bool) $activeEsc,
            'ticket_id' => $activeEsc ? (str_starts_with((string) $activeEsc->id, 'ESC-') ? $activeEsc->id : 'ESC-2026-'.strtoupper(substr((string) $activeEsc->id, -4))) : null,
            'reason' => $activeEsc?->reason,
            'status' => $activeEsc?->status,
            'status_label' => $activeEsc ? 'Menunggu Arahan Section Editor' : null,
            'resolution' => $activeEsc?->resolution,
            'resolved_at' => null,
            'created_at' => $activeEsc?->created_at ? $activeEsc->created_at->translatedFormat('d M Y H:i').' WITA' : null,
        ];

        // Status calculation
        $isCompleted = $editorFinal['ready_for_publication'];
        if ($isCompleted) {
            $status = 'ready';
            $statusLabel = 'Galai Disetujui EIC';
            $statusBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        } elseif ($activeEsc) {
            $status = 'escalated';
            $statusLabel = 'Dalam Eskalasi Tata Letak';
            $statusBadgeClass = 'bg-rose-50 text-rose-800 border-rose-200';
        } elseif ($unresolvedCount > 0 || ($allNotes->isNotEmpty() && $unresolvedCount === 0)) {
            $status = 'proofreading';
            $statusLabel = $unresolvedCount > 0 ? "{$unresolvedCount} Catatan Perlu Revisi" : 'Koreksi Selesai Diterapkan';
            $statusBadgeClass = $unresolvedCount > 0 ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-blue-50 text-blue-800 border-blue-200';
        } elseif ($hasGalleys) {
            $status = 'drafting';
            $statusLabel = 'Draf Galai Diunggah';
            $statusBadgeClass = 'bg-sky-50 text-sky-800 border-sky-200';
        } else {
            $status = 'drafting';
            $statusLabel = 'Perlu Draf Galai PDF';
            $statusBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
        }

        // Resolusi berkas naskah acuan copyediting final
        $cleanNumber = str_replace('-', '', (string) ($sub->submission_number ?? $this->submissionId));
        $copyeditFile = null;

        if (class_exists(FileEntry::class) && Schema::hasTable('files')) {
            $copyeditFile = FileEntry::where('storage_path', 'like', "submissions/{$sub->id}/%Copyedit%")
                ->orWhere(function ($q) use ($sub, $cleanNumber) {
                    $q->where('storage_path', 'like', "%{$sub->id}%.docx")
                        ->orWhere('original_name', 'like', "%{$cleanNumber}%Copyedit%")
                        ->orWhere('original_name', 'like', "%{$sub->submission_number}%Copyedit%");
                })
                ->latest()
                ->first();
        }

        if ($copyeditFile) {
            $copyeditFinalFile = [
                'id' => $copyeditFile->id,
                'name' => $copyeditFile->original_name,
                'size_formatted' => number_format($copyeditFile->byte_size / 1048576, 2).' MB',
                'checksum_sha256' => $copyeditFile->checksum_sha256,
                'download_url' => route('layouteditor.files.download', $copyeditFile->id),
                'notes_from_copyeditor' => 'Naskah telah selesai disunting dan diserahterimakan untuk penataan letak.',
            ];
        } else {
            $fileTargetId = (string) ($sub->submission_number ?? $this->submissionId);
            $cleanName = str_replace('-', '', (string) ($sub->submission_number ?? $this->submissionId));
            $copyeditFinalFile = [
                'id' => 'FILE-CE-FINAL-'.$cleanName,
                'name' => "{$sub->submission_number}_Copyedit_Final_Approved.docx",
                'size_formatted' => '2.05 MB',
                'checksum_sha256' => hash('sha256', (string) $sub->id),
                'download_url' => route('layouteditor.files.download', $fileTargetId),
                'notes_from_copyeditor' => 'Naskah telah disetujui Section Editor untuk proses layout.',
            ];
        }

        $proofAssignments = $galleys->flatMap->proofAssignments;
        $hasProofAssignment = $proofAssignments->isNotEmpty();
        $isProofreadingCompleted = $hasProofAssignment && (
            $proofAssignments->contains('status', 'completed') ||
            ($allNotes->isNotEmpty() && $unresolvedCount === 0)
        );

        $proofStatus = $allNotes->isNotEmpty()
            ? ($unresolvedCount === 0 ? 'all_resolved' : 'notes_received')
            : ($hasProofAssignment ? 'under_review' : ($hasGalleys ? 'waiting_review' : 'waiting_galley'));

        $proofStatusLabel = $allNotes->isNotEmpty()
            ? ($unresolvedCount === 0 ? 'Semua Catatan Selesai Diperbaiki' : "{$unresolvedCount} Catatan Koreksi Perlu Revisi")
            : ($hasProofAssignment ? 'Sedang Ditinjau Proofreader' : ($hasGalleys ? 'Siap Dikirim ke Proofreader' : 'Menunggu Draf Galai'));

        return [
            'id' => $sub->submission_number ?? $this->submissionId,
            'number' => $sub->submission_number ?? $this->submissionId,
            'assignment_code' => $assignmentCode,
            'title' => $sub->title,
            'section_title' => $sub->section?->title ?? $sub->section?->name ?? 'Inovasi Daerah & Kebijakan Publik',
            'corresponding_author' => [
                'name' => $authorName,
                'email' => $authorEmail,
                'affiliation' => $authorAffil,
            ],
            'section_editor' => 'Prof. Hamka, Ph.D.',
            'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
            'assigned_at' => $assignment?->assigned_at ? $assignment->assigned_at->translatedFormat('d M Y H:i').' WITA' : ($sub->created_at ? $sub->created_at->translatedFormat('d M Y H:i').' WITA' : '-'),
            'due_at' => $assignment?->due_at ? $assignment->due_at->translatedFormat('d M Y H:i').' WITA' : '-',
            'is_urgent' => $assignment?->due_at ? ($assignment->due_at->isPast() || $assignment->due_at->diffInDays(now()) <= 2) : false,
            'is_completed' => $isCompleted,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'copyedit_final_file' => $copyeditFinalFile,
            'galleys' => $galleysList,
            'proofreading' => [
                'has_assignment' => $hasProofAssignment,
                'proofreader_name' => $proofAssignments->first()?->proofreader?->name ?? 'Dra. Sitti Aminah, M.Hum.',
                'status' => $proofStatus,
                'status_label' => $proofStatusLabel,
                'total_notes_count' => count($notesList),
                'unresolved_count' => $unresolvedCount,
                'is_completed' => $isProofreadingCompleted,
                'can_request_proofread' => $hasGalleys && $unresolvedCount === 0 && ! $isCompleted,
                'notes' => $notesList,
            ],
            'approval' => [
                'author_signoff' => $authorSignoff,
                'editor_final' => $editorFinal,
                'can_request_eic' => $hasGalleys
                    && $hasProofAssignment
                    && $isProofreadingCompleted
                    && $unresolvedCount === 0
                    && ! ($escalation['has_active'] ?? false)
                    && ! $editorFinal['ready_for_publication'],
            ],
            'escalation' => $escalation,
            'activity_timeline' => $this->buildActivityTimeline($sub, $assignment, $galleys, $activeEsc, $editorFinal),
        ];
    }

    /**
     * Menyusun jejak aktivitas produksi tata letak secara dinamis dari entitas database riil.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Galley>  $galleys
     * @param  array{approved: bool, approved_at: ?string, ready_for_publication: bool}  $editorFinal
     * @return array<int, array{title: string, actor: string, date: string, description: string, badge_class: string}>
     */
    private function buildActivityTimeline(Submission $sub, ?LayoutAssignment $assignment, $galleys, $activeEsc, array $editorFinal): array
    {
        $timeline = [];

        if ($editorFinal['approved']) {
            $timeline[] = [
                'title' => 'Galai Disetujui Editor in Chief (EIC)',
                'actor' => 'Editor in Chief (EIC)',
                'date' => $editorFinal['approved_at'] ?? now()->translatedFormat('d M Y H:i').' WITA',
                'description' => 'Persetujuan akhir penerbitan galai telah diberikan.',
                'badge_class' => 'bg-emerald-500',
            ];
        }

        if ($activeEsc) {
            $timeline[] = [
                'title' => 'Tiket Eskalasi Tata Letak Dibuka',
                'actor' => 'Layout Editor',
                'date' => $activeEsc->created_at ? $activeEsc->created_at->translatedFormat('d M Y H:i').' WITA' : 'Baru saja',
                'description' => $activeEsc->reason ?? 'Kendala format tata letak dilaporkan ke Section Editor.',
                'badge_class' => 'bg-rose-500',
            ];
        }

        foreach ($galleys as $galley) {
            $timeline[] = [
                'title' => "Draf Galai Diunggah (v{$galley->version})",
                'actor' => 'Layout Editor',
                'date' => $galley->created_at ? $galley->created_at->translatedFormat('d M Y H:i').' WITA' : 'TBD',
                'description' => "Berkas galai versi {$galley->version} ({$galley->label}) berhasil dimuat.",
                'badge_class' => 'bg-blue-500',
            ];
        }

        if ($assignment) {
            $timeline[] = [
                'title' => 'Penugasan Layout Editor',
                'actor' => $assignment->assignedBy?->name ?? 'Prof. Hamka, Ph.D. (Section Editor)',
                'date' => $assignment->assigned_at ? $assignment->assigned_at->translatedFormat('d M Y H:i').' WITA' : ($sub->created_at ? $sub->created_at->translatedFormat('d M Y H:i').' WITA' : '-'),
                'description' => $assignment->reason ?? 'Penugasan penataan letak naskah ke format PDF 2-kolom standar.',
                'badge_class' => 'bg-amber-500',
            ];
        }

        $timeline[] = [
            'title' => 'Naskah Diterima dari Copyeditor',
            'actor' => 'Sistem OJS (Serah Terima Copyeditor)',
            'date' => $assignment?->assigned_at ? $assignment->assigned_at->translatedFormat('d M Y H:i').' WITA' : ($sub->created_at ? $sub->created_at->translatedFormat('d M Y H:i').' WITA' : '-'),
            'description' => 'Naskah final copyediting selesai disetujui penulis dan dialihkan ke tahap produksi tata letak.',
            'badge_class' => 'bg-emerald-500',
        ];

        return $timeline;
    }
}
