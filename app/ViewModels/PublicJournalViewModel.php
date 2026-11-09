<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\ArticleMetric;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class PublicJournalViewModel
{
    public function __construct(
        public readonly ?string $searchQuery = null,
        public readonly ?string $selectedCategory = null,
        protected readonly ?Issue $dbCurrentIssue = null,
        protected readonly ?LengthAwarePaginator $dbArticles = null,
        protected readonly ?Collection $dbArchives = null,
        protected ?Journal $journal = null
    ) {
        $this->journal = $journal ?? Journal::with(['sections' => fn ($q) => $q->orderBy('position')])->first();
    }

    public function journal(): ?Journal
    {
        return $this->journal;
    }

    /**
     * Get journal topbar & ISSN metadata.
     *
     * @return array<string, mixed>
     */
    public function topbarData(): array
    {
        return [
            'p_issn' => $this->journal?->issn ?: '2746-8194',
            'e_issn' => $this->journal?->eissn ?: '2746-8208',
            'languages' => [
                ['code' => 'id', 'label' => 'ID', 'active' => true],
                ['code' => 'en', 'label' => 'EN', 'active' => false],
            ],
        ];
    }

    /**
     * Get main navigation bar links.
     *
     * @return array<int, array{label: string, url: string, active: bool}>
     */
    public function navLinks(): array
    {
        return [
            ['label' => 'Beranda', 'url' => $this->safeRoute('journal.index', [], '/'), 'active' => request()->routeIs('journal.index')],
            ['label' => 'Cari Artikel', 'url' => $this->safeRoute('journal.articles.search', [], '/articles/search'), 'active' => request()->routeIs('journal.articles.search')],
            ['label' => 'Edisi Terkini', 'url' => $this->safeRoute('journal.issues.current', [], '#edisi-terkini'), 'active' => request()->routeIs('journal.issues.current')],
            ['label' => 'Arsip Edisi', 'url' => $this->safeRoute('journal.archives', [], '#arsip-edisi'), 'active' => request()->routeIs('journal.archives') || request()->routeIs('journal.issues.show')],
            ['label' => 'Tentang Jurnal', 'url' => $this->safeRoute('journal.about', [], '#tentang-jurnal'), 'active' => request()->routeIs('journal.about') || request()->routeIs('journal.about.policies') || request()->routeIs('journal.about.contact')],
            ['label' => 'Dewan Redaksi', 'url' => $this->safeRoute('journal.about.editorial-team', [], '#dewan-redaksi'), 'active' => request()->routeIs('journal.about.editorial-team')],
            ['label' => 'Panduan Penulis', 'url' => $this->safeRoute('journal.about.submissions', [], '#panduan-penulis'), 'active' => request()->routeIs('journal.about.submissions')],
            ['label' => 'Pengumuman', 'url' => $this->safeRoute('journal.announcements.index', [], '#pengumuman'), 'active' => request()->routeIs('journal.announcements.*')],
        ];
    }

    /**
     * Get active Call For Papers announcement banner data.
     *
     * @return array{title: string, deadline: string, detail_url: string}
     */
    public function announcement(): array
    {
        try {
            if (Schema::hasTable('announcements')) {
                $latest = Announcement::query()
                    ->when($this->journal, fn ($q) => $q->where('journal_id', $this->journal->id))
                    ->where('is_published', true)
                    ->latest('published_at')
                    ->first();

                if ($latest) {
                    return [
                        'title' => $latest->title,
                        'deadline' => $latest->published_at ? $latest->published_at->translatedFormat('d F Y') : 'Terbuka',
                        'detail_url' => $this->safeRoute('journal.announcements.show', ['id' => $latest->id], $this->safeRoute('journal.announcements.index', [], '#pengumuman')),
                    ];
                }
            }
        } catch (\Throwable) {
            // Fail gracefully to default banner
        }

        return [
            'title' => 'Volume 6 Nomor 2 (Edisi Desember 2026): Inovasi Kebijakan Publik & Ketahanan Perkotaan Menuju Makassar Kota Dunia.',
            'deadline' => '15 November 2026',
            'detail_url' => $this->safeRoute('journal.announcements.index', [], '#pengumuman'),
        ];
    }

    /**
     * Get catalog search scope categories.
     *
     * @return array<int, array{name: string, count: int}>
     */
    public function categories(): array
    {
        try {
            if ($this->journal && $this->journal->sections->isNotEmpty()) {
                return $this->journal->sections->map(fn ($s) => [
                    'name' => $s->name,
                    'count' => (int) $s->submissions()->count(),
                ])->all();
            }

            if (Schema::hasTable('sections')) {
                $sections = Section::withCount('submissions')->get();
                if ($sections->isNotEmpty()) {
                    return $sections->map(fn ($s) => [
                        'name' => $s->name,
                        'count' => (int) $s->submissions_count,
                    ])->all();
                }
            }
        } catch (\Throwable) {
            // Fallback graceful
        }

        return [
            ['name' => 'Tata Kelola Pemerintahan', 'count' => 0],
            ['name' => 'Makassar Smart City & IoT', 'count' => 0],
            ['name' => 'Lingkungan & Resiliensi Pesisir', 'count' => 0],
            ['name' => 'Ekonomi Kreatif & UMKM', 'count' => 0],
        ];
    }

    /**
     * Get Current Issue (Edisi Terkini) metadata.
     *
     * @return array<string, mixed>
     */
    public function currentIssue(): array
    {
        if ($this->dbCurrentIssue) {
            $totalArticles = $this->dbCurrentIssue->articles()->where('status', 'published')->count();
            $totalDownloads = 0;
            if (Schema::hasTable('article_metrics')) {
                $articleIds = $this->dbCurrentIssue->articles()->where('status', 'published')->pluck('articles.id');
                $totalDownloads = (int) ArticleMetric::whereIn('article_id', $articleIds)
                    ->where('event_type', 'galley_download')
                    ->sum('count');
            }

            return [
                'volume_label' => "Vol. {$this->dbCurrentIssue->volume} No. {$this->dbCurrentIssue->number} ({$this->dbCurrentIssue->year})",
                'edition_label' => $this->dbCurrentIssue->title,
                'published_at' => $this->dbCurrentIssue->published_at ? $this->dbCurrentIssue->published_at->format('d F Y') : ($this->dbCurrentIssue->publication_date ? $this->dbCurrentIssue->publication_date->format('d F Y') : $this->dbCurrentIssue->created_at->format('d F Y')),
                'doi' => $this->dbCurrentIssue->doi_prefix ? "{$this->dbCurrentIssue->doi_prefix}/vol{$this->dbCurrentIssue->volume}no{$this->dbCurrentIssue->number}" : "10.55981/sirida.{$this->dbCurrentIssue->year}.v{$this->dbCurrentIssue->volume}i{$this->dbCurrentIssue->number}",
                'title' => "Vol. {$this->dbCurrentIssue->volume} No. {$this->dbCurrentIssue->number} ({$this->dbCurrentIssue->year}): {$this->dbCurrentIssue->title}",
                'description' => $this->dbCurrentIssue->description ?? 'Edisi berkala ilmiah resmi terbitan '.($this->journal?->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar.'),
                'total_articles' => $totalArticles,
                'total_downloads' => $totalDownloads,
                'download_pdf_url' => $this->safeRoute('journal.issues.show', ['id' => $this->dbCurrentIssue->id], '#edisi-terkini'),
            ];
        }

        return [];
    }

    /**
     * Get published articles for the current issue or search query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function articles(): array
    {
        if ($this->dbArticles && $this->dbArticles->isNotEmpty()) {
            return $this->dbArticles->getCollection()->map(function (Article $a): array {
                $authors = $a->contributors->map(fn ($c) => $c->full_name)->all();
                $affiliations = $a->contributors
                    ->filter(fn ($c) => filled($c->affiliation))
                    ->pluck('affiliation')
                    ->values()
                    ->map(fn ($affiliation, $idx) => ($idx + 1).". {$affiliation}")
                    ->all();

                $firstGalley = $a->galleys->first();
                $downloadUrl = $firstGalley
                    ? route('journal.articles.galleys.download', ['slugOrId' => $a->slug, 'galleyId' => $firstGalley->id])
                    : null;

                $viewsCount = null;
                $downloadsCount = null;
                if (Schema::hasTable('article_metrics')) {
                    $viewsCount = (int) $a->metrics()->where('event_type', 'abstract_view')->sum('count');
                    $downloadsCount = (int) $a->metrics()->where('event_type', 'galley_download')->sum('count');
                }

                return [
                    'id' => $a->id,
                    'slug' => $a->slug,
                    'section' => $a->section?->name,
                    'pages' => $a->issues->first()?->pivot->pages,
                    'received_at' => $a->created_at?->format('d M Y'),
                    'published_at' => $a->published_at?->format('d M Y'),
                    'title' => $a->title,
                    'authors' => $authors,
                    'affiliations' => $affiliations,
                    'abstract' => $a->abstract,
                    'keywords' => is_array($a->keywords) ? $a->keywords : [],
                    'doi' => $a->doi,
                    'doi_url' => $a->doi ? "https://doi.org/{$a->doi}" : null,
                    'pdf_size' => null,
                    'downloads_count' => $downloadsCount,
                    'views_count' => $viewsCount,
                    'read_url' => route('journal.articles.show', $a->slug),
                    'download_url' => $downloadUrl,
                    'citation_url' => route('journal.articles.cite', ['slugOrId' => $a->slug, 'format' => 'apa']),
                ];
            })->all();
        }

        return [];
    }

    /**
     * Get Issue Archives (Arsip Edisi Terdahulu).
     *
     * @return array<int, array<string, mixed>>
     */
    public function archives(): array
    {
        if ($this->dbArchives && $this->dbArchives->isNotEmpty()) {
            return $this->dbArchives->map(fn (Issue $iss) => [
                'id' => $iss->id,
                'volume_no' => "Vol. {$iss->volume} No. {$iss->number}",
                'year_month' => $iss->year.' - '.($iss->published_at ? $iss->published_at->format('M') : ($iss->publication_date ? $iss->publication_date->format('M') : 'Edisi')),
                'title' => $iss->title,
                'total_articles' => $iss->articles_count ?? $iss->articles()->where('status', 'published')->count(),
                'url' => $this->safeRoute('journal.issues.show', ['id' => $iss->id], '#'),
            ])->all();
        }

        return [];
    }

    /**
     * Get Articles In Press (Terbitan Mendatang).
     *
     * @return array<int, array<string, mixed>>
     */
    public function articlesInPress(): array
    {
        try {
            if (Schema::hasTable('submissions')) {
                $inPress = Submission::with(['contributors', 'section'])
                    ->when($this->journal, fn ($q) => $q->where('journal_id', $this->journal->id))
                    ->whereIn('status', ['accepted', 'in_production'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get();

                return $inPress->map(fn ($sub) => [
                    'online_first_date' => $sub->updated_at ? $sub->updated_at->format('d F Y') : 'Online First',
                    'target_volume' => 'Menunggu Terbitan',
                    'title' => $sub->title,
                    'authors' => $sub->contributors->pluck('full_name')->all() ?: ['Peneliti'],
                    'preview_url' => '#',
                ])->all();
            }
        } catch (\Throwable) {
            //
        }

        return [];
    }

    /**
     * Get Publisher Metadata for Sidebar.
     *
     * @return array<string, string>
     */
    public function publisherInfo(): array
    {
        return [
            'journal_code' => $this->journal?->initials ?: 'SIRIDA',
            'peer_review_model' => $this->journal?->peer_review_policy ?: 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan).',
            'frequency' => $this->journal?->frequency ?: '2 Kali Setahun (Nomor 1 terbit Juni, Nomor 2 terbit Desember)',
            'apc_status' => $this->journal?->apc_fee ?: 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi / Diamond Open Access)',
            'publisher' => $this->journal?->publisher ?: 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
            'languages' => 'Bahasa Indonesia & Bahasa Inggris',
            'ethics_policy_url' => $this->safeRoute('journal.about.policies', [], '#etika-publikasi'),
        ];
    }

    /**
     * Get Indexing Logos and Accreditation metadata for Sidebar.
     *
     * @return array{accreditation: string, items: array<int, array<string, string>>}
     */
    public function indexingData(): array
    {
        return [
            'accreditation' => 'AKREDITASI S2',
            'items' => [
                ['name' => 'SINTA 2', 'provider' => 'Kemendikbudristek', 'desc' => 'Peringkat 2 Nasional', 'color' => 'text-amber-700'],
                ['name' => 'DOAJ', 'provider' => 'Directory Open Access', 'desc' => 'Indeks Internasional', 'color' => 'text-emerald-700'],
                ['name' => 'CROSSREF', 'provider' => 'Digital Object Identifier', 'desc' => 'Prefix 10.55981', 'color' => 'text-red-700'],
                ['name' => 'GARUDA', 'provider' => 'Garba Rujukan Digital', 'desc' => 'Portal Ilmiah Nasional', 'color' => 'text-sky-700'],
                ['name' => 'SCHOLAR', 'provider' => 'Google Scholar', 'desc' => 'Metrik Sitasi Global', 'color' => 'text-blue-700'],
                ['name' => 'DIMENSIONS', 'provider' => 'Digital Science', 'desc' => 'Matriks Bibliometrik', 'color' => 'text-indigo-700'],
            ],
        ];
    }

    /**
     * Get Impact & Citation Statistics for Sidebar (Data Nyata Database).
     *
     * @return array<string, string|int>
     */
    public function impactMetrics(): array
    {
        try {
            $totalArticles = Schema::hasTable('articles')
                ? Article::where('status', 'published')->count()
                : 0;

            $totalIssues = Schema::hasTable('issues')
                ? Issue::where('status', 'published')->count()
                : 0;

            $pdfDownloads = 0;
            $abstractViews = 0;
            if (Schema::hasTable('article_metrics')) {
                $pdfDownloads = (int) ArticleMetric::where('event_type', 'galley_download')->sum('count');
                $abstractViews = (int) ArticleMetric::where('event_type', 'abstract_view')->sum('count');
            }

            $acceptanceRate = '0%';
            if (Schema::hasTable('submissions')) {
                $totalSubmissions = Submission::count();
                $acceptedSubmissions = Submission::whereIn('status', ['published', 'accepted', 'in_production'])->count();
                if ($totalSubmissions > 0) {
                    $rate = round(($acceptedSubmissions / $totalSubmissions) * 100, 1);
                    $acceptanceRate = $rate.'%';
                }
            }

            return [
                'total_articles' => $totalArticles,
                'total_issues' => $totalIssues,
                'pdf_downloads' => $pdfDownloads,
                'abstract_views' => $abstractViews,
                'acceptance_rate' => $acceptanceRate,
            ];
        } catch (\Throwable) {
            return [
                'total_articles' => 0,
                'total_issues' => 0,
                'pdf_downloads' => 0,
                'abstract_views' => 0,
                'acceptance_rate' => '0%',
            ];
        }
    }

    /**
     * Get Editorial Announcements for Sidebar (Data Nyata Database).
     *
     * @return array<int, array{date: string, title: string, url: string}>
     */
    public function editorialAnnouncements(): array
    {
        try {
            if (Schema::hasTable('announcements')) {
                $announcements = Announcement::query()
                    ->when($this->journal, fn ($q) => $q->where('journal_id', $this->journal->id))
                    ->where('is_published', true)
                    ->latest('published_at')
                    ->take(4)
                    ->get();

                return $announcements->map(fn ($ann) => [
                    'date' => $ann->published_at ? $ann->published_at->format('d F Y') : $ann->created_at->format('d F Y'),
                    'title' => $ann->title,
                    'url' => $this->safeRoute('journal.announcements.show', ['id' => $ann->id], '#'),
                ])->all();
            }
        } catch (\Throwable) {
            //
        }

        return [];
    }

    /**
     * Get Footer details.
     *
     * @return array<string, string>
     */
    public function footerData(): array
    {
        return [
            'publisher_name' => $this->journal?->publisher ?: 'BRIDA KOTA MAKASSAR',
            'description' => $this->journal?->focus_scope ?: 'Publikasi berkala ilmiah resmi Badan Riset dan Inovasi Daerah Kota Makassar yang berfokus pada hasil penelitian terapan, kebijakan publik, inovasi perkotaan, dan teknologi daerah.',
            'p_issn' => $this->journal?->issn ?: '2746-8194',
            'e_issn' => $this->journal?->eissn ?: '2746-8208',
            'address' => "Gedung Balai Kota Makassar, Lantai 3\nJl. Ahmad Yani No. 2, Kel. Baru, Kec. Ujung Pandang\nKota Makassar, Sulawesi Selatan 90111",
            'email' => $this->journal?->editorial_email ?: 'jurnal.brida@makassar.go.id',
            'phone' => $this->journal?->contact_phone ?: '(0411) 3622455',
            'license_info' => $this->journal?->license ?: 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
            'copyright' => 'Badan Riset dan Inovasi Daerah Kota Makassar. Seluruh hak cipta dilindungi undang-undang.',
        ];
    }

    /**
     * Resolusi nama rute secara defensif dengan fallback jika rute belum terdaftar atau masih dalam cache lama.
     */
    protected function safeRoute(string $name, mixed $parameters = [], string $fallback = '#'): string
    {
        try {
            return Route::has($name) ? route($name, $parameters) : $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
