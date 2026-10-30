<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Public\PublicArticleService;
use App\ViewModels\PublicJournalViewModel;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $searchQuery = $request->input('q');
        $selectedCategory = $request->input('category');
        $year = $request->filled('year') ? (int) $request->input('year') : null;

        // Ambil data database nyata melalui Service
        $currentIssue = $this->publicArticleService->getCurrentIssue();
        $articles = $this->publicArticleService->searchArticles([
            'q' => $searchQuery,
            'category' => $selectedCategory,
            'year' => $year,
        ], 12);
        $archives = $this->publicArticleService->getIssueArchives();

        $viewModel = new PublicJournalViewModel(
            searchQuery: $searchQuery,
            selectedCategory: $selectedCategory,
            dbCurrentIssue: $currentIssue,
            dbArticles: $articles,
            dbArchives: $archives
        );

        return view('journal.index', compact('viewModel'));
    }

    /**
     * Menampilkan Halaman Hasil Pencarian Publik (UC-04).
     */
    public function search(Request $request): View
    {
        $query = $request->input('q');
        $category = $request->input('category');
        $year = $request->filled('year') ? (int) $request->input('year') : null;

        $results = $this->publicArticleService->searchArticles([
            'q' => $query,
            'category' => $category,
            'year' => $year,
        ], 10);

        $viewModel = new PublicJournalViewModel(
            searchQuery: $query,
            selectedCategory: $category,
            dbArticles: $results
        );

        return view('journal.search', compact('viewModel', 'results'));
    }

    /**
     * Menampilkan Halaman Detail Artikel Ilmiah Terbit (UC-03).
     */
    public function showArticle(Request $request, string $slugOrId): View
    {
        $article = $this->publicArticleService->findPublishedArticle($slugOrId);

        if (! $article) {
            abort(404, 'Artikel ilmiah tidak ditemukan atau belum diterbitkan untuk publik.');
        }

        // Catat metrik kunjungan abstrak COUNTER-compliant
        $this->publicArticleService->recordMetric($article, 'abstract_view', $request);

        $citations = $this->publicArticleService->generateCitations($article);
        $viewModel = new PublicJournalViewModel;

        return view('journal.article', compact('article', 'citations', 'viewModel'));
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
}
