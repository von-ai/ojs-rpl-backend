<?php

declare(strict_types=1);

namespace App\ViewModels;

class ProofreaderSubmissionsViewModel
{
    private string $activeTab;

    private string $searchQuery;

    public function __construct(string $activeTab = 'all', string $searchQuery = '')
    {
        $this->activeTab = $activeTab;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Data profil pengguna aktif dengan konteks peran Proofreader.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Dra. Sitti Aminah, M.Hum.',
            'role_label' => 'Proofreader',
            'affiliation' => 'Pusat Bahasa & Budaya Sulawesi Selatan',
            'initials' => 'SA',
        ];
    }

    /**
     * 4 Ringkasan metrik statistik penugasan pemeriksaan bukti baca.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        return [
            [
                'title' => 'Total Penugasan Proofread',
                'value' => 6,
                'description' => 'Naskah dalam tanggung jawab bukti baca',
                'badge_text' => '6 Naskah',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Perlu Diperiksa',
                'value' => 2,
                'description' => 'Galai baru atau revisi menunggu pemeriksaan',
                'badge_text' => 'Draf Baru',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
            ],
            [
                'title' => 'Menunggu Revisi Layout',
                'value' => 2,
                'description' => 'Catatan koreksi sedang ditindaklanjuti layout editor',
                'badge_text' => 'Catatan Terbuka',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Pemeriksaan Selesai',
                'value' => 18,
                'description' => 'Galai telah disetujui proofreader & tervalidasi',
                'badge_text' => 'Disetujui',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter status penugasan bukti baca.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allSubmissions();

        $counts = [
            'all' => count($all),
            'pending' => count(array_filter($all, fn ($item) => $item['status'] === 'pending')),
            'waiting_layout' => count(array_filter($all, fn ($item) => $item['status'] === 'waiting_layout')),
            'completed' => count(array_filter($all, fn ($item) => $item['status'] === 'completed')),
        ];

        return [
            'active' => $this->activeTab,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Penugasan',
                    'count' => $counts['all'],
                ],
                'pending' => [
                    'key' => 'pending',
                    'label' => 'Perlu Diperiksa',
                    'count' => $counts['pending'],
                ],
                'waiting_layout' => [
                    'key' => 'waiting_layout',
                    'label' => 'Menunggu Revisi Layout',
                    'count' => $counts['waiting_layout'],
                ],
                'completed' => [
                    'key' => 'completed',
                    'label' => 'Pemeriksaan Selesai',
                    'count' => $counts['completed'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan data naskah penugasan proofreading yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     layout_editor: string,
     *     galley_version: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    public function submissions(): array
    {
        $items = $this->allSubmissions();

        // Filter berdasarkan Tab Status
        if ($this->activeTab !== 'all') {
            $items = array_values(array_filter($items, fn ($item) => $item['status'] === $this->activeTab));
        }

        // Filter berdasarkan Query Pencarian
        if ($this->searchQuery !== '') {
            $query = mb_strtolower($this->searchQuery);
            $items = array_values(array_filter($items, function ($item) use ($query) {
                return str_contains(mb_strtolower($item['title']), $query)
                    || str_contains(mb_strtolower($item['number']), $query)
                    || str_contains(mb_strtolower($item['corresponding_author']['name']), $query)
                    || str_contains(mb_strtolower($item['section_title']), $query);
            }));
        }

        return $items;
    }

    /**
     * Metadata paginasi untuk daftar penugasan.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->submissions());

        return [
            'current_page' => 1,
            'per_page' => 10,
            'total' => $total,
            'from' => $total > 0 ? 1 : 0,
            'to' => $total,
            'last_page' => 1,
        ];
    }

    /**
     * Mendapatkan query pencarian aktif.
     */
    public function searchQuery(): string
    {
        return $this->searchQuery;
    }

    /**
     * Seluruh data penugasan proofreading (dataset mock realistis).
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     layout_editor: string,
     *     galley_version: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    private function allSubmissions(): array
    {
        return [
            [
                'id' => 'SUB-2026-00107',
                'number' => 'SUB-2026-00107',
                'title' => 'Analisis Spasial Kerentanan Banjir Rob Kawasan Pesisir Kota Makassar Menggunakan Sentinel-1A',
                'section_title' => 'Kebijakan Lingkungan & Mitigasi Bencana',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                    'affiliation' => 'Departemen Teknik Sipil, Universitas Hasanuddin',
                ],
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'galley_version' => 'v1',
                'due_at' => '27 Okt 2026',
                'is_urgent' => true,
                'status' => 'pending',
                'status_label' => 'Perlu Pemeriksaan Galai',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('proofreader.submissions.show', 'SUB-2026-00107'),
            ],
            [
                'id' => 'SUB-2026-00106',
                'number' => 'SUB-2026-00106',
                'title' => 'Strategi Pengembangan Destinasi Wisata Sejarah Benteng Somba Opu Berbasis Smart Tourism',
                'section_title' => 'Inovasi Pelayanan Publik & Pariwisata',
                'corresponding_author' => [
                    'name' => 'Dr. Andi Nurul Hidayah, M.Si.',
                    'affiliation' => 'Fakultas Ilmu Sosial dan Politik, Universitas Negeri Makassar',
                ],
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'galley_version' => 'v2',
                'due_at' => '28 Okt 2026',
                'is_urgent' => false,
                'status' => 'pending',
                'status_label' => 'Verifikasi Revisi Layout (v2)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'unresolved_notes_count' => 3,
                'url_workspace' => route('proofreader.submissions.show', 'SUB-2026-00106'),
            ],
            [
                'id' => 'SUB-2026-00105',
                'number' => 'SUB-2026-00105',
                'title' => 'Optimasi Pola Tanam Padi Sawah Irigasi Teknis di Daerah Aliran Sungai Saddang',
                'section_title' => 'Teknologi & Tata Kelola Perkotaan',
                'corresponding_author' => [
                    'name' => 'Dr. Ir. Muh. Asdar, M.Sc.',
                    'affiliation' => 'Fakultas Pertanian, Universitas Hasanuddin',
                ],
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'galley_version' => 'v1',
                'due_at' => '29 Okt 2026',
                'is_urgent' => false,
                'status' => 'waiting_layout',
                'status_label' => 'Menunggu Revisi Layout',
                'status_badge_class' => 'bg-blue-50 text-blue-800 border-blue-200/80',
                'unresolved_notes_count' => 2,
                'url_workspace' => route('proofreader.submissions.show', 'SUB-2026-00105'),
            ],
            [
                'id' => 'SUB-2026-00104',
                'number' => 'SUB-2026-00104',
                'title' => 'Evaluasi Efektivitas Layanan Pajak Daerah Online Sipapa di Bapenda Kota Makassar',
                'section_title' => 'Ekonomi Pembangunan & Keuangan Daerah',
                'corresponding_author' => [
                    'name' => 'Dr. Fatmawati, S.E., M.Si.',
                    'affiliation' => 'Departemen Ilmu Ekonomi, Universitas Hasanuddin',
                ],
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'galley_version' => 'v1',
                'due_at' => '30 Okt 2026',
                'is_urgent' => false,
                'status' => 'waiting_layout',
                'status_label' => 'Menunggu Revisi Layout',
                'status_badge_class' => 'bg-blue-50 text-blue-800 border-blue-200/80',
                'unresolved_notes_count' => 4,
                'url_workspace' => route('proofreader.submissions.show', 'SUB-2026-00104'),
            ],
            [
                'id' => 'SUB-2026-00103',
                'number' => 'SUB-2026-00103',
                'title' => 'Persepsi Masyarakat Terhadap Penerapan Transportasi Publik Teman Bus di Kawasan Aglomerasi Mamminasata',
                'section_title' => 'Teknologi & Tata Kelola Perkotaan',
                'corresponding_author' => [
                    'name' => 'Ir. Muhammad Arsyad, Ph.D.',
                    'affiliation' => 'Fakultas Teknik, Universitas Hasanuddin',
                ],
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'galley_version' => 'v2',
                'due_at' => 'Selesai 24 Okt 2026',
                'is_urgent' => false,
                'status' => 'completed',
                'status_label' => 'Pemeriksaan Bukti Selesai',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('proofreader.submissions.show', 'SUB-2026-00103'),
            ],
            [
                'id' => 'SUB-2026-00102',
                'number' => 'SUB-2026-00102',
                'title' => 'Kajian Daya Dukung Lingkungan Hidup Berbasis Kemampuan Lahan di Pulau Lae-Lae Makassar',
                'section_title' => 'Kebijakan Lingkungan & Mitigasi Bencana',
                'corresponding_author' => [
                    'name' => 'Dr. Firman, S.Kel., M.Si.',
                    'affiliation' => 'Fakultas Ilmu Kelautan dan Perikanan, Universitas Hasanuddin',
                ],
                'layout_editor' => 'Rahmat Hidayat, S.Kom.',
                'galley_version' => 'v1',
                'due_at' => 'Selesai 23 Okt 2026',
                'is_urgent' => false,
                'status' => 'completed',
                'status_label' => 'Pemeriksaan Bukti Selesai',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('proofreader.submissions.show', 'SUB-2026-00102'),
            ],
        ];
    }
}
