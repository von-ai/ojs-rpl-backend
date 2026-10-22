<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Section;
use App\Models\Submission;
use Illuminate\Support\Facades\Schema;

class AuthorSubmissionsViewModel
{
    /**
     * Cache internal untuk hasil rawSubmissions agar tidak terjadi query berulang dalam 1 siklus request.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $cachedRawSubmissions = null;

    /**
     * Data profil pengguna aktif dengan konteks peran Author.
     *
     * @return array{
     *     name: string,
     *     title: string,
     *     affiliation: string,
     *     role_label: string,
     *     academic_year: string,
     *     unread_messages: int,
     *     unread_notifications: int
     * }
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'title' => 'Dr. Andi Muhammad Ilyas, M.Si.',
            'affiliation' => 'Universitas Hasanuddin',
            'role_label' => 'Portal Penulis OJS 3.4',
            'academic_year' => 'Tahun Akademik 2026/2027',
            'unread_messages' => 1,
            'unread_notifications' => 2,
        ];
    }

    /**
     * Rekap ringkasan metrik statistik naskah penulis (4 kartu ringkasan atas).
     *
     * @return array<int, array{
     *     title: string,
     *     value: int|string,
     *     description: string,
     *     icon: string,
     *     color_class: string
     * }>
     */
    public function summaryMetrics(): array
    {
        $all = $this->rawSubmissions();

        $totalCount = count($all);
        $revisionCount = count(array_filter($all, fn (array $s): bool => in_array('perlu-revisi', $s['tabs'], true)));
        $productionCount = count(array_filter($all, fn (array $s): bool => in_array('produksi', $s['tabs'], true)));
        $publishedCount = count(array_filter($all, fn (array $s): bool => in_array('terbit', $s['tabs'], true)));

        return [
            [
                'title' => 'Total Naskah',
                'value' => $totalCount,
                'description' => 'Seluruh naskah terdaftar',
                'icon' => 'document-text',
                'color_class' => 'text-gray-700 bg-gray-100',
            ],
            [
                'title' => 'Perlu Revisi',
                'value' => $revisionCount,
                'description' => 'Menunggu perbaikan penulis',
                'icon' => 'exclamation-circle',
                'color_class' => 'text-red-700 bg-red-50',
            ],
            [
                'title' => 'Dalam Produksi',
                'value' => $productionCount,
                'description' => 'Tata letak & galai akhir',
                'icon' => 'cog',
                'color_class' => 'text-amber-700 bg-amber-50',
            ],
            [
                'title' => 'Telah Terbit',
                'value' => $publishedCount,
                'description' => 'Artikel resmi tayang',
                'icon' => 'check-circle',
                'color_class' => 'text-emerald-700 bg-emerald-50',
            ],
        ];
    }

    /**
     * Tab kategori filter naskah beserta jumlah item masing-masing tab (Bahasa Indonesia murni).
     *
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     count: int,
     *     badge_class: string
     * }>
     */
    public function tabs(): array
    {
        $all = $this->rawSubmissions();

        return [
            [
                'key' => 'semua',
                'label' => 'Semua',
                'count' => count($all),
                'badge_class' => 'bg-brand-maroon text-white',
            ],
            [
                'key' => 'draf',
                'label' => 'Draf',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('draf', $s['tabs'], true))),
                'badge_class' => 'bg-gray-100 text-gray-700',
            ],
            [
                'key' => 'aktif',
                'label' => 'Naskah Aktif',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('aktif', $s['tabs'], true))),
                'badge_class' => 'bg-blue-50 text-blue-700',
            ],
            [
                'key' => 'perlu-revisi',
                'label' => 'Perlu Revisi',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('perlu-revisi', $s['tabs'], true))),
                'badge_class' => 'bg-red-50 text-red-700',
            ],
            [
                'key' => 'produksi',
                'label' => 'Diterima & Produksi',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('produksi', $s['tabs'], true))),
                'badge_class' => 'bg-amber-50 text-amber-700',
            ],
            [
                'key' => 'terbit',
                'label' => 'Telah Terbit',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('terbit', $s['tabs'], true))),
                'badge_class' => 'bg-emerald-50 text-emerald-700',
            ],
            [
                'key' => 'arsip',
                'label' => 'Arsip & Ditolak',
                'count' => count(array_filter($all, fn (array $s): bool => in_array('arsip', $s['tabs'], true))),
                'badge_class' => 'bg-gray-100 text-gray-500',
            ],
        ];
    }

    /**
     * Daftar opsi tahun untuk filter dropdown.
     *
     * @return array<int, string>
     */
    public function availableYears(): array
    {
        $all = $this->rawSubmissions();
        $years = array_unique(array_map(fn (array $s): string => (string) $s['year'], $all));
        rsort($years);

        return ! empty($years) ? array_values($years) : ['2026', '2025'];
    }

    /**
     * Daftar seksi naskah ilmiah sesuai tabel sections pada ERD BRIDA.
     *
     * @return array<int, string>
     */
    public function journalSections(): array
    {
        $all = $this->rawSubmissions();
        $sections = array_unique(array_filter(array_map(fn (array $s): ?string => $s['section'] ?? $s['type'] ?? null, $all)));
        sort($sections);

        return ! empty($sections) ? array_values($sections) : ['Riset Orisinil', 'Kajian Kebijakan'];
    }

    /**
     * Daftar jenis/seksi artikel untuk filter dropdown.
     *
     * @return array<int, string>
     */
    public function articleTypes(): array
    {
        return $this->journalSections();
    }

    /**
     * Ambil naskah terfilter berdasarkan tab aktif, pencarian keyword, tahun, jenis artikel, dan opsi pengurutan.
     *
     * @param array{
     *     tab?: string|null,
     *     search?: string|null,
     *     year?: string|null,
     *     type?: string|null,
     *     sort?: string|null
     * } $filters
     * @return array<int, array<string, mixed>>
     */
    public function submissions(array $filters = []): array
    {
        $items = $this->rawSubmissions();

        $activeTab = $filters['tab'] ?? 'semua';
        if ($activeTab && $activeTab !== 'semua') {
            $items = array_values(array_filter($items, function (array $item) use ($activeTab): bool {
                return in_array($activeTab, $item['tabs'], true);
            }));
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $keyword = mb_strtolower($search);
            $items = array_values(array_filter($items, function (array $item) use ($keyword): bool {
                $searchable = mb_strtolower(implode(' ', [
                    $item['id'],
                    $item['title'],
                    $item['type'],
                    $item['doi'],
                    $item['status_label'],
                    $item['keywords'] ?? '',
                ]));

                return str_contains($searchable, $keyword);
            }));
        }

        $year = trim((string) ($filters['year'] ?? ''));
        if ($year !== '' && $year !== 'semua') {
            $items = array_values(array_filter($items, function (array $item) use ($year): bool {
                return (string) $item['year'] === $year;
            }));
        }

        $type = trim((string) ($filters['type'] ?? ''));
        if ($type !== '' && $type !== 'semua') {
            $items = array_values(array_filter($items, function (array $item) use ($type): bool {
                return mb_strtolower($item['type']) === mb_strtolower($type);
            }));
        }

        $sort = $filters['sort'] ?? '-updated_at';
        usort($items, function (array $a, array $b) use ($sort): int {
            return match ($sort) {
                'created_at' => strcmp($a['created_at_raw'], $b['created_at_raw']),
                '-created_at' => strcmp($b['created_at_raw'], $a['created_at_raw']),
                'title' => strcmp($a['title'], $b['title']),
                '-title' => strcmp($b['title'], $a['title']),
                'updated_at' => strcmp($a['updated_at_raw'], $b['updated_at_raw']),
                default => strcmp($b['updated_at_raw'], $a['updated_at_raw']),
            };
        });

        return $items;
    }

    /**
     * Sumber data naskah ilmiah penulis sesuai standar OpenAPI SIRIDA BRIDA.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rawSubmissions(): array
    {
        if ($this->cachedRawSubmissions !== null) {
            return $this->cachedRawSubmissions;
        }

        $dbItems = [];

        try {
            if (Schema::hasTable('submissions')) {
                $submissions = Submission::with(['section', 'contributors'])
                    ->latest('updated_at')
                    ->get();

                foreach ($submissions as $sub) {
                    $sectionName = $sub->section?->name ?? 'Artikel Riset Orisinil';
                    $isDraft = $sub->status === 'draft';
                    $tabs = $isDraft ? ['semua', 'draf'] : ['semua', 'aktif'];

                    $dbItems[] = [
                        'id' => $sub->submission_number,
                        'doi' => null,
                        'doi_url' => null,
                        'doi_status' => $isDraft ? 'draft' : 'pending',
                        'section' => $sectionName,
                        'type' => $sectionName,
                        'type_badge_class' => 'bg-slate-50 text-slate-700 border-slate-200',
                        'year' => (int) ($sub->created_at?->format('Y') ?? date('Y')),
                        'tabs' => $tabs,
                        'title' => $sub->title,
                        'keywords' => is_array($sub->keywords) ? implode(', ', $sub->keywords) : (string) ($sub->keywords ?? ''),
                        'submit_date' => $sub->submitted_at ? $sub->submitted_at->format('d M Y') : 'Belum Dikirim',
                        'updated_date' => $sub->updated_at ? $sub->updated_at->format('d M Y') : date('d M Y'),
                        'created_at_raw' => $sub->created_at ? $sub->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                        'updated_at_raw' => $sub->updated_at ? $sub->updated_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                        'status_label' => $isDraft ? 'Draf Belum Diajukan' : 'Pemeriksaan Awal Editor',
                        'status_badge_class' => $isDraft ? 'bg-gray-100 text-gray-700 border border-gray-200' : 'bg-blue-50 text-blue-700 border border-blue-200',
                        'status_subtitle' => null,
                        'status_subtitle_class' => '',
                        'action_label' => 'Detail',
                        'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                        'action_url' => route('author.submissions.show', $sub->submission_number ?: $sub->id),
                    ];
                }
            }
        } catch (\Throwable) {
            // Fallback graceful jika koneksi DB atau tabel belum siap
        }

        $defaultItems = [
            [
                'id' => 'SUB-2026-00124',
                'doi' => '10.55981/brida.v3i2.142',
                'doi_url' => 'https://doi.org/10.55981/brida.v3i2.142',
                'doi_status' => 'registered',
                'section' => 'Riset Orisinil',
                'type' => 'Riset Orisinil',
                'type_badge_class' => 'bg-slate-50 text-slate-700 border-slate-200',
                'year' => 2026,
                'tabs' => ['semua', 'aktif', 'perlu-revisi'],
                'title' => 'Evaluasi Kebijakan Lorong Wisata Berbasis Pemberdayaan Ekonomi Kreatif di Kota Makassar',
                'keywords' => 'Lorong Wisata, Ekonomi Kreatif, Kebijakan Publik Makassar',
                'submit_date' => '12 Sep 2026',
                'updated_date' => '18 Sep 2026',
                'created_at_raw' => '2026-09-12 10:00:00',
                'updated_at_raw' => '2026-09-18 14:20:00',
                'status_label' => 'Perlu Revisi',
                'status_badge_class' => 'bg-red-50 text-red-700 border border-red-200',
                'status_subtitle' => 'Batas Waktu: 15 Okt 2026',
                'status_subtitle_class' => 'text-red-700 font-medium',
                'action_label' => 'Detail',
                'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                'action_url' => route('author.submissions.show', 'SUB-2026-00124'),
            ],
            [
                'id' => 'SUB-2026-00142',
                'doi' => null,
                'doi_url' => null,
                'doi_status' => 'pending',
                'section' => 'Kajian Kebijakan',
                'type' => 'Kajian Kebijakan',
                'type_badge_class' => 'bg-sky-50 text-sky-800 border-sky-200',
                'year' => 2026,
                'tabs' => ['semua', 'aktif'],
                'title' => 'Analisis Ketahanan Pangan Rumah Tangga Berpenghasilan Rendah di Pulau-Pulau Pesisir Kota Makassar',
                'keywords' => 'Ketahanan Pangan, Wilayah Pesisir, Kepulauan Sangkarrang',
                'submit_date' => '28 Sep 2026',
                'updated_date' => '30 Sep 2026',
                'created_at_raw' => '2026-09-28 09:30:00',
                'updated_at_raw' => '2026-09-30 11:15:00',
                'status_label' => 'Pemeriksaan Awal Editor',
                'status_badge_class' => 'bg-blue-50 text-blue-700 border border-blue-200',
                'status_subtitle' => null,
                'status_subtitle_class' => '',
                'action_label' => 'Detail',
                'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                'action_url' => route('author.submissions.show', 'SUB-2026-00142'),
            ],
            [
                'id' => 'SUB-2026-00098',
                'doi' => '10.55981/brida.v3i2.143',
                'doi_url' => 'https://doi.org/10.55981/brida.v3i2.143',
                'doi_status' => 'registered',
                'section' => 'Riset Orisinil',
                'type' => 'Riset Orisinil',
                'type_badge_class' => 'bg-slate-50 text-slate-700 border-slate-200',
                'year' => 2026,
                'tabs' => ['semua', 'aktif', 'produksi'],
                'title' => 'Model Prediksi Kualitas Udara Perkotaan Berbasis Jaringan Sensor IoT di Wilayah Industri Makassar',
                'keywords' => 'Kualitas Udara, IoT Sensor, Wilayah Industri Biringkanaya',
                'submit_date' => '15 Jul 2026',
                'updated_date' => '02 Okt 2026',
                'created_at_raw' => '2026-07-15 08:45:00',
                'updated_at_raw' => '2026-10-02 16:30:00',
                'status_label' => 'Pemeriksaan Galai',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border border-amber-200',
                'status_subtitle' => null,
                'status_subtitle_class' => '',
                'action_label' => 'Detail',
                'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                'action_url' => route('author.submissions.show', 'SUB-2026-00098'),
            ],
            [
                'id' => 'SUB-2026-00155',
                'doi' => null,
                'doi_url' => null,
                'doi_status' => 'draft',
                'section' => 'Kajian Kebijakan',
                'type' => 'Kajian Kebijakan',
                'type_badge_class' => 'bg-sky-50 text-sky-800 border-sky-200',
                'year' => 2026,
                'tabs' => ['semua', 'draf'],
                'title' => 'Kajian Implementasi Geodatabase untuk Optimalisasi Retribusi Sampah dan Tata Kelola Smart City',
                'keywords' => 'Geodatabase, Smart City Makassar, Retribusi Persampahan',
                'submit_date' => '03 Okt 2026',
                'updated_date' => '03 Okt 2026',
                'created_at_raw' => '2026-10-03 14:00:00',
                'updated_at_raw' => '2026-10-03 15:20:00',
                'status_label' => 'Draf Belum Diajukan',
                'status_badge_class' => 'bg-gray-100 text-gray-700 border border-gray-200',
                'status_subtitle' => null,
                'status_subtitle_class' => '',
                'action_label' => 'Detail',
                'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                'action_url' => route('author.submissions.show', 'SUB-2026-00155'),
            ],
            [
                'id' => 'SUB-2025-00041',
                'doi' => '10.55981/brida.v2i2.88',
                'doi_url' => 'https://doi.org/10.55981/brida.v2i2.88',
                'doi_status' => 'registered',
                'section' => 'Riset Orisinil',
                'type' => 'Riset Orisinil',
                'type_badge_class' => 'bg-slate-50 text-slate-700 border-slate-200',
                'year' => 2025,
                'tabs' => ['semua', 'terbit'],
                'title' => 'Strategi Transformasi Digital Birokrasi Pelayanan Publik Terpadu Pemerintah Daerah Kota Makassar',
                'keywords' => 'Transformasi Digital, Pelayanan Publik, Birokrasi Makassar',
                'submit_date' => '10 Mei 2025',
                'updated_date' => '15 Des 2025',
                'created_at_raw' => '2025-05-10 09:00:00',
                'updated_at_raw' => '2025-12-15 11:00:00',
                'status_label' => 'Telah Terbit',
                'status_badge_class' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                'status_subtitle' => null,
                'status_subtitle_class' => '',
                'action_label' => 'Detail',
                'action_class' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
                'action_url' => route('author.submissions.show', 'SUB-2025-00041'),
            ],
        ];

        $existingIds = array_column($dbItems, 'id');
        $filteredDefaults = array_filter($defaultItems, fn (array $d): bool => ! in_array($d['id'], $existingIds, true));

        return $this->cachedRawSubmissions = array_merge($dbItems, array_values($filteredDefaults));
    }

    /**
     * Panduan 6 tahapan alur kerja editorial OJS 3 dan makna status bagi penulis.
     *
     * @return array<int, array{
     *     step: int,
     *     title: string,
     *     description: string
     * }>
     */
    public function editorialWorkflowSteps(): array
    {
        return [
            [
                'step' => 1,
                'title' => '1. Draf Submisi',
                'description' => 'Naskah disimpan sementara oleh penulis sebelum diajukan resmi ke redaksi.',
            ],
            [
                'step' => 2,
                'title' => '2. Pemeriksaan Awal',
                'description' => 'Pemeriksaan format penulisan, kesesuaian fokus dan ruang lingkup, serta uji kesamaan naskah.',
            ],
            [
                'step' => 3,
                'title' => '3. Telaah Sejawat',
                'description' => 'Proses penelaahan tertutup oleh minimal 2 mitra bestari independen.',
            ],
            [
                'step' => 4,
                'title' => '4. Perlu Revisi',
                'description' => 'Penulis mengunggah naskah perbaikan beserta catatan respon sebelum batas waktu yang ditentukan.',
            ],
            [
                'step' => 5,
                'title' => '5. Penyelarasan & Galai',
                'description' => 'Penyelarasan bahasa, format terbitan (PDF/XML), dan persetujuan akhir penulis.',
            ],
            [
                'step' => 6,
                'title' => '6. Telah Terbit',
                'description' => 'Naskah resmi diterbitkan pada terbitan aktif dengan pengenal Digital Object Identifier (DOI).',
            ],
        ];
    }

    /**
     * Daftar seksi jurnal aktif yang tersedia untuk pengajuan naskah baru.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     policy: string
     * }>
     */
    public function wizardJournalSections(): array
    {
        try {
            if (Schema::hasTable('sections')) {
                $dbSections = Section::where('accepting_submissions', true)
                    ->orderBy('position')
                    ->get();

                if ($dbSections->isNotEmpty()) {
                    return $dbSections->map(fn (Section $s): array => [
                        'id' => $s->slug ?: $s->id,
                        'raw_id' => $s->id,
                        'name' => $s->name,
                        'description' => $s->description ?? '',
                        'policy' => $s->policy ?? '',
                    ])->toArray();
                }
            }
        } catch (\Throwable) {
            // Fallback graceful jika koneksi DB atau tabel belum siap
        }

        return [
            [
                'id' => 'sec-riset-orisinil',
                'name' => 'Artikel Riset Orisinil',
                'description' => 'Laporan hasil penelitian empiris atau teoritis primer yang orisinil dan relevan dengan pembangunan daerah Kota Makassar.',
                'policy' => 'Wajib memuat metodologi riset yang jelas, data empiris primer/sekunder terverifikasi, dan analisis kebaruan.',
            ],
            [
                'id' => 'sec-kajian-kebijakan',
                'name' => 'Kajian Kebijakan Strategis',
                'description' => 'Telaah kritis berbasis bukti ilmiah untuk rekomendasi kebijakan publik dan inovasi daerah.',
                'policy' => 'Fokus pada rekomendasi actionable bagi Pemerintah Kota Makassar dan pemangku kepentingan.',
            ],
            [
                'id' => 'sec-studi-kasus',
                'name' => 'Studi Kasus & Inovasi Daerah',
                'description' => 'Dokumentasi implementasi inovasi tata kelola pemerintahan, layanan publik, atau teknologi tepat guna.',
                'policy' => 'Menguraikan tantangan nyata, proses implementasi solusi, dan dampak terukur terhadap masyarakat.',
            ],
            [
                'id' => 'sec-tinjauan-literatur',
                'name' => 'Tinjauan Pustaka Sistematis',
                'description' => 'Sintesis komprehensif dan meta-analisis literatur ilmiah terkini mengenai topik strategis perkotaan dan sains terapan.',
                'policy' => 'Wajib menggunakan protokol telaah terstandar seperti PRISMA dan mencantumkan kriteria inklusi serta eksklusi rujukan yang jelas.',
            ],
            [
                'id' => 'sec-komunikasi-pendek',
                'name' => 'Komunikasi Singkat Riset',
                'description' => 'Laporan ringkas hasil riset penting tahap awal atau temuan eksperimen mendesak yang membutuhkan publikasi cepat.',
                'policy' => 'Memprioritaskan temuan baru berurgensi tinggi dengan visualisasi data yang padat dan informatif.',
            ],
            [
                'id' => 'sec-opini-kebijakan',
                'name' => 'Opini Kebijakan & Komentar Ilmiah',
                'description' => 'Perspektif analitis dari pakar atau akademisi terhadap fenomena sosial-ekonomi atau regulasi daerah Kota Makassar.',
                'policy' => 'Gagasan harus berargumen rasional, didukung data faktual, dan menyajikan solusi konstruktif bagi pemangku kebijakan.',
            ],
        ];
    }

    /**
     * Poin-poin checklist kepatuhan etika dan ketentuan penulisan naskah BRIDA (Langkah 1).
     *
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     description: string
     * }>
     */
    public function submissionChecklist(): array
    {
        return [
            [
                'id' => 'chk-orisinil',
                'title' => 'Keaslian & Orisinalitas Karya',
                'description' => 'Naskah merupakan karya orisinil penulis, belum pernah dipublikasikan di jurnal manapun, dan tidak sedang dalam proses telaah di terbitan lain.',
            ],
            [
                'id' => 'chk-format',
                'title' => 'Format & Panduan Penulisan BRIDA',
                'description' => 'Berkas naskah disusun menggunakan template resmi BRIDA Makassar (.docx atau PDF), menggunakan jenis huruf standar, dan mematuhi batas kata.',
            ],
            [
                'id' => 'chk-blind',
                'title' => 'Pematuhan Penelaahan Tertutup',
                'description' => 'Pada berkas utama manuskrip, identitas nama penulis, afiliasi, dan ucapan terima kasih telah disamarkan demi objektivitas telaah mitra bestari.',
            ],
            [
                'id' => 'chk-sitasi',
                'title' => 'Manajemen Referensi & Bebas Plagiasi',
                'description' => 'Seluruh rujukan telah disitasi secara konsisten (disarankan menggunakan Mendeley/Zotero dengan gaya APA 7th) dan indeks kesamaan (Turnitin) < 20%.',
            ],
            [
                'id' => 'chk-etika',
                'title' => 'Persetujuan Etika & Bebas Konflik Kepentingan',
                'description' => 'Riset yang melibatkan subjek manusia/masyarakat telah memenuhi etika penelitian dan tidak terdapat konflik kepentingan finansial maupun institusional.',
            ],
        ];
    }

    /**
     * Tahapan alur 5-langkah wizard pengajuan naskah baru
     *
     * @return array<int, array{
     *     step: int,
     *     title: string,
     *     short_title: string,
     *     description: string,
     *     icon: string
     * }>
     */
    public function wizardSteps(): array
    {
        return [
            [
                'step' => 1,
                'title' => '1. Ketentuan & Seksi',
                'short_title' => 'Ketentuan',
                'description' => 'Pilih seksi jurnal dan persetujuan etika',
                'icon' => 'clipboard-check',
            ],
            [
                'step' => 2,
                'title' => '2. Unggah Berkas',
                'short_title' => 'Unggah',
                'description' => 'Unggah berkas naskah utama',
                'icon' => 'cloud-arrow-up',
            ],
            [
                'step' => 3,
                'title' => '3. Metadata Naskah',
                'short_title' => 'Metadata',
                'description' => 'Judul, abstrak, dan kata kunci',
                'icon' => 'document-text',
            ],
            [
                'step' => 4,
                'title' => '4. Kontributor',
                'short_title' => 'Kontributor',
                'description' => 'Penulis bersama & korespondensi',
                'icon' => 'user-group',
            ],
            [
                'step' => 5,
                'title' => '5. Konfirmasi & Kirim',
                'short_title' => 'Konfirmasi',
                'description' => 'Tinjauan final dan pengiriman',
                'icon' => 'paper-airplane',
            ],
        ];
    }

    /**
     * Profil kontributor utama bawaan dari sesi pengguna aktif (Penulis Pertama).
     *
     * @return array{
     *     given_name: string,
     *     family_name: string,
     *     email: string,
     *     affiliation: string,
     *     orcid_id: string,
     *     sequence: int,
     *     corresponding: bool
     * }
     */
    public function defaultContributor(): array
    {
        return [
            'given_name' => 'Andi Muhammad',
            'family_name' => 'Ilyas',
            'email' => 'andi.ilyas@unhas.ac.id',
            'affiliation' => 'Universitas Hasanuddin, Makassar',
            'orcid_id' => '0000-0002-1825-0097',
            'sequence' => 1,
            'corresponding' => true,
        ];
    }
}
