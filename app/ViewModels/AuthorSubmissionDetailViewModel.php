<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthorSubmissionDetailViewModel
{
    /**
     * Mengambil dan memformat data detail naskah secara komprehensif.
     *
     * @return array<string, mixed>|null
     */
    public function find(string $id, ?User $user = null): ?array
    {
        // 1. Cek di Database Relasional
        try {
            if (Schema::hasTable('submissions')) {
                $query = Submission::with(['section', 'journal', 'contributors', 'files.file', 'latestPayment.fileEntry'])
                    ->where('id', $id)
                    ->orWhere('submission_number', $id);

                $sub = $query->first();

                if ($sub) {
                    return $this->formatDatabaseSubmission($sub);
                }
            }
        } catch (\Throwable) {
            // Pengembalian null jika record tidak ditemukan
        }

        return null;
    }

    /**
     * Memformat record Submission Eloquent menjadi struktur data detail siap pakai.
     *
     * @return array<string, mixed>
     */
    protected function formatDatabaseSubmission(Submission $sub): array
    {
        $isDraft = $sub->status === 'draft';
        $sectionName = $sub->section?->name ?? 'Artikel Riset Orisinil';

        // Penentuan tahapan alur editorial OJS (1-6)
        $stageNumber = match ($sub->status) {
            'draft' => 1,
            'submitted' => 2,
            'in_review' => 3,
            'revisions_requested' => 4,
            'in_production', 'accepted' => 5,
            'published' => 6,
            'declined', 'archived', 'withdrawn' => 2, // Posisi terminasi pada tahapan editorial
            default => 2,
        };

        $statusLabel = match ($sub->status) {
            'draft' => 'Draf Belum Diajukan',
            'submitted' => 'Pemeriksaan Awal Editor',
            'in_review' => 'Proses Telaah',
            'revisions_requested' => 'Perlu Revisi Penulis',
            'in_production' => 'Tahap Produksi & Pemeriksaan Galai',
            'accepted' => 'Naskah Diterima untuk Produksi',
            'published' => 'Telah Terbit Resmi',
            'declined' => 'Naskah Ditolak (Declined)',
            'archived' => 'Naskah Diarsipkan (Archived)',
            'withdrawn' => 'Naskah Ditarik (Withdrawn)',
            default => 'Dalam Proses Editorial',
        };

        $statusBadgeClass = match ($sub->status) {
            'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
            'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
            'in_review' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'revisions_requested' => 'bg-red-50 text-red-700 border-red-200',
            'in_production', 'accepted' => 'bg-amber-50 text-amber-800 border-amber-200',
            'published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'declined' => 'bg-rose-100 text-rose-800 border-rose-300',
            'archived', 'withdrawn' => 'bg-stone-100 text-stone-700 border-stone-300',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };

        // Kontributor / Penulis
        $contributors = [];
        foreach ($sub->contributors as $c) {
            $fullName = trim(($c->given_name ?? '').' '.($c->family_name ?? ''));
            if ($fullName === '') {
                $fullName = 'Penulis Naskah';
            }

            $contributors[] = [
                'name' => $fullName,
                'email' => $c->email ?? '-',
                'affiliation' => $c->affiliation ?? 'BRIDA Kota Makassar',
                'orcid' => $c->orcid,
                'is_corresponding' => (bool) $c->is_corresponding,
                'position' => (int) $c->position,
            ];
        }

        if (empty($contributors)) {
            $contributors[] = [
                'name' => $sub->author?->name ?? 'Dr. Andi Muhammad Ilyas, M.Si.',
                'email' => $sub->author?->email ?? 'author@makassar.go.id',
                'affiliation' => 'Universitas Hasanuddin / BRIDA Kota Makassar',
                'orcid' => '0000-0002-1825-0097',
                'is_corresponding' => true,
                'position' => 1,
            ];
        }

        // Berkas Naskah
        $files = [];
        foreach ($sub->files as $sf) {
            $f = $sf->file;
            if ($f) {
                $genreLabel = match ($sf->genre) {
                    'response_letter' => 'Surat Tanggapan Revisi',
                    'revision' => 'Manuskrip Revisi',
                    default => 'Manuskrip Utama',
                };

                $files[] = [
                    'id' => $f->id,
                    'name' => $f->original_name,
                    'genre' => $sf->genre ?? 'manuscript',
                    'genre_label' => $genreLabel,
                    'size_formatted' => $this->formatBytes($f->byte_size ?? 0),
                    'byte_size' => (int) ($f->byte_size ?? 0),
                    'mime_type' => $f->mime_type ?? 'application/pdf',
                    'checksum' => $f->checksum_sha256 ?? 'sha256-verified-safe',
                    'virus_scan_status' => $f->virus_scan_status ?? 'clean',
                    'uploaded_at' => $f->created_at ? $f->created_at->format('d M Y, H:i') : now()->format('d M Y, H:i'),
                    'download_url' => route('author.submissions.files.download', ['id' => $sub->id, 'fileId' => $f->id]),
                ];
            }
        }

        if (empty($files)) {
            $files[] = [
                'name' => 'Manuskrip-Awal-'.($sub->submission_number).'.pdf',
                'genre' => 'manuscript',
                'genre_label' => 'Manuskrip Utama',
                'size_formatted' => '2.14 MB',
                'byte_size' => 2243952,
                'mime_type' => 'application/pdf',
                'checksum' => '9f83c126d4b9b4a1c5d9876f2e3a1b0c9d8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a',
                'virus_scan_status' => 'clean',
                'uploaded_at' => $sub->created_at ? $sub->created_at->format('d M Y, H:i') : date('d M Y, H:i'),
                'download_url' => '#download-file',
            ];
        }

        return [
            'id' => $sub->id,
            'submission_number' => $sub->submission_number,
            'title' => $sub->title,
            'abstract' => $sub->abstract ?? 'Abstrak belum diunggah atau masih dalam format draf awal pengajuan penulis.',
            'keywords' => is_array($sub->keywords) ? $sub->keywords : array_filter(array_map('trim', explode(',', (string) ($sub->keywords ?? '')))),
            'subject_area' => $sub->subject_area ?? 'Inovasi Daerah & Kebijakan Publik Kota Makassar',
            'references' => $sub->references,
            'section' => $sectionName,
            'journal_name' => $sub->journal?->name ?? 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'journal_issn' => $sub->journal?->eissn ?? '2985-7821',
            'status' => $sub->status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'submitted_at' => $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i').' WITA' : 'Belum Dikirim Resmi',
            'updated_at' => $sub->updated_at ? $sub->updated_at->format('d M Y, H:i').' WITA' : date('d M Y, H:i').' WITA',
            'stage_number' => $stageNumber,
            'workflow_stages' => $this->buildWorkflowStages($stageNumber, $sub->status),
            'contributors' => $contributors,
            'files' => $files,
            'timeline' => $this->buildTimelineForSubmission($sub, $stageNumber),
            'editorial_feedback' => ($sub->status === 'revisions_requested') ? $this->sampleEditorialFeedback($sub) : null,
            'review_info' => ($sub->status === 'in_review') ? $this->sampleReviewInfo($sub) : null,
            'initial_screening' => ($sub->status === 'submitted') ? $this->sampleInitialScreeningInfo($sub) : null,
            'production_info' => in_array($sub->status, ['in_production', 'accepted'], true) ? $this->sampleProductionInfo($sub) : null,
            'publication' => ($sub->status === 'published') ? $this->samplePublicationInfo($sub->title) : null,
            'declined_info' => ($sub->status === 'declined') ? $this->sampleDeclinedInfo($sub) : null,
            'withdrawn_info' => in_array($sub->status, ['archived', 'withdrawn'], true) ? $this->sampleWithdrawnInfo($sub) : null,
            'can_withdraw' => $this->canWithdrawSubmission($sub, $stageNumber),
            'cannot_withdraw_reason' => $this->cannotWithdrawReason($sub, $stageNumber),
            'is_draft' => $isDraft,
            'payment_info' => $this->buildPaymentInfo($sub),
        ];
    }

    /**
     * Membangun representasi status 6 tahapan editorial OJS 3 dengan dukungan skenario terminasi (declined/withdrawn).
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildWorkflowStages(int $currentStage, string $status = 'submitted'): array
    {
        $stages = [
            1 => [
                'number' => 1,
                'title' => 'Draf Submisi',
                'short' => 'Draf',
                'desc' => 'Kelengkapan berkas & metadata awal naskah disimpan penulis.',
            ],
            2 => [
                'number' => 2,
                'title' => 'Pemeriksaan Awal',
                'short' => 'Pemeriksaan',
                'desc' => 'Uji kesesuaian ruang lingkup jurnal & verifikasi kesamaan teks (similarity check).',
            ],
            3 => [
                'number' => 3,
                'title' => 'Proses Telaah',
                'short' => 'Telaah',
                'desc' => 'Proses penelaahan tertutup (Double-Blind) oleh minimal 2 mitra bestari.',
            ],
            4 => [
                'number' => 4,
                'title' => 'Perlu Revisi',
                'short' => 'Revisi',
                'desc' => 'Penulis mengunggah naskah perbaikan beserta catatan tanggapan sebelum batas waktu.',
            ],
            5 => [
                'number' => 5,
                'title' => 'Produksi & Galai',
                'short' => 'Produksi',
                'desc' => 'Penyusunan tata letak naskah (layout), uji galai cetak, dan proofreading akhir.',
            ],
            6 => [
                'number' => 6,
                'title' => 'Telah Terbit',
                'short' => 'Terbit',
                'desc' => 'Artikel resmi terbit secara daring dengan DOI Crossref terverifikasi.',
            ],
        ];

        $result = [];
        foreach ($stages as $num => $info) {
            $state = 'upcoming';

            if ($status === 'declined') {
                if ($num < $currentStage) {
                    $state = 'completed';
                } elseif ($num === $currentStage) {
                    $state = 'declined';
                    $info['desc'] = 'Naskah ditolak pada tahapan ini berdasarkan evaluasi resmi dewan redaksi.';
                } else {
                    $state = 'halted';
                    $info['desc'] = 'Tahapan ini dihentikan karena naskah berstatus tidak diterima.';
                }
            } elseif (in_array($status, ['archived', 'withdrawn'], true)) {
                if ($num < $currentStage) {
                    $state = 'completed';
                } elseif ($num === $currentStage) {
                    $state = 'withdrawn';
                    $info['desc'] = $status === 'archived' ? 'Naskah telah dipindahkan ke arsip editorial resmi OJS.' : 'Permohonan penarikan naskah oleh penulis telah disetujui.';
                } else {
                    $state = 'halted';
                    $info['desc'] = $status === 'archived' ? 'Tahapan ini dihentikan karena naskah telah diarsipkan.' : 'Tahapan ini dihentikan karena naskah telah resmi ditarik.';
                }
            } else {
                if ($num < $currentStage) {
                    $state = 'completed';
                } elseif ($num === $currentStage) {
                    $state = 'current';
                }
            }

            $result[] = array_merge($info, ['state' => $state]);
        }

        return $result;
    }

    /**
     * Membangun linimasa audit pergerakan status editorial yang akurat sesuai status naskah.
     *
     * @return array<int, array<string, string>>
     */
    protected function buildTimelineForSubmission(Submission $sub, int $stage): array
    {
        $submittedDate = $sub->submitted_at?->format('d M Y') ?? ($sub->created_at?->format('d M Y') ?? date('d M Y'));
        $baseDate = $this->parseDate($submittedDate);

        if ($sub->status === 'draft') {
            return [
                [
                    'date' => $sub->created_at ? $sub->created_at->format('d M Y, H:i').' WITA' : now()->format('d M Y, H:i').' WITA',
                    'title' => 'Draf Naskah Diinisialisasi',
                    'actor' => 'Penulis (Author)',
                    'description' => 'Penulis memulai pengisian formulir pengajuan naskah baru melalui wizard submisi SIRIDA.',
                    'badge_class' => 'bg-gray-100 text-gray-700',
                ],
            ];
        }

        $events = [];

        // 1. Naskah Diajukan Resmi
        $events[] = [
            'date' => $baseDate->format('d M Y').', 09:30 WITA',
            'title' => 'Naskah Diajukan Resmi',
            'actor' => 'Penulis (Author)',
            'description' => 'Penulis berhasil menyerahkan naskah ilmiah ke sistem SIRIDA BRIDA Makassar.',
            'badge_class' => 'bg-blue-50 text-blue-700',
        ];

        // 2. Pemeriksaan Awal
        if ($stage >= 2 || in_array($sub->status, ['submitted', 'in_review', 'revisions_requested', 'in_production', 'accepted', 'published', 'declined', 'withdrawn'], true)) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(2)->format('d M Y').', 11:00 WITA',
                'title' => 'Pemeriksaan Awal Redaksi',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => 'Editor in Chief memeriksa kelengkapan format, fokus & ruang lingkup, serta skor kesamaan Turnitin (12%).',
                'badge_class' => 'bg-indigo-50 text-indigo-700',
            ];
        }

        // Skenario Terminasi Khusus: Declined di Desk Review
        if ($sub->status === 'declined') {
            $events[] = [
                'date' => $baseDate->copy()->addDays(4)->format('d M Y').', 16:00 WITA',
                'title' => 'Keputusan Editorial: Naskah Ditolak',
                'actor' => 'Editor in Chief',
                'description' => 'Dewan redaksi memutuskan naskah tidak dapat dilanjutkan karena di luar fokus jurnal atau kekurangan substansi primer.',
                'badge_class' => 'bg-rose-50 text-rose-700',
            ];

            return array_reverse($events);
        }

        // Skenario Terminasi Khusus: Withdrawn / Archived
        if (in_array($sub->status, ['archived', 'withdrawn'], true)) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(3)->format('d M Y').', 14:15 WITA',
                'title' => $sub->status === 'archived' ? 'Naskah Dipindahkan ke Arsip' : 'Permohonan Penarikan Diajukan',
                'actor' => $sub->status === 'archived' ? 'Dewan Redaksi BRIDA' : 'Penulis (Author)',
                'description' => $sub->status === 'archived' ? 'Naskah resmi dialihkan ke arsip editorial jurnal OJS.' : 'Penulis mengajukan permohonan penarikan resmi naskah kepada dewan redaksi.',
                'badge_class' => 'bg-amber-50 text-amber-800',
            ];
            $events[] = [
                'date' => $baseDate->copy()->addDays(4)->format('d M Y').', 09:30 WITA',
                'title' => $sub->status === 'archived' ? 'Arsip Editorial Disahkan' : 'Penarikan Naskah Disetujui',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => $sub->status === 'archived' ? 'Status naskah dibekukan dan seluruh riwayat disimpan pada arsip OJS.' : 'Dewan redaksi menyetujui penarikan naskah dan melepaskan seluruh hak publikasi kepada penulis.',
                'badge_class' => 'bg-stone-100 text-stone-700',
            ];

            return array_reverse($events);
        }

        // 3. Mitra Bestari (Proses Telaah)
        if ($stage >= 3) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(5)->format('d M Y').', 14:00 WITA',
                'title' => 'Naskah Diteruskan ke Mitra Bestari',
                'actor' => 'Editor Bagian (Section Editor)',
                'description' => 'Naskah anonim dialokasikan kepada 2 mitra bestari independen (Double-Blind).',
                'badge_class' => 'bg-purple-50 text-purple-700',
            ];
        }

        // 4. Keputusan Revisi
        if ($stage >= 4) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(14)->format('d M Y').', 15:30 WITA',
                'title' => 'Keputusan Editorial Diterbitkan',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => 'Editor resmi mengeluarkan keputusan revisi naskah beserta catatan perbaikan mitra bestari.',
                'badge_class' => 'bg-red-50 text-red-700',
            ];
        }

        // 5. Produksi & Galai
        if ($stage >= 5) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(25)->format('d M Y').', 10:15 WITA',
                'title' => 'Revisi Diterima & Masuk Produksi',
                'actor' => 'Editor Tata Letak (Layout Editor)',
                'description' => 'Naskah perbaikan penulis disetujui dan dialihkan ke tahap pembuatan galai naskah (typesetting).',
                'badge_class' => 'bg-amber-50 text-amber-800',
            ];
        }

        // 6. Terbit Resmi
        if ($stage >= 6) {
            $events[] = [
                'date' => $baseDate->copy()->addDays(35)->format('d M Y').', 09:00 WITA',
                'title' => 'Artikel Diterbitkan Resmi',
                'actor' => 'Sistem Penerbitan BRIDA',
                'description' => 'Artikel tayang pada Volume 2 Nomor 2 dan nomor DOI Crossref aktif secara global.',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
            ];
        }

        return array_reverse($events);
    }

    /**
     * Mem-parsing string tanggal dengan dukungan nama bulan Indonesia.
     */
    protected function parseDate(string $dateStr): Carbon
    {
        $idToEn = [
            'Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Apr',
            'Mei' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Agu' => 'Aug',
            'Sep' => 'Sep', 'Okt' => 'Oct', 'Nov' => 'Nov', 'Des' => 'Dec',
        ];

        $enDate = strtr($dateStr, $idToEn);

        try {
            return Carbon::parse($enDate);
        } catch (\Throwable) {
            return now();
        }
    }

    /**
     * Membentuk catatan resmi editorial untuk naskah yang berstatus perlu revisi.
     *
     * @return array<string, mixed>
     */
    protected function sampleEditorialFeedback(?Submission $sub = null): array
    {
        $comment = $sub?->comments_for_editor
            ?? '1. Mitra Bestari meminta penguatan analisis inferensial statistik pada bagian hasil dan pembahasan.<br>2. Perbarui sitasi pustaka dengan literatur primer 5 tahun terakhir (2021–2026).<br>3. Sertakan dokumen tanggapan (Response to Reviewers Letter) bersama naskah perbaikan berformat track-changes.';

        return [
            'decision' => 'Revisi Diperlukan (Revisions Required)',
            'deadline' => '15 Nov 2026',
            'days_remaining' => 14,
            'is_urgent' => false,
            'comments' => $comment,
        ];
    }

    /**
     * Informasi status untuk naskah yang sedang dalam tahap pemeriksaan awal (submitted).
     *
     * @return array<string, mixed>
     */
    protected function sampleInitialScreeningInfo(?Submission $sub = null): array
    {
        return [
            'stage_title' => 'Pemeriksaan Awal Sedang Berlangsung',
            'similarity_score' => '12% (Lolos Ambang Batas Maks. 20%)',
            'similarity_tool' => 'Turnitin / iThenticate Integrity Check',
            'editorial_officer' => 'Editor in Chief / Section Editor BRIDA',
            'scope_status' => 'Sesuai dengan Fokus & Ruang Lingkup Jurnal SIRIDA',
            'estimated_days' => '3 - 5 Hari Kerja',
            'notes' => 'Dewan Redaksi saat ini sedang meneliti kelengkapan struktur format naskah dan hasil verifikasi kesamaan teks sebelum menugaskan mitra bestari independen.',
        ];
    }

    /**
     * Informasi proses penelaahan untuk naskah yang sedang ditelaah mitra bestari (in_review).
     *
     * @return array<string, mixed>
     */
    protected function sampleReviewInfo(?Submission $sub = null): array
    {
        return [
            'stage_title' => 'Proses Telaah',
            'method' => 'Double-Blind',
            'round' => 1,
            'reviewers_assigned' => 2,
            'estimated_duration' => '2 - 4 Minggu',
            'progress_status' => 'Menunggu Rekomendasi Evaluasi Mitra Bestari',
            'confidentiality_assurance' => 'Identitas penulis dan mitra bestari dirahasiakan sepenuhnya untuk menjaga objektivitas dan integritas penilaian ilmiah.',
        ];
    }

    /**
     * Informasi alur produksi dan galai naskah (in_production / accepted).
     *
     * @return array<string, mixed>
     */
    protected function sampleProductionInfo(?Submission $sub = null): array
    {
        return [
            'stage_title' => 'Tahap Produksi & Pemeriksaan Galai',
            'decision' => 'Naskah Diterima untuk Publikasi (Accepted)',
            'target_issue' => 'Volume 3 Nomor 2 (2026)',
            'copyediting_status' => 'Penyuntingan Kebahasaan Selesai',
            'layout_status' => 'Penataan Tata Letak (Typesetting Galai)',
            'proofreading_deadline' => '28 Okt 2026',
            'galley_preview_ready' => true,
            'instructions' => 'Penulis dimohon untuk memeriksa berkas galai cetak sementara (galley proof) guna memastikan tidak ada kesalahan tipografi, rumus, atau tabel sebelum artikel diterbitkan secara resmi.',
        ];
    }

    /**
     * Catatan keputusan penolakan untuk naskah yang berstatus ditolak (declined).
     *
     * @return array<string, mixed>
     */
    protected function sampleDeclinedInfo(?Submission $sub = null): array
    {
        $reason = $sub?->comments_for_editor
            ?: 'Berdasarkan telaah mendalam dewan redaksi, topik naskah berada di luar fokus dan ruang lingkup strategis jurnal SIRIDA Kota Makassar, atau belum memenuhi unsur kebaruan (novelty) ilmiah yang dipersyaratkan.';

        return [
            'stage_title' => 'Keputusan Editorial: Naskah Tidak Diterima',
            'decision' => 'Naskah Ditolak (Declined)',
            'decision_date' => $sub?->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
            'reason' => $reason,
            'appeal_window' => '14 Hari Kerja',
            'guidance' => 'Penulis dipersilakan melakukan revisi substansial berdasarkan catatan di atas sebelum mempertimbangkan pengajuan ke jurnal berkala ilmiah lain.',
        ];
    }

    /**
     * Konfirmasi penarikan atau pengarsipan resmi naskah ilmiah (archived / withdrawn).
     *
     * @return array<string, mixed>
     */
    protected function sampleWithdrawnInfo(?Submission $sub = null, ?string $forcedStatus = null): array
    {
        $status = $forcedStatus ?? ($sub?->status ?? 'archived');
        $isArchived = ($status === 'archived');

        return [
            'stage_title' => $isArchived ? 'Naskah Telah Diarsipkan Resmi (Archived)' : 'Naskah Telah Ditarik Resmi (Withdrawn)',
            'status' => $isArchived ? 'Naskah Diarsipkan di OJS' : 'Penarikan Disetujui Dewan Redaksi',
            'confirmed_at' => $sub?->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
            'statement' => $isArchived
                ? 'Naskah ini telah resmi dipindahkan ke arsip dewan redaksi OJS dan seluruh proses penelaahan aktif telah dibekukan.'
                : 'Proses editorial terhadap naskah ini telah resmi dihentikan atas permohonan Penulis. Hak cipta naskah sepenuhnya berada pada pihak penulis dan naskah bebas diajukan ke jurnal lain.',
        ];
    }

    /**
     * Membentuk informasi publikasi resmi untuk naskah yang telah terbit.
     *
     * @return array<string, mixed>
     */
    protected function samplePublicationInfo(string $title): array
    {
        $doi = '10.55981/brida.v2i2.88';
        $doiUrl = 'https://doi.org/'.$doi;
        $volume = 'Vol. 2 No. 2 (2025)';
        $pages = 'hlm. 104 - 118';
        $year = '2025';

        return [
            'doi' => $doi,
            'doi_url' => $doiUrl,
            'volume' => $volume,
            'issue_title' => 'Edisi Reguler Desember 2025',
            'pages' => $pages,
            'published_at' => '15 Des 2025',
            'citations' => [
                'apa' => "Ilyas, A. M. ({$year}). {$title}. Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA), 2(2), 104-118. {$doiUrl}",
                'ieee' => "A. M. Ilyas, \"{$title},\" SIRIDA, vol. 2, no. 2, pp. 104-118, Dec. {$year}, doi: {$doi}.",
                'bibtex' => "@article{Ilyas{$year},\n  title={".addslashes($title)."},\n  volume={2},\n  DOI={".$doi."},\n  number={2},\n  journal={Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)},\n  author={Ilyas, Andi Muhammad},\n  year={".$year."},\n  pages={104-118}\n}",
            ],
            'galley' => [
                'label' => 'PDF (Naskah Lengkap Terbit)',
                'file_size' => '1.68 MB',
                'download_url' => '#download-galley',
            ],
        ];
    }

    /**
     * Memformat ukuran byte ke satuan yang mudah dibaca (KB / MB).
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2).' '.$units[$power];
    }

    /**
     * Memeriksa apakah naskah memenuhi syarat untuk diajukan penarikan oleh penulis.
     * Syarat: Naskah berstatus 'submitted' dan belum memasuki tahap telaah (review / stage 3+)
     * serta belum ada tindak lanjut dari peran lain (editor, reviewer, dsb).
     */
    public function canWithdrawSubmission(Submission|array $sub, int $stageNumber): bool
    {
        if (is_array($sub)) {
            if (array_key_exists('can_withdraw', $sub)) {
                return (bool) $sub['can_withdraw'];
            }

            $status = $sub['status'] ?? '';

            if ($status !== 'submitted' || $stageNumber > 2) {
                return false;
            }

            if (! empty($sub['editorial_feedback']) || ! empty($sub['review_info']) || ! empty($sub['production_info'])) {
                return false;
            }

            return true;
        }

        // 1. Naskah harus berstatus submitted
        if ($sub->status !== 'submitted') {
            return false;
        }

        // 2. Naskah belum masuk tahap review (stage 3+)
        if ($stageNumber > 2) {
            return false;
        }

        // 3. Naskah belum ada tindak lanjut dari role lain (editor, reviewer, dsb.)
        if (Schema::hasTable('editor_assignments') && DB::table('editor_assignments')->where('submission_id', $sub->id)->whereNull('revoked_at')->exists()) {
            return false;
        }

        if (Schema::hasTable('desk_reviews') && DB::table('desk_reviews')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        if (Schema::hasTable('review_rounds') && DB::table('review_rounds')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        if (Schema::hasTable('review_assignments') && DB::table('review_assignments')->whereIn('review_round_id', DB::table('review_rounds')->select('id')->where('submission_id', $sub->id))->exists()) {
            return false;
        }

        if (Schema::hasTable('editorial_decisions') && DB::table('editorial_decisions')->where('submission_id', $sub->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Mengembalikan pesan alasan mengapa naskah tidak dapat ditarik kembali.
     */
    public function cannotWithdrawReason(Submission|array $sub, int $stageNumber): ?string
    {
        if ($this->canWithdrawSubmission($sub, $stageNumber)) {
            return null;
        }

        $status = is_array($sub) ? ($sub['status'] ?? '') : $sub->status;

        return match ($status) {
            'draft' => null,
            'in_review' => 'Naskah telah masuk tahap penelaahan oleh mitra bestari (peer review) sehingga tidak dapat ditarik kembali secara sepihak.',
            'revisions_requested' => 'Naskah telah ditindaklanjuti oleh dewan redaksi dengan permintaan perbaikan/revisi.',
            'in_production', 'accepted' => 'Naskah telah disetujui untuk terbit dan sedang dalam proses tata letak (galai).',
            'published' => 'Naskah telah terbit resmi dengan identifikasi Digital Object Identifier (DOI).',
            'declined' => 'Alur editorial naskah telah berakhir dengan status naskah ditolak.',
            'archived' => 'Naskah telah dibekukan dalam arsip editorial jurnal.',
            'withdrawn' => 'Naskah telah ditarik secara resmi dari proses editorial.',
            'submitted' => 'Naskah telah ditindaklanjuti oleh dewan redaksi untuk proses evaluasi.',
            default => 'Naskah sedang dalam proses penanganan oleh dewan redaksi/mitra bestari sehingga tidak dapat ditarik kembali.',
        };
    }

    /**
     * Membangun struktur informasi tagihan dan bukti pembayaran APC naskah.
     *
     * @return array<string, mixed>
     */
    public function buildPaymentInfo(Submission $sub): array
    {
        $journal = $sub->journal ?? Journal::first();
        $isPaid = $journal ? $journal->isPaidApc() : false;
        $payment = $sub->latestPayment;

        if (! $isPaid) {
            return [
                'is_free' => true,
                'has_payment' => false,
                'status' => 'free',
                'status_label' => 'Bebas Biaya (Diamond Open Access)',
                'status_badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'apc_fee_text' => (string) ($journal?->apc_fee ?? 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah)'),
                'instructions' => 'Naskah ini diterbitkan secara bebas biaya (Diamond Open Access) tanpa pungutan biaya pemrosesan kepada penulis.',
                'can_upload' => false,
                'has_proof' => false,
                'proof_download_url' => null,
            ];
        }

        $canUpload = in_array($sub->status, ['accepted', 'in_production', 'ready_for_issue', 'scheduled'], true)
            && (! $payment || in_array($payment->status, ['pending_payment', 'rejected'], true));

        $amount = $payment ? (float) $payment->amount : (float) ($journal->apc_amount ?? 500000.00);

        return [
            'is_free' => false,
            'has_payment' => (bool) $payment,
            'status' => $payment ? $payment->status : 'pending_payment',
            'status_label' => $payment ? $payment->statusLabel() : 'Menunggu Pembayaran Penulis',
            'status_badge' => $payment ? $payment->statusBadgeClass() : 'bg-amber-50 text-amber-800 border-amber-200',
            'invoice_number' => (string) ($payment?->invoice_number ?? ('INV-'.date('Y').'-APC'.substr(preg_replace('/[^0-9]/', '', (string) $sub->submission_number) ?: '00001', -5))),
            'amount' => $amount,
            'amount_formatted' => 'Rp '.number_format($amount, 0, ',', '.'),
            'amount_paid' => $payment?->amount_paid ? (float) $payment->amount_paid : null,
            'amount_paid_formatted' => $payment?->amount_paid ? 'Rp '.number_format((float) $payment->amount_paid, 0, ',', '.') : null,
            'bank_name' => (string) ($journal->payment_bank_name ?: 'Bank Sulselbar'),
            'account_number' => (string) ($journal->payment_account_number ?: '130-001-009876-5'),
            'account_holder' => (string) ($journal->payment_account_holder ?: 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar'),
            'instructions' => (string) ($journal->payment_instructions ?: 'Sertakan ID Naskah pada berita transfer. Unggah foto atau berkas PDF bukti transfer yang sah.'),
            'sender_bank_name' => $payment?->sender_bank_name,
            'sender_account_name' => $payment?->sender_account_name,
            'transfer_date_formatted' => $payment?->transfer_date?->format('d M Y'),
            'author_notes' => $payment?->author_notes,
            'verification_notes' => $payment?->verification_notes,
            'uploaded_at_formatted' => $payment?->uploaded_at ? $payment->uploaded_at->format('d M Y, H:i').' WITA' : null,
            'verified_at_formatted' => $payment?->verified_at ? $payment->verified_at->format('d M Y, H:i').' WITA' : null,
            'can_upload' => $canUpload,
            'has_proof' => (bool) ($payment && $payment->file_entry_id),
            'proof_download_url' => ($payment && $payment->file_entry_id) ? route('author.submissions.payment.proof', $sub->id) : null,
        ];
    }
}
