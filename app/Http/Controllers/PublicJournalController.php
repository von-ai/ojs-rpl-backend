<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Public\PublicArticleService;
use App\ViewModels\PublicJournalViewModel;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicJournalController extends Controller
{
    public function __construct(
        protected PublicArticleService $publicArticleService
    ) {}

    /**
     * Menampilkan Beranda Portal Jurnal Publik (UC-03).
     */
    public function index(Request $request): View
    {
        $filters = $this->searchFilters($request);
        $journal = $this->publicArticleService->getJournal();

        // Ambil data database nyata melalui Service
        $currentIssue = $this->publicArticleService->getCurrentIssue();
        $articles = $this->publicArticleService->searchArticles($filters, 12);
        $archives = $this->publicArticleService->getIssueArchives();

        $viewModel = new PublicJournalViewModel(
            searchQuery: $filters['q'] ?? null,
            selectedCategory: $filters['category'] ?? null,
            dbCurrentIssue: $currentIssue,
            dbArticles: $articles,
            dbArchives: $archives,
            journal: $journal
        );

        return view('journal.index', compact('viewModel', 'journal'));
    }

    /**
     * Menampilkan Halaman Hasil Pencarian Publik (UC-04).
     */
    public function search(Request $request): View
    {
        $filters = $this->searchFilters($request);
        $query = $filters['q'] ?? null;
        $category = $filters['category'] ?? null;
        $journal = $this->publicArticleService->getJournal();

        $results = $this->publicArticleService->searchArticles($filters, 10);

        $viewModel = new PublicJournalViewModel(
            searchQuery: $query,
            selectedCategory: $category,
            dbArticles: $results,
            journal: $journal
        );

        return view('journal.search', compact('viewModel', 'results', 'journal'));
    }

    /**
     * Menampilkan Halaman Detail Artikel Ilmiah Terbit (UC-03).
     */
    public function showArticle(Request $request, string $slugOrId): View
    {
        $journal = $this->publicArticleService->getJournal();
        $article = $this->publicArticleService->findPublishedArticle($slugOrId);

        if (! $article) {
            abort(404, 'Artikel ilmiah tidak ditemukan atau belum diterbitkan untuk publik.');
        }

        // Catat metrik kunjungan abstrak COUNTER-compliant
        $this->publicArticleService->recordMetric($article, 'abstract_view', $request);

        $citations = $this->publicArticleService->generateCitations($article);
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.article', compact('article', 'citations', 'viewModel', 'journal'));
    }

    /**
     * Mengunduh Berkas Galai PDF Publik (UC-03).
     */
    public function downloadGalley(
        Request $request,
        string $slugOrId,
        string $galleyId
    ): BinaryFileResponse|StreamedResponse {
        $article = $this->publicArticleService->findPublishedArticle($slugOrId);

        if (! $article) {
            abort(404, 'Artikel ilmiah tidak ditemukan.');
        }

        try {
            return $this->publicArticleService->downloadGalley($article, $galleyId, $request);
        } catch (DomainException $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Mengekspor Sitasi Artikel Ilmiah (BibTeX / RIS / Plain Text).
     */
    public function exportCitation(string $slugOrId, string $format): Response
    {
        $article = $this->publicArticleService->findPublishedArticle($slugOrId);

        if (! $article) {
            abort(404, 'Artikel ilmiah tidak ditemukan.');
        }

        $citations = $this->publicArticleService->generateCitations($article);
        $formatKey = strtolower($format);

        if (! isset($citations[$formatKey])) {
            abort(400, "Format sitasi '{$format}' tidak didukung.");
        }

        $content = $citations[$formatKey];
        $contentType = match ($formatKey) {
            'bibtex' => 'application/x-bibtex',
            'ris' => 'application/x-research-info-systems',
            default => 'text/plain; charset=UTF-8',
        };

        $extension = match ($formatKey) {
            'bibtex' => 'bib',
            'ris' => 'ris',
            default => 'txt',
        };

        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$article->slug}-citation.{$extension}\"",
        ]);
    }

    /**
     * Menampilkan Halaman Edisi Terkini (Current Issue).
     */
    public function currentIssue(Request $request): View
    {
        $journal = $this->publicArticleService->getJournal();
        $currentIssue = $this->publicArticleService->getCurrentIssue();

        if (! $currentIssue) {
            abort(404, 'Edisi terbitan terkini belum dipublikasikan.');
        }

        $viewModel = new PublicJournalViewModel(
            dbCurrentIssue: $currentIssue,
            dbArticles: null,
            journal: $journal
        );

        return view('journal.issues.show', [
            'issue' => $currentIssue,
            'viewModel' => $viewModel,
            'isCurrent' => true,
            'journal' => $journal,
        ]);
    }

    /**
     * Menampilkan Detail Edisi Tertentu (UC-03).
     */
    public function showIssue(Request $request, string $id): View
    {
        $journal = $this->publicArticleService->getJournal();
        $issue = $this->publicArticleService->getIssueById($id, $journal);

        if (! $issue) {
            abort(404, 'Edisi jurnal tidak ditemukan atau belum diterbitkan.');
        }

        $viewModel = new PublicJournalViewModel(
            dbCurrentIssue: $issue,
            dbArticles: null,
            journal: $journal
        );

        return view('journal.issues.show', [
            'issue' => $issue,
            'viewModel' => $viewModel,
            'isCurrent' => (bool) $issue->is_current,
            'journal' => $journal,
        ]);
    }

    /**
     * Menampilkan Halaman Arsip Seluruh Edisi Terbitan.
     */
    public function archives(Request $request): View
    {
        $journal = $this->publicArticleService->getJournal();
        $issues = $this->publicArticleService->getAllPublishedIssues($journal, 12);
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.issues.archive', compact('issues', 'viewModel', 'journal'));
    }

    /**
     * Menampilkan Informasi Umum Jurnal (Tentang Jurnal).
     */
    public function about(): View
    {
        $journal = $this->publicArticleService->getJournal();
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.about.index', compact('viewModel', 'journal'));
    }

    /**
     * Menampilkan Susunan Dewan Redaksi dan Mitra Bestari.
     */
    public function editorialTeam(): View
    {
        $journal = $this->publicArticleService->getJournal();
        $team = $this->publicArticleService->getEditorialTeam($journal);
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.about.editorial-team', compact('team', 'viewModel', 'journal'));
    }

    /**
     * Menampilkan Kebijakan Redaksi, Etika Publikasi & Hak Cipta.
     */
    public function policies(): View
    {
        $journal = $this->publicArticleService->getJournal();
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.about.policies', compact('viewModel', 'journal'));
    }

    /**
     * Menampilkan Panduan Penulis & Pengiriman Naskah (Author Guidelines).
     */
    public function submissionsGuide(): View
    {
        $journal = $this->publicArticleService->getJournal();
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.about.submissions', compact('viewModel', 'journal'));
    }

    /**
     * Menampilkan Informasi Kontak & Sekretariat Redaksi BRIDA Makassar.
     */
    public function contact(): View
    {
        $journal = $this->publicArticleService->getJournal();
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.about.contact', compact('viewModel', 'journal'));
    }

    /**
     * Menampilkan Daftar Pengumuman Resmi Jurnal.
     */
    public function announcements(): View
    {
        $journal = $this->publicArticleService->getJournal();
        $announcements = $this->publicArticleService->getAnnouncements($journal, 10);
        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.announcements.index', compact('announcements', 'viewModel', 'journal'));
    }

    /**
     * Menampilkan Detail Pengumuman Resmi.
     */
    public function showAnnouncement(string $id): View
    {
        $journal = $this->publicArticleService->getJournal();
        $announcement = $this->publicArticleService->findAnnouncement($id, $journal);

        if (! $announcement) {
            abort(404, 'Pengumuman tidak ditemukan atau belum dipublikasikan.');
        }

        $viewModel = new PublicJournalViewModel(journal: $journal);

        return view('journal.announcements.show', compact('announcement', 'viewModel', 'journal'));
    }

    /** @return array{q?: ?string, category?: ?string, year?: int|string|null} */
    private function searchFilters(Request $request): array
    {
        $validator = Validator::make($request->query(), [
            'q' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
            'year' => ['nullable', 'integer'],
        ]);
        abort_if($validator->fails(), 400, 'Parameter pencarian tidak valid.');

        return $validator->validated();
    }
}
