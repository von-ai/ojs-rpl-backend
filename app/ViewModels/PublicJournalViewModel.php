<?php

namespace App\ViewModels;

class PublicJournalViewModel
{
    public function __construct(
        public readonly ?string $searchQuery = null,
        public readonly ?string $selectedCategory = null
    ) {}

    /**
     * Get journal topbar & ISSN metadata.
     */
    public function topbarData(): array
    {
        return [
            'p_issn' => '2985-789X',
            'e_issn' => '2985-8888',
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
     */
    public function navLinks(): array
    {
        return [
            ['label' => 'Beranda', 'url' => route('journal.index'), 'active' => true],
            ['label' => 'Cari Artikel', 'url' => route('journal.index', ['filter' => 'search']), 'active' => false],
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
     */
    public function announcement(): array
    {
        return [
            'title' => 'Volume 4 Nomor 1 (Edisi Juni 2026): Fokus Inovasi Ekosistem Digital Maritim dan Kebijakan Publik Berkelanjutan.',
            'deadline' => '30 April 2026',
            'detail_url' => '#pengumuman',
        ];
    }

    /**
     * Get catalog search scope categories.
     */
    public function categories(): array
    {
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
     */
    public function currentIssue(): array
    {
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
     * Get published articles for the current issue (filtered if query present).
     */
    public function articles(): array
    {
        $articles = [
            [
                'id' => '18-88',
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
                'abstract' => 'Penelitian ini menganalisis akurasi pengukuran partikulat PM2.5 dan PM10 menggunakan jaringan sensor IoT berbiaya rendah yang terdistribusi di 24 titik lorong wisata tematik Kota Makassar. Kalibrasi dilakukan terhadap stasiun referensi BMKG selama 90 hari guna mengevaluasi tingkat keandalan data mikroklimat perkotaan sebagai dasar mitigasi emisi berbasis kelurahan.',
                'doi' => '10.55981/brida-mks.2025.v3i2.88',
                'views' => 438,
                'downloads' => 182,
                'category' => 'Makassar Smart City & IoT',
                'pdf_url' => '#',
                'citation_url' => '#',
            ],
            [
                'id' => '18-89',
                'section' => 'Artikel Riset Kebijakan',
                'pages' => '115–128',
                'received_at' => '19 Okt 2025',
                'published_at' => '15 Des 2025',
                'title' => 'Strategi Adaptasi Nelayan Tradisional Paotere terhadap Perubahan Morfologi Pantai dan Rob: Pendekatan Socio-Ecological System',
                'authors' => ['Rahmatia Amalia Sudirman', 'Zulkifli Tahir'],
                'affiliations' => [
                    '1. Pusat Studi Pesisir dan Kelautan Universitas Negeri Makassar',
                ],
                'abstract' => 'Kajian ini membedah ketahanan sosio-ekologis masyarakat pesisir di kawasan Pelabuhan Paotere Makassar dalam merespons kenaikan muka air laut dan intrusi air asin. Melalui permodelan mixed-methods dan pemetaan partisipatif, riset ini merumuskan rekomendasi skema tanggul ramah ekosistem dan diversifikasi mata pencaharian berbasis pemrosesan produk.',
                'doi' => '10.55981/brida-mks.2025.v3i2.89',
                'views' => 312,
                'downloads' => 119,
                'category' => 'Lingkungan & Resiliensi Pesisir',
                'pdf_url' => '#',
                'citation_url' => '#',
            ],
            [
                'id' => '18-90',
                'section' => 'Artikel Riset Asli',
                'pages' => '129–142',
                'received_at' => '04 Nov 2025',
                'published_at' => '15 Des 2025',
                'title' => 'Analisis Komparatif Performa Model Pembelajaran Mesin untuk Prediksi Beban Listrik Penerangan Jalan Umum Terpadu',
                'authors' => ['Hendrikus Wira', 'Siti Hardiyanti', 'Irwan Setiawan'],
                'affiliations' => [
                    '1,2. Program Magister Informatika UMI Makassar',
                    '3. Dinas Kominfo Kota Makassar',
                ],
                'abstract' => 'Studi ini membandingkan akurasi estimasi beban daya menggunakan arsitektur LSTM, XGBoost, dan SARIMAX pada 1.200 titik lampu penerangan jalan pintar di Jalan Somba Opu dan Pantai Losari. Data telemetri real-time selama 12 bulan diolah untuk mengidentifikasi anomali pemakaian daya serta memangkas pemborosan energi sebesar 21,4% pada jam-jam sepi lalu.',
                'doi' => '10.55981/brida-mks.2025.v3i2.90',
                'views' => 520,
                'downloads' => 208,
                'category' => 'Makassar Smart City & IoT',
                'pdf_url' => '#',
                'citation_url' => '#',
            ],
        ];

        if ($this->searchQuery) {
            $articles = array_filter($articles, function ($art) {
                return str_contains(strtolower($art['title']), strtolower($this->searchQuery))
                    || str_contains(strtolower($art['abstract']), strtolower($this->searchQuery))
                    || str_contains(strtolower(implode(' ', $art['authors'])), strtolower($this->searchQuery));
            });
        }

        if ($this->selectedCategory) {
            $articles = array_filter($articles, function ($art) {
                return $art['category'] === $this->selectedCategory;
            });
        }

        return array_values($articles);
    }

    /**
     * Get Articles in Press (Terbitan Mendatang).
     */
    public function articlesInPress(): array
    {
        return [
            [
                'online_first_date' => '14 Februari 2026',
                'target_volume' => 'Volume 4 No. 1 (2026)',
                'title' => 'Transformasi Layanan Perizinan Berusaha Terintegrasi Berbasis WebGIS di Kota Makassar Pasca Pemberlakuan UU Cipta Kerja',
                'authors' => ['Ilham Alimuddin', 'Rismawati Rusdi', 'Dewi Puspa Asri'],
                'preview_url' => '#',
            ],
            [
                'online_first_date' => '28 Januari 2026',
                'target_volume' => 'Volume 4 No. 1 (2026)',
                'title' => 'Formulasi Pakan Ikan Bandeng Alternatif Berbasis Maggot Black Soldier Fly pada Kelompok Pembudidaya Tallo',
                'authors' => ['Nur Alam Kasim', 'Syahrul Mubarak', 'Farida Samad'],
                'preview_url' => '#',
            ],
        ];
    }

    /**
     * Get Issue Archives (Arsip Edisi Terdahulu).
     */
    public function archives(): array
    {
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
     * Get Publisher Metadata for Sidebar.
     */
    public function publisherInfo(): array
    {
        return [
            'journal_code' => 'JRID-MKS',
            'peer_review_model' => 'Double-blind Peer Review',
            'frequency' => '2 Kali / Tahun (Juni & Des)',
            'apc_status' => 'Gratis / Rp 0 (Subsidized)',
            'publisher' => 'BRIDA Kota Makassar',
            'languages' => 'Indonesia & Inggris',
            'ethics_policy_url' => '#etika-publikasi',
        ];
    }

    /**
     * Get Indexing Logos and Accreditation metadata for Sidebar.
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
                'title' => 'Perekrutkan Reviewer Mitra Bestari Bidang Tata Kelola Lingkungan & Energi Terbarukan',
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
     */
    public function footerData(): array
    {
        return [
            'publisher_name' => 'BRIDA KOTA MAKASSAR',
            'description' => 'Publikasi berkala ilmiah resmi Badan Riset dan Inovasi Daerah Kota Makassar yang berfokus pada hasil penelitian terapan, kebijakan publik, inovasi perkotaan, dan teknologi daerah.',
            'p_issn' => '2985-789X',
            'e_issn' => '2985-8888',
            'address' => "Gedung Balai Kota Makassar, Lantai 3\nJl. Ahmad Yani No. 2, Kel. Baru, Kec. Ujung Pandang\nKota Makassar, Sulawesi Selatan 90111",
            'email' => 'jurnal.brida@makassarkota.go.id',
            'phone' => '(0411) 3622455',
            'license_info' => 'Creative Commons Attribution-ShareAlike 4.0 International License (CC BY-SA 4.0)',
            'copyright' => 'Badan Riset dan Inovasi Daerah Kota Makassar. Seluruh hak cipta dilindungi undang-undang.',
        ];
    }
}
