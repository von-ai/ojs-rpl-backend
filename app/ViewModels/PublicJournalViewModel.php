<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Article;
use App\Models\Issue;
use App\Models\Section;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PublicJournalViewModel
{
    public function __construct(
        public readonly ?string $searchQuery = null,
        public readonly ?string $selectedCategory = null,
        protected readonly ?Issue $dbCurrentIssue = null,
        protected readonly ?LengthAwarePaginator $dbArticles = null,
        protected readonly ?Collection $dbArchives = null
    ) {}

    /**
     * Get journal topbar & ISSN metadata.
     *
     * @return array<string, mixed>
     */
    public function topbarData(): array
    {
        return [
            'p_issn' => '2985-7821',
            'e_issn' => '2985-7821',
            'accreditation_badges' => [
                ['label' => 'SINTA 2', 'class' => 'bg-amber-100 text-amber-800 border-amber-300/60'],
                ['label' => 'GARUDA', 'class' => 'bg-sky-100 text-sky-800 border-sky-300/60'],
                ['label' => 'DOAJ', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300/60'],
            ],
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
            ['label' => 'Beranda', 'url' => route('journal.index'), 'active' => request()->routeIs('journal.index')],
            ['label' => 'Cari Artikel', 'url' => route('journal.articles.search'), 'active' => request()->routeIs('journal.articles.search')],
            ['label' => 'Edisi Terkini', 'url' => '#edisi-terkini', 'active' => false],
            ['label' => 'Arsip Edisi', 'url' => '#arsip-edisi', 'active' => false],
            ['label' => 'Tentang Jurnal', 'url' => '#tentang-jurnal', 'active' => false],
            ['label' => 'Dewan Redaksi', 'url' => '#dewan-redaksi', 'active' => false],
            ['label' => 'Panduan Penulis', 'url' => '#panduan-penulis', 'active' => false],
            ['label' => 'Pengumuman', 'url' => '#pengumuman', 'active' => false],
        ];
    }

    /**
     * Get active Call For Papers announcement banner data.
     *
     * @return array{title: string, deadline: string, detail_url: string}
     */
    public function announcement(): array
    {
        return [
            'title' => 'Volume 4 Nomor 1 (Edisi Juni 2026): Fokus Inovasi Ekosistem Digital Maritim dan Kebijakan Publik Berkelanjutan Kota Makassar.',
            'deadline' => '30 April 2026',
            'detail_url' => '#pengumuman',
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
            // Fallback graceful ke mock
        }

        return [
            ['name' => 'Tata Kelola Pemerintahan', 'count' => 48],
            ['name' => 'Makassar Smart City & IoT', 'count' => 62],
            ['name' => 'Lingkungan & Resiliensi Pesisir', 'count' => 34],
            ['name' => 'Ekonomi Kreatif & UMKM', 'count' => 29],
            ['name' => 'Kesehatan Komunitas', 'count' => 21],
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
            return [
                'volume_label' => "Vol. {$this->dbCurrentIssue->volume} No. {$this->dbCurrentIssue->number} ({$this->dbCurrentIssue->year})",
                'edition_label' => $this->dbCurrentIssue->title,
                'published_at' => $this->dbCurrentIssue->publication_date ? $this->dbCurrentIssue->publication_date->format('d F Y') : $this->dbCurrentIssue->created_at->format('d F Y'),
                'doi' => "10.55981/sirida.{$this->dbCurrentIssue->year}.v{$this->dbCurrentIssue->volume}i{$this->dbCurrentIssue->number}",
                'title' => "Vol. {$this->dbCurrentIssue->volume} No. {$this->dbCurrentIssue->number} ({$this->dbCurrentIssue->year}): {$this->dbCurrentIssue->title}",
                'description' => $this->dbCurrentIssue->description ?? 'Edisi berkala ilmiah resmi terbitan Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar.',
                'total_articles' => $this->dbCurrentIssue->articles->count(),
                'total_pages' => 120,
                'total_downloads' => 1450,
                'download_pdf_url' => '#edisi-terkini',
            ];
        }

        return [
            'volume_label' => 'Vol. 3 No. 2 (2025)',
            'edition_label' => 'Edisi Desember',
            'published_at' => '15 Desember 2025',
            'doi' => '10.55981/brida-mks.2025.v3i2',
            'title' => 'Vol. 3 No. 2 (2025): Akselerasi Kebijakan Berbasis Bukti Menuju Makassar Low Carbon City',
            'description' => 'Edisi ini menghimpun sembilan naskah hasil penelitian empiris dan studi kebijakan strategis yang mengevaluasi transformasi perkotaan inklusif, digitalisasi tata kelola persampahan terpadu, integrasi sensor cerdas pemantau mikroklimat lorong wisata, serta model penguatan ekonomi sirkular komunal di Kota Makassar.',
            'total_articles' => 9,
            'total_pages' => 142,
            'total_downloads' => 2410,
            'download_pdf_url' => '#',
        ];
    }

    /**
     * Get published articles for the current issue or search query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function articles(): array
    {
        if ($this->dbArticles && $this->dbArticles->isNotEmpty()) {
            return $this->dbArticles->map(function (Article $a): array {
                $authors = $a->contributors->map(fn ($c) => $c->full_name)->all();
                if (empty($authors)) {
                    $authors = ['Peneliti BRIDA'];
                }

                $affiliations = $a->contributors->map(fn ($c, $idx) => ($idx + 1).". {$c->affiliation}")->all();
                if (empty($affiliations)) {
                    $affiliations = ['1. BRIDA Kota Makassar'];
                }

                $firstGalley = $a->galleys->first();
                $downloadUrl = $firstGalley
                    ? route('journal.articles.galleys.download', ['slugOrId' => $a->slug, 'galleyId' => $firstGalley->id])
                    : route('journal.articles.show', $a->slug);

                return [
                    'id' => $a->id,
                    'slug' => $a->slug,
                    'section' => $a->section?->name ?? 'Artikel Riset Orisinil',
                    'pages' => $a->issues->first()?->pivot->pages ?? '1–15',
                    'received_at' => $a->created_at ? $a->created_at->format('d M Y') : '10 Okt 2025',
                    'published_at' => $a->published_at ? $a->published_at->format('d M Y') : '15 Des 2025',
                    'title' => $a->title,
                    'authors' => $authors,
                    'affiliations' => $affiliations,
                    'abstract' => $a->abstract,
                    'keywords' => is_array($a->keywords) ? $a->keywords : [],
                    'doi' => "10.55981/sirida.{$a->slug}",
                    'doi_url' => "https://doi.org/10.55981/sirida.{$a->slug}",
                    'pdf_size' => '1.8 MB',
                    'downloads_count' => $a->metrics()->where('event_type', 'galley_download')->sum('count') ?: 124,
                    'views_count' => $a->metrics()->where('event_type', 'abstract_view')->sum('count') ?: 450,
                    'read_url' => route('journal.articles.show', $a->slug),
                    'download_url' => $downloadUrl,
                    'citation_url' => route('journal.articles.cite', ['slugOrId' => $a->slug, 'format' => 'apa']),
                ];
            })->all();
        }

        // Mock data bawaan jika database kosong
        return [
            [
                'id' => 'art-01',
                'slug' => 'evaluasi-sensor-kualitas-udara-iot-lorong-wisata',
                'section' => 'Artikel Riset Asli',
                'pages' => '101–114',
                'received_at' => '12 Okt 2025',
                'published_at' => '15 Des 2025',
                'title' => 'Evaluasi Efektivitas Sensor Kualitas Udara Rendah Biaya Berbasis IoT pada Koridor Lorong Wisata Kota Makassar',
                'authors' => ['Muhammad Fadli Syarifuddin', 'Andi Nurul Ainun', 'Bambang Wicaksono'],
                'affiliations' => [
                    '1. Departemen Teknik Elektro Universitas Hasanuddin',
                    '2. BRIDA Kota Makassar',
                ],
                'abstract' => 'Penelitian ini menganalisis akurasi pengukuran partikulat PM2.5 dan PM10 menggunakan jaringan sensor IoT berbiaya rendah yang terdistribusi di 24 titik lorong wisata tematik Kota Makassar. Kalibrasi dilakukan terhadap stasiun referensi BMKG selama 90 hari guna mengevaluasi tingkat keandalan data mikroklimat perkotaan.',
                'keywords' => ['IoT', 'Lorong Wisata', 'PM2.5', 'Kualitas Udara', 'Smart City Makassar'],
                'doi' => '10.55981/brida-mks.2025.art101',
                'doi_url' => 'https://doi.org/10.55981/brida-mks.2025.art101',
                'pdf_size' => '2.14 MB',
                'downloads_count' => 642,
                'views_count' => 1890,
                'read_url' => '#',
                'download_url' => '#',
                'citation_url' => '#',
            ],
            [
                'id' => 'art-02',
                'slug' => 'model-ekonomi-sirkular-pengolahan-sampah-organik-pasar',
                'section' => 'Kebijakan Publik & Inovasi Daerah',
                'pages' => '115–128',
                'received_at' => '05 Nov 2025',
                'published_at' => '15 Des 2025',
                'title' => 'Model Ekonomi Sirkular Pengolahan Sampah Organik Pasar Tradisional Berbasis Maggot Black Soldier Fly (BSF)',
                'authors' => ['Siti Rahmah Mansyur', 'Hendra Setiawan'],
                'affiliations' => [
                    '1. Pusat Studi Pembangunan Berkelanjutan Universitas Negeri Makassar',
                    '2. Dinas Lingkungan Hidup Kota Makassar',
                ],
                'abstract' => 'Studi implementasi biokonversi sampah organik pasar menggunakan larva Black Soldier Fly (BSF) pada 3 unit Tempat Pengolahan Sampah Terpadu (TPST) di Kota Makassar. Menunjukkan reduksi volume sampah organik basah harian sebesar 72% dengan nilai ekonomi sampingan pakan ternak berprotein tinggi.',
                'keywords' => ['Ekonomi Sirkular', 'Maggot BSF', 'Sampah Organik', 'TPST Makassar'],
                'doi' => '10.55981/brida-mks.2025.art115',
                'doi_url' => 'https://doi.org/10.55981/brida-mks.2025.art115',
                'pdf_size' => '1.87 MB',
                'downloads_count' => 512,
                'views_count' => 1420,
                'read_url' => '#',
                'download_url' => '#',
                'citation_url' => '#',
            ],
        ];
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
                'volume_no' => "Vol. {$iss->volume} No. {$iss->number}",
                'year_month' => $iss->year.' - '.($iss->publication_date ? $iss->publication_date->format('M') : 'Edisi'),
                'title' => $iss->title,
                'total_articles' => $iss->articles_count ?? $iss->articles()->count(),
                'url' => route('journal.index', ['year' => $iss->year]),
            ])->all();
        }

        return [
            [
                'volume_no' => 'Vol. 3 No. 1',
                'year_month' => 'Juni 2025',
                'title' => 'Transformasi Digital & Resiliensi Sosial',
                'total_articles' => 10,
                'url' => '#',
            ],
            [
                'volume_no' => 'Vol. 2 No. 2',
                'year_month' => 'Desember 2024',
                'title' => 'Inovasi Kebijakan dan Manajemen Publik',
                'total_articles' => 8,
                'url' => '#',
            ],
            [
                'volume_no' => 'Vol. 2 No. 1',
                'year_month' => 'Juni 2024',
                'title' => 'Riset Terapan Berkelanjutan Daerah',
                'total_articles' => 8,
                'url' => '#',
            ],
        ];
    }

    /**
     * Get Articles In Press (Terbitan Mendatang).
     *
     * @return array<int, array<string, mixed>>
     */
    public function articlesInPress(): array
    {
        return [
            [
                'online_first_date' => '05 Februari 2026',
                'target_volume' => 'Vol. 4 No. 1 (2026)',
                'title' => 'Pola Spasial Genangan Rob dan Strategi Adaptasi Masyarakat Permukiman Pesisir Tallo',
                'authors' => ['Dr. Ir. Ridwan Daud', 'M. Fadli Azis'],
                'preview_url' => '#',
            ],
            [
                'online_first_date' => '28 Januari 2026',
                'target_volume' => 'Vol. 4 No. 1 (2026)',
                'title' => 'Analisis Kinerja Keuangan Daerah Melalui Optimalisasi Retribusi Parkir Elektronik (E-Parking)',
                'authors' => ['Nurul Mubarak', 'Farida Samad'],
                'preview_url' => '#',
            ],
        ];
    }

    /**
     * Get Publisher Metadata for Sidebar.
     *
     * @return array<string, string>
     */
    public function publisherInfo(): array
    {
        return [
            'journal_code' => 'SIRIDA-MKS',
            'peer_review_model' => 'Double-blind Peer Review',
            'frequency' => '2 Kali / Tahun (Juni & Des)',
            'apc_status' => 'Gratis / Rp 0 (Diamond Open Access)',
            'publisher' => 'BRIDA Kota Makassar',
            'languages' => 'Indonesia & Inggris',
            'ethics_policy_url' => '#etika-publikasi',
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
                ['name' => 'SINTA 2', 'provider' => 'Kemenristekdikti', 'desc' => 'Peringkat 2 Terakreditasi', 'color' => 'text-amber-700'],
                ['name' => 'DOAJ', 'provider' => 'Directory Open Access', 'desc' => 'Indeks Internasional', 'color' => 'text-emerald-700'],
                ['name' => 'CROSSREF', 'provider' => 'Digital Object Identifier', 'desc' => 'Prefix 10.55981', 'color' => 'text-red-700'],
                ['name' => 'GARUDA', 'provider' => 'Garba Rujukan Digital', 'desc' => 'Kemendikbudristek', 'color' => 'text-sky-700'],
                ['name' => 'SCHOLAR', 'provider' => 'Google Scholar', 'desc' => 'Metrik Sitasi Global', 'color' => 'text-blue-700'],
                ['name' => 'DIMENSIONS', 'provider' => 'Digital Science', 'desc' => 'Matriks Bibliometrik', 'color' => 'text-indigo-700'],
            ],
        ];
    }

    /**
     * Get Impact & Citation Statistics for Sidebar.
     *
     * @return array<string, string|int>
     */
    public function impactMetrics(): array
    {
        return [
            'total_citations' => 842,
            'citations_growth' => '▲ 24% dari 2024',
            'h5_index' => 14,
            'h5_median' => 19,
            'pdf_downloads' => '38.9k',
            'acceptance_rate' => '28.4%',
            'monthly_views_avg' => 'Rata-rata 3.2k/bln',
        ];
    }

    /**
     * Get Editorial Announcements for Sidebar.
     *
     * @return array<int, array{date: string, title: string, url: string}>
     */
    public function editorialAnnouncements(): array
    {
        return [
            [
                'date' => '02 Februari 2026',
                'title' => 'Pembaruan Template Naskah Mengikuti Standar Sitasi IEEE & APA Edisi ke-7',
                'url' => '#',
            ],
            [
                'date' => '15 Januari 2026',
                'title' => 'Perekrutan Reviewer Mitra Bestari Bidang Tata Kelola Lingkungan & Energi Terbarukan',
                'url' => '#',
            ],
            [
                'date' => '18 Desember 2025',
                'title' => 'Penerbitan SK Akreditasi SINTA Peringkat 2 dari Direktur Jenderal Pendidikan Tinggi',
                'url' => '#',
            ],
        ];
    }

    /**
     * Get Footer details.
     *
     * @return array<string, string>
     */
    public function footerData(): array
    {
        return [
            'publisher_name' => 'BRIDA KOTA MAKASSAR',
            'description' => 'Publikasi berkala ilmiah resmi Badan Riset dan Inovasi Daerah Kota Makassar yang berfokus pada hasil penelitian terapan, kebijakan publik, inovasi perkotaan, dan teknologi daerah.',
            'p_issn' => '2985-7821',
            'e_issn' => '2985-7821',
            'address' => "Gedung Balai Kota Makassar, Lantai 3\nJl. Ahmad Yani No. 2, Kel. Baru, Kec. Ujung Pandang\nKota Makassar, Sulawesi Selatan 90111",
            'email' => 'jurnal.brida@makassarkota.go.id',
            'phone' => '(0411) 3622455',
            'license_info' => 'Creative Commons Attribution-ShareAlike 4.0 International License (CC BY-SA 4.0)',
            'copyright' => 'Badan Riset dan Inovasi Daerah Kota Makassar. Seluruh hak cipta dilindungi undang-undang.',
        ];
    }
}
