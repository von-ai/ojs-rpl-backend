<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\ArticleContributor;
use App\Models\FileEntry;
use App\Models\Galley;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicJournalPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Journal $journal;

    protected Section $section;

    protected User $author;

    protected Issue $issue;

    protected Article $publishedArticle;

    protected Submission $privateSubmission;

    protected Galley $galley;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');

        $this->author = User::factory()->create([
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'email' => 'author@makassar.go.id',
        ]);

        $this->journal = Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'issn' => '2985-7821',
            'eissn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);

        $this->section = Section::create([
            'journal_id' => $this->journal->id,
            'slug' => 'inovasi-daerah',
            'name' => 'Inovasi Daerah & Kebijakan Publik',
            'description' => 'Artikel kajian inovasi dan kebijakan publik daerah',
            'accepting_submissions' => true,
            'position' => 1,
            'version' => 1,
        ]);

        $this->issue = Issue::create([
            'journal_id' => $this->journal->id,
            'volume' => '4',
            'number' => '1',
            'year' => 2026,
            'title' => 'Akselerasi Ekosistem Digital Kota Makassar',
            'description' => 'Edisi khusus publikasi hasil penelitian terapan',
            'status' => 'published',
            'publication_date' => now(),
            'version' => 1,
        ]);

        $submission = Submission::create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PUB01',
            'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Smart City',
            'abstract' => 'Penelitian ini mengevaluasi dampak sosial ekonomi lorong wisata di Kota Makassar.',
            'keywords' => ['Lorong Wisata', 'Smart City', 'Makassar'],
            'status' => 'published',
            'version' => 3,
        ]);

        $this->publishedArticle = Article::create([
            'submission_id' => $submission->id,
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'slug' => 'evaluasi-kebijakan-lorong-wisata-smart-city',
            'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Smart City',
            'abstract' => 'Penelitian ini mengevaluasi dampak sosial ekonomi lorong wisata di Kota Makassar.',
            'keywords' => ['Lorong Wisata', 'Smart City', 'Makassar'],
            'license' => 'CC BY 4.0',
            'status' => 'published',
            'published_at' => now(),
            'version' => 1,
        ]);

        $this->issue->articles()->attach($this->publishedArticle->id, [
            'position' => 1,
            'pages' => '1–15',
        ]);

        ArticleContributor::create([
            'article_id' => $this->publishedArticle->id,
            'given_name' => 'Andi Muhammad',
            'family_name' => 'Ilyas',
            'affiliation' => 'BRIDA Kota Makassar',
            'orcid' => '0000-0002-1825-0097',
            'position' => 1,
        ]);

        // Buat file PDF galai
        $pdfPath = "galleys/{$this->publishedArticle->id}/article.pdf";
        Storage::disk('local')->put($pdfPath, 'PDF Content for Lorong Wisata Article');

        $fileEntry = FileEntry::create([
            'storage_disk' => 'local',
            'storage_path' => $pdfPath,
            'original_name' => 'lorong-wisata.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 36,
            'checksum_sha256' => hash('sha256', 'PDF Content for Lorong Wisata Article'),
            'virus_scan_status' => 'clean',
            'created_by' => $this->author->id,
        ]);

        $this->galley = Galley::create([
            'submission_id' => $submission->id,
            'file_id' => $fileEntry->id,
            'format' => 'pdf',
            'stage' => 'approved',
            'status' => 'active',
            'checksum_sha256' => $fileEntry->checksum_sha256,
            'label' => 'PDF Resmi Publikasi',
            'version' => 1,
        ]);

        $this->publishedArticle->galleys()->attach($this->galley->id, ['position' => 1]);

        // Naskah privat yang belum terbit (tidak boleh muncul di publik)
        $this->privateSubmission = Submission::create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $this->author->id,
            'submission_number' => 'SUB-2026-PRIV99',
            'title' => 'Naskah Rahasia Masih Tahap Review Internal',
            'abstract' => 'Abstrak rahasia belum boleh dibaca publik.',
            'keywords' => ['Rahasia', 'Internal'],
            'status' => 'in_review',
            'version' => 1,
        ]);
    }

    /**
     * Beranda portal jurnal dapat diakses oleh Public User tanpa login (UC-03).
     */
    public function test_beranda_portal_jurnal_dapat_diakses_oleh_public_user_tanpa_login(): void
    {
        $response = $this->get(route('journal.index'));

        $response->assertStatus(200);
        $response->assertSee('2985-7821'); // ISSN
        $response->assertSee('Akselerasi Ekosistem Digital Kota Makassar'); // Current Issue Title
        $response->assertSee('Evaluasi Kebijakan Lorong Wisata Berbasis Smart City'); // Published Article
    }

    /**
     * Public user dapat mencari artikel ilmiah berdasarkan kata kunci (UC-04).
     */
    public function test_public_user_dapat_mencari_artikel_ilmiah_berdasarkan_kata_kunci(): void
    {
        $response = $this->get(route('journal.articles.search', ['q' => 'Lorong Wisata']));

        $response->assertStatus(200);
        $response->assertSee('Evaluasi Kebijakan Lorong Wisata Berbasis Smart City');
        $response->assertSee('1 Artikel Ditemukan');
    }

    /**
     * Pencarian publik tidak membocorkan naskah privat yang belum berstatus published (UC-04).
     */
    public function test_pencarian_publik_tidak_membocorkan_naskah_non_publik(): void
    {
        $response = $this->get(route('journal.articles.search', ['q' => 'Naskah Rahasia']));

        $response->assertStatus(200);
        $response->assertDontSee('Naskah Rahasia Masih Tahap Review Internal');
        $response->assertSee('Tidak ada artikel ilmiah yang sesuai');
    }

    public function test_pencarian_publik_menolak_filter_berbentuk_array(): void
    {
        foreach (['journal.index', 'journal.articles.search'] as $route) {
            foreach (['q', 'category', 'year'] as $filter) {
                $this->get(route($route, [$filter => ['uji']]))->assertBadRequest();
            }
        }
    }

    /**
     * Hasil pencarian kosong menampilkan pesan informatif tanpa galat (UC-04).
     */
    public function test_pencarian_kosong_menampilkan_pesan_informatif(): void
    {
        $response = $this->get(route('journal.articles.search', ['q' => 'kata_kunci_mustahil_xyz999']));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada artikel ilmiah yang sesuai');
        $response->assertSee('Tampilkan Seluruh Terbitan Resmi');
    }

    /**
     * Pembaca dapat melihat halaman artikel dan tercatat metrik COUNTER abstract view (UC-03).
     */
    public function test_pembaca_dapat_melihat_detail_artikel_dan_tercatat_metrik_view(): void
    {
        $response = $this->get(route('journal.articles.show', $this->publishedArticle->slug));

        $response->assertStatus(200);
        $response->assertSee($this->publishedArticle->title);
        $response->assertSee('Andi Muhammad Ilyas');
        $response->assertSee('BRIDA Kota Makassar');
        $response->assertSee('Open Access');
        $response->assertSee('citation_title', false); // Google Scholar meta tag

        // Metrik abstract_view tercatat di database sesuai ERD Tabel 48
        $this->assertDatabaseHas('article_metrics', [
            'article_id' => $this->publishedArticle->id,
            'event_type' => 'abstract_view',
        ]);
    }

    /**
     * Pembaca dapat mengunduh berkas galai PDF publik dan tercatat metrik download (UC-03).
     */
    public function test_pembaca_dapat_mengunduh_berkas_galai_pdf_dan_tercatat_metrik_download(): void
    {
        $response = $this->get(route('journal.articles.galleys.download', [
            'slugOrId' => $this->publishedArticle->slug,
            'galleyId' => $this->galley->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('X-Checksum-SHA256', $this->galley->checksum_sha256);

        // Metrik galley_download tercatat di database
        $this->assertDatabaseHas('article_metrics', [
            'article_id' => $this->publishedArticle->id,
            'galley_id' => $this->galley->id,
            'event_type' => 'galley_download',
        ]);
    }

    /**
     * Pembaca dapat mengekspor sitasi artikel ke format BibTeX (UC-03).
     */
    public function test_pembaca_dapat_mengekspor_sitasi_bibtex(): void
    {
        $response = $this->get(route('journal.articles.cite', [
            'slugOrId' => $this->publishedArticle->slug,
            'format' => 'bibtex',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/x-bibtex');
        $response->assertSee('@article{', false);
        $response->assertSee($this->publishedArticle->title);
    }
}
