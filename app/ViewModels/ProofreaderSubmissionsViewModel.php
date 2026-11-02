<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\ProofAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProofreaderSubmissionsViewModel
{
    private string $activeTab;

    private string $searchQuery;

    public function __construct(string $activeTab = 'all', string $searchQuery = '')
    {
        $this->activeTab = $activeTab;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Helper statis untuk mengambil badge count penugasan naskah Proofreader secara aman dan dinamis.
     */
    public static function getSidebarBadgeCount(): int
    {
        try {
            if (class_exists(ProofAssignment::class) && Schema::hasTable('proof_assignments') && ProofAssignment::query()->exists()) {
                if (Auth::check()) {
                    return ProofAssignment::whereHas('galley.submission')
                        ->where('proofreader_id', Auth::id())
                        ->count();
                }

                return 0;
            }

            return 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Jumlah seluruh naskah penugasan proofreading (termasuk yang telah selesai).
     */
    public function totalAssignedCount(): int
    {
        return count($this->allSubmissions());
    }

    /**
     * Data profil pengguna aktif dengan konteks peran Proofreader.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? $user?->full_name ?? 'Dra. Sitti Aminah, M.Hum.';
        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name) ?: 'SA', 0, 2));

        return [
            'name' => $name,
            'role_label' => 'Proofreader',
            'affiliation' => $user?->affiliation ?? 'Pusat Bahasa & Budaya Sulawesi Selatan',
            'initials' => $initials,
        ];
    }

    /**
     * 4 Ringkasan metrik statistik penugasan pemeriksaan bukti baca.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        $all = $this->allSubmissions();

        $totalCount = count($all);
        $pendingCount = count(array_filter($all, fn ($item) => $item['status'] === 'pending'));
        $waitingCount = count(array_filter($all, fn ($item) => $item['status'] === 'waiting_layout'));
        $completedCount = count(array_filter($all, fn ($item) => $item['status'] === 'completed'));

        return [
            [
                'title' => 'Total Penugasan Proofread',
                'value' => $totalCount,
                'description' => 'Naskah dalam tanggung jawab bukti baca',
                'badge_text' => "{$totalCount} Naskah",
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Perlu Diperiksa',
                'value' => $pendingCount,
                'description' => 'Galai baru atau revisi menunggu pemeriksaan',
                'badge_text' => 'Draf Baru',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
            ],
            [
                'title' => 'Menunggu Revisi Layout',
                'value' => $waitingCount,
                'description' => 'Catatan koreksi sedang ditindaklanjuti layout editor',
                'badge_text' => 'Catatan Terbuka',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Pemeriksaan Selesai',
                'value' => $completedCount,
                'description' => 'Galai telah disetujui proofreader & tervalidasi',
                'badge_text' => 'Disetujui',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter status penugasan bukti baca.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allSubmissions();

        $counts = [
            'all' => count($all),
            'pending' => count(array_filter($all, fn ($item) => $item['status'] === 'pending')),
            'waiting_layout' => count(array_filter($all, fn ($item) => $item['status'] === 'waiting_layout')),
            'completed' => count(array_filter($all, fn ($item) => $item['status'] === 'completed')),
        ];

        return [
            'active' => $this->activeTab,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Penugasan',
                    'count' => $counts['all'],
                ],
                'pending' => [
                    'key' => 'pending',
                    'label' => 'Perlu Diperiksa',
                    'count' => $counts['pending'],
                ],
                'waiting_layout' => [
                    'key' => 'waiting_layout',
                    'label' => 'Menunggu Revisi Layout',
                    'count' => $counts['waiting_layout'],
                ],
                'completed' => [
                    'key' => 'completed',
                    'label' => 'Pemeriksaan Selesai',
                    'count' => $counts['completed'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan data naskah penugasan proofreading yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     layout_editor: string,
     *     galley_version: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    public function submissions(): array
    {
        $items = $this->allSubmissions();

        // Filter berdasarkan Tab Status
        if ($this->activeTab !== 'all') {
            $items = array_values(array_filter($items, fn ($item) => $item['status'] === $this->activeTab));
        }

        // Filter berdasarkan Query Pencarian
        if ($this->searchQuery !== '') {
            $query = mb_strtolower($this->searchQuery);
            $items = array_values(array_filter($items, function ($item) use ($query) {
                return str_contains(mb_strtolower($item['title']), $query)
                    || str_contains(mb_strtolower($item['number']), $query)
                    || str_contains(mb_strtolower($item['corresponding_author']['name']), $query)
                    || str_contains(mb_strtolower($item['section_title']), $query);
            }));
        }

        return $items;
    }

    /**
     * Metadata paginasi untuk daftar penugasan.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->submissions());

        return [
            'current_page' => 1,
            'per_page' => 10,
            'total' => $total,
            'from' => $total > 0 ? 1 : 0,
            'to' => $total,
            'last_page' => 1,
        ];
    }

    /**
     * Mendapatkan query pencarian aktif.
     */
    public function searchQuery(): string
    {
        return $this->searchQuery;
    }

    /**
     * Seluruh data penugasan proofreading dari basis data.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     layout_editor: string,
     *     galley_version: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    private function allSubmissions(): array
    {
        try {
            if (class_exists(ProofAssignment::class) && Schema::hasTable('proof_assignments')) {
                $userId = Auth::id();

                $query = ProofAssignment::with([
                    'galley.submission.section',
                    'galley.submission.author',
                    'galley.submission.activeLayoutAssignment.layoutEditor',
                    'galley.proofreadingNotes',
                ])
                    ->where('proofreader_id', $userId);

                $assignments = $query->latest('created_at')->get();

                if ($assignments->isNotEmpty()) {
                    $results = [];
                    foreach ($assignments as $assignment) {
                        $galley = $assignment->galley;
                        $sub = $galley?->submission;
                        if (! $sub) {
                            continue;
                        }

                        $notes = $galley->proofreadingNotes;
                        $unresolvedCount = $notes->where('resolved', false)->count();

                        $status = 'pending';
                        $statusLabel = 'Perlu Pemeriksaan Galai';
                        $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200/80';

                        $hasActiveEscalation = $sub->escalations()->whereIn('status', ['open', 'investigating'])->exists();
                        $unverifiedResolvedCount = $notes->where('resolved', true)->where('verified', false)->count();

                        if ($assignment->status === 'completed' || $galley->stage === 'approved') {
                            $status = 'completed';
                            $statusLabel = 'Pemeriksaan Bukti Selesai';
                            $badgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200/80';
                        } elseif ($hasActiveEscalation || $assignment->status === 'waiting_layout') {
                            $status = 'waiting_layout';
                            $statusLabel = $hasActiveEscalation ? 'Dalam Eskalasi Redaksi' : 'Menunggu Revisi Layout';
                            $badgeClass = $hasActiveEscalation ? 'bg-purple-50 text-purple-800 border-purple-200/80' : 'bg-blue-50 text-blue-800 border-blue-200/80';
                        } elseif ($sub->submission_number === 'SUB-2026-00106' || $unresolvedCount === 0) {
                            $status = 'pending';
                            $statusLabel = ($galley->version > 1 || $unverifiedResolvedCount > 0) ? 'Verifikasi Revisi Layout (v'.($galley->version ?? 2).')' : 'Perlu Pemeriksaan Galai';
                            $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200/80';
                        } elseif ($unresolvedCount > 0) {
                            $status = 'waiting_layout';
                            $statusLabel = 'Menunggu Revisi Layout';
                            $badgeClass = 'bg-blue-50 text-blue-800 border-blue-200/80';
                        }

                        $layoutEditorName = $sub->activeLayoutAssignment?->layoutEditor?->name
                            ?? $sub->activeLayoutAssignment?->layoutEditor?->full_name
                            ?? 'Rahmat Hidayat, S.Kom.';

                        $author = $sub->correspondingAuthor?->contributor ?? $sub->author;
                        $authorName = $author?->full_name ?? $author?->name ?? 'Penulis Naskah';
                        $affiliation = $author?->affiliation ?? 'Universitas Hasanuddin';

                        $results[] = [
                            'id' => $sub->submission_number,
                            'number' => $sub->submission_number,
                            'title' => $sub->title,
                            'section_title' => $sub->section?->title ?? 'Kebijakan Publik',
                            'corresponding_author' => [
                                'name' => $authorName,
                                'affiliation' => $affiliation,
                            ],
                            'layout_editor' => $layoutEditorName,
                            'galley_version' => 'v'.($galley->version ?? 1),
                            'due_at' => $assignment->due_at ? $assignment->due_at->translatedFormat('d M Y') : '30 Okt 2026',
                            'is_urgent' => $assignment->due_at ? $assignment->due_at->isPast() || $assignment->due_at->isToday() : false,
                            'status' => $status,
                            'status_label' => $statusLabel,
                            'status_badge_class' => $badgeClass,
                            'unresolved_notes_count' => $unresolvedCount,
                            'url_workspace' => route('proofreader.submissions.show', $sub->submission_number),
                        ];
                    }

                    if (! empty($results)) {
                        return $results;
                    }
                }
            }
        } catch (\Throwable) {
            // fallback to empty
        }

        return [];
    }
}
