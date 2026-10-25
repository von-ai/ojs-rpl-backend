<?php

declare(strict_types=1);

namespace App\ViewModels;

class SysAdminJournalsViewModel
{
    private string $activeStatus;

    private string $searchQuery;

    public function __construct(string $activeStatus = 'all', string $searchQuery = '')
    {
        $this->activeStatus = $activeStatus;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Data profil pengguna aktif dengan konteks System Administrator.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Fahri Azis, S.T.',
            'role_label' => 'System Administrator',
            'affiliation' => 'Pengelola Sistem & Infrastruktur IT BRIDA',
            'initials' => 'FA',
        ];
    }

    /**
     * 4 Ringkasan metrik statistik multi-jurnal ilmiah.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        return [
            [
                'title' => 'Total Jurnal Ilmiah',
                'value' => 4,
                'description' => 'Instansi jurnal terdaftar dalam platform',
                'badge_text' => '4 Instansi',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Jurnal Aktif',
                'value' => 3,
                'description' => 'Jurnal aktif menerima dan mempublikasikan artikel',
                'badge_text' => 'Aktif Publikasi',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
            [
                'title' => 'Edisi Terbit Berjalan',
                'value' => 6,
                'description' => 'Total terbitan aktif tahun berjalan',
                'badge_text' => 'Volume 2026',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Jurnal Diarsipkan',
                'value' => 1,
                'description' => 'Arsip historis terbitan masa lalu',
                'badge_text' => 'Arsip Historis',
                'color_class' => 'text-gray-700 bg-gray-100 border-gray-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter status jurnal.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allJournals();

        $counts = [
            'all' => count($all),
            'active' => count(array_filter($all, fn ($item) => $item['status'] === 'active')),
            'archived' => count(array_filter($all, fn ($item) => $item['status'] === 'archived')),
            'unlisted' => count(array_filter($all, fn ($item) => $item['status'] === 'unlisted')),
        ];

        return [
            'active' => $this->activeStatus,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Jurnal',
                    'count' => $counts['all'],
                ],
                'active' => [
                    'key' => 'active',
                    'label' => 'Aktif Publikasi',
                    'count' => $counts['active'],
                ],
                'archived' => [
                    'key' => 'archived',
                    'label' => 'Diarsipkan',
                    'count' => $counts['archived'],
                ],
                'unlisted' => [
                    'key' => 'unlisted',
                    'label' => 'Tidak Terdaftar',
                    'count' => $counts['unlisted'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan data jurnal ilmiah yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     slug: string,
     *     name: string,
     *     description: string,
     *     issn: ?string,
     *     eissn: ?string,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     total_submissions: int,
     *     current_issue_title: ?string,
     *     primary_contact: string,
     *     url_public: string
     * }>
     */
    public function journals(): array
    {
        $items = $this->allJournals();

        // Filter berdasarkan status
        if ($this->activeStatus !== 'all') {
            $items = array_values(array_filter($items, fn ($item) => $item['status'] === $this->activeStatus));
        }

        // Filter berdasarkan kata kunci pencarian
        if ($this->searchQuery !== '') {
            $query = mb_strtolower($this->searchQuery);
            $items = array_values(array_filter($items, function ($item) use ($query) {
                return str_contains(mb_strtolower($item['name']), $query)
                    || str_contains(mb_strtolower($item['slug']), $query)
                    || ($item['issn'] && str_contains(mb_strtolower($item['issn']), $query))
                    || ($item['eissn'] && str_contains(mb_strtolower($item['eissn']), $query))
                    || str_contains(mb_strtolower($item['primary_contact']), $query);
            }));
        }

        return $items;
    }

    /**
     * Metadata paginasi untuk daftar jurnal.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->journals());

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
     * Seluruh data instansi jurnal ilmiah (mock realistis SIRIDA).
     *
     * @return array<int, array{
     *     id: string,
     *     slug: string,
     *     name: string,
     *     description: string,
     *     issn: ?string,
     *     eissn: ?string,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     total_submissions: int,
     *     current_issue_title: ?string,
     *     primary_contact: string,
     *     url_public: string
     * }>
     */
    private function allJournals(): array
    {
        return [
            [
                'id' => 'JOURNAL-01JARK-01',
                'slug' => 'jtiik-makassar',
                'name' => 'Jurnal Teknologi Informasi dan Inovasi Perkotaan (JTIIK)',
                'description' => 'Publikasi ilmiah berkala memuat riset terapan smart city, IoT, dan transformasi digital pemerintahan Kota Makassar.',
                'issn' => '2655-9012',
                'eissn' => '2722-4567',
                'status' => 'active',
                'status_label' => 'Aktif Publikasi',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'total_submissions' => 48,
                'current_issue_title' => 'Vol. 4 No. 2 (2026): Edisi Reguler Oktober',
                'primary_contact' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'url_public' => '/jtiik-makassar',
            ],
            [
                'id' => 'JOURNAL-01JARK-02',
                'slug' => 'sirida-inovasi',
                'name' => 'Jurnal Inovasi Daerah & Kebijakan Publik Makassar (JIKPM)',
                'description' => 'Kajian empiris efektivitas tata kelola birokrasi, inovasi pelayanan publik, dan pembiayaan pembangunan daerah.',
                'issn' => '2774-8890',
                'eissn' => '2809-1234',
                'status' => 'active',
                'status_label' => 'Aktif Publikasi',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'total_submissions' => 36,
                'current_issue_title' => 'Vol. 3 No. 1 (2026): Inovasi Layanan Terpadu',
                'primary_contact' => 'Dr. Andi Nurul Hidayah, M.Si.',
                'url_public' => '/sirida-inovasi',
            ],
            [
                'id' => 'JOURNAL-01JARK-03',
                'slug' => 'jurnal-lingkungan',
                'name' => 'Jurnal Riset Lingkungan & Mitigasi Bencana Pesisir (JRLMBP)',
                'description' => 'Kajian multidisiplin oseanografi, mitigasi banjir rob kawasan kepulauan spermonde, dan adaptasi perubahan iklim.',
                'issn' => '2985-3341',
                'eissn' => '2986-7789',
                'status' => 'active',
                'status_label' => 'Aktif Publikasi',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'total_submissions' => 29,
                'current_issue_title' => 'Vol. 2 No. 2 (2026): Mitigasi Iklim Wilayah Kepulauan',
                'primary_contact' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                'url_public' => '/jurnal-lingkungan',
            ],
            [
                'id' => 'JOURNAL-01JARK-04',
                'slug' => 'arsip-litbang-makassar',
                'name' => 'Buletin Riset Litbang Kota Makassar (Historis 2018-2022)',
                'description' => 'Arsip terbitan monograf dan buletin penelitian Badan Penelitian dan Pengembangan Daerah pra-transformasi BRIDA.',
                'issn' => '2301-4455',
                'eissn' => null,
                'status' => 'archived',
                'status_label' => 'Diarsipkan',
                'status_badge_class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'total_submissions' => 62,
                'current_issue_title' => 'Edisi Khusus Penutup (2022)',
                'primary_contact' => 'Fahri Azis, S.T. (SysAdmin)',
                'url_public' => '/arsip-litbang-makassar',
            ],
        ];
    }
}
