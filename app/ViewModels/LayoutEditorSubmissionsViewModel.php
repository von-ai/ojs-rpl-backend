<?php

declare(strict_types=1);

namespace App\ViewModels;

class LayoutEditorSubmissionsViewModel
{
    private string $activeTab;

    private string $searchQuery;

    public function __construct(string $activeTab = 'all', string $searchQuery = '')
    {
        $this->activeTab = $activeTab;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Data profil pengguna aktif dengan konteks peran Layout Editor.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Rahmat Hidayat, S.Kom.',
            'role_label' => 'Layout Editor',
            'affiliation' => 'Bagian Publikasi Ilmiah BRIDA Makassar',
            'initials' => 'RH',
        ];
    }

    /**
     * 4 Ringkasan metrik statistik penugasan tata letak publikasi.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        return [
            [
                'title' => 'Total Penugasan Layout',
                'value' => 8,
                'description' => 'Naskah dalam tanggung jawab tata letak',
                'badge_text' => '8 Naskah',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Perlu Draf Galai',
                'value' => 3,
                'description' => 'Naskah baru belum ada berkas PDF',
                'badge_text' => 'Prioritas',
                'color_class' => 'text-amber-700 bg-amber-50 border-amber-200',
            ],
            [
                'title' => 'Koreksi Proofreading',
                'value' => 3,
                'description' => 'Ada catatan perbaikan dari proofreader',
                'badge_text' => 'Perlu Revisi',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Galai Siap Terbit',
                'value' => 14,
                'description' => 'Telah disetujui EIC untuk penerbitan isu',
                'badge_text' => 'Disetujui EIC',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter status penugasan tata letak.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allSubmissions();

        $counts = [
            'all' => count($all),
            'drafting' => count(array_filter($all, fn ($item) => $item['status'] === 'drafting')),
            'proofreading' => count(array_filter($all, fn ($item) => $item['status'] === 'proofreading')),
            'ready' => count(array_filter($all, fn ($item) => $item['status'] === 'ready')),
        ];

        return [
            'active' => $this->activeTab,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Penugasan',
                    'count' => $counts['all'],
                ],
                'drafting' => [
                    'key' => 'drafting',
                    'label' => 'Perlu Draf Galai',
                    'count' => $counts['drafting'],
                ],
                'proofreading' => [
                    'key' => 'proofreading',
                    'label' => 'Koreksi Proofreading',
                    'count' => $counts['proofreading'],
                ],
                'ready' => [
                    'key' => 'ready',
                    'label' => 'Siap Terbit',
                    'count' => $counts['ready'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan data naskah penugasan layout yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     proofreader: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     galley_format: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    public function submissions(): array
    {
        $items = $this->allSubmissions();

        // Filter tab
        if ($this->activeTab !== 'all') {
            $items = array_filter($items, fn ($item) => $item['status'] === $this->activeTab);
        }

        // Filter search query
        if ($this->searchQuery !== '') {
            $q = mb_strtolower($this->searchQuery);
            $items = array_filter($items, function ($item) use ($q) {
                return str_contains(mb_strtolower($item['title']), $q)
                    || str_contains(mb_strtolower($item['number']), $q)
                    || str_contains(mb_strtolower($item['corresponding_author']['name']), $q);
            });
        }

        return array_values($items);
    }

    /**
     * Data pagination.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->submissions());
        $from = $total > 0 ? 1 : 0;
        $to = min($total, 10);

        return [
            'current_page' => 1,
            'per_page' => 10,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'last_page' => max(1, (int) ceil($total / 10)),
        ];
    }

    /**
     * Dataset naskah penugasan tata letak galai.
     *
     * @return array<int, array{
     *     id: string,
     *     number: string,
     *     title: string,
     *     section_title: string,
     *     corresponding_author: array{name: string, affiliation: string},
     *     proofreader: string,
     *     due_at: string,
     *     is_urgent: bool,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     galley_format: string,
     *     unresolved_notes_count: int,
     *     url_workspace: string
     * }>
     */
    private function allSubmissions(): array
    {
        return [
            [
                'id' => 'SUB-2026-00106',
                'number' => 'SUB-2026-00106',
                'title' => 'Analisis Spasial Ruang Terbuka Hijau Terhadap Efek Urban Heat Island Kota Makassar',
                'section_title' => 'Inovasi Daerah & Kebijakan Publik',
                'corresponding_author' => [
                    'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                    'affiliation' => 'Universitas Hasanuddin',
                ],
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'due_at' => '02 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'status' => 'drafting',
                'status_label' => 'Perlu Draf Galai PDF',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'galley_format' => 'PDF (Belum Ada Galai)',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('layouteditor.submissions.show', 'SUB-2026-00106'),
            ],
            [
                'id' => 'SUB-2026-00107',
                'number' => 'SUB-2026-00107',
                'title' => 'Transformasi Digital Pelayanan Publik Kelurahan di Kota Makassar: Evaluasi Implementasi Aplikasi Lorong',
                'section_title' => 'Sosial Humaniora & Tata Kelola',
                'corresponding_author' => [
                    'name' => 'Prof. Dr. Ir. Muhammad Basri, M.T.',
                    'affiliation' => 'Politeknik Negeri Ujung Pandang',
                ],
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'due_at' => '28 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'status' => 'proofreading',
                'status_label' => '2 Catatan Proofreading',
                'status_badge_class' => 'bg-blue-50 text-blue-800 border-blue-200',
                'galley_format' => 'PDF Full Text (v1)',
                'unresolved_notes_count' => 2,
                'url_workspace' => route('layouteditor.submissions.show', 'SUB-2026-00107'),
            ],
            [
                'id' => 'SUB-2026-00108',
                'number' => 'SUB-2026-00108',
                'title' => 'Kajian Karakteristik Geoteknik Sedimen Pesisir Pantai Losari untuk Mitigasi Erosi Gelombang Pasang',
                'section_title' => 'Teknologi & Infrastruktur Berkelanjutan',
                'corresponding_author' => [
                    'name' => 'Dr. Eng. Ir. Faisal Syarif, S.T., M.Eng.',
                    'affiliation' => 'Fakultas Teknik Universitas Hasanuddin',
                ],
                'proofreader' => 'Drs. H. Anwar Kasim, M.Pd.',
                'due_at' => '31 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'status' => 'proofreading',
                'status_label' => '1 Catatan Mayor',
                'status_badge_class' => 'bg-purple-50 text-purple-800 border-purple-200',
                'galley_format' => 'PDF Full Text (v1)',
                'unresolved_notes_count' => 1,
                'url_workspace' => route('layouteditor.submissions.show', 'SUB-2026-00108'),
            ],
            [
                'id' => 'SUB-2026-00109',
                'number' => 'SUB-2026-00109',
                'title' => 'Efektivitas Program Lorong Wisata dalam Peningkatan Pendapatan Rumah Tangga Berpenghasilan Rendah',
                'section_title' => 'Ekonomi Pembangunan & Pemberdayaan Masyarakat',
                'corresponding_author' => [
                    'name' => 'Hj. Fatmawati, S.E., M.Si.',
                    'affiliation' => 'Universitas Fajar Makassar',
                ],
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'due_at' => '05 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'status' => 'ready',
                'status_label' => 'Galai Disetujui EIC',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'galley_format' => 'PDF + HTML (v2 Final)',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('layouteditor.submissions.show', 'SUB-2026-00109'),
            ],
            [
                'id' => 'SUB-2026-00110',
                'number' => 'SUB-2026-00110',
                'title' => 'Strategi Pengelolaan Sampah Berbasis Komunitas di Kawasan Pesisir Tallo',
                'section_title' => 'Lingkungan & Ketahanan Kota',
                'corresponding_author' => [
                    'name' => 'Dr. Muh. Ridwan, S.Kel., M.Si.',
                    'affiliation' => 'Pusat Riset Kebencanaan BRIDA',
                ],
                'proofreader' => 'Drs. H. Anwar Kasim, M.Pd.',
                'due_at' => '07 Nov 2026 23:59 WITA',
                'is_urgent' => false,
                'status' => 'drafting',
                'status_label' => 'Perlu Draf Galai PDF',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'galley_format' => 'PDF (Belum Ada Galai)',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('layouteditor.submissions.show', 'SUB-2026-00110'),
            ],
            [
                'id' => 'SUB-2026-00111',
                'number' => 'SUB-2026-00111',
                'title' => 'Inovasi Pelayanan Kesehatan Terpadu Ibu dan Anak Berbasis Posyandu Lorong di Kota Makassar',
                'section_title' => 'Kesehatan Masyarakat & Gizi',
                'corresponding_author' => [
                    'name' => 'Dr. dr. Risnah, S.Kep., Ns., M.Kes.',
                    'affiliation' => 'UIN Alauddin Makassar',
                ],
                'proofreader' => 'Dra. Sitti Aminah, M.Hum.',
                'due_at' => '29 Okt 2026 23:59 WITA',
                'is_urgent' => true,
                'status' => 'ready',
                'status_label' => 'Siap Terbit',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'galley_format' => 'PDF Full Text (v2 Final)',
                'unresolved_notes_count' => 0,
                'url_workspace' => route('layouteditor.submissions.show', 'SUB-2026-00111'),
            ],
        ];
    }
}
