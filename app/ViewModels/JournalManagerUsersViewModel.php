<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class JournalManagerUsersViewModel
{
    private string $activeFilter;

    private string $searchQuery;

    private int $currentPage;

    private int $perPage;

    public function __construct(string $activeFilter = 'all', string $searchQuery = '', int $currentPage = 1, int $perPage = 10)
    {
        $this->activeFilter = $activeFilter;
        $this->searchQuery = trim($searchQuery);
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, $perPage);
    }

    /**
     * Helper statis untuk mengambil badge count jumlah pengguna jurnal secara dinamis dan aman.
     */
    public static function getSidebarBadgeCount(): int
    {
        try {
            if (class_exists(User::class) && Schema::hasTable('users') && User::query()->exists()) {
                $journalId = Auth::user()?->userRoles()->where('role_code', 'manager')->whereNotNull('journal_id')->orderBy('journal_id')->value('journal_id');
                $query = User::whereDoesntHave('userRoles', function ($q) {
                    $q->where('role_code', 'sysadmin')->whereNull('journal_id');
                });

                if ($journalId) {
                    $query->where(function ($q) use ($journalId) {
                        $q->whereHas('userRoles', fn ($r) => $r->where('journal_id', $journalId))
                            ->orWhereDoesntHave('userRoles');
                    });
                }

                return $query->count();
            }

            return 15;
        } catch (\Throwable) {
            return 15;
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
                'value' => 0,
                'description' => 'Akun terdaftar dalam lingkup jurnal',
                'badge_text' => '0 Anggota',
                'color_class' => 'text-gray-900 bg-gray-50 border-gray-200',
            ],
            [
                'title' => 'Dewan Redaksi (Editorial)',
                'value' => 0,
                'description' => 'EIC, Section Editor, dan Tim Produksi',
                'badge_text' => 'Editorial Board',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Direktori Penelaah',
                'value' => 0,
                'description' => 'Peer Reviewer dengan kepakaran terverifikasi',
                'badge_text' => 'Penelaah Aktif',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Penulis & Pembaca',
                'value' => 0,
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
    /**
     * Kumpulan seluruh pengguna yang telah difilter (tanpa paginasi).
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
    public function filteredUsers(): array
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
     * Kumpulan pengguna untuk halaman aktif (dipotong berdasarkan paginasi).
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
        $filtered = $this->filteredUsers();
        $offset = ($this->currentPage - 1) * $this->perPage;

        return array_slice($filtered, $offset, $this->perPage);
    }

    /**
     * Seluruh pengguna terdaftar (untuk opsi autocomplete / modal tanpa dipotong per halaman).
     */
    public function allRegisteredUsers(): array
    {
        return $this->allUsers();
    }

    /**
     * Data pagination.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int, has_more_pages: bool, has_previous_page: bool, previous_page: ?int, next_page: ?int}
     */
    public function pagination(): array
    {
        $total = count($this->filteredUsers());
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        $currentPage = min($this->currentPage, $lastPage);

        $from = $total > 0 ? (($currentPage - 1) * $this->perPage) + 1 : 0;
        $to = min($currentPage * $this->perPage, $total);

        return [
            'current_page' => $currentPage,
            'per_page' => $this->perPage,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'last_page' => $lastPage,
            'has_more_pages' => $currentPage < $lastPage,
            'has_previous_page' => $currentPage > 1,
            'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
            'next_page' => $currentPage < $lastPage ? $currentPage + 1 : null,
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
            $journalId = Auth::user()?->userRoles()->where('role_code', 'manager')->whereNotNull('journal_id')->orderBy('journal_id')->value('journal_id');

            $query = User::with([
                'profile',
                'userRoles' => function ($r) use ($journalId) {
                    if ($journalId) {
                        $r->where('journal_id', $journalId);
                    }
                },
            ]);

            if (Schema::hasTable('copyedits')) {
                $query->withCount('copyedits');
            }
            if (Schema::hasTable('layout_assignments')) {
                $query->withCount('layoutAssignments');
            }

            // Eksklusikan akun murni System Administrator global dari direktori pengguna jurnal
            $query->whereDoesntHave('userRoles', function ($q) {
                $q->where('role_code', 'sysadmin')->whereNull('journal_id');
            });

            // Batasi pada pengguna yang memiliki peran pada jurnal ini atau pengguna unassigned (tanpa peran)
            if ($journalId) {
                $query->where(function ($q) use ($journalId) {
                    $q->whereHas('userRoles', fn ($r) => $r->where('journal_id', $journalId))
                        ->orWhereDoesntHave('userRoles');
                });
            }

            $users = $query->latest('updated_at')->get();

            if ($users->isNotEmpty()) {
                // Hitung total manager dan EIC untuk perlindungan last admin / last eic
                $managerQuery = UserRole::where('role_code', 'manager');
                $eicQuery = UserRole::where('role_code', 'editor_in_chief');
                if ($journalId) {
                    $managerQuery->where('journal_id', $journalId);
                    $eicQuery->where('journal_id', $journalId);
                }
                $managerCount = $managerQuery->count();
                $eicCount = $eicQuery->count();

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

        return [];
    }
}
