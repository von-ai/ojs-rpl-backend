<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Announcement;
use App\Models\Article;
use App\Models\ArticleContributor;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\Section;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected Journal $journal;

    protected Section $section;

    protected User $editor;

    protected Issue $publishedIssue;

    protected Issue $draftIssue;

    protected Article $publishedArticle;

    protected Announcement $publishedAnnouncement;

    protected Announcement $draftAnnouncement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

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
            'slug' => 'kebijakan-publik',
            'name' => 'Kajian Kebijakan Publik',
            'accepting_submissions' => true,
            'position' => 1,
            'version' => 1,
        ]);

        $this->editor = User::factory()->create([
            'name' => 'Prof. Dr. H. Muh. Anshar, M.Si.',
            'email' => 'eic@makassar.go.id',
        ]);

        UserProfile::create([
            'user_id' => $this->editor->id,
            'affiliation' => 'BRIDA Kota Makassar & UNHAS',
            'country_code' => 'ID',
        ]);

        UserRole::create([
            'user_id' => $this->editor->id,
            'journal_id' => $this->journal->id,
            'role_code' => 'editor_in_chief',
            'expertise_keywords' => ['Kebijakan Publik', 'Inovasi Daerah'],
        ]);

        $this->publishedIssue = Issue::create([
            'journal_id' => $this->journal->id,
            'volume' => '6',
            'number' => '1',
            'year' => 2026,
            'title' => 'Transformasi Digital dan Ketahanan Perkotaan',
            'description' => 'Edisi terbitan resmi semester pertama tahun 2026.',
            'status' => 'published',
            'is_current' => true,
            'doi_prefix' => '10.55981/sirida.v6i1',
            'published_at' => now()->subMonths(2),
            'version' => 1,
        ]);

        $this->draftIssue = Issue::create([
            'journal_id' => $this->journal->id,
            'volume' => '6',
            'number' => '2',
            'year' => 2026,
            'title' => 'Edisi Rahasia Belum Terbit',
            'status' => 'draft',
            'is_current' => false,
            'version' => 1,
        ]);

        $author = User::factory()->create([
            'name' => 'Dr. Andi Bau Sawa, M.Si.',
            'email' => 'bausawa@makassar.go.id',
        ]);

        $submission = Submission::create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'author_id' => $author->id,
            'submission_number' => 'SUB-2026-TEST01',
            'title' => 'Optimalisasi Sensor Kualitas Udara Rendah Biaya di Makassar',
            'abstract' => 'Penelitian ini menganalisis akurasi data sensor partikulat PM2.5 di Kota Makassar.',
            'keywords' => ['IoT', 'Kualitas Udara', 'Makassar'],
            'status' => 'published',
            'version' => 2,
        ]);

        $this->publishedArticle = Article::create([
            'submission_id' => $submission->id,
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'slug' => 'optimalisasi-sensor-kualitas-udara-makassar',
            'title' => 'Optimalisasi Sensor Kualitas Udara Rendah Biaya di Makassar',
            'abstract' => 'Penelitian ini menganalisis akurasi data sensor partikulat PM2.5 di Kota Makassar.',
            'keywords' => ['IoT', 'Kualitas Udara', 'Makassar'],
            'status' => 'published',
            'published_at' => now()->subMonth(),
            'version' => 1,
        ]);

        ArticleContributor::create([
            'article_id' => $this->publishedArticle->id,
            'given_name' => 'Andi Bau',
            'family_name' => 'Sawa',
            'full_name' => 'Dr. Andi Bau Sawa, M.Si.',
            'email' => 'bausawa@makassar.go.id',
            'affiliation' => 'Universitas Hasanuddin',
            'country' => 'ID',
            'position' => 1,
            'is_corresponding' => true,
        ]);

        $this->publishedIssue->articles()->attach($this->publishedArticle->id, [
            'id' => (string) Str::ulid(),
            'position' => 1,
            'pages' => '1–14',
        ]);

        $this->publishedAnnouncement = Announcement::create([
            'journal_id' => $this->journal->id,
            'title' => 'Panggilan Pengajuan Naskah Vol. 6 No. 2 Tahun 2026',
            'body' => 'Redaksi mengundang para peneliti untuk mengirimkan naskah orisinal terbaik.',
            'is_published' => true,
            'published_at' => now()->subDays(10),
        ]);

        $this->draftAnnouncement = Announcement::create([
            'journal_id' => $this->journal->id,
            'title' => 'Pengumuman Draf Internal Redaksi',
            'body' => 'Pengumuman ini masih dalam peninjauan internal.',
            'is_published' => false,
        ]);
    }

    public function test_halaman_beranda_publik_dapat_diakses(): void
    {
        $response = $this->get(route('journal.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Riset & Inovasi BRIDA Makassar', false);
        $response->assertSee('Edisi Terkini');
        $response->assertSee('Optimalisasi Sensor Kualitas Udara');
    }

    public function test_halaman_edisi_terkini_dapat_diakses(): void
    {
        $response = $this->get(route('journal.issues.current'));

        $response->assertOk();
        $response->assertSee('Vol. 6 No. 1 (2026)');
        $response->assertSee('Transformasi Digital dan Ketahanan Perkotaan');
        $response->assertSee('Optimalisasi Sensor Kualitas Udara Rendah Biaya di Makassar');
    }

    public function test_halaman_detail_edisi_spesifik_dapat_diakses(): void
    {
        $response = $this->get(route('journal.issues.show', $this->publishedIssue->id));

        $response->assertOk();
        $response->assertSee('Vol. 6 No. 1 (2026)');
        $response->assertSee('Kajian Kebijakan Publik');
        $response->assertSee('Optimalisasi Sensor Kualitas Udara Rendah Biaya di Makassar');
    }

    public function test_edisi_berstatus_draf_tidak_dapat_diakses_publik(): void
    {
        $response = $this->get(route('journal.issues.show', $this->draftIssue->id));

        $response->assertNotFound();
    }

    public function test_halaman_arsip_edisi_menampilkan_riwayat_terbitan(): void
    {
        $response = $this->get(route('journal.archives'));

        $response->assertOk();
        $response->assertSee('Arsip Seluruh Edisi Terbitan');
        $response->assertSee('Vol. 6 No. 1');
    }

    public function test_halaman_tentang_jurnal_menampilkan_fokus_dan_ruang_lingkup(): void
    {
        $response = $this->get(route('journal.about'));

        $response->assertOk();
        $response->assertSee('Tentang Jurnal Riset & Inovasi Daerah (SIRIDA)', false);
        $response->assertSee('Fokus dan Ruang Lingkup');
        $response->assertSee('Diamond Open Access');
    }

    public function test_halaman_dewan_redaksi_menampilkan_editor_dan_reviewer(): void
    {
        $response = $this->get(route('journal.about.editorial-team'));

        $response->assertOk();
        $response->assertSee('Dewan Redaksi & Mitra Bestari', false);
        $response->assertSee('Prof. Dr. H. Muh. Anshar, M.Si.');
        $response->assertSee('Pemimpin Redaksi');
    }

    public function test_halaman_kebijakan_dan_etika_menampilkan_standar_cope(): void
    {
        $response = $this->get(route('journal.about.policies'));

        $response->assertOk();
        $response->assertSee('Kebijakan Redaksi & Etika Publikasi Ilmiah', false);
        $response->assertSee('Double-Blind Peer Review');
        $response->assertSee('CC BY-SA 4.0');
    }

    public function test_halaman_panduan_penulis_menampilkan_struktur_imrad(): void
    {
        $response = $this->get(route('journal.about.submissions'));

        $response->assertOk();
        $response->assertSee('Panduan Penulis (Author Guidelines)');
        $response->assertSee('IMRAD Structure');
        $response->assertSee('Template Resmi Naskah SIRIDA');
    }

    public function test_halaman_kontak_redaksi_menampilkan_alamat_balai_kota(): void
    {
        $response = $this->get(route('journal.about.contact'));

        $response->assertOk();
        $response->assertSee('Kontak dan Sekretariat Redaksi');
        $response->assertSee('Gedung Balai Kota Makassar');
        $response->assertSee('jurnal.brida@makassarkota.go.id');
    }

    public function test_halaman_daftar_pengumuman_menampilkan_pengumuman_terbit(): void
    {
        $response = $this->get(route('journal.announcements.index'));

        $response->assertOk();
        $response->assertSee('Warta, Berita & Pengumuman Jurnal', false);
        $response->assertSee('Panggilan Pengajuan Naskah Vol. 6 No. 2');
        $response->assertDontSee('Pengumuman Draf Internal Redaksi');
    }

    public function test_halaman_detail_pengumuman_dapat_dibaca(): void
    {
        $response = $this->get(route('journal.announcements.show', $this->publishedAnnouncement->id));

        $response->assertOk();
        $response->assertSee('Panggilan Pengajuan Naskah Vol. 6 No. 2');
        $response->assertSee('Redaksi mengundang para peneliti untuk mengirimkan naskah orisinal terbaik.');
    }

    public function test_pengumuman_draf_tidak_dapat_diakses_publik(): void
    {
        $response = $this->get(route('journal.announcements.show', $this->draftAnnouncement->id));

        $response->assertNotFound();
    }

    public function test_halaman_detail_artikel_memuat_meta_tag_dublin_core_dan_google_scholar(): void
    {
        $response = $this->get(route('journal.articles.show', $this->publishedArticle->slug));

        $response->assertOk();
        $response->assertSee('citation_title', false);
        $response->assertSee('DC.Title', false);
        $response->assertSee('DC.Creator.PersonalName', false);
        $response->assertSee('DC.Format', false);
    }
}
