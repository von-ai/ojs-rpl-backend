<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Issue;
use App\Models\IssueArticle;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class JournalManagerIssuesViewModel
{
    private string $activeTab;

    private string $searchQuery;

    public function __construct(string $activeTab = 'all', string $searchQuery = '')
    {
        $this->activeTab = $activeTab;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Profil pengguna aktif Journal Manager.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? 'Andi Muhammad Yusuf, M.Si.';
        $affiliation = $user?->profile?->affiliation ?? 'BRIDA Kota Makassar';

        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name) ?: 'AY';
        $initials = strtoupper(substr($cleanName, 0, 2));

        return [
            'name' => $name,
            'role_label' => 'Journal Manager',
            'affiliation' => $affiliation,
            'initials' => $initials,
        ];
    }

    /**
     * 4 Ringkasan metrik statistik operasional terbitan jurnal.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        if (Schema::hasTable('issues') && Issue::count() > 0) {
            $publishedCount = Issue::where('status', 'published')->count();
            $futureCount = Issue::whereIn('status', ['draft', 'scheduled'])->count();

            // Artikel siap terbit: Naskah tahap produksi yang belum masuk ke dalam edisi apapun
            $readyCount = 0;
            if (Schema::hasTable('submissions')) {
                $readyCount = Submission::where(function ($query) {
                    $query->whereIn('status', ['in_production', 'approved', 'ready_for_issue'])
                        ->orWhereHas('galleys', fn ($g) => $g->where('status', 'active'));
                })
                    ->whereDoesntHave('issueArticle')
                    ->count();
            }

            // Total artikel terbit: Akumulasi artikel dalam seluruh edisi terbit
            $totalPublishedArticles = 0;
            if (Schema::hasTable('issue_articles')) {
                $totalPublishedArticles = IssueArticle::whereHas('issue', fn ($q) => $q->where('status', 'published'))->count();
            }

            return [
                [
                    'title' => 'Edisi Terbit Resmi',
                    'value' => $publishedCount,
                    'description' => 'Tersedia di katalog publik & terindeks',
                    'badge_text' => 'Publik',
                    'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                ],
                [
                    'title' => 'Draf & Terjadwal',
                    'value' => $futureCount,
                    'description' => 'Dalam proses kurasi daftar isi',
                    'badge_text' => 'Produksi',
                    'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
                ],
                [
                    'title' => 'Artikel Siap Terbit',
                    'value' => $readyCount,
                    'description' => 'Galai disetujui EIC siap masuk edisi',
                    'badge_text' => 'Antrean EIC',
                    'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
                ],
                [
                    'title' => 'Total Artikel Terbit',
                    'value' => $totalPublishedArticles,
                    'description' => 'Akumulasi publikasi naskah resmi jurnal',
                    'badge_text' => 'Publikasi',
                    'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
                ],
            ];
        }

        return $this->fallbackMetrics();
    }

    /**
     * Konfigurasi tab filter status edisi terbitan.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allIssues();

        $counts = [
            'all' => count($all),
            'published' => count(array_filter($all, fn ($item) => $item['status'] === 'published')),
            'future' => count(array_filter($all, fn ($item) => in_array($item['status'], ['draft', 'scheduled'], true))),
        ];

        return [
            'active' => $this->activeTab,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Edisi',
                    'count' => $counts['all'],
                ],
                'published' => [
                    'key' => 'published',
                    'label' => 'Edisi Terbit',
                    'count' => $counts['published'],
                ],
                'future' => [
                    'key' => 'future',
                    'label' => 'Draf & Terjadwal',
                    'count' => $counts['future'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan data edisi nomor terbitan yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     volume: string,
     *     number: string,
     *     year: int,
     *     title: string,
     *     description: ?string,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     is_current: bool,
     *     articles_count: int,
     *     published_at: ?string,
     *     scheduled_at: ?string,
     *     url_workspace: string
     * }>
     */
    public function issues(): array
    {
        $items = $this->allIssues();

        // Filter tab
        if ($this->activeTab === 'published') {
            $items = array_filter($items, fn ($item) => $item['status'] === 'published');
        } elseif ($this->activeTab === 'future') {
            $items = array_filter($items, fn ($item) => in_array($item['status'], ['draft', 'scheduled'], true));
        }

        // Filter pencarian
        if ($this->searchQuery !== '') {
            $q = mb_strtolower($this->searchQuery);
            $items = array_filter($items, function ($item) use ($q) {
                return str_contains(mb_strtolower($item['title']), $q)
                    || str_contains(mb_strtolower($item['volume']), $q)
                    || str_contains(mb_strtolower($item['number']), $q)
                    || str_contains((string) $item['year'], $q);
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
        $total = count($this->issues());
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
     * Dataset naskah edisi terbitan jurnal BRIDA dari basis data atau fallback.
     */
    private function allIssues(): array
    {
        if (Schema::hasTable('issues')) {
            $issues = Issue::withCount('issueArticles')
                ->orderByDesc('year')
                ->orderByDesc('volume')
                ->orderByDesc('number')
                ->get();

            if ($issues->isNotEmpty()) {
                return $issues->map(function (Issue $issue) {
                    return [
                        'id' => (string) $issue->id,
                        'volume' => (string) $issue->volume,
                        'number' => (string) $issue->number,
                        'year' => (int) $issue->year,
                        'title' => (string) $issue->title,
                        'description' => $issue->description,
                        'status' => (string) $issue->status,
                        'status_label' => $issue->status_label,
                        'status_badge_class' => $issue->status_badge_class,
                        'is_current' => (bool) $issue->is_current,
                        'articles_count' => (int) $issue->issue_articles_count,
                        'published_at' => $issue->published_at ? $issue->published_at->translatedFormat('d M Y') : null,
                        'scheduled_at' => $issue->scheduled_at ? $issue->scheduled_at->translatedFormat('d M Y') : null,
                        'url_workspace' => route('journalmanager.issues.show', $issue->id),
                    ];
                })->all();
            }
        }

        return $this->registryIssues();
    }

    private function fallbackMetrics(): array
    {
        return [
            [
                'title' => 'Edisi Terbit Resmi',
                'value' => 12,
                'description' => 'Tersedia di katalog publik & terindeks',
                'badge_text' => 'Publik',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
            [
                'title' => 'Draf & Terjadwal',
                'value' => 2,
                'description' => 'Dalam proses kurasi daftar isi',
                'badge_text' => 'Produksi',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
            ],
            [
                'title' => 'Artikel Siap Terbit',
                'value' => 5,
                'description' => 'Galai disetujui EIC siap masuk edisi',
                'badge_text' => 'Antrean EIC',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Total Artikel Terbit',
                'value' => 84,
                'description' => 'Akumulasi publikasi Volume 1 hingga 6',
                'badge_text' => 'Volume 1-6',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
        ];
    }

    private function registryIssues(): array
    {
        return [
            [
                'id' => 'ISSUE-01JARK960A',
                'volume' => '6',
                'number' => '2',
                'year' => 2026,
                'title' => 'Vol. 6 No. 2 (2026): Inovasi Kebijakan Publik & Ketahanan Perkotaan',
                'description' => 'Edisi reguler semester kedua 2026 yang memuat riset inovasi pelayanan publik dan mitigasi lingkungan perkotaan.',
                'status' => 'draft',
                'status_label' => 'Draf Kurasi Naskah',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'is_current' => false,
                'articles_count' => 3,
                'published_at' => null,
                'scheduled_at' => '30 Nov 2026',
                'url_workspace' => route('journalmanager.issues.show', 'ISSUE-01JARK960A'),
            ],
            [
                'id' => 'ISSUE-01JARK961B',
                'volume' => '6',
                'number' => '1',
                'year' => 2026,
                'title' => 'Vol. 6 No. 1 (2026): Transformasi Digital dan Pembangunan Daerah Berkelanjutan',
                'description' => 'Edisi terbitan berjalan resmi semester pertama tahun 2026.',
                'status' => 'published',
                'status_label' => 'Terbit (Current Issue)',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'is_current' => true,
                'articles_count' => 8,
                'published_at' => '30 Jun 2026',
                'scheduled_at' => null,
                'url_workspace' => route('journalmanager.issues.show', 'ISSUE-01JARK961B'),
            ],
            [
                'id' => 'ISSUE-01JARK962C',
                'volume' => '5',
                'number' => '2',
                'year' => 2025,
                'title' => 'Vol. 5 No. 2 (2025): Tata Kelola Lingkungan dan Adaptasi Perubahan Iklim',
                'description' => 'Edisi reguler kedua tahun 2025.',
                'status' => 'published',
                'status_label' => 'Terbit (Arsip)',
                'status_badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'is_current' => false,
                'articles_count' => 8,
                'published_at' => '31 Des 2025',
                'scheduled_at' => null,
                'url_workspace' => route('journalmanager.issues.show', 'ISSUE-01JARK962C'),
            ],
            [
                'id' => 'ISSUE-01JARK963D',
                'volume' => '5',
                'number' => '1',
                'year' => 2025,
                'title' => 'Vol. 5 No. 1 (2025): Pemberdayaan Ekonomi Masyarakat Pesisir',
                'description' => 'Edisi reguler pertama tahun 2025.',
                'status' => 'published',
                'status_label' => 'Terbit (Arsip)',
                'status_badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'is_current' => false,
                'articles_count' => 7,
                'published_at' => '30 Jun 2025',
                'scheduled_at' => null,
                'url_workspace' => route('journalmanager.issues.show', 'ISSUE-01JARK963D'),
            ],
            [
                'id' => 'ISSUE-01JARK964E',
                'volume' => '7',
                'number' => '1',
                'year' => 2027,
                'title' => 'Vol. 7 No. 1 (2027): Edisi Khusus Riset Kebencanaan & Ketahanan Pesisir',
                'description' => 'Rencana edisi khusus awal tahun 2027.',
                'status' => 'scheduled',
                'status_label' => 'Terjadwal Terbit',
                'status_badge_class' => 'bg-blue-50 text-blue-800 border-blue-200',
                'is_current' => false,
                'articles_count' => 1,
                'published_at' => null,
                'scheduled_at' => '30 Jun 2027',
                'url_workspace' => route('journalmanager.issues.show', 'ISSUE-01JARK964E'),
            ],
        ];
    }
}
