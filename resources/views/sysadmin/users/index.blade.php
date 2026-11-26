@extends('layouts.sysadmin')

@section('title', 'Direktori Pengguna & Hak Akses Global — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman & Tombol Aksi Utama -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-sans">
                Pengguna dan hak akses
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Tambah pengguna dan atur peran mereka di setiap jurnal.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 self-start sm:self-center">
            <button
                type="button"
                onclick="openImportUsersModal()"
                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 border border-gray-300 rounded-xl transition shadow-2xs cursor-pointer whitespace-nowrap shrink-0"
                data-tooltip="Impor data pengguna secara massal via berkas CSV"
            >
                <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Impor CSV</span>
            </button>

            <button
                type="button"
                onclick="openAssignRoleModal()"
                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-2xs transition cursor-pointer whitespace-nowrap shrink-0"
                data-tooltip="Tetapkan peran jurnal baru untuk pengguna sistem"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Tetapkan Peran</span>
            </button>
        </div>
    </div>

    <!-- Multi-Tenant Global Context Bar -->
    <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-white border border-outline-variant text-xs shadow-none">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-900 text-white shrink-0">
                Ruang Kerja Sistem
            </span>
            <span class="font-semibold text-gray-900 truncate">
                Multi-Tenant Seluruh Portal Jurnal Ilmiah BRIDA Kota Makassar
            </span>
        </div>
        <span class="text-[11px] text-gray-500 hidden md:inline font-medium">
            Portal System Administrator Terintegrasi
        </span>
    </div>

    <!-- 4 Metrik Ringkas KPI (Statis, Bersih, Tanpa Shadow Card) -->
    @php
        $sysAdminUserIcons = [
            0 => 'users',
            1 => 'shield',
            2 => 'book',
            3 => 'search',
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $idx => $metric)
            <x-kpi-card
                :title="$metric['title']"
                :value="$metric['value']"
                :icon="$sysAdminUserIcons[$idx] ?? 'users'"
            />
        @endforeach
    </div>

    <!-- Main Container: Filter Peran, Pencarian & Tabel Pengguna -->
    <div class="bg-white rounded-xl border border-outline-variant shadow-none overflow-hidden">

        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-3.5 sm:p-4 border-b border-divider flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Peran Pengguna -->
            <div class="section-menu grow" aria-label="Filter Peran Pengguna">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a
                        href="{{ route('sysadmin.users.index', ['role' => $tabKey, 'q' => request('q')]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] rounded-full tabular-nums {{ $isActive ? 'bg-white/20 text-white font-bold' : 'bg-gray-200/70 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Cepat -->
            <form action="{{ route('sysadmin.users.index') }}" method="GET" class="relative w-full md:w-72 shrink-0">
                <input type="hidden" name="role" value="{{ $tabs['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama, surel, username, atau afiliasi..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-outline rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
                >
                @if(request('q'))
                    <a
                        href="{{ route('sysadmin.users.index', ['role' => $tabs['active']]) }}"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                        title="Hapus filter pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Pengguna Terdaftar -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-divider text-gray-600 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-2.5 px-2 text-center whitespace-nowrap w-8">NO</th>
                        <th scope="col" class="py-2.5 px-2.5">Pengguna</th>
                        <th scope="col" class="py-2.5 px-2.5">Email</th>
                        <th scope="col" class="py-2.5 px-2.5 hidden xl:table-cell">Afiliasi</th>
                        <th scope="col" class="py-2.5 px-2.5">Peran</th>
                        <th scope="col" class="py-2.5 px-2.5 whitespace-nowrap">Akses</th>
                        <th scope="col" class="py-2.5 px-2.5 text-center whitespace-nowrap">Status</th>
                        <th scope="col" class="py-2.5 px-2.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    @php
                        $pagination = $viewModel->pagination();
                        $usersList = $viewModel->users();
                    @endphp
                    @forelse ($usersList as $user)
                        @php
                            $rowNumber = $loop->iteration + ($pagination['from'] > 0 ? $pagination['from'] - 1 : 0);
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- 1. Kolom NO -->
                            <td class="py-2.5 px-2 text-center whitespace-nowrap text-xs font-normal text-gray-500 tabular-nums">
                                {{ $rowNumber }}
                            </td>

                            <!-- 2. Kolom Nama Pengguna (Klik Nama Membuka Modal Detail) -->
                            <td class="py-2.5 px-2.5 max-w-44">
                                <div class="min-w-0">
                                    <button
                                        type="button"
                                        onclick="openUserDetailModal('{{ $user['id'] }}')"
                                        class="font-semibold text-gray-900 hover:text-brand-maroon transition text-left leading-tight cursor-pointer truncate max-w-full block"
                                        title="Klik untuk melihat detail profil pengguna"
                                    >
                                        {{ $user['name'] }}
                                    </button>
                                    <span class="text-[11px] text-gray-500 block mt-0.5 truncate">
                                        {{ $user['created_at'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Kolom Email Terpisah (Font & Ukuran Konsisten) -->
                            <td class="py-2.5 px-2.5 max-w-48">
                                <div class="flex items-center gap-1 min-w-0">
                                    <span class="text-xs text-gray-700 select-all font-sans truncate" title="{{ $user['email'] }}">
                                        {{ $user['email'] }}
                                    </span>
                                    <button
                                        type="button"
                                        onclick="copyUserEmail('{{ $user['email'] }}', this)"
                                        class="p-1 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded transition cursor-pointer shrink-0"
                                        data-tooltip="Salin Email"
                                        aria-label="Salin Email"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- 4. Kolom Afiliasi -->
                            <td class="py-2.5 px-2.5 hidden xl:table-cell max-w-40">
                                <span class="text-xs text-gray-700 block line-clamp-2" title="{{ $user['affiliation'] }}">
                                    {{ $user['affiliation'] }}
                                </span>
                            </td>

                            <!-- 5. Kolom Peran & Keanggotaan (Cukup Peran Unik) -->
                            <td class="py-2.5 px-2.5 max-w-48">
                                @if(!empty($user['unique_roles']) && count($user['unique_roles']) > 0)
                                    @php
                                        $rolesCount = count($user['unique_roles']);
                                        $showExpandable = $rolesCount > 3;
                                        $visibleCount = $showExpandable ? 2 : $rolesCount;
                                        $visibleRoles = array_slice($user['unique_roles'], 0, $visibleCount);
                                        $hiddenRoles = $showExpandable ? array_slice($user['unique_roles'], $visibleCount) : [];
                                        $hiddenRoleLabels = implode(', ', array_column($hiddenRoles, 'role_label'));
                                    @endphp

                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-center gap-1">
                                            @foreach($visibleRoles as $role)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $role['badge_class'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                                    {{ $role['role_label'] }}
                                                </span>
                                            @endforeach

                                            @if($showExpandable)
                                                <button
                                                    type="button"
                                                    onclick="toggleMoreRoles('{{ $user['id'] }}', this)"
                                                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 transition-colors cursor-pointer select-none"
                                                    title="Peran lainnya: {{ $hiddenRoleLabels }}"
                                                    data-count="{{ count($hiddenRoles) }}"
                                                    data-expanded="false"
                                                >
                                                    <span>+{{ count($hiddenRoles) }}</span>
                                                    <svg class="w-2.5 h-2.5 text-gray-500 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>

                                        @if($showExpandable)
                                            <div id="more-roles-{{ $user['id'] }}" class="hidden flex flex-wrap items-center gap-1 pt-1.5 border-t border-dashed border-divider">
                                                @foreach($hiddenRoles as $role)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $role['badge_class'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                                        {{ $role['role_label'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($user['journal_memberships_count'] > 0)
                                            <button
                                                type="button"
                                                onclick="openUserDetailModal('{{ $user['id'] }}')"
                                                class="inline-flex items-center gap-1 text-[11px] text-gray-500 hover:text-brand-maroon transition mt-1 cursor-pointer font-medium"
                                                title="Klik untuk melihat distribusi peran per instansi jurnal"
                                            >
                                                <span>{{ $user['journal_memberships_count'] }} Jurnal</span>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-200 text-gray-800">
                                        Terdaftar Global
                                    </span>
                                @endif
                            </td>

                            <!-- 6. Kolom Hak Akses Sistem -->
                            <td class="py-2.5 px-2.5 whitespace-nowrap">
                                @if($user['is_sysadmin'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-900 text-white">
                                        <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <span>SysAdmin</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-200 text-gray-800">
                                        Standar
                                    </span>
                                @endif
                            </td>

                            <!-- 7. Kolom Status (Tanpa Dot Bulatan) -->
                            <td class="py-2.5 px-2.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $user['status_badge_class'] }}">
                                    {{ $user['status_label'] }}
                                </span>
                            </td>

                            <!-- 8. Kolom Aksi (Konsisten: Ikon Detail, Reset Sandi, Ubah Peran + Tombol Admin) -->
                            <td class="py-2.5 px-2.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Aksi 1: Lihat Detail Profil (Ikon Mata) -->
                                    <button
                                        type="button"
                                        onclick="openUserDetailModal('{{ $user['id'] }}')"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-blue-700 hover:bg-gray-100 transition cursor-pointer"
                                        data-tooltip="Lihat Detail Profil"
                                        title="Lihat Detail Profil"
                                        aria-label="Lihat Detail Profil"
                                    >
                                        <svg class="w-4.5 h-4.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>

                                    <!-- Aksi 2: Reset Sandi (Ikon Kunci) -->
                                    <button
                                        type="button"
                                        onclick="openResetPasswordModal('{{ $user['email'] }}', '{{ addslashes($user['name']) }}')"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-amber-700 hover:bg-gray-100 transition cursor-pointer"
                                        data-tooltip="Reset Sandi Pengguna"
                                        title="Reset Sandi Pengguna"
                                        aria-label="Reset Sandi Pengguna"
                                    >
                                        <svg class="w-4.5 h-4.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                        </svg>
                                    </button>

                                    <!-- Aksi 3: Kelola Peran Jurnal (Ikon Sunting) -->
                                    <button
                                        type="button"
                                        onclick="openAssignRoleModal('{{ $user['id'] }}')"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-brand-maroon hover:bg-gray-100 transition cursor-pointer"
                                        data-tooltip="Kelola Peran Jurnal"
                                        title="Kelola Peran Jurnal"
                                        aria-label="Kelola Peran Jurnal"
                                    >
                                        <svg class="w-4.5 h-4.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Aksi 4: Toggle Hak Akses System Administrator -->
                                    @if($user['is_sysadmin'])
                                        <form
                                            action="{{ route('sysadmin.users.revoke-sysadmin') }}"
                                            method="POST"
                                            class="inline"
                                            data-confirm="Apakah Anda yakin ingin mencabut hak akses System Administrator dari pengguna ini?"
                                            data-confirm-title="Cabut Akses System Administrator?"
                                            data-confirm-btn="Cabut Akses"
                                            data-confirm-cancel="Batal"
                                            data-confirm-type="danger"
                                        >
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                            <button
                                                type="submit"
                                                class="min-h-7 px-2 py-1 inline-flex items-center justify-center rounded-md border border-gray-300 bg-white text-red-700 hover:bg-red-700 hover:text-white hover:border-red-700 text-xs font-semibold transition shadow-2xs cursor-pointer"
                                                title="Cabut Hak Akses SysAdmin"
                                                aria-label="Cabut Hak Akses SysAdmin"
                                            >
                                                <span>Cabut Admin</span>
                                            </button>
                                        </form>
                                    @else
                                        <form
                                            action="{{ route('sysadmin.users.assign-sysadmin') }}"
                                            method="POST"
                                            class="inline"
                                            data-confirm="Apakah Anda yakin ingin memberikan hak akses superadmin System Administrator kepada pengguna ini?"
                                            data-confirm-title="Jadikan System Administrator?"
                                            data-confirm-btn="Jadikan SysAdmin"
                                            data-confirm-cancel="Batal"
                                            data-confirm-type="primary"
                                        >
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                            <button
                                                type="submit"
                                                class="min-h-7 px-2 py-1 inline-flex items-center justify-center rounded-md border border-gray-300 bg-white text-gray-800 hover:bg-gray-800 hover:text-white hover:border-gray-800 text-xs font-semibold transition shadow-2xs cursor-pointer"
                                                title="Jadikan System Administrator"
                                                aria-label="Jadikan System Administrator"
                                            >
                                                <span>+ Admin</span>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <svg class="w-8 h-8 text-gray-400 mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada pengguna ditemukan</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada akun anggota yang cocok dengan filter atau kata kunci pencarian ini.
                                    </p>
                                    @if(request('q') || request('role', 'all') !== 'all')
                                        <a href="{{ route('sysadmin.users.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-maroon hover:underline">
                                            <span>Reset Filter &amp; Pencarian</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Interaktif -->
        <div class="px-5 py-3 border-t border-divider bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <span>
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> pengguna
            </span>

            <div class="flex items-center gap-1.5">
                @if ($pagination['has_previous_page'])
                    <a
                        href="{{ route('sysadmin.users.index', array_merge(request()->query(), ['page' => $pagination['previous_page']])) }}"
                        class="px-2.5 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium transition shadow-none"
                    >
                        Sebelumnya
                    </a>
                @else
                    <span class="px-2.5 py-1 rounded-md border border-outline-variant bg-gray-100/70 text-gray-400 cursor-not-allowed select-none">
                        Sebelumnya
                    </span>
                @endif

                @for ($p = 1; $p <= $pagination['last_page']; $p++)
                    @if ($p == $pagination['current_page'])
                        <span class="w-7 h-7 flex items-center justify-center rounded-md bg-brand-maroon text-white font-bold text-xs select-none shadow-none">
                            {{ $p }}
                        </span>
                    @elseif ($p == 1 || $p == $pagination['last_page'] || abs($p - $pagination['current_page']) <= 1)
                        <a
                            href="{{ route('sysadmin.users.index', array_merge(request()->query(), ['page' => $p])) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium transition"
                        >
                            {{ $p }}
                        </a>
                    @elseif (abs($p - $pagination['current_page']) == 2)
                        <span class="w-7 h-7 flex items-center justify-center text-gray-400 select-none">
                            ...
                        </span>
                    @endif
                @endfor

                @if ($pagination['has_next_page'])
                    <a
                        href="{{ route('sysadmin.users.index', array_merge(request()->query(), ['page' => $pagination['next_page']])) }}"
                        class="px-2.5 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium transition shadow-none"
                    >
                        Selanjutnya
                    </a>
                @else
                    <span class="px-2.5 py-1 rounded-md border border-outline-variant bg-gray-100/70 text-gray-400 cursor-not-allowed select-none">
                        Selanjutnya
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Container Utama 2: Riwayat Pengimporan Massal CSV (Tanpa Font Mono, Tanpa Shadow Card) -->
    <div class="bg-white rounded-xl border border-outline-variant shadow-none p-5 sm:p-6 space-y-4">
        <div class="border-b border-divider pb-3">
            <h2 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                Riwayat Pengimporan Massal Pengguna via CSV
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Periksa jumlah akun yang berhasil diimpor dan baris yang gagal.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-divider text-gray-500 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3">ID &amp; Jurnal Tujuan</th>
                        <th class="py-2.5 px-3">Nama Berkas CSV</th>
                        <th class="py-2.5 px-3">Total Baris</th>
                        <th class="py-2.5 px-3">Hasil Proses</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Waktu Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider">
                    @forelse ($viewModel->recentImports() as $import)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-bold text-gray-800 block">ID: {{ $import['id'] }}</span>
                                <span class="text-gray-500 block text-[11px]">{{ $import['journal_name'] }}</span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap font-medium text-gray-700">
                                {{ $import['file_name'] }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap font-semibold text-gray-900">
                                {{ $import['total_rows'] }} baris
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="text-emerald-700 font-semibold">{{ $import['processed_rows'] }} Sukses</span>
                                @if($import['failed_rows'] > 0)
                                    <span class="text-red-600 font-semibold ml-1">({{ $import['failed_rows'] }} Gagal)</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $import['status_badge_class'] }}">
                                    {{ $import['status_label'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap text-right text-gray-500">
                                {{ $import['created_at'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">
                                Belum ada riwayat pengimporan CSV.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 1: Detail Profil Pengguna                                     -->
<!-- ========================================================================= -->
<div id="modal-user-detail" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-detail-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 py-6 sm:py-10">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/60 transition-opacity backdrop-blur-sm cursor-pointer" onclick="closeUserDetailModal()"></div>

        <!-- Panel Konten Modal -->
        <div class="relative bg-white rounded-2xl text-left overflow-visible shadow-2xl shadow-gray-950/20 transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-100 my-auto">
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-divider">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-blue-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 font-sans" id="modal-detail-title">
                            Detail Profil Pengguna
                        </h3>
                        <p class="text-[11px] text-gray-500">
                            Informasi keanggotaan jurnal, hak akses portal, dan data akun
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="closeUserDetailModal()"
                    class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 flex items-center justify-center cursor-pointer hover:bg-gray-100 transition"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="mt-4 space-y-4">
                <!-- Header Card Profil -->
                <div class="p-3.5 bg-gray-50/90 rounded-xl border border-outline-variant">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h4 id="detail_user_name" class="text-sm sm:text-base font-bold text-gray-900 font-sans"></h4>
                                <span id="detail_sysadmin_badge" class="hidden inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    System Admin
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span id="detail_user_email" class="text-xs text-gray-600 font-sans"></span>
                                <button
                                    type="button"
                                    id="detail_copy_email_btn"
                                    onclick="copyUserEmailFromDetail()"
                                    class="p-0.5 text-gray-400 hover:text-gray-700 transition cursor-pointer"
                                    data-tooltip="Salin email"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <span id="detail_user_status" class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border shrink-0"></span>
                    </div>

                    <!-- Afiliasi & Tanggal Daftar -->
                    <div class="mt-3 pt-3 border-t border-divider grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Afiliasi / Institusi:</span>
                            <span id="detail_user_affiliation" class="text-gray-800 font-medium block mt-0.5"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Tanggal Bergabung:</span>
                            <span id="detail_user_joined" class="text-gray-800 font-medium block mt-0.5"></span>
                        </div>
                    </div>
                </div>

                <!-- Bagian Distribusi Peran Per Jurnal -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Distribusi Peran Per Jurnal</span>
                        </span>
                        <span id="detail_journal_count_badge" class="text-[10px] text-gray-500 font-medium bg-gray-100 px-2 py-0.5 rounded-full border border-outline-variant"></span>
                    </div>

                    <div id="detail_journal_cards_container" class="space-y-2 max-h-56 overflow-y-auto pr-0.5">
                        <!-- Disuntikkan dinamis lewat JS: Tiap Jurnal menjadi Card tersendiri dengan daftar peran di dalamnya -->
                    </div>
                </div>
            </div>

            <!-- Footer Modal Detail dengan Quick Action -->
            <div class="mt-5 pt-3.5 border-t border-divider flex items-center justify-between gap-2">
                <button
                    type="button"
                    onclick="closeUserDetailModal()"
                    class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-xl border border-gray-300 transition cursor-pointer"
                >
                    Tutup
                </button>

                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        onclick="triggerResetFromDetail()"
                        class="px-3 py-2 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-200 transition cursor-pointer"
                    >
                        Reset Sandi
                    </button>
                    <button
                        type="button"
                        onclick="triggerAssignRoleFromDetail()"
                        class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-xl transition cursor-pointer"
                    >
                        Kelola Peran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 2: Tetapkan / Kelola Peran Jurnal Pengguna                    -->
<!-- ========================================================================= -->
<div id="modal-journal-roles" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-assign-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 py-6 sm:py-10">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/60 transition-opacity backdrop-blur-sm cursor-pointer" onclick="closeAssignRoleModal()"></div>

        <!-- Panel Modal -->
        <div class="relative bg-white rounded-2xl text-left overflow-visible shadow-2xl shadow-gray-950/20 transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-100 space-y-4 my-auto">
            <div class="flex items-center justify-between border-b border-divider pb-3">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 font-sans" id="modal-assign-title">
                            Kelola Peran Jurnal Pengguna
                        </h3>
                        <p id="modal-user-subtitle" class="text-[11px] text-gray-500">
                            Pemberian wewenang peran dewan redaksi atau mitra bestari pada jurnal
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="closeAssignRoleModal()"
                    class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 flex items-center justify-center cursor-pointer hover:bg-gray-100 transition"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Card Profil Pengguna yang Dipilih (Jika Dipilih) -->
            <div id="assign_user_info_card" class="hidden p-3 bg-gray-50/90 rounded-xl border border-outline-variant text-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <span id="assign_user_name_display" class="font-bold text-gray-900 block font-sans"></span>
                        <span id="assign_user_email_display" class="text-gray-500 block text-[11px]"></span>
                    </div>
                    <span id="assign_user_affil_display" class="text-gray-600 text-[11px] text-right truncate max-w-44 block"></span>
                </div>
            </div>

            <!-- Bagian 1: Peran Jurnal Aktif Pengguna Ini -->
            <div id="assign_active_roles_section" class="space-y-2">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider text-[11px]">
                    Peran Jurnal Saat Ini
                </h4>
                <div id="active-roles-list" class="space-y-2 max-h-36 overflow-y-auto">
                    <!-- Diisi secara dinamis lewat JS -->
                </div>
            </div>

            <template id="active-role-template">
                <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl border border-outline-variant text-xs">
                    <div class="min-w-0 pr-2">
                        <span data-journal-name class="font-bold text-gray-900 block truncate"></span>
                        <span data-role-label class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border mt-0.5"></span>
                    </div>
                    <form action="{{ route('sysadmin.users.revoke-journal-role') }}"
                          method="POST"
                          class="inline shrink-0"
                          data-confirm="Apakah Anda yakin ingin mencabut peran ini?"
                          data-confirm-title="Cabut Peran Pengguna?"
                          data-confirm-btn="Cabut Peran"
                          data-confirm-cancel="Batal"
                          data-confirm-type="danger">
                        @csrf
                        <input type="hidden" name="user_id">
                        <input type="hidden" name="journal_id">
                        <input type="hidden" name="role_code">
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-semibold px-2 py-1 hover:bg-red-50 rounded-lg transition cursor-pointer">
                            Cabut
                        </button>
                    </form>
                </div>
            </template>

            <!-- Garis Pemisah -->
            <hr class="border-gray-200">

            <!-- Bagian 2: Form Tetapkan Peran Jurnal Baru -->
            <form action="{{ route('sysadmin.users.assign-journal-role') }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="user_id" id="assign_user_id" value="">

                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider text-[11px]">
                    Tetapkan Peran Jurnal Baru
                </h4>

                <!-- Opsi Pemilihan Pengguna (Hanya Muncul jika Dibuka Global dari Tombol Atas) -->
                <div id="assign_user_picker_group" class="hidden">
                    <label for="assign_select_user" class="block text-xs font-semibold text-gray-700 mb-1">
                        Pilih Akun Pengguna <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="assign_select_user"
                        onchange="onSelectUserChanged(this.value)"
                        class="w-full text-xs text-gray-900 bg-white border border-outline rounded-xl p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none cursor-pointer"
                    >
                        <option value="">Pilih Pengguna Terdaftar</option>
                        @foreach ($viewModel->filteredUsers() as $u)
                            <option value="{{ $u['id'] }}">{{ $u['name'] }} ({{ $u['email'] }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Opsi Pemilihan Instansi Jurnal dengan Live Search Dropdown -->
                <div>
                    <label for="assign_journal_combobox_trigger" class="block text-xs font-semibold text-gray-700 mb-1">
                        Pilih Instansi Jurnal <span class="text-red-500">*</span>
                    </label>

                    <!-- Native select tersembunyi untuk integritas form HTML & validasi -->
                    <select
                        id="assign_journal_id"
                        name="journal_id"
                        required
                        class="sr-only"
                        tabindex="-1"
                        aria-hidden="true"
                    >
                        <option value="">Pilih Instansi Jurnal</option>
                        @foreach ($viewModel->selectableJournals() as $j)
                            <option value="{{ $j['id'] }}" data-name="{{ $j['name'] }}" data-initials="{{ $j['initials'] ?? '' }}">{{ $j['name'] }}</option>
                        @endforeach
                    </select>

                    <!-- Custom Searchable Combobox Component -->
                    <div class="relative" id="assign_journal_combobox_wrapper">
                        <!-- Trigger Button -->
                        <button
                            type="button"
                            id="assign_journal_combobox_trigger"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            class="w-full text-left bg-white hover:bg-gray-50 border border-outline hover:border-gray-400 rounded-xl py-2.5 px-3 flex items-center justify-between gap-2.5 shadow-2xs focus:outline-hidden focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition cursor-pointer group"
                        >
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="w-6 h-6 rounded-lg bg-red-50 text-brand-maroon flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </span>
                                <span id="assign_journal_display_text" class="text-xs font-normal text-gray-500 truncate flex-1">
                                    Pilih Instansi Jurnal
                                </span>
                                <span id="assign_journal_display_badge" class="hidden text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 border border-outline-variant shrink-0"></span>
                            </div>
                            <div class="flex items-center text-gray-400 group-hover:text-gray-600 transition shrink-0">
                                <svg id="assign_journal_chevron" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Dropdown Popover dengan Kolom Pencarian -->
                        <div
                            id="assign_journal_combobox_dropdown"
                            class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white rounded-xl border border-outline-variant shadow-2xl z-50 overflow-hidden transition-all duration-150"
                        >
                            <!-- Search Input Box -->
                            <div class="p-2 border-b border-divider bg-gray-50/90">
                                <div class="relative flex items-center">
                                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input
                                        type="text"
                                        id="assign_journal_search_input"
                                        placeholder="Ketik untuk mencari jurnal atau singkatan..."
                                        autocomplete="off"
                                        class="w-full pl-8 pr-7 py-1.5 text-xs bg-white border border-outline-variant rounded-lg focus:outline-hidden focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 text-gray-800 placeholder-gray-400 transition"
                                    >
                                    <button
                                        type="button"
                                        id="assign_journal_search_clear"
                                        class="hidden absolute right-2 text-gray-400 hover:text-gray-600 p-0.5 rounded cursor-pointer"
                                        aria-label="Hapus pencarian"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- List of Options -->
                            <div id="assign_journal_options_list" class="max-h-56 overflow-y-auto divide-y divide-divider p-1" role="listbox">
                                @foreach ($viewModel->selectableJournals() as $j)
                                    <button
                                        type="button"
                                        data-journal-option
                                        data-id="{{ $j['id'] }}"
                                        data-name="{{ $j['name'] }}"
                                        data-initials="{{ $j['initials'] ?? '' }}"
                                        role="option"
                                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-red-50/80 text-xs text-gray-800 flex items-center justify-between gap-2 transition cursor-pointer group"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <span class="font-medium text-gray-900 group-hover:text-brand-maroon transition-colors line-clamp-1">
                                                {{ $j['name'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            @if(!empty($j['initials']))
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600 border border-outline-variant group-hover:bg-red-100 group-hover:text-brand-maroon group-hover:border-red-200 transition-colors">
                                                    {{ $j['initials'] }}
                                                </span>
                                            @endif
                                            <svg data-journal-check class="w-4 h-4 text-brand-maroon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <!-- Empty State -->
                            <div id="assign_journal_empty_state" class="hidden py-6 px-4 text-center text-xs text-gray-400">
                                <svg class="w-6 h-6 mx-auto mb-1.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="font-medium text-gray-500">Instansi jurnal tidak ditemukan</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Coba gunakan kata kunci atau singkatan lain</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="assign_role_code" class="block text-xs font-semibold text-gray-700 mb-1">
                        Pilih Peran yang Diberikan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="assign_role_code"
                        name="role_code"
                        required
                        class="w-full text-xs text-gray-900 bg-white border border-outline rounded-xl p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none cursor-pointer"
                    >
                        <option value="manager">Journal Manager (Manajer Jurnal)</option>
                        <option value="editor_in_chief">Editor in Chief (Pimpinan Redaksi)</option>
                        <option value="section_editor">Section Editor (Editor Bagian)</option>
                        <option value="copyeditor">Copy Editor (Penyunting Bahasa)</option>
                        <option value="layout_editor">Layout Editor (Penata Letak Galai)</option>
                        <option value="proofreader">Proofreader (Korektor Akhir)</option>
                        <option value="reviewer">Peer Reviewer (Mitra Bestari)</option>
                        <option value="author">Author (Penulis)</option>
                    </select>
                </div>

                <div id="expertise_field" class="hidden">
                    <label for="assign_expertise_keywords" class="block text-xs font-semibold text-gray-700 mb-1">
                        Kata Kunci Kepakaran (Khusus Reviewer)
                    </label>
                    <input
                        type="text"
                        id="assign_expertise_keywords"
                        name="expertise_keywords"
                        placeholder="Contoh: Tata Kelola Publik, Smart City (pisahkan koma)"
                        class="w-full text-xs text-gray-900 bg-white border border-outline rounded-xl p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
                    >
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-divider">
                    <button
                        type="button"
                        onclick="closeAssignRoleModal()"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-xl border border-gray-300 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-xl transition shadow-none cursor-pointer"
                    >
                        Simpan Peran Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 3: Kirim Tautan Reset Sandi Pengguna                          -->
<!-- ========================================================================= -->
<div id="modal-reset-password" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-reset-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 py-6 sm:py-10">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/60 transition-opacity backdrop-blur-sm cursor-pointer" onclick="closeResetPasswordModal()"></div>

        <!-- Panel Modal -->
        <div class="relative bg-white rounded-2xl text-left overflow-visible shadow-2xl shadow-gray-950/20 transform transition-all sm:my-8 sm:max-w-md w-full p-5 sm:p-6 border border-gray-100 space-y-4 my-auto">
            <div class="flex items-center justify-between pb-3 border-b border-divider">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 font-sans" id="modal-reset-title">
                            Kirim Tautan Reset Sandi
                        </h3>
                        <p class="text-[11px] text-gray-500">
                            Pengaturan ulang kata sandi melalui surel resmi pengguna
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="closeResetPasswordModal()"
                    class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 flex items-center justify-center cursor-pointer hover:bg-gray-100 transition"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('sysadmin.users.reset-password') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="email" id="reset_password_email" value="">

                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 text-xs space-y-1">
                    <p class="text-gray-700">
                        Tautan instruksi pengaturan ulang kata sandi akan dikirimkan ke surel:
                    </p>
                    <p id="reset_password_user_summary" class="font-bold text-amber-900 font-sans break-all"></p>
                </div>

                <p class="text-xs text-gray-500">
                    Tautan reset sandi ini berlaku selama 60 menit dan hanya dapat digunakan satu kali.
                </p>

                <div class="flex justify-end gap-2 pt-2 border-t border-divider">
                    <button
                        type="button"
                        onclick="closeResetPasswordModal()"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-xl border border-gray-300 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition shadow-none cursor-pointer"
                    >
                        Kirim Tautan Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 4: Impor Pengguna CSV Massal                                 -->
<!-- ========================================================================= -->
<div id="modal-import-users" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-import-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 py-6 sm:py-10">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/60 transition-opacity backdrop-blur-sm cursor-pointer" onclick="closeImportUsersModal()"></div>

        <!-- Panel Modal -->
        <div class="relative bg-white rounded-2xl text-left overflow-visible shadow-2xl shadow-gray-950/20 transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-100 space-y-4 my-auto">
            <div class="flex items-center justify-between border-b border-divider pb-3">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 font-sans" id="modal-import-title">
                            Impor Pengguna secara Massal (CSV)
                        </h3>
                        <p class="text-[11px] text-gray-500">
                            Unggah berkas spreadsheet CSV untuk pendaftaran akun secara massal
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="closeImportUsersModal()"
                    class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 flex items-center justify-center hover:bg-gray-100 transition cursor-pointer"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('sysadmin.users.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label for="import_journal_id" class="block text-xs font-semibold text-gray-700 mb-1">
                        Jurnal Tujuan Alokasi Pengguna <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="import_journal_id"
                        name="journal_id"
                        required
                        class="w-full text-xs text-gray-900 bg-white border border-outline rounded-xl p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none cursor-pointer"
                    >
                        <option value="">Pilih Instansi Jurnal</option>
                        @foreach ($viewModel->selectableJournals() as $journalOption)
                            <option value="{{ $journalOption['id'] }}">{{ $journalOption['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="csv_file" class="block text-xs font-semibold text-gray-700 mb-1">
                        Berkas CSV Sumber Pengguna <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="file"
                        id="csv_file"
                        name="file"
                        accept=".csv"
                        required
                        class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-maroon/10 file:text-brand-maroon hover:file:bg-brand-maroon/20 cursor-pointer border border-gray-300 rounded-xl p-1.5"
                    >
                    <p class="text-[11px] text-gray-400 mt-1">
                        Format kolom header wajib: <span class="font-medium text-gray-600">name, email, affiliation, role</span>.
                    </p>
                </div>

                <div class="flex items-start gap-2 pt-1">
                    <input
                        type="checkbox"
                        id="send_invitation"
                        name="send_invitation_email"
                        value="1"
                        checked
                        class="mt-0.5 rounded text-brand-maroon focus:ring-brand-maroon"
                    >
                    <label for="send_invitation" class="text-xs text-gray-600">
                        Kirim surel notifikasi akun dan instruksi pengaturan kata sandi baru kepada seluruh pengguna yang diimpor.
                    </label>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-divider">
                    <button
                        type="button"
                        onclick="closeImportUsersModal()"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-xl border border-gray-300 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-xl transition shadow-none cursor-pointer"
                    >
                        Unggah &amp; Proses Antrean CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Inisialisasi Cache Pengguna
    const USERS_CACHE = @js(collect($viewModel->users())->keyBy('id'));
    let activeDetailUser = null;

    /**
     * Toggle Tampilan Peran Tambahan pada Tabel
     */
    function toggleMoreRoles(userId, btn) {
        const target = document.getElementById('more-roles-' + userId);
        if (!target) return;

        const isHidden = target.classList.contains('hidden');
        const icon = btn.querySelector('svg');
        const count = btn.getAttribute('data-count') || '';

        if (isHidden) {
            target.classList.remove('hidden');
            btn.setAttribute('data-expanded', 'true');
            btn.querySelector('span').textContent = 'Sembunyikan';
            btn.classList.add('bg-gray-200');
            if (icon) icon.classList.add('rotate-180');
        } else {
            target.classList.add('hidden');
            btn.setAttribute('data-expanded', 'false');
            btn.querySelector('span').textContent = `+${count} lainnya`;
            btn.classList.remove('bg-gray-200');
            if (icon) icon.classList.remove('rotate-180');
        }
    }

    /**
     * Buka Modal Detail Pengguna
     */
    function openUserDetailModal(userId) {
        const idKey = String(userId);
        const userData = USERS_CACHE[idKey];
        if (!userData) {
            console.warn('Data pengguna tidak ditemukan untuk id:', userId);
            return;
        }
        activeDetailUser = userData;

        const nameEl = document.getElementById('detail_user_name');
        const emailEl = document.getElementById('detail_user_email');
        const affilEl = document.getElementById('detail_user_affiliation');
        const joinedEl = document.getElementById('detail_user_joined');
        const statusEl = document.getElementById('detail_user_status');
        const sysadminBadge = document.getElementById('detail_sysadmin_badge');

        if (nameEl) nameEl.textContent = userData.name || '-';
        if (emailEl) emailEl.textContent = userData.email || '-';
        if (affilEl) affilEl.textContent = userData.affiliation || '-';
        if (joinedEl) joinedEl.textContent = userData.joined_at || userData.created_at || '-';

        if (statusEl) {
            statusEl.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border shrink-0 ' + (userData.status_badge_class || 'bg-gray-100 text-gray-700 border-gray-200');
            statusEl.textContent = userData.status_label || 'Aktif';
        }

        if (sysadminBadge) {
            if (userData.is_sysadmin) {
                sysadminBadge.classList.remove('hidden');
            } else {
                sysadminBadge.classList.add('hidden');
            }
        }

        const journalContainer = document.getElementById('detail_journal_cards_container');
        const countBadge = document.getElementById('detail_journal_count_badge');
        const grouped = userData.journal_roles_grouped || [];

        if (countBadge) {
            countBadge.textContent = grouped.length > 0 ? `${grouped.length} Jurnal Terdaftar` : '0 Jurnal';
        }

        if (journalContainer) {
            journalContainer.replaceChildren();

            if (grouped.length === 0) {
                journalContainer.innerHTML = `
                    <div class="p-3 bg-gray-50 rounded-lg border border-outline-variant text-center">
                        <p class="text-xs text-gray-500 font-medium">Pengguna Terdaftar Global</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Belum punya peran di jurnal mana pun.</p>
                    </div>
                `;
            } else {
                grouped.forEach(function(j) {
                    const card = document.createElement('div');
                    card.className = 'p-3 bg-gray-50/70 rounded-lg border border-outline-variant space-y-2';

                    let rolesHtml = '';
                    let expertiseList = [];

                    j.roles.forEach(function(r) {
                        if (r.expertise_keywords && r.expertise_keywords.length > 0) {
                            expertiseList.push(...r.expertise_keywords);
                        }

                        rolesHtml += `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-semibold border ${r.badge_class || 'bg-gray-100 text-gray-700 border-gray-200'}">
                                ${r.role_label}
                            </span>
                        `;
                    });

                    let expHtml = '';
                    if (expertiseList.length > 0) {
                        const uniqueExp = [...new Set(expertiseList)];
                        expHtml = `
                            <div class="text-[10px] text-gray-500 pt-1 border-t border-divider flex items-center gap-1 flex-wrap">
                                <span class="font-medium text-gray-600">Kepakaran:</span>
                                <span class="italic text-gray-500">${uniqueExp.join(', ')}</span>
                            </div>
                        `;
                    }

                    card.innerHTML = `
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-gray-900 truncate block font-sans">${j.journal_name}</span>
                            <span class="text-[10px] text-gray-400 font-medium shrink-0">${j.roles.length} Peran</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 pt-1.5 border-t border-divider">
                            ${rolesHtml}
                        </div>
                        ${expHtml}
                    `;

                    journalContainer.appendChild(card);
                });
            }
        }

        openModal('modal-user-detail');
    }

    function closeUserDetailModal() {
        closeModal('modal-user-detail');
    }

    /**
     * Buka Modal Tetapkan / Kelola Peran Jurnal
     */
    function openAssignRoleModal(userId = '') {
        const idKey = String(userId);
        const userData = userId ? USERS_CACHE[idKey] : null;

        const idInput = document.getElementById('assign_user_id');
        const userPickerGroup = document.getElementById('assign_user_picker_group');
        const userInfoCard = document.getElementById('assign_user_info_card');
        const activeRolesSection = document.getElementById('assign_active_roles_section');
        const nameDisplay = document.getElementById('assign_user_name_display');
        const emailDisplay = document.getElementById('assign_user_email_display');
        const affilDisplay = document.getElementById('assign_user_affil_display');
        const listContainer = document.getElementById('active-roles-list');
        const titleEl = document.getElementById('modal-assign-title');

        if (idInput) idInput.value = userId || '';

        // Reset pilihan combobox instansi jurnal ke keadaan awal
        resetAssignJournalCombobox();

        if (userData) {
            // Mode per-pengguna
            if (titleEl) titleEl.textContent = 'Kelola Peran Jurnal: ' + userData.name;
            if (userPickerGroup) userPickerGroup.classList.add('hidden');
            if (userInfoCard) {
                userInfoCard.classList.remove('hidden');
                if (nameDisplay) nameDisplay.textContent = userData.name;
                if (emailDisplay) emailDisplay.textContent = userData.email;
                if (affilDisplay) affilDisplay.textContent = userData.affiliation;
            }
            if (activeRolesSection) activeRolesSection.classList.remove('hidden');

            renderActiveRoles(userData.id, userData.journal_roles || []);
        } else {
            // Mode global dari tombol atas
            if (titleEl) titleEl.textContent = 'Tetapkan Peran Pengguna';
            if (userPickerGroup) {
                userPickerGroup.classList.remove('hidden');
                const userSelect = document.getElementById('assign_select_user');
                if (userSelect) userSelect.value = '';
            }
            if (userInfoCard) userInfoCard.classList.add('hidden');
            if (activeRolesSection) activeRolesSection.classList.add('hidden');
            listContainer.replaceChildren();
        }

        // Toggle bidang kepakaran bila peran reviewer dipilih
        const roleSelect = document.getElementById('assign_role_code');
        const expField = document.getElementById('expertise_field');
        if (roleSelect && expField) {
            roleSelect.onchange = function() {
                if (this.value === 'reviewer') {
                    expField.classList.remove('hidden');
                } else {
                    expField.classList.add('hidden');
                }
            };
            if (roleSelect.value === 'reviewer') {
                expField.classList.remove('hidden');
            } else {
                expField.classList.add('hidden');
            }
        }

        openModal('modal-journal-roles');
    }

    function onSelectUserChanged(selectedId) {
        const idInput = document.getElementById('assign_user_id');
        const activeRolesSection = document.getElementById('assign_active_roles_section');
        if (idInput) idInput.value = selectedId;

        if (selectedId && USERS_CACHE[String(selectedId)]) {
            const u = USERS_CACHE[String(selectedId)];
            if (activeRolesSection) activeRolesSection.classList.remove('hidden');
            renderActiveRoles(u.id, u.journal_roles || []);
        } else {
            if (activeRolesSection) activeRolesSection.classList.add('hidden');
        }
    }

    function renderActiveRoles(userId, roles) {
        const listContainer = document.getElementById('active-roles-list');
        listContainer.replaceChildren();

        if (!roles || roles.length === 0) {
            listContainer.innerHTML = '<p class="text-xs text-gray-400 italic bg-gray-50/80 p-3 rounded-xl border border-outline-variant text-center">Pengguna belum memiliki peran pada jurnal spesifik (Pengguna Terdaftar Global).</p>';
        } else {
            const template = document.getElementById('active-role-template');
            roles.forEach(function(role) {
                const item = template.content.cloneNode(true);
                item.querySelector('[data-journal-name]').textContent = role.journal_name;
                const label = item.querySelector('[data-role-label]');
                label.textContent = role.role_label;
                label.className += ' ' + (role.badge_class || 'bg-gray-100 text-gray-700 border-gray-200');
                item.querySelector('[name="user_id"]').value = userId;
                item.querySelector('[name="journal_id"]').value = role.journal_id;
                item.querySelector('[name="role_code"]').value = role.role_code;
                listContainer.appendChild(item);
            });
        }
    }

    function closeAssignRoleModal() {
        closeAssignJournalCombobox();
        closeModal('modal-journal-roles');
    }

    /**
     * Buka Modal Reset Sandi
     */
    function openResetPasswordModal(email, name) {
        document.getElementById('reset_password_email').value = email;
        document.getElementById('reset_password_user_summary').textContent = name + ' (' + email + ')';
        openModal('modal-reset-password');
    }

    function closeResetPasswordModal() {
        closeModal('modal-reset-password');
    }

    /**
     * Buka & Tutup Modal Impor Pengguna CSV
     */
    function openImportUsersModal() {
        openModal('modal-import-users');
    }

    function closeImportUsersModal() {
        closeModal('modal-import-users');
    }

    /**
     * Helper Pengelolaan Modal & Body Scroll Lock
     */
    function openModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('hidden');
        }
        const openModals = document.querySelectorAll('#modal-user-detail:not(.hidden), #modal-journal-roles:not(.hidden), #modal-reset-password:not(.hidden), #modal-import-users:not(.hidden)');
        if (openModals.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    /**
     * Searchable Dropdown Combobox untuk Instansi Jurnal
     */
    const journalSelect = document.getElementById('assign_journal_id');
    const journalTrigger = document.getElementById('assign_journal_combobox_trigger');
    const journalDropdown = document.getElementById('assign_journal_combobox_dropdown');
    const journalChevron = document.getElementById('assign_journal_chevron');
    const journalDisplayText = document.getElementById('assign_journal_display_text');
    const journalDisplayBadge = document.getElementById('assign_journal_display_badge');
    const journalSearchInput = document.getElementById('assign_journal_search_input');
    const journalSearchClear = document.getElementById('assign_journal_search_clear');
    const journalEmptyState = document.getElementById('assign_journal_empty_state');
    const journalOptionBtns = document.querySelectorAll('[data-journal-option]');

    function toggleAssignJournalCombobox(forceState = null) {
        if (!journalDropdown) return;
        const willOpen = forceState !== null ? forceState : journalDropdown.classList.contains('hidden');
        if (willOpen) {
            journalDropdown.classList.remove('hidden');
            if (journalTrigger) journalTrigger.setAttribute('aria-expanded', 'true');
            if (journalChevron) journalChevron.classList.add('rotate-180');
            if (journalSearchInput) {
                setTimeout(() => journalSearchInput.focus(), 50);
            }
        } else {
            journalDropdown.classList.add('hidden');
            if (journalTrigger) journalTrigger.setAttribute('aria-expanded', 'false');
            if (journalChevron) journalChevron.classList.remove('rotate-180');
        }
    }

    function closeAssignJournalCombobox() {
        if (journalDropdown && !journalDropdown.classList.contains('hidden')) {
            toggleAssignJournalCombobox(false);
            return true;
        }
        return false;
    }

    function selectAssignJournal(id, name, initials) {
        if (journalSelect) {
            journalSelect.value = id;
            journalSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (journalDisplayText) {
            journalDisplayText.textContent = name;
            journalDisplayText.classList.remove('text-gray-500', 'font-normal');
            journalDisplayText.classList.add('text-gray-900', 'font-semibold');
        }
        if (journalDisplayBadge) {
            if (initials) {
                journalDisplayBadge.textContent = initials;
                journalDisplayBadge.classList.remove('hidden');
            } else {
                journalDisplayBadge.classList.add('hidden');
            }
        }
        if (journalOptionBtns) {
            journalOptionBtns.forEach(btn => {
                const check = btn.querySelector('[data-journal-check]');
                if (btn.getAttribute('data-id') === String(id)) {
                    btn.classList.add('bg-red-50/70');
                    if (check) check.classList.remove('hidden');
                } else {
                    btn.classList.remove('bg-red-50/70');
                    if (check) check.classList.add('hidden');
                }
            });
        }
        toggleAssignJournalCombobox(false);
    }

    function resetAssignJournalCombobox() {
        if (journalSelect) journalSelect.value = '';
        if (journalDisplayText) {
            journalDisplayText.textContent = 'Pilih Instansi Jurnal';
            journalDisplayText.classList.remove('text-gray-900', 'font-semibold');
            journalDisplayText.classList.add('text-gray-500', 'font-normal');
        }
        if (journalDisplayBadge) journalDisplayBadge.classList.add('hidden');
        if (journalSearchInput) journalSearchInput.value = '';
        if (journalSearchClear) journalSearchClear.classList.add('hidden');
        filterAssignJournalOptions('');
        if (journalOptionBtns) {
            journalOptionBtns.forEach(btn => {
                btn.classList.remove('bg-red-50/70');
                const check = btn.querySelector('[data-journal-check]');
                if (check) check.classList.add('hidden');
            });
        }
    }

    function filterAssignJournalOptions(query) {
        const q = (query || '').toLowerCase().trim();
        let matchCount = 0;
        if (journalOptionBtns) {
            journalOptionBtns.forEach(btn => {
                const name = (btn.getAttribute('data-name') || '').toLowerCase();
                const initials = (btn.getAttribute('data-initials') || '').toLowerCase();
                const isMatch = !q || name.includes(q) || initials.includes(q);
                if (isMatch) {
                    btn.classList.remove('hidden');
                    matchCount++;
                } else {
                    btn.classList.add('hidden');
                }
            });
        }
        if (journalEmptyState) {
            if (matchCount === 0) {
                journalEmptyState.classList.remove('hidden');
            } else {
                journalEmptyState.classList.add('hidden');
            }
        }
        if (journalSearchClear) {
            if (q.length > 0) {
                journalSearchClear.classList.remove('hidden');
            } else {
                journalSearchClear.classList.add('hidden');
            }
        }
    }

    if (journalTrigger) {
        journalTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleAssignJournalCombobox();
        });
    }

    if (journalSearchInput) {
        journalSearchInput.addEventListener('input', function(e) {
            filterAssignJournalOptions(e.target.value);
        });
    }

    if (journalSearchClear) {
        journalSearchClear.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (journalSearchInput) {
                journalSearchInput.value = '';
                filterAssignJournalOptions('');
                journalSearchInput.focus();
            }
        });
    }

    if (journalOptionBtns) {
        journalOptionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                selectAssignJournal(
                    btn.getAttribute('data-id'),
                    btn.getAttribute('data-name'),
                    btn.getAttribute('data-initials')
                );
            });
        });
    }

    // Tutup dropdown combobox saat klik di luar
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#assign_journal_combobox_wrapper')) {
            closeAssignJournalCombobox();
        }
    });

    // Dukungan tombol Escape untuk menutup combobox atau modal aktif
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (closeAssignJournalCombobox()) {
                return;
            }
            closeUserDetailModal();
            closeAssignRoleModal();
            closeResetPasswordModal();
            closeImportUsersModal();
        }
    });

    /**
     * Quick actions dari dalam User Detail Modal
     */
    function triggerResetFromDetail() {
        if (!activeDetailUser) return;
        const user = activeDetailUser;
        closeUserDetailModal();
        openResetPasswordModal(user.email, user.name);
    }

    function triggerAssignRoleFromDetail() {
        if (!activeDetailUser) return;
        const user = activeDetailUser;
        closeUserDetailModal();
        openAssignRoleModal(user.id);
    }

    /**
     * Salin Email ke Clipboard
     */
    function copyUserEmail(email, buttonEl) {
        if (!navigator.clipboard) {
            fallbackCopy(email);
            return;
        }
        navigator.clipboard.writeText(email).then(() => {
            showCopyToast();
        }).catch(() => {
            fallbackCopy(email);
        });
    }

    function copyUserEmailFromDetail() {
        if (!activeDetailUser) return;
        copyUserEmail(activeDetailUser.email, document.getElementById('detail_copy_email_btn'));
    }

    function fallbackCopy(text) {
        const temp = document.createElement('input');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        showCopyToast();
    }

    function showCopyToast() {
        if (typeof window.showNotification === 'function') {
            window.showNotification('Alamat email berhasil disalin ke clipboard!', 'success');
        } else {
            alert('Alamat email berhasil disalin ke clipboard!');
        }
    }
</script>
@endsection
