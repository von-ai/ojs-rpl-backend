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
use Illuminate\Support\Str;

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
            'journals' => $this->availableJournals(),
        ];
    }

    /**
     * Mengambil daftar seluruh jurnal aktif untuk pengalih dan navigasi portal publik.
     *
     * @return array<int, array{id: string, name: string, initials: string, slug: string, url: string, archives_url: string, is_current: bool}>
     */
    public function availableJournals(): array
    {
        try {
            if (! Schema::hasTable('journals')) {
                return [];
            }

            return Journal::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'initials', 'publisher', 'issn', 'eissn', 'cover_image_path', 'cover_image_updated_at'])
                ->map(fn ($j) => [
                    'id' => (string) $j->id,
                    'name' => (string) $j->name,
                    'initials' => (string) ($j->initials ?: strtoupper(substr($j->name, 0, 6))),
                    'slug' => (string) $j->slug,
                    'publisher' => (string) ($j->publisher ?: 'BRIDA Kota Makassar'),
                    'issn' => $j->issn,
                    'eissn' => $j->eissn,
                    'cover_url' => $j->coverImageUrl(),
                    'url' => url('/'.$j->slug),
                    'archives_url' => url('/'.$j->slug.'/archives'),
                    'is_current' => $this->journal && (string) $this->journal->id === (string) $j->id,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Mengambil daftar komprehensif seluruh jurnal ilmiah untuk halaman direktori publik.
     *
     * @return array<int, array<string, mixed>>
     */
    public function directoryJournals(?string $search = null): array
    {
        try {
            if (! Schema::hasTable('journals')) {
                return [];
            }

            $query = Journal::query()
                ->where('status', 'active')
                ->withCount([
                    'issues' => fn ($q) => $q->where('status', 'published'),
                    'articles as published_articles_count' => fn ($q) => $q->where('status', 'published'),
                ])
                ->with(['currentIssue']);

            if ($search !== null && trim($search) !== '') {
                $term = '%'.trim($search).'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('initials', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('publisher', 'like', $term)
                        ->orWhere('issn', 'like', $term)
                        ->orWhere('eissn', 'like', $term);
                });
            }

            return $query->orderBy('name')->get()->map(function (Journal $j): array {
                $slug = (string) $j->slug;
                $initials = (string) ($j->initials ?: strtoupper(substr($j->name, 0, 6)));

                return [
                    'id' => (string) $j->id,
                    'name' => (string) $j->name,
                    'initials' => $initials,
                    'slug' => $slug,
                    'publisher' => $j->publisher ?: 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                    'issn' => $j->issn,
                    'eissn' => $j->eissn,
                    'description' => $j->description ?: $j->focus_scope,
                    'frequency' => $j->frequency ?: '2 Kali Setahun',
                    'apc_label' => $j->isPaidApc() ? 'Berbayar (APC)' : 'Bebas Biaya (Gold OA)',
                    'is_paid' => $j->isPaidApc(),
                    'published_issues_count' => (int) $j->issues_count,
                    'published_articles_count' => (int) $j->published_articles_count,
                    'current_issue' => $j->currentIssue ? [
                        'id' => (string) $j->currentIssue->id,
                        'title' => (string) $j->currentIssue->title,
                        'volume' => (string) $j->currentIssue->volume,
                        'number' => (string) $j->currentIssue->number,
                        'year' => (int) $j->currentIssue->year,
                    ] : null,
                    'url' => url('/'.$slug),
                    'cover_url' => $j->coverImageUrl(),
                    'archives_url' => url('/'.$slug.'/archives'),
                    'current_issue_url' => url('/'.$slug.'/issues/current'),
                    'about_url' => url('/'.$slug.'/about'),
                    'is_current' => $this->journal && (string) $this->journal->id === (string) $j->id,
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Menyusun daftar slide Hero Beranda yang berganti-ganti dari semua edisi & jurnal aktif.
     *
     * @return array<int, array<string, mixed>>
     */
    public function heroSlides(): array
    {
        try {
            if (! Schema::hasTable('journals')) {
                return [];
            }

            $slides = [];

            if (Schema::hasTable('issues')) {
                $publishedIssues = Issue::query()
                    ->where('status', 'published')
                    ->whereHas('journal', fn ($q) => $q->where('status', 'active'))
                    ->with(['journal'])
                    ->orderByDesc('is_current')
                    ->orderByDesc('published_at')
                    ->orderByDesc('year')
                    ->orderByDesc('volume')
                    ->orderByDesc('number')
                    ->limit(8)
                    ->get();

                foreach ($publishedIssues as $issue) {
                    $j = $issue->journal;
                    if (! $j) {
                        continue;
                    }

                    $volumeLabel = "Vol. {$issue->volume} No. {$issue->number} ({$issue->year})";
                    $slides[] = [
                        'journal_id' => (string) $j->id,
                        'name' => (string) $j->name,
                        'initials' => (string) ($j->initials ?: strtoupper(substr($j->name, 0, 6))),
                        'publisher' => (string) ($j->publisher ?: 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar'),
                        'issn' => $j->issn ?: '2746-8194',
                        'eissn' => $j->eissn ?: '2746-8208',
                        'cover_url' => $j->coverImageUrl(),
                        'volume_label' => $volumeLabel,
                        'issue_title' => $issue->title ? (string) $issue->title : null,
                        'description' => (string) ($issue->description ?: $j->focus_scope ?: 'Publikasi ilmiah berkala resmi Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar.'),
                        'is_paid' => $j->isPaidApc(),
                        'apc_label' => $j->isPaidApc() ? ($j->apc_fee ?: 'Berbayar') : 'Rp 0 (Open Access)',
                        'issue_url' => url('/'.$j->slug.'/issues/'.$issue->id),
                        'journal_url' => url('/'.$j->slug),
                        'about_url' => url('/'.$j->slug.'/about'),
                        'submissions_url' => url('/'.$j->slug.'/about/submissions'),
                    ];
                }
            }

            // Pastikan seluruh jurnal aktif yang belum memiliki edisi terbit juga tampil di rotasi hero
            $coveredJournalIds = collect($slides)->pluck('journal_id')->unique()->all();
            $remainingJournals = Journal::query()
                ->where('status', 'active')
                ->when(! empty($coveredJournalIds), fn ($q) => $q->whereNotIn('id', $coveredJournalIds))
                ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$this->journal?->id ?? ''])
                ->orderBy('name')
                ->get();

            foreach ($remainingJournals as $j) {
                $slides[] = [
                    'journal_id' => (string) $j->id,
                    'name' => (string) $j->name,
                    'initials' => (string) ($j->initials ?: strtoupper(substr($j->name, 0, 6))),
                    'publisher' => (string) ($j->publisher ?: 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar'),
                    'issn' => $j->issn ?: '2746-8194',
                    'eissn' => $j->eissn ?: '2746-8208',
                    'cover_url' => $j->coverImageUrl(),
                    'volume_label' => null,
                    'issue_title' => null,
                    'description' => (string) ($j->focus_scope ?: 'Publikasi ilmiah berkala resmi Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar.'),
                    'is_paid' => $j->isPaidApc(),
                    'apc_label' => $j->isPaidApc() ? ($j->apc_fee ?: 'Berbayar') : 'Rp 0 (Open Access)',
                    'issue_url' => url('/'.$j->slug.'/issues/current'),
                    'journal_url' => url('/'.$j->slug),
                    'about_url' => url('/'.$j->slug.'/about'),
                    'submissions_url' => url('/'.$j->slug.'/about/submissions'),
                ];
            }

            return $slides;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Statistik ringkas lintas portal untuk strip metrik sederhana di bawah hero beranda.
     *
     * @return array{total_journals: int, total_articles: int, total_issues: int, pdf_downloads: int}
     */
    public function portalSummaryMetrics(): array
    {
        $metrics = $this->impactMetrics();
        $totalJournals = 1;

        try {
            if (Schema::hasTable('journals')) {
                $totalJournals = max(1, Journal::where('status', 'active')->count());
            }
        } catch (\Throwable) {
            $totalJournals = 1;
        }

        return [
            'total_journals' => $totalJournals,
            'total_articles' => (int) ($metrics['total_articles'] ?? 0),
            'total_issues' => (int) ($metrics['total_issues'] ?? 0),
            'pdf_downloads' => (int) ($metrics['pdf_downloads'] ?? 0),
        ];
    }

    /**
     * Get main navigation bar links.
     *
     * @return array<int, array{label: string, url: string, active: bool}>
     */
    public function navLinks(): array
    {
        $scopedSlug = request()->route('journalSlug');
        $basePrefix = $scopedSlug ? "/{$scopedSlug}" : '';

        return [
            ['label' => 'Beranda', 'url' => $scopedSlug ? url($basePrefix) : $this->safeRoute('journal.index', [], '/'), 'active' => request()->routeIs('journal.index') || request()->routeIs('journal.scoped.index')],
            ['label' => 'Katalog Jurnal', 'url' => $this->safeRoute('journal.journals.index', [], '/journals'), 'active' => request()->routeIs('journal.journals.*') || request()->routeIs('journal.scoped.journals.*')],
            ['label' => 'Cari Artikel', 'url' => $scopedSlug ? url("{$basePrefix}/articles/search") : $this->safeRoute('journal.articles.search', [], '/articles/search'), 'active' => request()->routeIs('journal.articles.search') || request()->routeIs('journal.scoped.articles.search')],
            ['label' => 'Pengumuman', 'url' => $scopedSlug ? url("{$basePrefix}/announcements") : $this->safeRoute('journal.announcements.index', [], '/announcements'), 'active' => request()->routeIs('journal.announcements.*') || request()->routeIs('journal.scoped.announcements.*')],
            ['label' => 'Tentang', 'url' => $scopedSlug ? url("{$basePrefix}/about") : $this->safeRoute('journal.about', [], '/about'), 'active' => request()->routeIs('journal.about*') || request()->routeIs('journal.scoped.about*')],
        ];
    }

    /**
     * Get active Call For Papers announcement banner data from database.
     *
     * @return array{title: string, deadline: string, detail_url: string}|null
     */
    public function announcement(): ?array
    {
        try {
            if (Schema::hasTable('announcements')) {
                $scopedSlug = request()->route('journalSlug');
                $latest = Announcement::query()
                    ->when(
                        $scopedSlug && $this->journal,
                        fn ($q) => $q->where('journal_id', $this->journal->id),
                        fn ($q) => $q->whereHas('journal', fn ($jq) => $jq->where('status', 'active'))
                    )
                    ->where('is_published', true)
                    ->latest('published_at')
                    ->first();

                if ($latest) {
                    return [
                        'title' => $latest->title,
                        'deadline' => $latest->published_at ? $latest->published_at->translatedFormat('d F Y') : 'Terbuka',
                        'detail_url' => $this->safeRoute('journal.announcements.show', ['id' => $latest->id], $this->safeRoute('journal.announcements.index', [], '/announcements')),
                    ];
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Mengambil hingga 3 pengumuman terbit terbaru dari database untuk ditampilkan di beranda.
     *
     * @return array<int, array{id: string, title: string, excerpt: string, date: string, journal_initials: ?string, url: string}>
     */
    public function latestAnnouncements(int $limit = 3): array
    {
        try {
            if (! Schema::hasTable('announcements')) {
                return [];
            }

            $scopedSlug = request()->route('journalSlug');

            return Announcement::with(['journal'])
                ->where('is_published', true)
                ->when(
                    $scopedSlug && $this->journal,
                    fn ($q) => $q->where('journal_id', $this->journal->id),
                    fn ($q) => $q->whereHas('journal', fn ($jq) => $jq->where('status', 'active'))
                )
                ->orderByDesc('published_at')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function (Announcement $ann) use ($scopedSlug): array {
                    $url = $scopedSlug
                        ? url("/{$scopedSlug}/announcements/{$ann->id}")
                        : $this->safeRoute('journal.announcements.show', ['id' => $ann->id], '/announcements');

                    return [
                        'id' => (string) $ann->id,
                        'title' => (string) $ann->title,
                        'excerpt' => Str::limit(trim(strip_tags((string) $ann->body)), 160),
                        'date' => $ann->published_at
                            ? $ann->published_at->translatedFormat('d M Y')
                            : ($ann->created_at ? $ann->created_at->translatedFormat('d M Y') : ''),
                        'journal_initials' => $ann->journal?->initials ?: $ann->journal?->name,
                        'url' => $url,
                    ];
                })
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Get catalog search scope categories.
     *
     * @return array<int, array{name: string, count: int}>
     */
    public function categories(): array
    {
        try {
            if ($this->journal && Schema::hasTable('sections')) {
                $sections = $this->journal->sections()
                    ->when(Schema::hasTable('articles'), fn ($q) => $q->withCount(['articles' => fn ($aq) => $aq->where('status', 'published')]))
                    ->get();

                if ($sections->isNotEmpty()) {
                    return $sections->map(fn ($s) => [
                        'name' => $s->name,
                        'count' => (int) ($s->articles_count ?? 0),
                    ])->all();
                }
            }

            if (Schema::hasTable('sections')) {
                $sections = Section::query()
                    ->when(Schema::hasTable('articles'), fn ($q) => $q->withCount(['articles' => fn ($aq) => $aq->where('status', 'published')]))
                    ->get();
                if ($sections->isNotEmpty()) {
                    return $sections->map(fn ($s) => [
                        'name' => $s->name,
                        'count' => (int) ($s->articles_count ?? 0),
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

            $rawTitle = (string) $this->dbCurrentIssue->title;
            $cleanThemeTitle = $rawTitle;
            if (preg_match('/^Vol\.\s*\d+\s*No\.\s*\d+(\s*\(\d{4}\))?\s*:\s*(.+)$/i', $cleanThemeTitle, $matches)) {
                $cleanThemeTitle = trim($matches[2]);
            }

            $volumeLabel = "Vol. {$this->dbCurrentIssue->volume} No. {$this->dbCurrentIssue->number} ({$this->dbCurrentIssue->year})";
            $fullTitle = $cleanThemeTitle !== '' ? "{$volumeLabel}: {$cleanThemeTitle}" : $volumeLabel;

            return [
                'volume_label' => $volumeLabel,
                'edition_label' => $cleanThemeTitle,
                'theme_title' => $cleanThemeTitle,
                'published_at' => $this->dbCurrentIssue->published_at ? $this->dbCurrentIssue->published_at->locale('id')->translatedFormat('d F Y') : ($this->dbCurrentIssue->publication_date ? $this->dbCurrentIssue->publication_date->locale('id')->translatedFormat('d F Y') : $this->dbCurrentIssue->created_at->locale('id')->translatedFormat('d F Y')),
                'doi' => $this->dbCurrentIssue->doi_prefix ? "{$this->dbCurrentIssue->doi_prefix}/vol{$this->dbCurrentIssue->volume}no{$this->dbCurrentIssue->number}" : "10.55981/sirida.{$this->dbCurrentIssue->year}.v{$this->dbCurrentIssue->volume}i{$this->dbCurrentIssue->number}",
                'title' => $fullTitle,
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
                'volume' => $iss->volume,
                'number' => $iss->number,
                'year' => $iss->year,
                'volume_no' => "Vol. {$iss->volume} No. {$iss->number}",
                'year_month' => $iss->published_at ? $iss->published_at->locale('id')->translatedFormat('F Y') : ($iss->publication_date ? $iss->publication_date->locale('id')->translatedFormat('F Y') : "Tahun {$iss->year}"),
                'title' => $iss->title,
                'description' => $iss->description,
                'is_current' => (bool) ($iss->is_current ?? false),
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
                    ->whereIn('status', ['accepted', 'in_production', 'ready_for_issue', 'scheduled'])
                    ->latest('updated_at')
                    ->take(5)
                    ->get();

                return $inPress->map(fn ($sub) => [
                    'online_first_date' => $sub->updated_at ? $sub->updated_at->locale('id')->translatedFormat('d F Y') : 'Online First',
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
                $acceptedSubmissions = Submission::whereIn('status', ['published', 'accepted', 'in_production', 'ready_for_issue', 'scheduled'])->count();
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

    public function licenseLabel(): string
    {
        return trim((string) $this->journal?->license) ?: 'Lisensi belum ditetapkan';
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
            'license_info' => $this->licenseLabel(),
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
