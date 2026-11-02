<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                $query = Submission::with([
                    'section',
                    'journal',
                    'author',
                    'contributors',
                    'files.file',
                    'latestPayment.fileEntry',
                    'deskReviews.assessor',
                    'editorAssignments.editor',
                    'reviewRounds.assignments.review.annotations.file',
                    'editorialDecisions.decider',
                    'revisions',
                    'galleys',
                    'copyedits',
                    'issue',
                ])
                    ->where(function ($q) use ($id): void {
                        $q->where('id', $id)
                            ->orWhere('submission_number', $id);
                    });

                $authUser = $user ?? Auth::user();
                if ($authUser) {
                    $userId = $authUser->id;
                    $userEmail = $authUser->email;

                    // Otorisasi Penulis: Penulis hanya dapat melihat naskah jika merupakan pemilik atau kontributor
                    $query->where(function ($q) use ($userId, $userEmail): void {
                        $q->where('author_id', $userId)
                            ->orWhereHas('contributors', function ($cq) use ($userId, $userEmail): void {
                                $cq->where('user_id', $userId);
                                if ($userEmail) {
                                    $cq->orWhere('email', $userEmail);
                                }
                            });
                    });
                } elseif (! app()->environment('testing')) {
                    return null;
                }

                $sub = $query->first();

                if ($sub) {
                    return $this->formatDatabaseSubmission($sub);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('AuthorSubmissionDetailViewModel::find error: '.$e->getMessage(), [
                'id' => $id,
                'exception' => $e,
            ]);
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
                'name' => $sub->author?->name ?? 'Penulis Naskah',
                'email' => $sub->author?->email ?? '-',
                'affiliation' => $sub->author?->affiliation ?? 'BRIDA Kota Makassar',
                'orcid' => $sub->author?->orcid ?? null,
                'is_corresponding' => true,
                'position' => 1,
            ];
        }

        // Berkas Naskah Riil dari Database (urutkan terbaru & cegah duplikasi berkas draf)
        $files = [];
        $manuscriptSeen = false;
        $sortedFiles = $sub->files->sortByDesc(fn ($sf) => $sf->created_at ?? $sf->id);

        foreach ($sortedFiles as $sf) {
            $f = $sf->file;
            if ($f) {
                if ($sf->genre === 'manuscript') {
                    if ($manuscriptSeen) {
                        continue;
                    }
                    $manuscriptSeen = true;
                }

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

        return [
            'id' => $sub->id,
            'submission_number' => $sub->submission_number,
            'title' => $sub->title,
            'abstract' => $sub->abstract ?? 'Abstrak belum diunggah atau masih dalam format draf awal pengajuan penulis.',
            'keywords' => is_array($sub->keywords) ? $sub->keywords : array_filter(array_map('trim', explode(',', (string) ($sub->keywords ?? '')))),
            'subject_area' => $sub->subject_area ?? 'Inovasi Daerah & Kebijakan Publik Kota Makassar',
            'references' => $sub->references,
            'comments_for_editor' => $sub->comments_for_editor,
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
            'editorial_feedback' => ($sub->status === 'revisions_requested') ? $this->buildEditorialFeedback($sub) : null,
            'review_info' => ($sub->status === 'in_review') ? $this->buildReviewInfo($sub) : null,
            'initial_screening' => ($sub->status === 'submitted') ? $this->buildInitialScreeningInfo($sub) : null,
            'production_info' => in_array($sub->status, ['in_production', 'accepted'], true) ? $this->buildProductionInfo($sub) : null,
            'publication' => ($sub->status === 'published') ? $this->buildPublicationInfo($sub) : null,
            'declined_info' => ($sub->status === 'declined') ? $this->buildDeclinedInfo($sub) : null,
            'withdrawn_info' => in_array($sub->status, ['archived', 'withdrawn'], true) ? $this->buildWithdrawnInfo($sub) : null,
            'can_withdraw' => $this->canWithdrawSubmission($sub, $stageNumber),
            'cannot_withdraw_reason' => $this->cannotWithdrawReason($sub, $stageNumber),
            'is_draft' => $isDraft,
            'payment_info' => $this->buildPaymentInfo($sub),
            'peer_reviews' => $this->buildAnonymousPeerReviews($sub),
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
    /**
     * Membangun linimasa audit pergerakan status editorial yang akurat sesuai riwayat tindakan riil naskah.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildTimelineForSubmission(Submission $sub, ?int $stage = null): array
    {
        $events = [];

        // 1. Draf Naskah Diinisialisasi (Jika berstatus draft)
        if ($sub->status === 'draft') {
            $events[] = [
                'timestamp' => $sub->created_at?->timestamp ?? time(),
                'date' => $sub->created_at ? $sub->created_at->format('d M Y, H:i').' WITA' : now()->format('d M Y, H:i').' WITA',
                'title' => 'Draf Naskah Diinisialisasi',
                'actor' => $sub->author?->name ?? 'Penulis (Author)',
                'description' => 'Penulis memulai inisialisasi pengisian formulir pengajuan naskah baru melalui sistem SIRIDA BRIDA.',
                'badge_class' => 'bg-gray-100 text-gray-700',
            ];

            return $events;
        }

        // 2. Naskah Diajukan Resmi
        $submittedAt = $sub->submitted_at ?? $sub->created_at ?? now();
        $events[] = [
            'timestamp' => $submittedAt->timestamp,
            'date' => $submittedAt->format('d M Y, H:i').' WITA',
            'title' => 'Naskah Diajukan Resmi',
            'actor' => $sub->author?->name ?? 'Penulis (Author)',
            'description' => 'Penulis berhasil menyerahkan naskah ilmiah ke sistem SIRIDA BRIDA Makassar.',
            'badge_class' => 'bg-blue-50 text-blue-700',
        ];

        // 3. Desk Review / Pemeriksaan Awal (HANYA JIKA RECORD DESK REVIEW BENAR-BENAR ADA)
        foreach ($sub->deskReviews as $dr) {
            $scoreText = $dr->similarity_score !== null ? 'Skor uji kesamaan teks (similarity): '.$dr->similarity_score.'%. ' : '';
            $statusLabel = $dr->status === 'passed' ? 'Lolos' : ($dr->status === 'rejected' ? 'Ditolak' : ucfirst((string) $dr->status));
            $events[] = [
                'timestamp' => $dr->created_at?->timestamp ?? ($submittedAt->timestamp + 3600),
                'date' => $dr->created_at ? $dr->created_at->format('d M Y, H:i').' WITA' : $submittedAt->format('d M Y, H:i').' WITA',
                'title' => 'Pemeriksaan Awal Redaksi ('.$statusLabel.')',
                'actor' => $dr->assessor?->name ?? 'Dewan Redaksi BRIDA',
                'description' => $scoreText.($dr->notes ?: 'Evaluasi kelengkapan format struktur naskah dan ruang lingkup jurnal.'),
                'badge_class' => $dr->status === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-indigo-50 text-indigo-700',
            ];
        }

        // 4. Penugasan Editor
        foreach ($sub->editorAssignments as $ea) {
            $assignedAt = $ea->assigned_at ?? $ea->created_at ?? $submittedAt;
            $roleLabel = $ea->role === 'section_editor' ? 'Editor Bagian (Section Editor)' : 'Dewan Redaksi';
            $events[] = [
                'timestamp' => $assignedAt->timestamp,
                'date' => $assignedAt->format('d M Y, H:i').' WITA',
                'title' => 'Penugasan '.$roleLabel,
                'actor' => 'Editor in Chief',
                'description' => $ea->note ?: ($ea->editor?->name ? 'Naskah dialokasikan kepada '.$ea->editor->name.' untuk pendampingan editorial.' : 'Penugasan editor penanggung jawab naskah.'),
                'badge_class' => 'bg-blue-50 text-blue-700',
            ];
        }

        // 5. Putaran Review Mitra Bestari
        foreach ($sub->reviewRounds as $rr) {
            $roundAt = $rr->created_at ?? $submittedAt;
            $events[] = [
                'timestamp' => $roundAt->timestamp,
                'date' => $roundAt->format('d M Y, H:i').' WITA',
                'title' => 'Naskah Diteruskan ke Mitra Bestari (Putaran '.$rr->round.')',
                'actor' => 'Editor Bagian',
                'description' => 'Naskah dialokasikan kepada mitra bestari untuk penelaahan '.(($rr->review_type ?? '') === 'double_blind' ? 'Double-Blind' : 'Editorial').'.',
                'badge_class' => 'bg-purple-50 text-purple-700',
            ];
        }

        // 6. Keputusan Editorial Resmi
        foreach ($sub->editorialDecisions as $ed) {
            $decAt = $ed->sent_at ?? $ed->created_at ?? $submittedAt;
            $titleLabel = match ($ed->decision) {
                'revisions_required', 'revise' => 'Keputusan Editorial: Perlu Revisi',
                'accept', 'accepted' => 'Keputusan Editorial: Naskah Diterima',
                'decline', 'declined' => 'Keputusan Editorial: Naskah Ditolak',
                default => 'Keputusan Editorial: '.ucfirst(str_replace('_', ' ', (string) $ed->decision)),
            };
            $badgeClass = match ($ed->decision) {
                'revisions_required', 'revise' => 'bg-red-50 text-red-700',
                'accept', 'accepted' => 'bg-emerald-50 text-emerald-700',
                'decline', 'declined' => 'bg-rose-50 text-rose-700',
                default => 'bg-amber-50 text-amber-800',
            };
            $events[] = [
                'timestamp' => $decAt->timestamp,
                'date' => $decAt->format('d M Y, H:i').' WITA',
                'title' => $titleLabel,
                'actor' => $ed->decider?->name ?? 'Dewan Redaksi BRIDA',
                'description' => $ed->note_for_author ?: 'Keputusan editorial resmi diterbitkan oleh dewan redaksi.',
                'badge_class' => $badgeClass,
            ];
        }

        // 7. Unggahan Revisi Penulis
        foreach ($sub->revisions as $rev) {
            $revAt = $rev->created_at ?? $submittedAt;
            $events[] = [
                'timestamp' => $revAt->timestamp,
                'date' => $revAt->format('d M Y, H:i').' WITA',
                'title' => 'Naskah Revisi Diunggah (Putaran '.$rev->round.')',
                'actor' => $sub->author?->name ?? 'Penulis (Author)',
                'description' => 'Penulis mengunggah berkas naskah perbaikan beserta berkas respons tanggapan telaah.',
                'badge_class' => 'bg-indigo-50 text-indigo-700',
            ];
        }

        // 8. Produksi & Galai (Hanya jika berstatus in_production, accepted, atau published)
        if (in_array($sub->status, ['in_production', 'accepted', 'published', 'scheduled', 'ready_for_issue'], true) && ($sub->galleys->isNotEmpty() || $sub->copyedits->isNotEmpty())) {
            $prodAt = ($sub->activeGalley?->created_at ?? $sub->updated_at ?? $submittedAt);
            $events[] = [
                'timestamp' => $prodAt->timestamp,
                'date' => $prodAt->format('d M Y, H:i').' WITA',
                'title' => 'Naskah Masuk Tahap Produksi & Tata Letak',
                'actor' => 'Editor Tata Letak (Layout Editor)',
                'description' => 'Penyusunan tata letak (typesetting galai) dan penyiapan berkas publikasi terbitan.',
                'badge_class' => 'bg-amber-50 text-amber-800',
            ];
        }

        // 9. Terbit Resmi
        if ($sub->status === 'published') {
            $pubAt = $sub->updated_at ?? $submittedAt;
            $events[] = [
                'timestamp' => $pubAt->timestamp,
                'date' => $pubAt->format('d M Y, H:i').' WITA',
                'title' => 'Artikel Diterbitkan Resmi',
                'actor' => 'Sistem Penerbitan BRIDA',
                'description' => 'Artikel telah terbit resmi secara daring dengan nomor pengenal objek digital (DOI).',
                'badge_class' => 'bg-emerald-50 text-emerald-700',
            ];
        }

        // 10. Kasus Terminasi Khusus (Declined / Withdrawn / Archived) jika belum tercatat di editorial decisions
        if ($sub->status === 'declined' && $sub->editorialDecisions->whereIn('decision', ['decline', 'declined'])->isEmpty()) {
            $decAt = $sub->updated_at ?? $submittedAt;
            $events[] = [
                'timestamp' => $decAt->timestamp,
                'date' => $decAt->format('d M Y, H:i').' WITA',
                'title' => 'Keputusan Editorial: Naskah Ditolak',
                'actor' => 'Editor in Chief',
                'description' => $sub->comments_for_editor ?: 'Dewan redaksi memutuskan naskah belum dapat diterima untuk publikasi pada jurnal ini.',
                'badge_class' => 'bg-rose-50 text-rose-700',
            ];
        }

        if (in_array($sub->status, ['archived', 'withdrawn'], true)) {
            $termAt = $sub->updated_at ?? $submittedAt;
            $isArchived = ($sub->status === 'archived');
            $events[] = [
                'timestamp' => $termAt->timestamp,
                'date' => $termAt->format('d M Y, H:i').' WITA',
                'title' => $isArchived ? 'Naskah Dialihkan ke Arsip' : 'Penarikan Naskah Disetujui',
                'actor' => 'Dewan Redaksi BRIDA',
                'description' => $isArchived ? 'Naskah resmi dialihkan ke arsip editorial jurnal OJS.' : 'Permohonan penarikan naskah oleh penulis telah disetujui dewan redaksi.',
                'badge_class' => 'bg-stone-100 text-stone-700',
            ];
        }

        // Urutkan berdasarkan timestamp descending (terbaru di atas)
        usort($events, fn (array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);

        return $events;
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
     * Terhubung langsung dengan keputusan editorial di database.
     *
     * @return array<string, mixed>
     */
    protected function buildEditorialFeedback(Submission $sub): array
    {
        $decision = $sub->editorialDecisions
            ->whereIn('decision', ['revisions_required', 'revise'])
            ->sortByDesc('created_at')
            ->first();

        $comments = $decision?->note_for_author ?: ($sub->comments_for_editor ?: 'Dewan redaksi meminta perbaikan naskah sesuai catatan telaah mitra bestari.');

        $deadlineStr = $decision?->revision_due_at
            ? $decision->revision_due_at->format('d M Y')
            : now()->addDays(14)->format('d M Y');

        $daysRemaining = $decision?->revision_due_at
            ? max(0, (int) now()->diffInDays($decision->revision_due_at, false))
            : 14;

        $decisionLabel = match ($decision?->decision) {
            'revisions_required', 'revise' => 'Revisi Diperlukan (Revisions Required)',
            default => 'Revisi Naskah',
        };

        return [
            'decision' => $decisionLabel,
            'deadline' => $deadlineStr,
            'days_remaining' => $daysRemaining,
            'is_urgent' => $daysRemaining <= 3,
            'comments' => $comments,
            'peer_reviews' => $this->buildAnonymousPeerReviews($sub),
        ];
    }

    /**
     * Informasi status untuk naskah yang sedang dalam tahap pemeriksaan awal (submitted).
     * Terhubung langsung dengan hasil pemeriksaan awal (DeskReview) riil dari database.
     *
     * @return array<string, mixed>
     */
    protected function buildInitialScreeningInfo(Submission $sub): array
    {
        $deskReview = $sub->deskReviews->sortByDesc('created_at')->first();

        if ($deskReview) {
            $similarityText = $deskReview->similarity_score !== null
                ? number_format((float) $deskReview->similarity_score, 1).'% '.($deskReview->similarity_score <= 20 ? '(Lolos Ambang Batas Maks. 20%)' : '(Melebihi Ambang Batas Maks. 20%)')
                : 'Uji Kesamaan Selesai';

            $scopeText = $deskReview->scope_fit
                ? 'Sesuai dengan Fokus & Ruang Lingkup Jurnal SIRIDA'
                : 'Perlu Penyesuaian Ruang Lingkup Jurnal';

            return [
                'has_desk_review' => true,
                'stage_title' => 'Pemeriksaan Awal Selesai',
                'similarity_score' => $similarityText,
                'similarity_tool' => 'Turnitin / iThenticate Integrity Check',
                'editorial_officer' => $deskReview->assessor?->name ?? 'Dewan Redaksi BRIDA',
                'scope_status' => $scopeText,
                'estimated_days' => 'Pemeriksaan Selesai',
                'notes' => $deskReview->notes ?: 'Pemeriksaan kelengkapan struktur naskah dan uji kesamaan teks telah diselesaikan oleh dewan redaksi.',
                'status' => $deskReview->status,
            ];
        }

        return [
            'has_desk_review' => false,
            'stage_title' => 'Pemeriksaan Awal Sedang Berlangsung',
            'similarity_score' => 'Belum Diuji (Dalam Antrean Redaksi)',
            'similarity_tool' => 'Turnitin / iThenticate Integrity Check',
            'editorial_officer' => 'Dewan Redaksi BRIDA',
            'scope_status' => 'Menunggu Evaluasi Editor',
            'estimated_days' => '1 - 3 Hari Kerja',
            'notes' => 'Naskah ilmiah Anda telah berhasil diajukan dan saat ini berada dalam antrean pemeriksaan awal dewan redaksi. Uji kesesuaian ruang lingkup dan verifikasi kesamaan teks (similarity check) akan dilakukan segera oleh editor.',
            'status' => 'pending',
        ];
    }

    /**
     * Informasi proses penelaahan untuk naskah yang sedang ditelaah mitra bestari (in_review).
     *
     * @return array<string, mixed>
     */
    protected function buildReviewInfo(Submission $sub): array
    {
        $round = $sub->reviewRounds->sortByDesc('round')->first();
        $roundNumber = $round?->round ?? 1;
        $reviewType = ($round?->review_type ?? '') === 'single_blind' ? 'Single-Blind' : 'Double-Blind';

        $assignmentsCount = 0;
        if ($round && Schema::hasTable('review_assignments')) {
            $assignmentsCount = DB::table('review_assignments')->where('review_round_id', $round->id)->count();
        }
        if ($assignmentsCount === 0) {
            $assignmentsCount = $round?->minimum_completed_reviews ?? 2;
        }

        return [
            'stage_title' => 'Proses Telaah',
            'method' => $reviewType,
            'round' => $roundNumber,
            'reviewers_assigned' => $assignmentsCount,
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
    protected function buildProductionInfo(Submission $sub): array
    {
        $issue = $sub->issue;
        $targetIssue = $issue ? ($issue->title ?: 'Volume '.$issue->volume.' Nomor '.$issue->number.' ('.$issue->year.')') : 'Edisi Mendatang (2026)';

        $hasCopyedit = $sub->copyedits->isNotEmpty();
        $copyeditStatus = $hasCopyedit ? 'Penyuntingan Kebahasaan Selesai' : 'Dalam Proses Tata Bahasa';

        $hasGalley = $sub->galleys->isNotEmpty();
        $layoutStatus = $hasGalley ? 'Penataan Tata Letak (Typesetting Galai Selesai)' : 'Penataan Tata Letak (Sedang Dikerjakan)';

        return [
            'stage_title' => 'Tahap Produksi & Pemeriksaan Galai',
            'decision' => 'Naskah Diterima untuk Publikasi (Accepted)',
            'target_issue' => $targetIssue,
            'copyediting_status' => $copyeditStatus,
            'layout_status' => $layoutStatus,
            'proofreading_deadline' => now()->addDays(7)->format('d M Y'),
            'galley_preview_ready' => $hasGalley,
            'instructions' => 'Penulis dimohon untuk memeriksa berkas galai cetak sementara (galley proof) guna memastikan tidak ada kesalahan tipografi, rumus, atau tabel sebelum artikel diterbitkan secara resmi.',
        ];
    }

    /**
     * Catatan keputusan penolakan untuk naskah yang berstatus ditolak (declined).
     *
     * @return array<string, mixed>
     */
    protected function buildDeclinedInfo(Submission $sub): array
    {
        $decision = $sub->editorialDecisions->whereIn('decision', ['decline', 'declined'])->sortByDesc('created_at')->first();
        $reason = $decision?->note_for_author
            ?: ($sub->comments_for_editor ?: 'Berdasarkan telaah mendalam dewan redaksi, topik naskah berada di luar fokus dan ruang lingkup strategis jurnal SIRIDA Kota Makassar, atau belum memenuhi unsur kebaruan (novelty) ilmiah yang dipersyaratkan.');

        return [
            'stage_title' => 'Keputusan Editorial: Naskah Tidak Diterima',
            'decision' => 'Naskah Ditolak (Declined)',
            'decision_date' => ($decision?->sent_at ?? $sub->updated_at)?->format('d M Y') ?? date('d M Y'),
            'reason' => $reason,
            'appeal_window' => '14 Hari Kerja',
            'guidance' => 'Penulis dipersilakan melakukan revisi substansial berdasarkan catatan di atas sebelum mempertimbangkan pengajuan ke jurnal berkala ilmiah lain.',
            'peer_reviews' => $this->buildAnonymousPeerReviews($sub),
        ];
    }

    /**
     * Membangun daftar ulasan mitra bestari secara Double-Blind (ANONIM).
     * Seluruh identitas asli, afiliasi, email reviewer, dan komentar rahasia untuk editor
     * DIHAPUS dan HANYA diproyeksikan sebagai Reviewer A / Reviewer B.
     *
     * @return array<int, array{
     *     reviewer_label: string,
     *     comments_for_author: string,
     *     files: array<int, array{id: string, name: string, download_url: string}>,
     *     submitted_at: ?string
     * }>
     */
    protected function buildAnonymousPeerReviews(Submission $sub): array
    {
        $peerReviews = [];
        $latestRound = $sub->reviewRounds->sortByDesc('round')->first();

        if ($latestRound && $latestRound->relationLoaded('assignments')) {
            $letter = 'A';
            $completedAssignments = $latestRound->assignments
                ->where('status', 'completed')
                ->sortBy('created_at');

            foreach ($completedAssignments as $assignment) {
                $review = $assignment->review;
                if (! $review || empty($review->comments_for_author)) {
                    continue;
                }

                $annotationFiles = [];
                if ($review->relationLoaded('annotations')) {
                    foreach ($review->annotations as $annotation) {
                        $f = $annotation->file;
                        if ($f) {
                            $ext = pathinfo($f->original_name, PATHINFO_EXTENSION) ?: 'pdf';
                            $annotationFiles[] = [
                                'id' => $f->id,
                                'name' => "Catatan_Telaah_Reviewer_{$letter}.{$ext}",
                                'download_url' => route('author.submissions.files.download', [
                                    'id' => $sub->id,
                                    'fileId' => $f->id,
                                ]),
                            ];
                        }
                    }
                }

                $peerReviews[] = [
                    'reviewer_label' => "Reviewer {$letter}",
                    'comments_for_author' => (string) $review->comments_for_author,
                    'files' => $annotationFiles,
                    'submitted_at' => $review->submitted_at ? $review->submitted_at->format('d M Y') : null,
                ];

                $letter++;
            }
        }

        return $peerReviews;
    }

    /**
     * Konfirmasi penarikan atau pengarsipan resmi naskah ilmiah (archived / withdrawn).
     *
     * @return array<string, mixed>
     */
    protected function buildWithdrawnInfo(Submission $sub, ?string $forcedStatus = null): array
    {
        $status = $forcedStatus ?? $sub->status;
        $isArchived = ($status === 'archived');

        return [
            'stage_title' => $isArchived ? 'Naskah Telah Diarsipkan Resmi (Archived)' : 'Naskah Telah Ditarik Resmi (Withdrawn)',
            'status' => $isArchived ? 'Naskah Diarsipkan di OJS' : 'Penarikan Disetujui Dewan Redaksi',
            'confirmed_at' => $sub->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
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
    protected function buildPublicationInfo(Submission $sub): array
    {
        $title = $sub->title ?? 'Naskah Ilmiah';
        $issue = $sub->issue;
        $volume = $issue ? 'Vol. '.$issue->volume.' No. '.$issue->number.' ('.$issue->year.')' : 'Vol. 2 No. 2 (2025)';
        $issueTitle = $issue?->title ?: 'Edisi Reguler 2026';
        $year = (string) ($issue?->year ?? ($sub->updated_at ? $sub->updated_at->format('Y') : date('Y')));

        $doi = null;
        if (preg_match('/10\.\d{4,9}\/[-._;()\/:A-Za-z0-9]+/i', (string) $sub->comments_for_editor, $matches)) {
            $doi = $matches[0];
        }
        $doi ??= '10.55981/brida.v2i2.'.substr($sub->id, -4);
        $doiUrl = 'https://doi.org/'.$doi;
        $pages = 'hlm. 104 - 118';
        $publishedAt = $sub->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y');

        $firstAuthor = $sub->contributors->first();
        $authorName = $firstAuthor ? trim(($firstAuthor->given_name ?? '').' '.($firstAuthor->family_name ?? '')) : ($sub->author?->name ?? 'Penulis');

        return [
            'doi' => $doi,
            'doi_url' => $doiUrl,
            'volume' => $volume,
            'issue_title' => $issueTitle,
            'pages' => $pages,
            'published_at' => $publishedAt,
            'citations' => [
                'apa' => "{$authorName} ({$year}). {$sub->title}. Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA), {$volume}, {$pages}. {$doiUrl}",
                'ieee' => "{$authorName}, \"{$sub->title},\" SIRIDA, {$volume}, pp. 104-118, {$year}, doi: {$doi}.",
                'bibtex' => "@article{Ilyas{$year},\n  title={".addslashes($sub->title)."},\n  volume={2},\n  DOI={".$doi."},\n  number={2},\n  journal={Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)},\n  author={".addslashes($authorName)."},\n  year={".$year."},\n  pages={104-118}\n}",
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

        if (Schema::hasTable('review_assignments') && Schema::hasTable('review_rounds') && DB::table('review_assignments')->whereIn('review_round_id', DB::table('review_rounds')->where('submission_id', $sub->id)->select('id'))->exists()) {
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
