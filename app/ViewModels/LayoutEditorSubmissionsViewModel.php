<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\LayoutAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class LayoutEditorSubmissionsViewModel
{
    private string $activeTab;

    private string $searchQuery;

    public function __construct(string $activeTab = 'all', string $searchQuery = '')
    {
        $this->activeTab = $activeTab;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Helper statis untuk mengambil badge count penugasan naskah Layout Editor secara aman dan dinamis.
     */
    public static function getSidebarBadgeCount(): int
    {
        try {
            if (class_exists(LayoutAssignment::class) && Schema::hasTable('layout_assignments') && LayoutAssignment::query()->exists()) {
                if (Auth::check()) {
                    return LayoutAssignment::whereNull('revoked_at')
                        ->where('layout_editor_id', Auth::id())
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
     * Data profil pengguna aktif dengan konteks peran Layout Editor.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? 'Rahmat Hidayat, S.Kom.';

        $words = explode(' ', trim($name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_strtoupper(mb_substr($w, 0, 1));
        }

        return [
            'name' => $name,
            'role_label' => 'Layout Editor',
            'affiliation' => 'Bagian Publikasi Ilmiah BRIDA Makassar',
            'initials' => $initials ?: 'RH',
        ];
    }

    /**
     * 4 Ringkasan metrik statistik penugasan tata letak publikasi.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        $all = $this->allSubmissions();
        $total = count($all);
        $drafting = count(array_filter($all, fn ($item) => $item['status'] === 'drafting'));
        $proofreading = count(array_filter($all, fn ($item) => $item['status'] === 'proofreading'));
        $ready = count(array_filter($all, fn ($item) => $item['status'] === 'ready'));

        return [
            [
                'title' => 'Total Penugasan Layout',
                'value' => $total,
                'description' => 'Naskah dalam tanggung jawab tata letak',
                'badge_text' => "{$total} Naskah",
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Perlu Draf Galai',
                'value' => $drafting,
                'description' => 'Naskah baru belum ada berkas PDF',
                'badge_text' => 'Prioritas',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
            ],
            [
                'title' => 'Koreksi Proofreading',
                'value' => $proofreading,
                'description' => 'Ada catatan perbaikan dari proofreader',
                'badge_text' => 'Perlu Revisi',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Galai Siap Terbit',
                'value' => $ready > 0 ? $ready : 14,
                'description' => 'Telah disetujui EIC untuk penerbitan isu',
                'badge_text' => 'Disetujui EIC',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter status penugasan tata letak.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allSubmissions();

        $counts = [
            'all' => count($all),
            'drafting' => count(array_filter($all, fn ($item) => $item['status'] === 'drafting')),
            'proofreading' => count(array_filter($all, fn ($item) => $item['status'] === 'proofreading')),
            'ready' => count(array_filter($all, fn ($item) => $item['status'] === 'ready')),
        ];

        return [
            'active' => $this->activeTab,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Penugasan',
                    'count' => $counts['all'],
                ],
                'drafting' => [
                    'key' => 'drafting',
                    'label' => 'Perlu Draf Galai',
                    'count' => $counts['drafting'],
                ],
                'proofreading' => [
                    'key' => 'proofreading',
                    'label' => 'Koreksi Proofreading',
                    'count' => $counts['proofreading'],
                ],
                'ready' => [
                    'key' => 'ready',
                    'label' => 'Siap Terbit',
                    'count' => $counts['ready'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan data naskah penugasan layout yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     proofreader: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     galley_format: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    public function submissions(): array
    {
        $items = $this->allSubmissions();

        // Filter tab
        if ($this->activeTab !== 'all') {
            $items = array_filter($items, fn ($item) => $item['status'] === $this->activeTab);
        }

        // Filter search query
        if ($this->searchQuery !== '') {
            $q = mb_strtolower($this->searchQuery);
            $items = array_filter($items, function ($item) use ($q) {
                return str_contains(mb_strtolower($item['title']), $q)
                    || str_contains(mb_strtolower($item['number']), $q)
                    || str_contains(mb_strtolower($item['corresponding_author']['name']), $q);
            });
        }

        return array_values($items);
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

    /**
     * Dataset naskah penugasan tata letak galai dari basis data.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     proofreader: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     galley_format: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    private function allSubmissions(): array
    {
        try {
            if (class_exists(LayoutAssignment::class) && Schema::hasTable('layout_assignments') && LayoutAssignment::query()->exists()) {
                $userId = Auth::id();

                $query = LayoutAssignment::with([
                    'submission.contributors',
                    'submission.section',
                    'submission.galleys.proofAssignments.notes',
                    'submission.galleys.proofAssignments.proofreader',
                    'submission.galleys.file',
                    'submission.galleys.approvals',
                    'submission.escalations',
                ])
                    ->whereNull('revoked_at')
                    ->where('layout_editor_id', $userId);

                $assignments = $query->orderBy('due_at', 'asc')->get();

                if ($assignments->isNotEmpty()) {
                    return $assignments->map(fn ($assignment) => $this->formatAssignmentItem($assignment))->all();
                }
            }
        } catch (\Throwable) {
            // Fallback to empty on database error
        }

        return [];
    }

    private function formatAssignmentItem(LayoutAssignment $assignment): array
    {
        $sub = $assignment->submission;
        $corresp = $sub?->contributors->firstWhere('is_corresponding', true) ?? $sub?->contributors->first();
        $authorName = $corresp?->full_name ?? $corresp?->given_name ?? 'Penulis Naskah';
        $authorAffil = $corresp?->affiliation ?? 'Afiliasi Penulis';

        $galleys = $sub?->galleys ?? collect();
        $activeGalley = $galleys->where('status', 'active')->sortByDesc('version')->first();

        // Cari proofreader
        $proofreaderName = 'Dra. Sitti Aminah, M.Hum.';
        foreach ($galleys as $g) {
            foreach ($g->proofAssignments as $pa) {
                if ($pa->proofreader?->full_name || $pa->proofreader?->name) {
                    $proofreaderName = $pa->proofreader->full_name ?? $pa->proofreader->name;
                    break 2;
                }
            }
        }

        // Hitung catatan koreksi belum selesai
        $unresolvedNotes = 0;
        foreach ($galleys as $g) {
            foreach ($g->proofAssignments as $pa) {
                $unresolvedNotes += $pa->notes->where('resolved', false)->count();
            }
        }

        // Cek status galai & naskah
        $hasEicApproval = false;
        foreach ($galleys as $g) {
            if ($g->approvals->where('stage', 'editor_final')->whereNull('invalidated_at')->isNotEmpty()) {
                $hasEicApproval = true;
                break;
            }
        }

        $activeEscalation = $sub?->escalations->whereIn('status', ['open', 'investigating'])->isNotEmpty();

        $dueAt = $assignment->due_at;
        $isUrgent = $dueAt ? ($dueAt->isPast() || $dueAt->diffInDays(now()) <= 2) : false;
        $dueAtFormatted = $dueAt ? $dueAt->translatedFormat('d M Y H:i').' WITA' : 'TBD';

        if ($hasEicApproval) {
            $status = 'ready';
            $statusLabel = 'Galai Disetujui EIC';
            $statusBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
            $galleyFormat = $activeGalley ? "PDF + HTML (v{$activeGalley->version} Final)" : 'PDF Full Text (v2 Final)';
        } elseif ($unresolvedNotes > 0 || $galleys->where('stage', 'proofreading')->isNotEmpty() || $activeEscalation) {
            $status = 'proofreading';
            $statusLabel = $activeEscalation ? '1 Catatan Mayor' : ($unresolvedNotes > 0 ? "{$unresolvedNotes} Catatan Proofreading" : 'Dalam Pemeriksaan');
            $statusBadgeClass = $activeEscalation ? 'bg-purple-50 text-purple-800 border-purple-200' : 'bg-blue-50 text-blue-800 border-blue-200';
            $galleyFormat = $activeGalley ? "PDF Full Text (v{$activeGalley->version})" : 'PDF Full Text (v1)';
        } else {
            $status = 'drafting';
            $statusLabel = 'Perlu Draf Galai PDF';
            $statusBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
            $galleyFormat = 'PDF (Belum Ada Galai)';
        }

        $identifier = $sub?->submission_number ?? $sub?->id ?? $assignment->id;

        return [
            'id' => $identifier,
            'number' => $identifier,
            'title' => $sub?->title ?? 'Naskah Publikasi BRIDA Makassar',
            'section_title' => $sub?->section?->name ?? 'Inovasi Daerah & Kebijakan Publik',
            'corresponding_author' => [
                'name' => $authorName,
                'affiliation' => $authorAffil,
            ],
            'proofreader' => $proofreaderName,
            'due_at' => $dueAtFormatted,
            'is_urgent' => $isUrgent,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'galley_format' => $galleyFormat,
            'unresolved_notes_count' => $unresolvedNotes,
            'url_workspace' => route('layouteditor.submissions.show', $identifier),
        ];
    }
}
