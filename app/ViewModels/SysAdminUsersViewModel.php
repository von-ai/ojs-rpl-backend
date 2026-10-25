<?php

declare(strict_types=1);

namespace App\ViewModels;

class SysAdminUsersViewModel
{
    private string $activeRole;

    private string $searchQuery;

    public function __construct(string $activeRole = 'all', string $searchQuery = '')
    {
        $this->activeRole = $activeRole;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * 4 Ringkasan metrik statistik direktori pengguna global.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        return [
            [
                'title' => 'Total Akun Pengguna',
                'value' => 142,
                'description' => 'Pengguna terverifikasi lintas instansi jurnal',
                'badge_text' => '142 Akun',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'System Administrator',
                'value' => 2,
                'description' => 'Pemegang hak akses superadmin global',
                'badge_text' => 'Superadmin',
                'color_class' => 'text-purple-700 bg-purple-50 border-purple-200',
            ],
            [
                'title' => 'Dewan Redaksi Aktif',
                'value' => 18,
                'description' => 'Editor in Chief, Section Editor, & Manager',
                'badge_text' => 'Redaksi Aktif',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Reviewer Terdaftar',
                'value' => 64,
                'description' => 'Mitra bestari dengan bidang kepakaran terdata',
                'badge_text' => 'Mitra Bestari',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter peran pengguna.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allUsers();

        $counts = [
            'all' => count($all),
            'sysadmin' => count(array_filter($all, fn ($item) => $item['is_sysadmin'])),
            'editor' => count(array_filter($all, fn ($item) => in_array('editor', $item['role_codes'], true))),
            'reviewer' => count(array_filter($all, fn ($item) => in_array('reviewer', $item['role_codes'], true))),
            'author' => count(array_filter($all, fn ($item) => in_array('author', $item['role_codes'], true))),
        ];

        return [
            'active' => $this->activeRole,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Pengguna',
                    'count' => $counts['all'],
                ],
                'sysadmin' => [
                    'key' => 'sysadmin',
                    'label' => 'System Admin',
                    'count' => $counts['sysadmin'],
                ],
                'editor' => [
                    'key' => 'editor',
                    'label' => 'Dewan Redaksi',
                    'count' => $counts['editor'],
                ],
                'reviewer' => [
                    'key' => 'reviewer',
                    'label' => 'Reviewer',
                    'count' => $counts['reviewer'],
                ],
                'author' => [
                    'key' => 'author',
                    'label' => 'Penulis',
                    'count' => $counts['author'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan pengguna terdaftar yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     email: string,
     *     affiliation: string,
     *     is_sysadmin: bool,
     *     roles_formatted: string,
     *     role_codes: array<int, string>,
     *     journal_memberships_count: int,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     created_at: string
     * }>
     */
    public function users(): array
    {
        $items = $this->allUsers();

        // Filter berdasarkan peran
        if ($this->activeRole !== 'all') {
            if ($this->activeRole === 'sysadmin') {
                $items = array_values(array_filter($items, fn ($item) => $item['is_sysadmin']));
            } else {
                $role = $this->activeRole;
                $items = array_values(array_filter($items, fn ($item) => in_array($role, $item['role_codes'], true)));
            }
        }

        // Filter pencarian
        if ($this->searchQuery !== '') {
            $query = mb_strtolower($this->searchQuery);
            $items = array_values(array_filter($items, function ($item) use ($query) {
                return str_contains(mb_strtolower($item['name']), $query)
                    || str_contains(mb_strtolower($item['email']), $query)
                    || str_contains(mb_strtolower($item['affiliation']), $query);
            }));
        }

        return $items;
    }

    /**
     * Riwayat pengimporan massal pengguna via berkas CSV (Tabel 54 user_imports).
     *
     * @return array<int, array{
     *     id: string,
     *     journal_name: string,
     *     file_name: string,
     *     total_rows: int,
     *     processed_rows: int,
     *     failed_rows: int,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     created_at: string
     * }>
     */
    public function recentImports(): array
    {
        return [
            [
                'id' => 'IMPORT-01JARK-01',
                'journal_name' => 'JTIIK Makassar',
                'file_name' => 'Reviewer_Kepakaran_SmartCity_2026.csv',
                'total_rows' => 35,
                'processed_rows' => 35,
                'failed_rows' => 0,
                'status' => 'completed',
                'status_label' => 'Selesai Sukses',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '22 Okt 2026, 14:15 WITA',
            ],
            [
                'id' => 'IMPORT-01JARK-02',
                'journal_name' => 'JIKPM Makassar',
                'file_name' => 'Dosen_Fisip_UNM_Kontributor.csv',
                'total_rows' => 20,
                'processed_rows' => 19,
                'failed_rows' => 1,
                'status' => 'completed',
                'status_label' => 'Selesai (1 Gagal)',
                'status_badge_class' => 'bg-amber-50 text-amber-800 border-amber-200',
                'created_at' => '18 Okt 2026, 09:30 WITA',
            ],
        ];
    }

    /**
     * Metadata paginasi pengguna.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->users());

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
     * Daftar seluruh data pengguna global.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     email: string,
     *     affiliation: string,
     *     is_sysadmin: bool,
     *     roles_formatted: string,
     *     role_codes: array<int, string>,
     *     journal_memberships_count: int,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     created_at: string
     * }>
     */
    private function allUsers(): array
    {
        return [
            [
                'id' => 'USER-01JARK-01',
                'name' => 'Fahri Azis, S.T.',
                'email' => 'fahri.azis@makassar.go.id',
                'affiliation' => 'Bidang Aplikasi Informatika & Inovasi BRIDA Makassar',
                'is_sysadmin' => true,
                'roles_formatted' => 'System Administrator',
                'role_codes' => ['sysadmin'],
                'journal_memberships_count' => 4,
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '01 Jan 2026',
            ],
            [
                'id' => 'USER-01JARK-02',
                'name' => 'Muh. Ilham, S.Kom., M.T.',
                'email' => 'ilham.it@makassar.go.id',
                'affiliation' => 'Dinas Komunikasi dan Informatika Kota Makassar',
                'is_sysadmin' => true,
                'roles_formatted' => 'System Administrator, Journal Manager',
                'role_codes' => ['sysadmin', 'editor'],
                'journal_memberships_count' => 2,
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '15 Jan 2026',
            ],
            [
                'id' => 'USER-01JARK-03',
                'name' => 'Dr. Eng. Ir. Rita Irmawaty, S.T., M.T.',
                'email' => 'rita.irmawaty@unhas.ac.id',
                'affiliation' => 'Departemen Teknik Arsitektur & Perencanaan, Universitas Hasanuddin',
                'is_sysadmin' => false,
                'roles_formatted' => 'Editor in Chief (JTIIK), Reviewer',
                'role_codes' => ['editor', 'reviewer'],
                'journal_memberships_count' => 1,
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '02 Feb 2026',
            ],
            [
                'id' => 'USER-01JARK-04',
                'name' => 'Dr. Andi Nurul Hidayah, M.Si.',
                'email' => 'andi.nurul@unm.ac.id',
                'affiliation' => 'Fakultas Ilmu Sosial dan Politik, Universitas Negeri Makassar',
                'is_sysadmin' => false,
                'roles_formatted' => 'Journal Manager (JIKPM), Penulis',
                'role_codes' => ['editor', 'author'],
                'journal_memberships_count' => 1,
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '10 Feb 2026',
            ],
            [
                'id' => 'USER-01JARK-05',
                'name' => 'Prof. Dr. Ir. M. Saleh Pallu, M.Eng.',
                'email' => 'saleh.pallu@unhas.ac.id',
                'affiliation' => 'Departemen Teknik Sipil, Universitas Hasanuddin',
                'is_sysadmin' => false,
                'roles_formatted' => 'Reviewer Senior, Penulis',
                'role_codes' => ['reviewer', 'author'],
                'journal_memberships_count' => 2,
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '20 Feb 2026',
            ],
            [
                'id' => 'USER-01JARK-06',
                'name' => 'Dra. Sitti Aminah, M.Hum.',
                'email' => 'sitti.aminah@bahasa.kemdikbud.go.id',
                'affiliation' => 'Pusat Bahasa & Budaya Sulawesi Selatan',
                'is_sysadmin' => false,
                'roles_formatted' => 'Proofreader, Reviewer Bahasa',
                'role_codes' => ['reviewer'],
                'journal_memberships_count' => 3,
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'created_at' => '05 Mar 2026',
            ],
        ];
    }
}
