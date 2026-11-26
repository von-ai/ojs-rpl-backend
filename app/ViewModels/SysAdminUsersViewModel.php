<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Journal;
use App\Models\User;
use App\Models\UserImport;
use App\Models\UserRole;
use Illuminate\Support\Facades\Schema;

class SysAdminUsersViewModel
{
    private string $activeRole;

    private string $searchQuery;

    private int $currentPage;

    private int $perPage;

    public function __construct(string $activeRole = 'all', string $searchQuery = '', int $currentPage = 1, int $perPage = 10)
    {
        $this->activeRole = $activeRole;
        $this->searchQuery = trim($searchQuery);
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, $perPage);
    }

    /**
     * Helper statis untuk mengambil badge count jumlah seluruh pengguna sistem secara dinamis dan aman.
     */
    public static function getSidebarBadgeCount(): int
    {
        try {
            if (class_exists(User::class) && Schema::hasTable('users')) {
                return User::query()->count();
            }

            return 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * 4 Ringkasan metrik statistik direktori pengguna global.
     *
     * @return array<int, array{title: string, value: int, description: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        $all = $this->allUsers();
        $total = count($all);
        $sysadminCount = count(array_filter($all, fn ($item) => $item['is_sysadmin']));
        $editorCount = count(array_filter($all, fn ($item) => in_array('editor', $item['role_codes'], true) || in_array('editor_in_chief', $item['role_codes'], true) || in_array('section_editor', $item['role_codes'], true) || in_array('manager', $item['role_codes'], true)));
        $reviewerCount = count(array_filter($all, fn ($item) => in_array('reviewer', $item['role_codes'], true)));

        return [
            [
                'title' => 'Total Akun Pengguna',
                'value' => $total,
                'description' => 'Pengguna terverifikasi lintas instansi jurnal',
                'badge_text' => "{$total} Akun",
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'System Administrator',
                'value' => $sysadminCount,
                'description' => 'Pemegang hak akses superadmin global',
                'badge_text' => 'Superadmin',
                'color_class' => 'text-purple-700 bg-purple-50 border-purple-200',
            ],
            [
                'title' => 'Dewan Redaksi Aktif',
                'value' => $editorCount,
                'description' => 'Editor in Chief, Section Editor, & Manager',
                'badge_text' => 'Redaksi Aktif',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Reviewer Terdaftar',
                'value' => $reviewerCount,
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
            'editor' => count(array_filter($all, fn ($item) => in_array('editor', $item['role_codes'], true) || in_array('editor_in_chief', $item['role_codes'], true) || in_array('section_editor', $item['role_codes'], true) || in_array('manager', $item['role_codes'], true))),
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
     * Kumpulan pengguna terdaftar yang telah difilter secara lengkap.
     *
     * @return array<int, array<string, mixed>>
     */
    public function filteredUsers(): array
    {
        $items = $this->allUsers();

        // Filter berdasarkan peran
        if ($this->activeRole !== 'all') {
            if ($this->activeRole === 'sysadmin') {
                $items = array_values(array_filter($items, fn ($item) => $item['is_sysadmin']));
            } elseif ($this->activeRole === 'editor') {
                $items = array_values(array_filter($items, fn ($item) => in_array('editor', $item['role_codes'], true) || in_array('editor_in_chief', $item['role_codes'], true) || in_array('section_editor', $item['role_codes'], true) || in_array('manager', $item['role_codes'], true)));
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
                    || str_contains(mb_strtolower($item['username'] ?? ''), $query)
                    || str_contains(mb_strtolower($item['affiliation']), $query);
            }));
        }

        return $items;
    }

    /**
     * Kumpulan pengguna terdaftar yang telah difilter dan dipotong per halaman.
     *
     * @return array<int, array<string, mixed>>
     */
    public function users(): array
    {
        $filtered = $this->filteredUsers();
        $offset = ($this->currentPage - 1) * $this->perPage;

        return array_values(array_slice($filtered, $offset, $this->perPage));
    }

    /**
     * Seluruh pengguna terdaftar (untuk opsi autocomplete / modal tanpa dipotong per halaman).
     *
     * @return array<int, array<string, mixed>>
     */
    public function allRegisteredUsers(): array
    {
        return $this->allUsers();
    }

    /**
     * Riwayat pengimporan massal pengguna via berkas CSV (Tabel user_imports).
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
        try {
            if (Schema::hasTable('user_imports')) {
                return UserImport::with('journal')->latest()->get()->map(function (UserImport $import) {
                    return [
                        'id' => (string) $import->id,
                        'journal_name' => $import->journal?->name ?? 'Lintas Jurnal Global',
                        'file_name' => (string) $import->file_name,
                        'total_rows' => (int) $import->total_rows,
                        'processed_rows' => (int) $import->processed_rows,
                        'failed_rows' => (int) $import->failed_rows,
                        'status' => (string) $import->status,
                        'status_label' => $import->status_label,
                        'status_badge_class' => $import->status_badge_class,
                        'created_at' => $import->created_at?->format('d M Y, H:i').' WITA',
                    ];
                })->toArray();
            }
        } catch (\Throwable) {
            // Graceful fallback
        }

        return [];
    }

    /**
     * Metadata paginasi pengguna.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int, has_previous_page: bool, has_next_page: bool, previous_page: int, next_page: int}
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
            'has_previous_page' => $currentPage > 1,
            'has_next_page' => $currentPage < $lastPage,
            'previous_page' => $currentPage - 1,
            'next_page' => $currentPage + 1,
        ];
    }

    /**
     * Opsi jurnal ilmiah untuk alokasi pengguna saat impor CSV dan penugasan peran.
     *
     * @return array<int, array{id: string, name: string, initials: string, slug: string}>
     */
    public function selectableJournals(): array
    {
        try {
            if (Schema::hasTable('journals')) {
                return Journal::query()
                    ->where('status', '!=', 'archived')
                    ->orderBy('name')
                    ->get()
                    ->map(fn ($j) => [
                        'id' => (string) $j->id,
                        'name' => (string) $j->name,
                        'initials' => (string) ($j->initials ?: strtoupper(substr($j->slug ?? '', 0, 6))),
                        'slug' => (string) ($j->slug ?? ''),
                    ])->toArray();
            }
        } catch (\Throwable) {
            // Graceful fallback
        }

        return [];
    }

    /**
     * Daftar seluruh data pengguna global (Database).
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
        try {
            if (Schema::hasTable('users')) {
                return User::with(['profile', 'userRoles.journal'])->latest('updated_at')->get()->map(function (User $user) {
                    $isSysAdmin = $user->isSysAdmin();
                    $roleCodes = $user->userRoles->pluck('role_code')->toArray();

                    $labels = array_map(fn ($code) => UserRole::roleLabel($code), $roleCodes);
                    if ($isSysAdmin && ! in_array('System Administrator', $labels, true)) {
                        array_unshift($labels, 'System Administrator');
                    }
                    $rolesFormatted = ! empty($labels) ? implode(', ', array_unique($labels)) : 'Pengguna Terdaftar';

                    $journalCount = $user->userRoles->whereNotNull('journal_id')->pluck('journal_id')->unique()->count();
                    $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $user->name) ?: 'U', 0, 2));

                    $uniqueRolesMap = [];
                    $journalRolesGroupedMap = [];

                    foreach ($user->userRoles->whereNotNull('journal_id') as $ur) {
                        if (! isset($uniqueRolesMap[$ur->role_code])) {
                            $uniqueRolesMap[$ur->role_code] = [
                                'role_code' => (string) $ur->role_code,
                                'role_label' => UserRole::roleLabel($ur->role_code),
                                'badge_class' => UserRole::roleBadgeClass($ur->role_code),
                            ];
                        }

                        $jId = (string) $ur->journal_id;
                        if (! isset($journalRolesGroupedMap[$jId])) {
                            $journalRolesGroupedMap[$jId] = [
                                'journal_id' => $jId,
                                'journal_name' => (string) ($ur->journal?->name ?? 'Jurnal Ilmiah'),
                                'roles' => [],
                            ];
                        }

                        $journalRolesGroupedMap[$jId]['roles'][] = [
                            'id' => (string) $ur->id,
                            'role_code' => (string) $ur->role_code,
                            'role_label' => UserRole::roleLabel($ur->role_code),
                            'badge_class' => UserRole::roleBadgeClass($ur->role_code),
                            'expertise_keywords' => is_array($ur->expertise_keywords) ? $ur->expertise_keywords : [],
                        ];
                    }

                    $catalogOrder = array_flip(array_keys(UserRole::roleCatalog()));
                    uksort($uniqueRolesMap, function ($a, $b) use ($catalogOrder) {
                        return ($catalogOrder[$a] ?? 99) <=> ($catalogOrder[$b] ?? 99);
                    });

                    return [
                        'id' => (string) $user->id,
                        'name' => (string) $user->name,
                        'email' => (string) $user->email,
                        'username' => (string) ($user->username ?? ''),
                        'initials' => $initials,
                        'affiliation' => (string) ($user->profile?->affiliation ?? 'Sivitas Akademika BRIDA Makassar'),
                        'is_sysadmin' => $isSysAdmin,
                        'roles_formatted' => $rolesFormatted,
                        'role_codes' => $roleCodes,
                        'unique_roles' => array_values($uniqueRolesMap),
                        'journal_roles_grouped' => array_values($journalRolesGroupedMap),
                        'journal_memberships_count' => $journalCount,
                        'journal_roles' => $user->userRoles->whereNotNull('journal_id')->map(function ($ur) {
                            return [
                                'id' => (string) $ur->id,
                                'role_code' => (string) $ur->role_code,
                                'role_label' => UserRole::roleLabel($ur->role_code),
                                'badge_class' => UserRole::roleBadgeClass($ur->role_code),
                                'journal_id' => (string) $ur->journal_id,
                                'journal_name' => (string) ($ur->journal?->name ?? 'Jurnal'),
                                'expertise_keywords' => $ur->expertise_keywords ?? [],
                            ];
                        })->values()->toArray(),
                        'status' => (string) ($user->status ?? 'active'),
                        'status_label' => ($user->status ?? 'active') === 'active' ? 'Aktif' : 'Non-Aktif',
                        'status_badge_class' => ($user->status ?? 'active') === 'active'
                            ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                            : 'bg-gray-100 text-gray-700 border-gray-200',
                        'created_at' => $user->created_at?->format('d M Y') ?? '01 Jan 2026',
                        'joined_at' => $user->created_at?->format('d M Y, H:i').' WITA',
                    ];
                })->toArray();
            }
        } catch (\Throwable) {
            // Graceful fallback
        }

        return [];
    }
}
