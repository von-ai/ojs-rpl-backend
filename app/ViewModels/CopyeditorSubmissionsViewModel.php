<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Copyedit;
use Illuminate\Support\Facades\Auth;

class CopyeditorSubmissionsViewModel
{
    /**
     * Tab status yang sedang aktif.
     */
    private string $activeTab;

    /**
     * Kata kunci pencarian.
     */
    private string $searchQuery;

    public function __construct(string $activeTab = 'all', string $searchQuery = '')
    {
        $this->activeTab = $activeTab;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Data profil pengguna aktif dengan konteks peran Copy Editor.
     *
     * @return array{
     *     name: string,
     *     title: string,
     *     affiliation: string,
     *     role_label: string,
     *     initials: string,
     *     unread_notifications: int
     * }
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? 'Andi Kurniawan, S.Hum., M.Pd.';

        $words = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_strtoupper(mb_substr($w, 0, 1));
        }

        return [
            'name' => $name,
            'title' => $name,
            'affiliation' => 'Balai Penelitian & Pengembangan BRIDA Makassar',
            'role_label' => 'Portal Copy Editor',
            'initials' => $initials ?: 'AK',
            'unread_notifications' => 3,
        ];
    }

    /**
     * Rekap ringkasan metrik statistik penugasan copyeditor (4 kartu metrik).
     *
     * @return array<int, array{
     *     title: string,
     *     value: int,
     *     description: string,
     *     badge_text: string,
     *     color_class: string,
     *     icon: string
     * }>
     */
    public function summaryMetrics(): array
    {
        if ($this->hasDatabaseRecords()) {
            $copyedits = $this->loadDatabaseCopyedits();

            $totalActive = $copyedits->where('status', '!=', 'completed')->count();
            $awaitingApproval = $copyedits->filter(function ($c) {
                $latest = $c->approvals->first(fn ($a) => is_null($a->invalidated_at));

                return $latest && $latest->decision === 'requested';
            })->count();
            $escalated = $copyedits->filter(function ($c) {
                return $c->escalations->contains(fn ($e) => in_array($e->status, ['open', 'investigating'], true));
            })->count();
            $completed = $copyedits->where('status', 'completed')->count();

            return [
                [
                    'title' => 'Total Penugasan Aktif',
                    'value' => $totalActive,
                    'description' => 'Naskah dalam tanggung jawab Anda',
                    'badge_text' => "{$totalActive} Naskah",
                    'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
                    'icon' => 'document-text',
                ],
                [
                    'title' => 'Menunggu Persetujuan',
                    'value' => $awaitingApproval,
                    'description' => 'Output set dikirim ke penulis',
                    'badge_text' => 'Perlu Pantauan',
                    'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
                    'icon' => 'clock',
                ],
                [
                    'title' => 'Eskalasi Produksi',
                    'value' => $escalated,
                    'description' => 'Dalam mediasi Section Editor',
                    'badge_text' => 'Perlu Mediasi',
                    'color_class' => 'text-red-700 bg-red-50 border-red-200',
                    'icon' => 'alert-triangle',
                ],
                [
                    'title' => 'Riwayat Selesai',
                    'value' => $completed,
                    'description' => 'Telah diserahkan ke Galley Layout',
                    'badge_text' => 'Rampung',
                    'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                    'icon' => 'check-circle',
                ],
            ];
        }

        return [
            [
                'title' => 'Total Penugasan Aktif',
                'value' => 12,
                'description' => 'Naskah dalam tanggung jawab Anda',
                'badge_text' => '12 Naskah',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
                'icon' => 'document-text',
            ],
            [
                'title' => 'Menunggu Persetujuan',
                'value' => 4,
                'description' => 'Output set dikirim ke penulis',
                'badge_text' => 'Perlu Pantauan',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
                'icon' => 'clock',
            ],
            [
                'title' => 'Eskalasi Produksi',
                'value' => 2,
                'description' => 'Dalam mediasi Section Editor',
                'badge_text' => 'Perlu Mediasi',
                'color_class' => 'text-red-700 bg-red-50 border-red-200',
                'icon' => 'alert-triangle',
            ],
            [
                'title' => 'Riwayat Selesai',
                'value' => 18,
                'description' => 'Telah diserahkan ke Galley Layout',
                'badge_text' => 'Rampung',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                'icon' => 'check-circle',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter dan jumlah data per status.
     *
     * @return array{
     *     active: string,
     *     items: array<string, array{label: string, count: int, key: string}>
     * }
     */
    public function tabs(): array
    {
        if ($this->hasDatabaseRecords()) {
            $copyedits = $this->loadDatabaseCopyedits();

            $totalAll = $copyedits->count();
            $completedCount = $copyedits->where('status', 'completed')->count();
            $escalatedCount = $copyedits->filter(fn ($c) => $c->escalations->contains(fn ($e) => in_array($e->status, ['open', 'investigating'], true)))->count();
            $awaitingCount = $copyedits->filter(function ($c) {
                $latest = $c->approvals->first(fn ($a) => is_null($a->invalidated_at));

                return $latest && $latest->decision === 'requested';
            })->count();
            $editingCount = $copyedits->filter(function ($c) {
                if ($c->status === 'completed') {
                    return false;
                }
                if ($c->escalations->contains(fn ($e) => in_array($e->status, ['open', 'investigating'], true))) {
                    return false;
                }
                $latest = $c->approvals->first(fn ($a) => is_null($a->invalidated_at));

                return ! ($latest && $latest->decision === 'requested');
            })->count();

            return [
                'active' => $this->activeTab,
                'items' => [
                    'all' => [
                        'key' => 'all',
                        'label' => 'Semua Penugasan',
                        'count' => $totalAll,
                    ],
                    'editing' => [
                        'key' => 'editing',
                        'label' => 'Sedang Disunting',
                        'count' => $editingCount,
                    ],
                    'awaiting_approval' => [
                        'key' => 'awaiting_approval',
                        'label' => 'Menunggu Penulis',
                        'count' => $awaitingCount,
                    ],
                    'escalated' => [
                        'key' => 'escalated',
                        'label' => 'Eskalasi Mediasi',
                        'count' => $escalatedCount,
                    ],
                    'completed' => [
                        'key' => 'completed',
                        'label' => 'Selesai Serah Terima',
                        'count' => $completedCount,
                    ],
                ],
            ];
        }

        return [
            'active' => $this->activeTab,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Penugasan',
                    'count' => 12,
                ],
                'editing' => [
                    'key' => 'editing',
                    'label' => 'Sedang Disunting',
                    'count' => 6,
                ],
                'awaiting_approval' => [
                    'key' => 'awaiting_approval',
                    'label' => 'Menunggu Penulis',
                    'count' => 4,
                ],
                'escalated' => [
                    'key' => 'escalated',
                    'label' => 'Eskalasi Mediasi',
                    'count' => 2,
                ],
                'completed' => [
                    'key' => 'completed',
                    'label' => 'Selesai Serah Terima',
                    'count' => 18,
                ],
            ],
        ];
    }

    /**
     * Kumpulan data naskah penugasan copyediting.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, email: string, affiliation: string},
     *     section_editor: string,
     *     assigned_at: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     is_overdue: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     output_set_version: ?string,
     *     files_count: int,
     *     has_active_escalation: bool,
     *     url_workspace: string
     * }>
     */
    public function submissions(): array
    {
        $all = $this->hasDatabaseRecords()
            ? $this->buildSubmissionsFromDatabase()
            : $this->fallbackMockSubmissions();

        // Filter berdasarkan tab
        $filtered = array_filter($all, function ($item) {
            if ($this->activeTab === 'editing') {
                return $item['status'] === 'in_progress';
            }
            if ($this->activeTab === 'awaiting_approval') {
                return $item['status'] === 'awaiting_author_approval';
            }
            if ($this->activeTab === 'escalated') {
                return $item['status'] === 'escalated';
            }
            if ($this->activeTab === 'completed') {
                return $item['status'] === 'completed';
            }

            return true; // 'all'
        });

        // Filter berdasarkan pencarian
        if ($this->searchQuery !== '') {
            $q = mb_strtolower($this->searchQuery);
            $filtered = array_filter($filtered, function ($item) use ($q) {
                return str_contains(mb_strtolower($item['title']), $q)
                    || str_contains(mb_strtolower($item['number']), $q)
                    || str_contains(mb_strtolower($item['corresponding_author']['name']), $q);
            });
        }

        return array_values($filtered);
    }

    /**
     * Data pagination.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->submissions());
        $from = $total > 0 ? 1 : 0;
        $to = min($total, 10);

        return [
            'current_page' => 1,
            'per_page' => 10,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'last_page' => max(1, (int) ceil($total / 10)),
        ];
    }

    private function hasDatabaseRecords(): bool
    {
        try {
            return class_exists(Copyedit::class)
                && \Illuminate\Support\Facades\Schema::hasTable('copyedits')
                && Copyedit::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Copyedit>
     */
    private function loadDatabaseCopyedits()
    {
        return Copyedit::with([
            'submission.section',
            'submission.journal',
            'submission.author',
            'submission.contributors',
            'outputs.file',
            'approvals',
            'escalations',
        ])->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSubmissionsFromDatabase(): array
    {
        $copyedits = $this->loadDatabaseCopyedits();
        $items = [];

        foreach ($copyedits as $copyedit) {
            $submission = $copyedit->submission;
            if (! $submission) {
                continue;
            }

            $isOverdue = $copyedit->due_at ? $copyedit->due_at->isPast() && $copyedit->status !== 'completed' : false;
            $isUrgent = $copyedit->due_at ? $copyedit->due_at->diffInDays(now()) <= 3 && $copyedit->status !== 'completed' : false;

            $activeEscalation = $copyedit->escalations->first(fn ($e) => in_array($e->status, ['open', 'investigating'], true));
            $latestApproval = $copyedit->approvals->first(fn ($a) => is_null($a->invalidated_at));

            $status = 'in_progress';
            $statusLabel = 'Tahap Copyediting Aktif';
            $statusBadgeClass = 'bg-sky-50 text-sky-800 border-sky-200';

            if ($copyedit->status === 'completed') {
                $status = 'completed';
                $statusLabel = 'Selesai Serah Terima';
                $statusBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
            } elseif ($activeEscalation) {
                $status = 'escalated';
                $statusLabel = 'Eskalasi Mediasi Redaksi';
                $statusBadgeClass = 'bg-red-50 text-red-700 border-red-200';
            } elseif ($latestApproval && $latestApproval->decision === 'approved') {
                $status = 'approved';
                $statusLabel = 'Disetujui Penulis';
                $statusBadgeClass = 'bg-teal-50 text-teal-800 border-teal-200';
            } elseif ($latestApproval && $latestApproval->decision === 'requested') {
                $status = 'awaiting_author_approval';
                $statusLabel = 'Menunggu Persetujuan Penulis';
                $statusBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
            }

            $outputVersion = $copyedit->outputs->count() > 0
                ? 'Output Set v'.($copyedit->approvals->max('output_version') ?: 1)
                : null;

            $corresponding = $submission->contributors->firstWhere('is_corresponding', true);
            $authorName = $corresponding?->full_name ?? $submission->author?->name ?? 'Penulis Naskah';
            $authorEmail = $corresponding?->email ?? $submission->author?->email ?? '';
            $authorAffiliation = $corresponding?->affiliation ?? 'Afiliasi Penulis';

            $items[] = [
                'id' => $copyedit->id,
                'number' => $submission->submission_number ?: $submission->id,
                'title' => $submission->title,
                'section_title' => $submission->section?->name ?? 'Inovasi Daerah & Kebijakan Publik',
                'corresponding_author' => [
                    'name' => $authorName,
                    'email' => $authorEmail,
                    'affiliation' => $authorAffiliation,
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'assigned_at' => $copyedit->created_at ? $copyedit->created_at->format('d M Y') : now()->format('d M Y'),
                'due_at' => $copyedit->due_at ? $copyedit->due_at->format('d M Y H:i \W\I\T\A') : now()->format('d M Y H:i \W\I\T\A'),
                'is_urgent' => $isUrgent,
                'is_overdue' => $isOverdue,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_badge_class' => $statusBadgeClass,
                'output_set_version' => $outputVersion,
                'files_count' => $copyedit->outputs->count(),
                'has_active_escalation' => $activeEscalation !== null,
                'url_workspace' => route('copyeditor.submissions.show', $submission->submission_number ?: $copyedit->id),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackMockSubmissions(): array
    {
        return [
            [
                'id' => '01JARK948X0010600000000001',
                'number' => 'SUB-2026-00106',
                'title' => 'Analisis Spasial Ruang Terbuka Hijau Terhadap Efek Urban Heat Island Kota Makassar',
                'section_title' => 'Inovasi Daerah & Kebijakan Publik',
                'corresponding_author' => [
                    'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                    'email' => 'nurfadillah.bahar@unhas.ac.id',
                    'affiliation' => 'Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'assigned_at' => '18 Okt 2026',
                'due_at' => '29 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'is_overdue' => false,
                'status' => 'in_progress',
                'status_label' => 'Tahap Copyediting Aktif',
                'status_badge_class' => 'bg-sky-50 text-sky-800 border-sky-200',
                'output_set_version' => 'Output Set v2',
                'files_count' => 2,
                'has_active_escalation' => true,
                'url_workspace' => route('copyeditor.submissions.show', 'SUB-2026-00106'),
            ],
            [
                'id' => '01JARK948X0010700000000002',
                'number' => 'SUB-2026-00107',
                'title' => 'Transformasi Digital Pelayanan Publik Kelurahan di Kota Makassar: Evaluasi Implementasi Aplikasi Lorong',
                'section_title' => 'Sosial Humaniora & Tata Kelola',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. Muhammad Basri, M.T.',
                    'email' => 'm.basri@poliupg.ac.id',
                    'affiliation' => 'Politeknik Negeri Ujung Pandang',
                ],
                'section_editor' => 'Dr. Hj. St. Rohani, M.Si.',
                'assigned_at' => '19 Okt 2026',
                'due_at' => '02 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'is_overdue' => false,
                'status' => 'awaiting_author_approval',
                'status_label' => 'Menunggu Persetujuan Penulis',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'output_set_version' => 'Output Set v1',
                'files_count' => 1,
                'has_active_escalation' => false,
                'url_workspace' => route('copyeditor.submissions.show', 'SUB-2026-00107'),
            ],
            [
                'id' => '01JARK948X0010800000000003',
                'number' => 'SUB-2026-00108',
                'title' => 'Kajian Karakteristik Geoteknik Sedimen Pesisir Pantai Losari untuk Mitigasi Erosi Gelombang Pasang',
                'section_title' => 'Teknologi & Infrastruktur Berkelanjutan',
                'corresponding_author' => [
                    'name' => 'Dr. Eng. Ir. Faisal Syarif, S.T., M.Eng.',
                    'email' => 'faisal.syarif@unhas.ac.id',
                    'affiliation' => 'Fakultas Teknik Universitas Hasanuddin',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'assigned_at' => '20 Okt 2026',
                'due_at' => '30 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'is_overdue' => false,
                'status' => 'escalated',
                'status_label' => 'Eskalasi Mediasi Redaksi',
                'status_badge_class' => 'bg-red-50 text-red-700 border-red-200',
                'output_set_version' => 'Output Set v1',
                'files_count' => 3,
                'has_active_escalation' => true,
                'url_workspace' => route('copyeditor.submissions.show', 'SUB-2026-00108'),
            ],
            [
                'id' => '01JARK948X0010900000000004',
                'number' => 'SUB-2026-00109',
                'title' => 'Efektivitas Program Lorong Wisata dalam Pemberdayaan UMKM Kuliner Tradisional Kota Makassar',
                'section_title' => 'Ekonomi Pembangunan & Kewirausahaan',
                'corresponding_author' => [
                    'name' => 'Dr. Hj. Fatmawati, S.E., M.Si.',
                    'email' => 'fatmawati.se@unismuh.ac.id',
                    'affiliation' => 'Universitas Muhammadiyah Makassar',
                ],
                'section_editor' => 'Prof. Hamka, Ph.D.',
                'assigned_at' => '21 Okt 2026',
                'due_at' => '03 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'is_overdue' => false,
                'status' => 'approved',
                'status_label' => 'Disetujui Penulis',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'output_set_version' => 'Output Set v2',
                'files_count' => 2,
                'has_active_escalation' => false,
                'url_workspace' => route('copyeditor.submissions.show', 'SUB-2026-00109'),
            ],
            [
                'id' => '01JARK948X0011000000000005',
                'number' => 'SUB-2026-00110',
                'title' => 'Strategi Pengelolaan Sampah Berbasis Komunitas Melalui Bank Sampah Sektoral di Kecamatan Rappocini',
                'section_title' => 'Lingkungan Hidup & Keberlanjutan',
                'corresponding_author' => [
                    'name' => 'Ir. Syamsul Rijal, M.Env.Sc.',
                    'email' => 'syamsul.rijal@uim-makassar.ac.id',
                    'affiliation' => 'Universitas Islam Makassar',
                ],
                'section_editor' => 'Dr. Hj. St. Rohani, M.Si.',
                'assigned_at' => '15 Okt 2026',
                'due_at' => '25 Okt 2026 23:59 WITA',
                'is_urgent' => false,
                'is_overdue' => false,
                'status' => 'completed',
                'status_label' => 'Selesai Diserahkan',
                'status_badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'output_set_version' => 'Output Set Final',
                'files_count' => 2,
                'has_active_escalation' => false,
                'url_workspace' => route('copyeditor.submissions.show', 'SUB-2026-00110'),
            ],
        ];
    }
}
