<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class JournalManagerUsersViewModel
{
    private string $activeFilter;

    private string $searchQuery;

    public function __construct(string $activeFilter = 'all', string $searchQuery = '')
    {
        $this->activeFilter = $activeFilter;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * Helper statis untuk mengambil badge count jumlah pengguna jurnal secara dinamis dan aman.
     */
    public static function getSidebarBadgeCount(): int
    {
        try {
            if (class_exists(User::class) && Schema::hasTable('users') && User::query()->exists()) {
                return User::query()->count();
            }

            return 28;
        } catch (\Throwable) {
            return 28;
        }
    }

    /**
     * Profil pengguna aktif Journal Manager.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? 'Andi Muhammad Yusuf, M.Si.';
        $affiliation = $user?->profile?->affiliation ?? 'BRIDA Kota Makassar';

        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name) ?: 'AY';
        $initials = strtoupper(substr($cleanName, 0, 2));

        return [
            'name' => $name,
            'role_label' => 'Journal Manager',
            'affiliation' => $affiliation,
            'initials' => $initials,
        ];
    }

    /**
     * 4 Ringkasan metrik statistik pengguna dan tim redaksi jurnal.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        if (Schema::hasTable('users') && User::count() > 0) {
            $all = $this->allUsers();
            $total = count($all);

            $editorialCount = count(array_filter($all, fn ($u) => count(array_intersect($u['role_codes'], ['manager', 'editor_in_chief', 'section_editor'])) > 0));
            $reviewersCount = count(array_filter($all, fn ($u) => in_array('reviewer', $u['role_codes'], true)));
            $authorsCount = count(array_filter($all, fn ($u) => in_array('author', $u['role_codes'], true)));

            return [
                [
                    'title' => 'Total Pengguna Jurnal',
                    'value' => $total,
                    'description' => 'Akun terdaftar dalam lingkup jurnal',
                    'badge_text' => "{$total} Anggota",
                    'color_class' => 'text-gray-900 bg-gray-50 border-gray-200',
                ],
                [
                    'title' => 'Dewan Redaksi (Editorial)',
                    'value' => $editorialCount,
                    'description' => 'EIC, Section Editor, dan Tim Produksi',
                    'badge_text' => 'Editorial Board',
                    'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
                ],
                [
                    'title' => 'Direktori Penelaah',
                    'value' => $reviewersCount,
                    'description' => 'Peer Reviewer dengan kepakaran terverifikasi',
                    'badge_text' => 'Penelaah Aktif',
                    'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
                ],
                [
                    'title' => 'Penulis & Pembaca',
                    'value' => $authorsCount,
                    'description' => 'Author aktif dan kontributor naskah',
                    'badge_text' => 'Kontributor',
                    'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                ],
            ];
        }

        return [
            [
                'title' => 'Total Pengguna Jurnal',
                'value' => 28,
                'description' => 'Akun terdaftar dalam lingkup jurnal',
                'badge_text' => '28 Anggota',
                'color_class' => 'text-gray-900 bg-gray-50 border-gray-200',
            ],
            [
                'title' => 'Dewan Redaksi (Editorial)',
                'value' => 8,
                'description' => 'EIC, Section Editor, dan Tim Produksi',
                'badge_text' => 'Editorial Board',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Direktori Penelaah',
                'value' => 12,
                'description' => 'Peer Reviewer dengan kepakaran terverifikasi',
                'badge_text' => 'Penelaah Aktif',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Penulis & Pembaca',
                'value' => 14,
                'description' => 'Author aktif dan kontributor naskah',
                'badge_text' => 'Kontributor',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter peran pengguna.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function filters(): array
    {
        $all = $this->allUsers();

        $counts = [
            'all' => count($all),
            'editorial' => count(array_filter($all, fn ($u) => count(array_intersect($u['role_codes'], ['manager', 'editor_in_chief', 'section_editor'])) > 0)),
            'production' => count(array_filter($all, fn ($u) => count(array_intersect($u['role_codes'], ['copyeditor', 'layout_editor', 'proofreader'])) > 0)),
            'reviewer' => count(array_filter($all, fn ($u) => in_array('reviewer', $u['role_codes'], true))),
            'author' => count(array_filter($all, fn ($u) => in_array('author', $u['role_codes'], true))),
            'unassigned' => count(array_filter($all, fn ($u) => empty($u['role_codes']))),
        ];

        return [
            'active' => $this->activeFilter,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Pengguna',
                    'count' => $counts['all'],
                ],
                'editorial' => [
                    'key' => 'editorial',
                    'label' => 'Dewan Editor',
                    'count' => $counts['editorial'],
                ],
                'production' => [
                    'key' => 'production',
                    'label' => 'Tim Produksi',
                    'count' => $counts['production'],
                ],
                'reviewer' => [
                    'key' => 'reviewer',
                    'label' => 'Direktori Penelaah',
                    'count' => $counts['reviewer'],
                ],
                'author' => [
                    'key' => 'author',
                    'label' => 'Penulis (Author)',
                    'count' => $counts['author'],
                ],
                'unassigned' => [
                    'key' => 'unassigned',
                    'label' => 'Belum Ada Peran',
                    'count' => $counts['unassigned'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan pengguna yang telah difilter berdasarkan peran dan pencarian.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     email: string,
     *     affiliation: string,
     *     role_codes: array<int, string>,
     *     roles: array<int, array{code: string, label: string, badge_class: string}>,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     expertise: array<int, string>,
     *     active_assignments_count: int,
     *     is_last_admin: bool,
     *     is_last_eic: bool,
     *     joined_at: string
     * }>
     */
    public function users(): array
    {
        $items = $this->allUsers();

        // Filter tab peran
        if ($this->activeFilter === 'editorial') {
            $items = array_filter($items, fn ($u) => count(array_intersect($u['role_codes'], ['manager', 'editor_in_chief', 'section_editor'])) > 0);
        } elseif ($this->activeFilter === 'production') {
            $items = array_filter($items, fn ($u) => count(array_intersect($u['role_codes'], ['copyeditor', 'layout_editor', 'proofreader'])) > 0);
        } elseif ($this->activeFilter === 'reviewer') {
            $items = array_filter($items, fn ($u) => in_array('reviewer', $u['role_codes'], true));
        } elseif ($this->activeFilter === 'author') {
            $items = array_filter($items, fn ($u) => in_array('author', $u['role_codes'], true));
        } elseif ($this->activeFilter === 'unassigned') {
            $items = array_filter($items, fn ($u) => empty($u['role_codes']));
        }

        // Filter pencarian
        if ($this->searchQuery !== '') {
            $q = mb_strtolower($this->searchQuery);
            $items = array_filter($items, function ($u) use ($q) {
                if (str_contains(mb_strtolower($u['name']), $q)
                    || str_contains(mb_strtolower($u['email']), $q)
                    || str_contains(mb_strtolower($u['affiliation']), $q)) {
                    return true;
                }
                foreach ($u['expertise'] as $exp) {
                    if (str_contains(mb_strtolower($exp), $q)) {
                        return true;
                    }
                }

                return false;
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
        $total = count($this->users());
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
     * Dataset keanggotaan pengguna dan peran jurnal dari basis data.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     email: string,
     *     affiliation: string,
     *     role_codes: array<int, string>,
     *     roles: array<int, array{code: string, label: string, badge_class: string}>,
     *     status: string,
     *     status_label: string,
     *     status_badge_class: string,
     *     expertise: array<int, string>,
     *     active_assignments_count: int,
     *     is_last_admin: bool,
     *     is_last_eic: bool,
     *     joined_at: string
     * }>
     */
    private function allUsers(): array
    {
        if (Schema::hasTable('users')) {
            $query = User::with(['profile', 'userRoles']);

            if (Schema::hasTable('copyedits')) {
                $query->withCount('copyedits');
            }
            if (Schema::hasTable('layout_assignments')) {
                $query->withCount('layoutAssignments');
            }

            $users = $query->get();

            if ($users->isNotEmpty()) {
                // Hitung total manager dan EIC untuk perlindungan last admin / last eic
                $managerCount = UserRole::where('role_code', 'manager')->count();
                $eicCount = UserRole::where('role_code', 'editor_in_chief')->count();

                return $users->map(function (User $u) use ($managerCount, $eicCount) {
                    $roles = $u->userRoles;
                    $roleCodes = $roles->pluck('role_code')->all();

                    // Jika belum ada record eksplisit di user_roles, biarkan kosong sebagai pengguna biasa / belum ada peran
                    if (empty($roleCodes)) {
                        $roleCodes = [];
                        $formattedRoles = [
                            [
                                'code' => 'unassigned',
                                'label' => 'Belum Ada Peran',
                                'badge_class' => 'bg-gray-100 text-gray-600 border-gray-200 border-dashed',
                            ],
                        ];
                    } else {
                        $formattedRoles = array_map(function ($code) {
                            return [
                                'code' => $code,
                                'label' => UserRole::roleLabel($code),
                                'badge_class' => UserRole::roleBadgeClass($code),
                            ];
                        }, $roleCodes);
                    }

                    // Gabungkan kata kunci kepakaran dari peran reviewer
                    $expertise = [];
                    foreach ($roles as $r) {
                        if (! empty($r->expertise_keywords) && is_array($r->expertise_keywords)) {
                            $expertise = array_merge($expertise, $r->expertise_keywords);
                        }
                    }

                    $assignmentsCount = (int) ($u->copyedits_count ?? 0) + (int) ($u->layout_assignments_count ?? 0);

                    $isLastAdmin = in_array('manager', $roleCodes, true) && $managerCount <= 1;
                    $isLastEic = in_array('editor_in_chief', $roleCodes, true) && $eicCount <= 1;

                    return [
                        'id' => (string) $u->id,
                        'name' => (string) ($u->name ?: $u->full_name),
                        'email' => (string) $u->email,
                        'affiliation' => (string) ($u->profile?->affiliation ?? 'Pemerintah Kota Makassar'),
                        'role_codes' => $roleCodes,
                        'roles' => $formattedRoles,
                        'status' => (string) ($u->status ?? 'active'),
                        'status_label' => ($u->status === 'active' || empty($u->status)) ? 'Aktif' : ucfirst($u->status),
                        'status_badge_class' => ($u->status === 'active' || empty($u->status))
                            ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                            : 'bg-blue-50 text-blue-800 border-blue-200',
                        'expertise' => array_values(array_unique($expertise)),
                        'active_assignments_count' => $assignmentsCount,
                        'is_last_admin' => $isLastAdmin,
                        'is_last_eic' => $isLastEic,
                        'joined_at' => $u->created_at ? $u->created_at->translatedFormat('d M Y') : '12 Jan 2024',
                    ];
                })->all();
            }
        }

        return $this->registryUsers();
    }

    private function registryUsers(): array
    {
        return [
            [
                'id' => 'USR-01JARK-001',
                'name' => 'Andi Muhammad Yusuf, M.Si.',
                'email' => 'yusuf.brida@makassar.go.id',
                'affiliation' => 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar',
                'role_codes' => ['manager'],
                'roles' => [
                    ['code' => 'manager', 'label' => 'Journal Manager', 'badge_class' => 'bg-purple-50 text-purple-800 border-purple-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Kebijakan Publik', 'Manajemen Riset Daerah'],
                'active_assignments_count' => 0,
                'is_last_admin' => true,
                'is_last_eic' => false,
                'joined_at' => '12 Jan 2024',
            ],
            [
                'id' => 'USR-01JARK-002',
                'name' => 'Prof. Dr. Ir. H. Nasaruddin, M.T.',
                'email' => 'nasaruddin.eic@unhas.ac.id',
                'affiliation' => 'Universitas Hasanuddin',
                'role_codes' => ['editor_in_chief'],
                'roles' => [
                    ['code' => 'editor_in_chief', 'label' => 'Editor in Chief', 'badge_class' => 'bg-brand-maroon text-white border-brand-maroon'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Infrastruktur Perkotaan', 'Teknologi Lingkungan'],
                'active_assignments_count' => 4,
                'is_last_admin' => false,
                'is_last_eic' => true,
                'joined_at' => '15 Jan 2024',
            ],
            [
                'id' => 'USR-01JARK-003',
                'name' => 'Prof. Hamka, Ph.D.',
                'email' => 'hamka.editor@unhas.ac.id',
                'affiliation' => 'Fakultas Ilmu Sosial & Ilmu Politik UNHAS',
                'role_codes' => ['section_editor'],
                'roles' => [
                    ['code' => 'section_editor', 'label' => 'Section Editor', 'badge_class' => 'bg-blue-50 text-blue-800 border-blue-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Inovasi Pemerintahan', 'Sosial Humaniora'],
                'active_assignments_count' => 3,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '01 Feb 2024',
            ],
            [
                'id' => 'USR-01JARK-004',
                'name' => 'Dr. Hj. St. Rohani, M.Si.',
                'email' => 'rohani.editor@unm.ac.id',
                'affiliation' => 'Universitas Negeri Makassar',
                'role_codes' => ['section_editor'],
                'roles' => [
                    ['code' => 'section_editor', 'label' => 'Section Editor', 'badge_class' => 'bg-blue-50 text-blue-800 border-blue-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Pemberdayaan Masyarakat', 'Ekonomi Kerakyatan'],
                'active_assignments_count' => 2,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '10 Feb 2024',
            ],
            [
                'id' => 'USR-01JARK-005',
                'name' => 'Rahmat Hidayat, S.Kom.',
                'email' => 'rahmat.layout@brida.makassar.go.id',
                'affiliation' => 'Bagian Publikasi Ilmiah BRIDA Makassar',
                'role_codes' => ['layout_editor'],
                'roles' => [
                    ['code' => 'layout_editor', 'label' => 'Layout Editor', 'badge_class' => 'bg-amber-50 text-amber-800 border-amber-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Tipografi Ilmiah', 'Format Galai PDF/HTML'],
                'active_assignments_count' => 5,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '01 Mar 2024',
            ],
            [
                'id' => 'USR-01JARK-006',
                'name' => 'Dra. Sitti Aminah, M.Hum.',
                'email' => 'sitti.aminah@unhas.ac.id',
                'affiliation' => 'Pusat Bahasa & Budaya Sulawesi Selatan',
                'role_codes' => ['copyeditor', 'proofreader'],
                'roles' => [
                    ['code' => 'copyeditor', 'label' => 'Copy Editor', 'badge_class' => 'bg-sky-50 text-sky-800 border-sky-200'],
                    ['code' => 'proofreader', 'label' => 'Proofreader', 'badge_class' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['EYD Edisi V', 'Tata Bahasa Akademik', 'Koreksi Galai'],
                'active_assignments_count' => 4,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '05 Mar 2024',
            ],
            [
                'id' => 'USR-01JARK-007',
                'name' => 'Dr. Ir. Budi Hartono, M.Eng.',
                'email' => 'budi.hartono@eng.unhas.ac.id',
                'affiliation' => 'Fakultas Teknik Geologi UNHAS',
                'role_codes' => ['reviewer'],
                'roles' => [
                    ['code' => 'reviewer', 'label' => 'Peer Reviewer', 'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif (Tersedia)',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Geoteknik Pesisir', 'Mitigasi Abrasi Pantai', 'Mekanika Tanah'],
                'active_assignments_count' => 1,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '20 Apr 2024',
            ],
            [
                'id' => 'USR-01JARK-008',
                'name' => 'Dr. Nurfadillah Bahar, M.Kes.',
                'email' => 'nurfadillah.bahar@unhas.ac.id',
                'affiliation' => 'Fakultas Kesehatan Masyarakat UNHAS',
                'role_codes' => ['reviewer', 'author'],
                'roles' => [
                    ['code' => 'reviewer', 'label' => 'Peer Reviewer', 'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['code' => 'author', 'label' => 'Author', 'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Kesehatan Lingkungan', 'Urban Heat Island', 'Epidemiologi'],
                'active_assignments_count' => 2,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '12 Mei 2024',
            ],
            [
                'id' => 'USR-01JARK-009',
                'name' => 'Prof. Dr. Ir. Muhammad Basri, M.T.',
                'email' => 'm.basri@poliupg.ac.id',
                'affiliation' => 'Politeknik Negeri Ujung Pandang',
                'role_codes' => ['author'],
                'roles' => [
                    ['code' => 'author', 'label' => 'Author', 'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200'],
                ],
                'status' => 'active',
                'status_label' => 'Aktif',
                'status_badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'expertise' => ['Sistem Informasi Publik', 'Smart City'],
                'active_assignments_count' => 1,
                'is_last_admin' => false,
                'is_last_eic' => false,
                'joined_at' => '18 Jun 2024',
            ],
        ];
    }
}
