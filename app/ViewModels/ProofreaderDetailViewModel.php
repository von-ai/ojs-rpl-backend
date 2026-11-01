<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Support\Facades\Schema;

class ProofreaderDetailViewModel
{
    private string $submissionId;

    public function __construct(string $submissionId)
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Mendapatkan ID naskah aktif.
     */
    public function submissionId(): string
    {
        return $this->submissionId;
    }

    /**
     * Data naskah dan penugasan proofreading spesifik.
     */
    public function submission(): ?array
    {
        try {
            if (class_exists(Submission::class) && Schema::hasTable('submissions')) {
                $sub = Submission::with([
                    'section',
                    'author',
                    'contributors',
                    'galleys.file',
                    'galleys.proofAssignments',
                    'activeLayoutAssignment.layoutEditor',
                    'escalations.openedBy',
                ])
                    ->where('id', $this->submissionId)
                    ->orWhere('submission_number', $this->submissionId)
                    ->first();

                if ($sub) {
                    return $this->buildFromDatabase($sub);
                }
            }
        } catch (\Throwable) {
            // fallback
        }

        return null;
    }

    /**
     * Menyusun data tampilan detail naskah dari entitas basis data nyata.
     */
    protected function buildFromDatabase(Submission $sub): array
    {
        $activeGalley = $sub->galleys()->where('status', 'active')->latest('version')->first()
            ?? $sub->galleys()->latest('version')->first();

        $assignment = null;
        if ($activeGalley) {
            $assignment = ProofAssignment::where('galley_id', $activeGalley->id)->latest()->first();
        }

        $notesCollection = $activeGalley
            ? ProofreadingNote::where('galley_id', $activeGalley->id)->orderBy('created_at')->get()
            : collect();

        $notes = [];
        foreach ($notesCollection as $note) {
            $severity = $note->severity ?? 'minor';
            $severityBadge = match ($severity) {
                'critical' => 'bg-red-50 text-red-700 border-red-200',
                'major' => 'bg-amber-50 text-amber-800 border-amber-200',
                default => 'bg-blue-50 text-blue-700 border-blue-200',
            };

            $severityLabel = match ($severity) {
                'critical' => 'Kritis',
                'major' => 'Major',
                default => 'Minor',
            };

            $categoryLabel = match ($note->category) {
                'typo' => 'Tipografi / Ejaan',
                'layout' => 'Tata Letak / Format',
                'reference' => 'Sitasi & Pustaka',
                'metadata' => 'Metadata / Afiliasi',
                default => ucfirst((string) $note->category),
            };

            $notes[] = [
                'id' => $note->id,
                'page' => $note->page,
                'category' => $note->category,
                'category_label' => $categoryLabel,
                'severity' => $severity,
                'severity_badge' => $severityBadge,
                'severity_label' => $severityLabel,
                'description' => $note->description,
                'resolved' => (bool) $note->resolved,
                'resolution' => $note->resolution,
                'resolved_at' => $note->resolved_at ? $note->resolved_at->translatedFormat('d M Y, H:i').' WITA' : null,
                'verified' => (bool) $note->verified,
            ];
        }

        // Escalation Information
        $activeEscalation = $sub->escalations()->whereIn('status', ['open', 'investigating'])->latest()->first();
        $escalationData = [
            'has_active' => (bool) $activeEscalation,
            'ticket_id' => $activeEscalation ? '#ESC-'.substr($activeEscalation->id, -8) : null,
            'reason' => $activeEscalation?->reason,
            'status_label' => $activeEscalation ? ($activeEscalation->status === 'investigating' ? 'Sedang Dimediasi Section Editor' : 'Menunggu Tindak Lanjut Editor') : null,
            'created_at' => $activeEscalation?->created_at ? $activeEscalation->created_at->translatedFormat('d M Y, H:i').' WITA' : null,
        ];

        $totalNotes = count($notes);
        $unresolvedNotes = count(array_filter($notes, fn ($n) => ! $n['resolved']));
        $resolvedNotes = count(array_filter($notes, fn ($n) => $n['resolved']));
        $unverifiedNotes = count(array_filter($notes, fn ($n) => $n['resolved'] && ! $n['verified']));

        $canSignoff = ($unresolvedNotes === 0 && $unverifiedNotes === 0 && ! $escalationData['has_active']);
        $signoffGuardReason = null;
        if ($escalationData['has_active']) {
            $signoffGuardReason = "Persetujuan akhir (sign-off) ditangguhkan karena terdapat tiket eskalasi aktif ({$escalationData['ticket_id']}) yang sedang ditinjau Section Editor.";
        } elseif ($unresolvedNotes > 0) {
            $criticalUnresolved = count(array_filter($notes, fn ($n) => ! $n['resolved'] && in_array($n['severity'], ['critical', 'major'], true)));
            $signoffGuardReason = $criticalUnresolved > 0
                ? "Terdapat {$unresolvedNotes} catatan berkategori Kritis/Major yang belum diselesaikan oleh Layout Editor."
                : "Terdapat {$unresolvedNotes} catatan koreksi yang belum diselesaikan oleh Layout Editor.";
        } elseif ($unverifiedNotes > 0) {
            $signoffGuardReason = "Terdapat {$unverifiedNotes} perbaikan dari Layout Editor yang belum Anda verifikasi.";
        }

        // Penulis Korespondensi & Editor
        $author = $sub->correspondingAuthor?->contributor ?? $sub->author;
        $authorName = $author?->full_name ?? $author?->name ?? 'Penulis Naskah';
        $authorEmail = $author?->email ?? 'author@unhas.ac.id';
        $affiliation = $author?->affiliation ?? 'Universitas Hasanuddin';

        $layoutEditorName = $sub->activeLayoutAssignment?->layoutEditor?->name
            ?? $sub->activeLayoutAssignment?->layoutEditor?->full_name
            ?? 'Rahmat Hidayat, S.Kom.';

        // Status Penugasan
        $isCompleted = ($assignment?->status === 'completed' || $activeGalley?->stage === 'approved');
        $status = 'in_review';
        $statusLabel = 'Verifikasi Draf Galai (v'.($activeGalley->version ?? 1).')';
        $statusBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200/80';

        if ($isCompleted) {
            $status = 'completed';
            $statusLabel = 'Pemeriksaan Selesai & Disetujui';
            $statusBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
        } elseif ($escalationData['has_active']) {
            $status = 'escalated';
            $statusLabel = 'Dalam Eskalasi Redaksi';
            $statusBadgeClass = 'bg-purple-50 text-purple-800 border-purple-200/80';
        }

        // Active Galley Information
        $galleyFile = $activeGalley?->file;
        $activeGalleyData = [
            'id' => (string) ($activeGalley?->id ?? 'GAL-001'),
            'version' => (int) ($activeGalley?->version ?? 1),
            'version_label' => 'Versi '.($activeGalley?->version ?? 1).' ('.($activeGalley && $activeGalley->version > 1 ? 'Revisi' : 'Draf Awal').')',
            'file_name' => $galleyFile?->original_name ?? "{$sub->submission_number}_Galley_v".($activeGalley->version ?? 1).'.pdf',
            'size_formatted' => $galleyFile ? $this->formatBytes($galleyFile->byte_size) : '3.8 MB',
            'checksum_sha256' => (string) ($activeGalley?->checksum_sha256 ?? '9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b'),
            'download_url' => route('proofreader.galleys.download', $activeGalley?->id ?? $sub->submission_number),
            'uploaded_at' => $activeGalley?->created_at ? $activeGalley->created_at->translatedFormat('d M Y, H:i').' WITA' : 'Baru saja',
        ];

        // Copyedit Reference File
        $copyeditFile = FileEntry::where('storage_path', 'like', "%{$sub->id}%")
            ->where('original_name', 'like', '%.docx')
            ->latest()
            ->first();

        if (! $copyeditFile) {
            $subFile = SubmissionFile::where('submission_id', $sub->id)->latest()->first();
            $copyeditFile = $subFile?->file;
        }

        $copyeditReference = [
            'id' => (string) ($copyeditFile?->id ?? 'FILE-CE-FINAL'),
            'file_name' => $copyeditFile?->original_name ?? "{$sub->submission_number}_Copyedited_Final.docx",
            'size_formatted' => $copyeditFile ? $this->formatBytes($copyeditFile->byte_size) : '642 KB',
            'checksum_sha256' => (string) ($copyeditFile?->checksum_sha256 ?? '3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d'),
            'download_url' => route('proofreader.files.download', $copyeditFile?->id ?? $sub->submission_number),
            'description' => 'Naskah teks bersih hasil copyediting final untuk rujukan verifikasi tata letak teks.',
        ];

        // Timeline
        $timeline = [];
        if ($activeEscalation) {
            $timeline[] = [
                'title' => 'Tiket Eskalasi Diajukan ke Section Editor',
                'actor' => 'Proofreader',
                'date' => $activeEscalation->created_at ? $activeEscalation->created_at->translatedFormat('d M Y, H:i').' WITA' : 'Baru saja',
                'description' => 'Ketidaksesuaian naskah: "'.$activeEscalation->reason.'"',
                'badge_class' => 'bg-purple-100 text-purple-700',
            ];
        }
        if ($isCompleted) {
            $timeline[] = [
                'title' => 'Persetujuan Proofreader Selesai (Sign-off)',
                'actor' => 'Proofreader',
                'date' => now()->translatedFormat('d M Y, H:i').' WITA',
                'description' => 'Telah memverifikasi seluruh catatan koreksi dan menyetujui kelaikan galai.',
                'badge_class' => 'bg-emerald-100 text-emerald-700',
            ];
        }
        if ($activeGalley && $activeGalley->version > 1) {
            $timeline[] = [
                'title' => 'Galai Revisi v'.$activeGalley->version.' Diunggah',
                'actor' => $layoutEditorName.' (Layout Editor)',
                'date' => $activeGalley->created_at->translatedFormat('d M Y, H:i').' WITA',
                'description' => 'Telah menindaklanjuti catatan koreksi dan memperbarui draf galai PDF.',
                'badge_class' => 'bg-blue-100 text-blue-700',
            ];
        }
        if ($assignment) {
            $timeline[] = [
                'title' => 'Penugasan Bukti Baca Resmi Diterbitkan',
                'actor' => 'Sistem OJS BRIDA',
                'date' => $assignment->assigned_at ? $assignment->assigned_at->translatedFormat('d M Y, H:i').' WITA' : 'Baru saja',
                'description' => 'Naskah dialokasikan untuk pemeriksaan bukti baca galai.',
                'badge_class' => 'bg-gray-100 text-gray-700',
            ];
        }

        return [
            'id' => $sub->submission_number,
            'number' => $sub->submission_number,
            'assignment_code' => '#PR-'.substr(strtoupper(hash('sha256', $sub->id)), 0, 10),
            'title' => $sub->title,
            'section_title' => $sub->section?->title ?? 'Kebijakan Publik',
            'corresponding_author' => [
                'name' => $authorName,
                'email' => $authorEmail,
                'affiliation' => $affiliation,
            ],
            'section_editor' => 'Prof. Hamka, Ph.D. (Editor Bidang)',
            'layout_editor' => $layoutEditorName,
            'due_at' => $assignment?->due_at ? $assignment->due_at->translatedFormat('d M Y') : '30 Okt 2026',
            'assigned_at' => $assignment?->assigned_at ? $assignment->assigned_at->translatedFormat('d M Y, H:i').' WITA' : '21 Okt 2026, 10:15 WITA',
            'is_urgent' => $assignment?->due_at ? $assignment->due_at->isPast() || $assignment->due_at->isToday() : false,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'is_completed' => $isCompleted,
            'active_galley' => $activeGalleyData,
            'copyedit_reference' => $copyeditReference,
            'notes' => $notes,
            'summary' => [
                'total_notes' => $totalNotes,
                'unresolved_notes' => $unresolvedNotes,
                'resolved_notes' => $resolvedNotes,
                'can_signoff' => $canSignoff,
                'signoff_guard_reason' => $signoffGuardReason,
            ],
            'escalation' => $escalationData,
            'activity_timeline' => $timeline,
        ];
    }

    /**
     * Format byte size menjadi representasi terbaca manusia (KB, MB).
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Kumpulan data naskah uji coba realistis untuk ruang kerja proofreading.
     *
     * @return array<string, array<string, mixed>>
     */
    private function registry(): array
    {
        return [
            'SUB-2026-00106' => [
                'id' => 'SUB-2026-00106',
                'number' => 'SUB-2026-00106',
                'assignment_code' => '#PR-01JARK949Y',
                'title' => 'Strategi Pengembangan Destinasi Wisata Sejarah Benteng Somba Opu Berbasis Smart Tourism',
                'section_title' => 'Inovasi Pelayanan Publik & Pariwisata',
                'corresponding_author' => [
                    'name' => 'Dr. Andi Nurul Hidayah, M.Si.',
                    'email' => 'andi.nurul@unm.ac.id',
                    'affiliation' => 'Fakultas Ilmu Sosial dan Politik, Universitas Negeri Makassar',
                ],
                'section_editor' => 'Dr. Ir. Rusman, M.T. (Editor Bidang)',
                'layout_editor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                'due_at' => '28 Okt 2026',
                'assigned_at' => '21 Okt 2026, 10:15 WITA',
                'is_urgent' => false,
                'status' => 'in_review',
                'status_label' => 'Verifikasi Revisi Galai (v2)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'is_completed' => false,
                'active_galley' => [
                    'id' => 'GAL-01JARK-02',
                    'version' => 2,
                    'version_label' => 'Versi 2 (Revisi)',
                    'file_name' => 'SUB-2026-00106_Galley_v2.pdf',
                    'size_formatted' => '3.8 MB',
                    'checksum_sha256' => '9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b',
                    'download_url' => route('proofreader.galleys.download', 'SUB-2026-00106'),
                    'uploaded_at' => '24 Okt 2026, 14:30 WITA',
                ],
                'copyedit_reference' => [
                    'id' => 'FILE-CE-FINAL-106',
                    'file_name' => 'SUB-2026-00106_Copyedited_Final.docx',
                    'size_formatted' => '642 KB',
                    'checksum_sha256' => '3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d',
                    'download_url' => route('proofreader.files.download', 'SUB-2026-00106'),
                    'description' => 'Naskah teks bersih hasil copyediting final untuk rujukan verifikasi tata letak teks.',
                ],
                'notes' => [
                    [
                        'id' => 'NOTE-01JARK-01',
                        'page' => 2,
                        'category' => 'typo',
                        'category_label' => 'Tipografi / Ejaan',
                        'severity' => 'minor',
                        'severity_badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'severity_label' => 'Minor',
                        'description' => 'Kesalahan penulisan kata "pariwista" seharusnya "pariwisata" pada alinea kedua paragraf pembuka.',
                        'resolved' => true,
                        'resolution' => 'Sudah diperbaiki pada draf galai v2 sesuai teks copyediting final.',
                        'resolved_at' => '24 Okt 2026, 14:15 WITA',
                        'verified' => true,
                    ],
                    [
                        'id' => 'NOTE-01JARK-02',
                        'page' => 4,
                        'category' => 'layout',
                        'category_label' => 'Tata Letak / Format',
                        'severity' => 'major',
                        'severity_badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'severity_label' => 'Major',
                        'description' => 'Keterangan Gambar 3 (Peta Kawasan Benteng Somba Opu) terpotong margin bawah kolom sebelah kanan.',
                        'resolved' => true,
                        'resolution' => 'Ukuran skala gambar 3 diperkecil 10% dan posisi keterangan dipindahkan ke baris penuh.',
                        'resolved_at' => '24 Okt 2026, 14:20 WITA',
                        'verified' => false,
                    ],
                    [
                        'id' => 'NOTE-01JARK-03',
                        'page' => 7,
                        'category' => 'reference',
                        'category_label' => 'Sitasi & Pustaka',
                        'severity' => 'critical',
                        'severity_badge' => 'bg-red-50 text-red-700 border-red-200',
                        'severity_label' => 'Kritis',
                        'description' => 'Sitasi (BPS Kota Makassar, 2025) pada halaman 3 belum tercantum dalam daftar pustaka.',
                        'resolved' => false,
                        'resolution' => null,
                        'resolved_at' => null,
                        'verified' => false,
                    ],
                ],
                'summary' => [
                    'total_notes' => 3,
                    'unresolved_notes' => 1,
                    'resolved_notes' => 2,
                    'can_signoff' => false,
                    'signoff_guard_reason' => 'Terdapat 1 catatan berkategori Kritis yang belum diselesaikan oleh Layout Editor.',
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status_label' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Galai Revisi v2 Diunggah',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '24 Okt 2026, 14:30 WITA',
                        'description' => 'Telah menindaklanjuti 2 catatan koreksi dan mengunggah berkas PDF galai revisi v2.',
                        'badge_class' => 'bg-blue-100 text-blue-700',
                    ],
                    [
                        'title' => 'Catatan Koreksi Bukti Baca Dikirim',
                        'actor' => 'Dra. Sitti Aminah, M.Hum. (Proofreader)',
                        'date' => '22 Okt 2026, 16:45 WITA',
                        'description' => 'Mengirimkan 3 catatan koreksi terkait typo, layout gambar, dan kelengkapan daftar pustaka.',
                        'badge_class' => 'bg-amber-100 text-amber-700',
                    ],
                    [
                        'title' => 'Penugasan Bukti Baca Resmi Diterbitkan',
                        'actor' => 'Sistem OJS BRIDA',
                        'date' => '21 Okt 2026, 10:15 WITA',
                        'description' => 'Naskah dialokasikan kepada Dra. Sitti Aminah, M.Hum. dengan batas waktu 28 Okt 2026.',
                        'badge_class' => 'bg-gray-100 text-gray-700',
                    ],
                ],
            ],
            'SUB-2026-00107' => [
                'id' => 'SUB-2026-00107',
                'number' => 'SUB-2026-00107',
                'assignment_code' => '#PR-01JARL018Z',
                'title' => 'Analisis Spasial Kerentanan Banjir Rob Kawasan Pesisir Kota Makassar Menggunakan Sentinel-1A',
                'section_title' => 'Kebijakan Lingkungan & Mitigasi Bencana',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                    'email' => 'saleh.pallu@unhas.ac.id',
                    'affiliation' => 'Departemen Teknik Sipil, Universitas Hasanuddin',
                ],
                'section_editor' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'due_at' => '27 Okt 2026',
                'assigned_at' => '23 Okt 2026, 09:00 WITA',
                'is_urgent' => true,
                'status' => 'in_review',
                'status_label' => 'Perlu Pemeriksaan Galai (v1)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'is_completed' => false,
                'active_galley' => [
                    'id' => 'GAL-01JARL-01',
                    'version' => 1,
                    'version_label' => 'Versi 1 (Draf Awal)',
                    'file_name' => 'SUB-2026-00107_Galley_v1.pdf',
                    'size_formatted' => '5.2 MB',
                    'checksum_sha256' => 'b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2',
                    'download_url' => route('proofreader.galleys.download', 'SUB-2026-00107'),
                    'uploaded_at' => '23 Okt 2026, 08:30 WITA',
                ],
                'copyedit_reference' => [
                    'id' => 'FILE-CE-FINAL-107',
                    'file_name' => 'SUB-2026-00107_Copyedited_Final.docx',
                    'size_formatted' => '820 KB',
                    'checksum_sha256' => '4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c',
                    'download_url' => route('proofreader.files.download', 'SUB-2026-00107'),
                    'description' => 'Naskah teks bersih rujukan copyediting final dengan rumus dan persamaan hidro-oseanografi.',
                ],
                'notes' => [],
                'summary' => [
                    'total_notes' => 0,
                    'unresolved_notes' => 0,
                    'resolved_notes' => 0,
                    'can_signoff' => true,
                    'signoff_guard_reason' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status_label' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Galai PDF v1 Diterbitkan',
                        'actor' => 'Rahmat Hidayat, S.Kom. (Layout Editor)',
                        'date' => '23 Okt 2026, 08:30 WITA',
                        'description' => 'Draf galai format 2-kolom telah rampung dan siap diperiksa bukti baca.',
                        'badge_class' => 'bg-blue-100 text-blue-700',
                    ],
                ],
            ],
            'SUB-2026-00103' => [
                'id' => 'SUB-2026-00103',
                'number' => 'SUB-2026-00103',
                'assignment_code' => '#PR-01JARJ821X',
                'title' => 'Persepsi Masyarakat Terhadap Penerapan Transportasi Publik Teman Bus di Kawasan Aglomerasi Mamminasata',
                'section_title' => 'Teknologi & Tata Kelola Perkotaan',
                'corresponding_author' => [
                    'name' => 'Ir. Muhammad Arsyad, Ph.D.',
                    'email' => 'arsyad@unhas.ac.id',
                    'affiliation' => 'Fakultas Teknik, Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'due_at' => 'Selesai 24 Okt 2026',
                'assigned_at' => '18 Okt 2026, 14:00 WITA',
                'is_urgent' => false,
                'status' => 'completed',
                'status_label' => 'Proofreading Selesai & Disetujui',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                'is_completed' => true,
                'active_galley' => [
                    'id' => 'GAL-01JARJ-02',
                    'version' => 2,
                    'version_label' => 'Versi 2 (Final Disetujui)',
                    'file_name' => 'SUB-2026-00103_Galley_v2_Approved.pdf',
                    'size_formatted' => '4.1 MB',
                    'checksum_sha256' => 'c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1',
                    'download_url' => route('proofreader.galleys.download', 'SUB-2026-00103'),
                    'uploaded_at' => '24 Okt 2026, 11:20 WITA',
                ],
                'copyedit_reference' => [
                    'id' => 'FILE-CE-FINAL-103',
                    'file_name' => 'SUB-2026-00103_Copyedited_Final.docx',
                    'size_formatted' => '710 KB',
                    'checksum_sha256' => '5a4b3c2d1e0f9a8b9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f',
                    'download_url' => route('proofreader.files.download', 'SUB-2026-00103'),
                    'description' => 'Naskah teks bersih copyediting final disetujui penulis.',
                ],
                'notes' => [
                    [
                        'id' => 'NOTE-01JARJ-01',
                        'page' => 1,
                        'category' => 'typo',
                        'category_label' => 'Tipografi / Ejaan',
                        'severity' => 'minor',
                        'severity_badge' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'severity_label' => 'Minor',
                        'description' => 'Perbaikan penulisan singkatan BRT Mamminasata pada kata kunci.',
                        'resolved' => true,
                        'resolution' => 'Sudah diperbaiki pada draf galai v2.',
                        'resolved_at' => '24 Okt 2026, 10:45 WITA',
                        'verified' => true,
                    ],
                ],
                'summary' => [
                    'total_notes' => 1,
                    'unresolved_notes' => 0,
                    'resolved_notes' => 1,
                    'can_signoff' => true,
                    'signoff_guard_reason' => null,
                ],
                'escalation' => [
                    'has_active' => false,
                    'ticket_id' => null,
                    'reason' => null,
                    'status_label' => null,
                ],
                'activity_timeline' => [
                    [
                        'title' => 'Proofreading Selesai & Disetujui',
                        'actor' => 'Dra. Sitti Aminah, M.Hum. (Proofreader)',
                        'date' => '24 Okt 2026, 16:00 WITA',
                        'description' => 'Telah memverifikasi seluruh perbaikan dan memberikan persetujuan sign-off.',
                        'badge_class' => 'bg-emerald-100 text-emerald-700',
                    ],
                ],
            ],
        ];
    }
}
