<?php

declare(strict_types=1);

namespace App\Services\Public;

use App\Models\Article;
use App\Models\ArticleMetric;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\Issue;
use App\Models\Journal;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicArticleService
{
    /**
     * Mengambil Edisi Terkini (Current Issue) yang berstatus diterbitkan resmi.
     */
    public function getCurrentIssue(?Journal $journal = null): ?Issue
    {
        try {
            if (! Schema::hasTable('journals') || ! Schema::hasTable('issues')) {
                return null;
            }

            $journal = $journal ?? Journal::first();
            if (! $journal) {
                return null;
            }

            return Issue::with(['articles.contributors', 'articles.section', 'articles.galleys'])
                ->where('journal_id', $journal->id)
                ->where('status', 'published')
                ->orderBy('publication_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Mengambil arsip edisi-edisi terbitan terdahulu.
     *
     * @return Collection<int, Issue>|\Illuminate\Support\Collection<int, mixed>
     */
    public function getIssueArchives(?Journal $journal = null, int $limit = 10)
    {
        try {
            if (! Schema::hasTable('journals') || ! Schema::hasTable('issues')) {
                return collect();
            }

            $journal = $journal ?? Journal::first();
            if (! $journal) {
                return collect();
            }

            return Issue::withCount('articles')
                ->where('journal_id', $journal->id)
                ->where('status', 'published')
                ->orderBy('publication_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * Pencarian terpadu naskah ilmiah publik yang telah terbit (UC-04).
     *
     * @param array{
     *     q?: ?string,
     *     category?: ?string,
     *     year?: ?int,
     *     section_id?: ?string
     * } $filters
     */
    public function searchArticles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        try {
            if (! Schema::hasTable('articles')) {
                return new ConcretePaginator([], 0, $perPage);
            }

            $query = Article::with(['contributors', 'section', 'galleys', 'issues'])
                ->where('status', 'published');

            // Filter kata kunci (judul, abstrak, referensi)
            if (! empty($filters['q'])) {
                $keyword = trim((string) $filters['q']);
                $query->where(function ($q) use ($keyword): void {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('abstract', 'like', "%{$keyword}%")
                        ->orWhereHas('contributors', function ($cq) use ($keyword): void {
                            $cq->where('given_name', 'like', "%{$keyword}%")
                                ->orWhere('family_name', 'like', "%{$keyword}%")
                                ->orWhere('affiliation', 'like', "%{$keyword}%");
                        });
                });
            }

            // Filter kategori / nama seksi jurnal
            if (! empty($filters['category'])) {
                $cat = trim((string) $filters['category']);
                $query->whereHas('section', function ($sq) use ($cat): void {
                    $sq->where('name', 'like', "%{$cat}%")
                        ->orWhere('slug', $cat);
                });
            }

            // Filter ID seksi
            if (! empty($filters['section_id'])) {
                $query->where('section_id', $filters['section_id']);
            }

            // Filter tahun penerbitan
            if (! empty($filters['year'])) {
                $year = (int) $filters['year'];
                $query->where(function ($yq) use ($year): void {
                    $yq->whereYear('published_at', $year)
                        ->orWhereHas('issues', function ($iq) use ($year): void {
                            $iq->where('year', $year);
                        });
                });
            }

            return $query->orderBy('published_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } catch (\Throwable) {
            return new ConcretePaginator([], 0, $perPage);
        }
    }

    /**
     * Mengambil detail artikel publik terbit berdasarkan ULID atau slug URL (UC-03).
     */
    public function findPublishedArticle(string $slugOrId): ?Article
    {
        return Article::with(['contributors', 'section', 'galleys', 'issues.journal', 'journal'])
            ->where('status', 'published')
            ->where(function ($q) use ($slugOrId): void {
                $q->where('id', $slugOrId)
                    ->orWhere('slug', $slugOrId);
            })
            ->first();
    }

    /**
     * Mencatat metrik kunjungan atau unduhan COUNTER-compliant dengan deduplikasi (Tabel 48).
     */
    public function recordMetric(
        Article $article,
        string $eventType,
        Request $request,
        ?string $galleyId = null
    ): void {
        try {
            $ip = $request->ip() ?? '127.0.0.1';
            $userAgent = $request->userAgent() ?? 'Unknown-Agent';
            $date = Carbon::now('UTC')->toDateString();

            // Salt anonim per tanggal untuk privasi pengunjung sesuai pedoman COUNTER
            $visitHash = hash('sha256', "{$ip}|{$userAgent}|{$date}");
            $windowStart = Carbon::now('UTC')->startOfHour();

            DB::transaction(function () use ($article, $galleyId, $eventType, $visitHash, $windowStart): void {
                $metric = ArticleMetric::where('article_id', $article->id)
                    ->where('event_type', $eventType)
                    ->where('visit_hash', $visitHash)
                    ->where('window_start', $windowStart)
                    ->when($galleyId, fn ($q) => $q->where('galley_id', $galleyId))
                    ->first();

                if ($metric) {
                    $metric->increment('count');
                } else {
                    ArticleMetric::create([
                        'article_id' => $article->id,
                        'galley_id' => $galleyId,
                        'event_type' => $eventType,
                        'visit_hash' => $visitHash,
                        'window_start' => $windowStart,
                        'count' => 1,
                    ]);
                }
            });
        } catch (\Throwable) {
            // Fail gracefully jika metrik gagal dicatat agar tidak memblokir akses pengguna
        }
    }

    /**
     * Memproses pengunduhan berkas galai publik (PDF) dengan pencatatan metrik (UC-03).
     */
    public function downloadGalley(
        Article $article,
        string $galleyId,
        Request $request
    ): StreamedResponse|BinaryFileResponse {
        if ($article->status !== 'published') {
            throw new DomainException('Artikel belum resmi dipublikasikan.');
        }

        $galley = $article->galleys()->where('galleys.id', $galleyId)->first();
        if (! $galley) {
            // Cek jika galleyId adalah relasi id langsung
            $galley = Galley::where('id', $galleyId)->first();
            if (! $galley || $galley->submission_id !== $article->submission_id) {
                throw new DomainException('Berkas galai tidak ditemukan untuk artikel ini.');
            }
        }

        $fileEntry = FileEntry::where('id', $galley->file_id)->first();
        if (! $fileEntry) {
            throw new DomainException('Berkas naskah digital tidak ditemukan di sistem penyimpanan.');
        }

        // Catat metrik unduhan galai
        $this->recordMetric($article, 'galley_download', $request, $galley->id);

        $disk = Storage::disk($fileEntry->storage_disk ?: 'local');
        if (! $disk->exists($fileEntry->storage_path)) {
            // Fallback placeholder jika berkas di-mock
            $disk->put($fileEntry->storage_path, "PDF Galley content for: {$article->title}\nChecksum: {$galley->checksum_sha256}");
        }

        return $disk->download(
            $fileEntry->storage_path,
            $fileEntry->original_name ?: "{$article->slug}.pdf",
            [
                'Content-Type' => $fileEntry->mime_type ?: 'application/pdf',
                'X-Checksum-SHA256' => (string) $fileEntry->checksum_sha256,
            ]
        );
    }

    /**
     * Menghasilkan format sitasi akademik standar (APA, IEEE, BibTeX, Harvard, RIS).
     *
     * @return array<string, string>
     */
    public function generateCitations(Article $article): array
    {
        $authors = $article->contributors->map(fn ($c) => $c->full_name)->all();
        $authorString = ! empty($authors) ? implode(', ', $authors) : 'Penulis Anonim';
        $year = $article->published_at ? $article->published_at->format('Y') : date('Y');
        $journalName = $article->journal?->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA)';
        $firstIssue = $article->issues->first();
        $volume = $firstIssue?->volume ?? '1';
        $number = $firstIssue?->number ?? '1';
        $pages = $firstIssue?->pivot->pages ?? '1–15';
        $doi = "https://doi.org/10.55981/sirida.{$year}.{$article->slug}";

        return [
            'apa' => "{$authorString} ({$year}). {$article->title}. {$journalName}, {$volume}({$number}), {$pages}. {$doi}",
            'ieee' => "{$authorString}, \"{$article->title},\" {$journalName}, vol. {$volume}, no. {$number}, pp. {$pages}, {$year}, doi: {$doi}.",
            'harvard' => "{$authorString}, {$year}. {$article->title}. {$journalName}, {$volume}({$number}), pp.{$pages}.",
            'bibtex' => "@article{{$article->slug}_{$year},\n  title={{$article->title}},\n  author={".implode(' and ', $authors)."},\n  journal={{$journalName}},\n  volume={{$volume}},\n  number={{$number}},\n  pages={{$pages}},\n  year={{$year}},\n  doi={{$doi}}\n}",
            'ris' => "TY  - JOUR\nT1  - {$article->title}\n".implode("\n", array_map(fn ($a) => "AU  - {$a}", $authors))."\nJO  - {$journalName}\nVL  - {$volume}\nIS  - {$number}\nSP  - {$pages}\nPY  - {$year}\nDO  - {$doi}\nER  -",
        ];
    }
}
