@extends('layouts.journalmanager')

@section('title', 'Manajemen Pengguna & Tim Redaksi — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman & Tombol Aksi Utama -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Pengguna & Tim Redaksi
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola keanggotaan jurnal, hak akses dewan redaksi, dan direktori mitra bestari.
            </p>
        </div>

        <button 
            type="button" 
            onclick="openAssignRoleModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-2xs transition self-start sm:self-center cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Tetapkan Peran Pengguna</span>
        </button>
    </div>

    @if(isset($journal) && $journal)
        <!-- Tenant Workspace Active Context Bar -->
        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-white border border-gray-200/90 shadow-2xs text-xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-rose-50 text-brand-maroon border border-rose-200/70 shrink-0">
                    Ruang Kerja Jurnal
                </span>
                <span class="font-semibold text-gray-900 truncate" title="{{ $journal->name }}">
                    {{ $journal->name }}
                </span>
                <span class="text-gray-300 hidden sm:inline">•</span>
                <span class="text-gray-500 font-mono text-[11px] hidden sm:inline">/{{ $journal->slug }}</span>
            </div>
            <span class="text-[11px] text-gray-400 hidden md:inline font-medium">
                Portal Manajemen Terintegrasi
            </span>
        </div>
    @endif

    <!-- 4 Metrik Ringkas KPI (Statis, Tidak Dapat Diklik, Bersih Tanpa Tag & Tanpa Kata Orang) -->
    @php
        $userIcons = [
            0 => 'users',
            1 => 'shield',
            2 => 'search',
            3 => 'book',
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $idx => $metric)
            <x-kpi-card
                :title="$metric['title']"
                :value="$metric['value']"
                :icon="$userIcons[$idx] ?? 'users'"
            />
        @endforeach
    </div>

    <!-- Main Container: Filter Peran, Pencarian & Tabel Pengguna -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
        
        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-3.5 sm:p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Status/Peran -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 no-scrollbar" aria-label="Filter Peran Pengguna">
                @php
                    $filters = $viewModel->filters();
                @endphp
                @foreach ($filters['items'] as $filterKey => $filter)
                    @php
                        $isActive = $filters['active'] === $filterKey;
                    @endphp
                    <a 
                        href="{{ route('journalmanager.users.index', ['role' => $filterKey, 'q' => request('q')]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                    >
                        <span>{{ $filter['label'] }}</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] rounded-full tabular-nums {{ $isActive ? 'bg-white/20 text-white font-bold' : 'bg-gray-200/70 text-gray-600' }}">
                            {{ $filter['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Cepat -->
            <form action="{{ route('journalmanager.users.index') }}" method="GET" class="relative w-full md:w-72 shrink-0">
                <input type="hidden" name="role" value="{{ $filters['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="search" 
                    name="q" 
                    value="{{ request('q') }}"
                    placeholder="Cari nama, email, afiliasi, kepakaran..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a 
                        href="{{ route('journalmanager.users.index', ['role' => $filters['active']]) }}" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                        title="Hapus pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Pengguna Jurnal (Kolom Terpisah: Kepakaran & Beban Naskah) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 min-w-44">Nama Pengguna</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 min-w-48 whitespace-nowrap">Email</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 min-w-44">Afiliasi</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 min-w-36">Peran Jurnal</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 min-w-40">Bidang Kepakaran</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 min-w-32 whitespace-nowrap">Beban Naskah</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-center whitespace-nowrap">Status</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
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
                            <td class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap text-xs font-normal text-gray-400 tabular-nums">
                                {{ $rowNumber }}
                            </td>

                            <!-- 2. Kolom Nama Pengguna (Klik Nama Membuka Modal Detail) -->
                            <td class="py-3.5 px-4 sm:px-5">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <button 
                                            type="button" 
                                            onclick="openUserDetailModal('{{ $user['id'] }}')"
                                            class="font-semibold text-gray-900 hover:text-brand-maroon transition text-left leading-tight cursor-pointer"
                                            title="Klik untuk melihat detail profil pengguna"
                                        >
                                            {{ $user['name'] }}
                                        </button>
                                        @if($user['is_last_admin'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200 shrink-0" title="Manajer tunggal yang dilindungi aturan sistem">
                                                Manajer Tunggal
                                            </span>
                                        @endif
                                        @if($user['is_last_eic'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-800 border border-red-200 shrink-0" title="Pemimpin redaksi tunggal yang dilindungi aturan sistem">
                                                Pemimpin Redaksi Tunggal
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-gray-400 block mt-0.5">
                                        Terdaftar: {{ $user['joined_at'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Kolom Email Terpisah (Font & Ukuran Konsisten) -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-600 select-all">
                                        {{ $user['email'] }}
                                    </span>
                                    <div class="relative group/tooltip inline-block">
                                        <button 
                                            type="button" 
                                            onclick="copyUserEmail('{{ $user['email'] }}', this)"
                                            class="p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded transition cursor-pointer"
                                            aria-label="Salin Email"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 hidden group-hover/tooltip:flex group-focus/tooltip:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                            <div class="px-2 py-0.5 text-[10px] font-semibold text-white bg-gray-900 rounded shadow-md">
                                                Salin
                                            </div>
                                            <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 4. Kolom Afiliasi Terpisah -->
                            <td class="py-3.5 px-4 sm:px-5">
                                <span class="text-xs text-gray-600 block line-clamp-2" title="{{ $user['affiliation'] }}">
                                    {{ $user['affiliation'] }}
                                </span>
                            </td>

                            <!-- 5. Kolom Peran Jurnal -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($user['roles'] as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $role['badge_class'] }}">
                                            {{ $role['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- 6. Kolom Bidang Kepakaran (Terpisah Sendiri) -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-xs">
                                @if(count($user['expertise']) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($user['expertise'], 0, 2) as $exp)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 border border-gray-200/70">
                                                {{ $exp }}
                                            </span>
                                        @endforeach
                                        @if(count($user['expertise']) > 2)
                                            <span 
                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500 cursor-help border border-gray-200/70"
                                                title="{{ implode(', ', array_slice($user['expertise'], 2)) }}"
                                            >
                                                +{{ count($user['expertise']) - 2 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 select-none">—</span>
                                @endif
                            </td>

                            <!-- 7. Kolom Beban Naskah (Terpisah Sendiri dengan Status Jelas) -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                @if($user['active_assignments_count'] > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>{{ $user['active_assignments_count'] }} Naskah Aktif</span>
                                    </span>
                                @else
                                    @php
                                        $hasEditorialOrReviewer = count(array_intersect($user['role_codes'] ?? [], ['manager', 'editor_in_chief', 'section_editor', 'reviewer', 'copyeditor', 'layout_editor', 'proofreader'])) > 0;
                                    @endphp
                                    @if($hasEditorialOrReviewer)
                                        <span class="inline-flex items-center text-[11px] font-medium text-emerald-700 bg-emerald-50/80 border border-emerald-200/70 px-2 py-0.5 rounded-md">
                                            <span>Siap Ditugaskan</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 select-none">—</span>
                                    @endif
                                @endif
                            </td>

                            <!-- 8. Kolom Status -->
                            <td class="py-3.5 px-4 sm:px-5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border {{ $user['status_badge_class'] }}">
                                    {{ $user['status_label'] }}
                                </span>
                            </td>

                            <!-- 9. Kolom Aksi (Icon Buttons dengan Tooltip Halus) -->
                            <td class="py-3.5 px-4 sm:px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">

                                    <!-- Tombol Aksi 1: Lihat Detail Profil Pengguna (Modal Trigger) -->
                                    <div class="relative group/tooltip inline-block">
                                        <button 
                                            type="button" 
                                            onclick="openUserDetailModal('{{ $user['id'] }}')"
                                            class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50/70 text-blue-700 hover:bg-blue-100 hover:border-blue-300 shadow-2xs transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-blue-400"
                                            aria-label="Lihat Detail Profil"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 hidden group-hover/tooltip:flex group-focus/tooltip:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                            <div class="px-2 py-1 text-[10px] font-semibold text-white bg-gray-900 rounded-md shadow-md">
                                                Lihat Detail
                                            </div>
                                            <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45"></div>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi 2: Reset Sandi -->
                                    <div class="relative group/tooltip inline-block">
                                        <button 
                                            type="button" 
                                            onclick="openResetPasswordModal('{{ $user['email'] }}', '{{ addslashes($user['name']) }}')"
                                            class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50/70 text-amber-700 hover:bg-amber-100 hover:border-amber-300 shadow-2xs transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-amber-400"
                                            aria-label="Reset Sandi Pengguna"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                            </svg>
                                        </button>
                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 hidden group-hover/tooltip:flex group-focus/tooltip:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                            <div class="px-2 py-1 text-[10px] font-semibold text-white bg-gray-900 rounded-md shadow-md">
                                                Reset Sandi
                                            </div>
                                            <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45"></div>
                                        </div>
                                    </div>

                                    <!-- Tombol Aksi 3: Ubah Peran -->
                                    <div class="relative group/tooltip inline-block">
                                        <button 
                                            type="button" 
                                            onclick="openAssignRoleModal('{{ $user['id'] }}')"
                                            class="w-7 h-7 inline-flex items-center justify-center rounded-lg border border-brand-maroon/20 bg-brand-maroon/5 text-brand-maroon hover:bg-brand-maroon hover:text-white shadow-2xs transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/40"
                                            aria-label="Ubah Peran Pengguna"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <!-- Tooltip (Orientasi ke Kanan agar Tidak Terpotong Layar) -->
                                        <div class="absolute bottom-full right-0 mb-1.5 hidden group-hover/tooltip:flex group-focus/tooltip:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                            <div class="px-2 py-1 text-[10px] font-semibold text-white bg-gray-900 rounded-md shadow-md">
                                                Ubah Peran
                                            </div>
                                            <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45 self-end mr-2.5"></div>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada pengguna ditemukan</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada akun anggota yang cocok dengan filter atau kata kunci pencarian ini.
                                    </p>
                                    @if(request('q') || request('role', 'all') !== 'all')
                                        <a href="{{ route('journalmanager.users.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-maroon hover:underline">
                                            <span>Reset Filter & Pencarian</span>
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
        <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <span>
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> pengguna
            </span>

            <div class="flex items-center gap-1.5">
                @if ($pagination['has_previous_page'])
                    <a 
                        href="{{ route('journalmanager.users.index', array_merge(request()->query(), ['page' => $pagination['previous_page']])) }}"
                        class="px-2.5 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium transition shadow-2xs"
                    >
                        Sebelumnya
                    </a>
                @else
                    <span class="px-2.5 py-1 rounded-md border border-gray-200 bg-gray-100/70 text-gray-400 cursor-not-allowed select-none">
                        Sebelumnya
                    </span>
                @endif

                @for ($p = 1; $p <= $pagination['last_page']; $p++)
                    @if ($p == $pagination['current_page'])
                        <span class="w-7 h-7 flex items-center justify-center rounded-md bg-brand-maroon text-white font-bold text-xs shadow-2xs select-none">
                            {{ $p }}
                        </span>
                    @else
                        <a 
                            href="{{ route('journalmanager.users.index', array_merge(request()->query(), ['page' => $p])) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs transition shadow-2xs"
                        >
                            {{ $p }}
                        </a>
                    @endif
                @endfor

                @if ($pagination['has_more_pages'])
                    <a 
                        href="{{ route('journalmanager.users.index', array_merge(request()->query(), ['page' => $pagination['next_page']])) }}"
                        class="px-2.5 py-1 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium transition shadow-2xs"
                    >
                        Selanjutnya
                    </a>
                @else
                    <span class="px-2.5 py-1 rounded-md border border-gray-200 bg-gray-100/70 text-gray-400 cursor-not-allowed select-none">
                        Selanjutnya
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 1: Lihat Detail Profil Pengguna                              -->
<!-- ========================================================================= -->
<div id="modal-user-detail" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-detail-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs cursor-pointer" onclick="closeUserDetailModal()"></div>

        <!-- Panel Konten Modal -->
        <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-detail-title">
                            Detail Profil Pengguna
                        </h3>
                        <p class="text-[11px] text-gray-500">
                            Informasi keanggotaan jurnal dan hak akses editorial
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    onclick="closeUserDetailModal()" 
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
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
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200/80">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h4 id="detail_user_name" class="text-sm sm:text-base font-bold text-gray-900"></h4>
                                <span id="detail_last_admin_badge" class="hidden inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    Manajer Tunggal
                                </span>
                                <span id="detail_last_eic_badge" class="hidden inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-800 border border-red-200">
                                    Pemimpin Redaksi Tunggal
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span id="detail_user_email" class="text-xs text-gray-600"></span>
                                <button 
                                    type="button" 
                                    id="detail_copy_email_btn"
                                    onclick="copyUserEmailFromDetail()"
                                    class="p-0.5 text-gray-400 hover:text-gray-700 transition cursor-pointer"
                                    title="Salin email"
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
                    <div class="mt-3 pt-3 border-t border-gray-200/60 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
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

                <!-- Bagian Peran Aktif Jurnal -->
                <div>
                    <span class="text-xs font-semibold text-gray-700 block mb-1.5">
                        Peran Jurnal yang Diemban
                    </span>
                    <div id="detail_roles_list" class="flex flex-wrap gap-1.5"></div>
                </div>

                <!-- Bagian Kepakaran Penelaah (Muncul jika ada) -->
                <div id="detail_expertise_section" class="hidden">
                    <span class="text-xs font-semibold text-gray-700 block mb-1.5">
                        Bidang Kepakaran Penelaah
                    </span>
                    <div id="detail_expertise_list" class="flex flex-wrap gap-1"></div>
                </div>

                <!-- Beban Penugasan Naskah -->
                <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-gray-700 font-medium">Beban Naskah Aktif:</span>
                    </div>
                    <span id="detail_assignments_count" class="font-bold text-blue-800"></span>
                </div>
            </div>

            <!-- Footer Modal Detail dengan Quick Action -->
            <div class="mt-5 pt-3.5 border-t border-gray-100 flex items-center justify-between gap-2">
                <button 
                    type="button" 
                    onclick="closeUserDetailModal()" 
                    class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer"
                >
                    Tutup
                </button>

                <div class="flex items-center gap-1.5">
                    <button 
                        type="button" 
                        onclick="triggerResetFromDetail()"
                        class="px-3 py-2 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 transition cursor-pointer"
                    >
                        Reset Sandi
                    </button>
                    <button 
                        type="button" 
                        onclick="triggerAssignRoleFromDetail()"
                        class="px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-2xs transition cursor-pointer"
                    >
                        Ubah Peran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 2: Tetapkan / Ubah Peran Pengguna                             -->
<!-- ========================================================================= -->
<div id="modal-assign-role" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-assign-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs cursor-pointer" onclick="closeAssignRoleModal()"></div>

        <!-- Panel Konten Modal -->
        <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-assign-title">
                            Tetapkan Peran Jurnal
                        </h3>
                        <p class="text-[11px] text-gray-500" id="modal-assign-subtitle">
                            Pemberian wewenang peran dewan redaksi atau mitra bestari
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    onclick="closeAssignRoleModal()" 
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Kartu Info Ringkas Pengguna Terpilih (Ditampilkan saat Ubah Peran) -->
            <div id="assign_user_info_card" class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg hidden">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Pengguna Terpilih:</span>
                        <h4 id="assign_user_name_display" class="text-xs sm:text-sm font-bold text-gray-900 truncate mt-0.5"></h4>
                        <span id="assign_user_affiliation_display" class="text-[11px] text-gray-500 block truncate"></span>
                        <span id="assign_user_email_display" class="text-xs text-gray-600 block truncate mt-0.5"></span>
                    </div>
                </div>
                <div id="assign_user_current_roles_container" class="mt-2 pt-2 border-t border-gray-200/80">
                    <span class="text-[10px] text-gray-400 block mb-1">Peran Saat Ini:</span>
                    <div id="assign_user_current_roles_badges" class="flex flex-wrap gap-1"></div>
                </div>
            </div>

            <form action="{{ route('journalmanager.users.assign-role') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="user_id" id="assign_user_id" value="">
                
                <!-- Input Email Pengguna -->
                <div id="assign_user_email_group">
                    <label for="assign_user_email" class="block text-xs font-semibold text-gray-700 mb-1">
                        Email Akun Pengguna <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="assign_user_email" 
                        required 
                        list="registered_users_email_list"
                        placeholder="Masukkan alamat email akun pengguna..." 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon" 
                        value=""
                    >
                    <datalist id="registered_users_email_list">
                        @foreach ($viewModel->allRegisteredUsers() as $registeredUser)
                            <option value="{{ $registeredUser['email'] }}">{{ $registeredUser['name'] }}</option>
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label for="role_code" class="block text-xs font-semibold text-gray-700 mb-1">
                        Pilih Peran yang Diberikan <span class="text-red-500">*</span>
                    </label>
                    <x-custom-dropdown
                        name="role_code"
                        id="role_code"
                        :required="true"
                        :value="'section_editor'"
                        :options="[
                            'section_editor' => 'Editor Bagian',
                            'reviewer' => 'Mitra Bestari',
                            'copyeditor' => 'Penyunting Naskah',
                            'layout_editor' => 'Penata Letak',
                            'proofreader' => 'Korektor Galai',
                            'author' => 'Penulis',
                        ]"
                        container-class="w-full"
                        button-class="w-full px-3 py-2 text-xs border-gray-300 bg-white"
                        panel-class="w-full"
                    />
                    <p class="text-[11px] text-gray-400 mt-1">
                        Peran baru akan ditambahkan ke akun pengguna untuk jurnal ini.
                    </p>
                </div>

                <div id="expertise_keywords_group">
                    <label for="expertise_keywords" class="block text-xs font-semibold text-gray-700 mb-1">
                        Kata Kunci Kepakaran <span class="text-gray-400 font-normal">(Khusus Mitra Bestari)</span>
                    </label>
                    <input 
                        type="text" 
                        name="expertise_keywords" 
                        id="expertise_keywords" 
                        placeholder="Contoh: Tata Kelola, Kebijakan Publik, Epidemiologi (pisahkan dengan koma)" 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
                    >
                    <p class="text-[10px] text-gray-400 mt-1">
                        Kata kunci ini membantu editor saat mencocokkan naskah dengan mitra bestari yang kompeten.
                    </p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        onclick="closeAssignRoleModal()" 
                        class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-2xs transition cursor-pointer"
                    >
                        Simpan Penugasan Peran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal Dialog 3: Reset Kata Sandi Pengguna                                 -->
<!-- ========================================================================= -->
<div id="modal-reset-password" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-reset-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs cursor-pointer" onclick="closeResetPasswordModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-reset-title">
                            Kirim Tautan Reset Sandi
                        </h3>
                        <p class="text-[11px] text-gray-500">
                            Memicu token pemulihan akun aman ke email pengguna
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    onclick="closeResetPasswordModal()" 
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.users.reset-password') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="email" id="reset_user_email" value="">

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs space-y-1">
                    <span class="text-gray-400 block text-[10px] uppercase font-bold tracking-wider">Pengguna Tujuan:</span>
                    <strong id="reset_user_name_preview" class="text-gray-900 text-sm block"></strong>
                    <span class="text-gray-400 block text-[10px] uppercase font-bold tracking-wider mt-2">Alamat Email:</span>
                    <span id="reset_user_email_preview" class="text-xs text-gray-700 block break-all"></span>
                </div>

                <div class="p-2.5 bg-blue-50/70 rounded-lg border border-blue-200 text-[11px] text-blue-900 leading-relaxed">
                    Sistem akan mengirimkan email resmi berisi tautan aman untuk mengatur kata sandi baru. Manajer Jurnal tidak memanipulasi kata sandi secara langsung demi menjaga privasi dan keamanan akun pengguna.
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        onclick="closeResetPasswordModal()" 
                        class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 active:bg-amber-800 rounded-lg shadow-2xs transition cursor-pointer"
                    >
                        Kirim Tautan Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Database cache pengguna yang sedang tampil di halaman (diindeks berdasarkan ID)
    const USERS_CACHE = @json(collect($usersList)->keyBy('id'));

    // State pengguna yang sedang dibuka modal detailnya
    let activeDetailUser = null;

    /**
     * Buka modal detail profil pengguna lengkap
     * @param {string|number} userId
     */
    function openUserDetailModal(userId) {
        const idKey = String(userId);
        const userData = USERS_CACHE[idKey];
        if (!userData) {
            console.warn('Data pengguna tidak ditemukan untuk id:', userId);
            return;
        }
        activeDetailUser = userData;

        const modal = document.getElementById('modal-user-detail');
        const nameEl = document.getElementById('detail_user_name');
        const emailEl = document.getElementById('detail_user_email');
        const affilEl = document.getElementById('detail_user_affiliation');
        const joinedEl = document.getElementById('detail_user_joined');
        const statusEl = document.getElementById('detail_user_status');
        const rolesListEl = document.getElementById('detail_roles_list');
        const expSection = document.getElementById('detail_expertise_section');
        const expListEl = document.getElementById('detail_expertise_list');
        const assignCountEl = document.getElementById('detail_assignments_count');
        const lastAdminBadge = document.getElementById('detail_last_admin_badge');
        const lastEicBadge = document.getElementById('detail_last_eic_badge');

        if (nameEl) nameEl.innerText = userData.name || '-';
        if (emailEl) emailEl.innerText = userData.email || '-';
        if (affilEl) affilEl.innerText = userData.affiliation || '-';
        if (joinedEl) joinedEl.innerText = userData.joined_at || '-';

        // Status badge
        if (statusEl) {
            statusEl.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border shrink-0 ' + (userData.status_badge_class || 'bg-gray-100 text-gray-700 border-gray-200');
            statusEl.innerText = userData.status_label || 'Aktif';
        }

        // Special protection badges
        if (lastAdminBadge) {
            if (userData.is_last_admin) lastAdminBadge.classList.remove('hidden');
            else lastAdminBadge.classList.add('hidden');
        }
        if (lastEicBadge) {
            if (userData.is_last_eic) lastEicBadge.classList.remove('hidden');
            else lastEicBadge.classList.add('hidden');
        }

        // Roles badges
        if (rolesListEl) {
            rolesListEl.innerHTML = '';
            if (Array.isArray(userData.roles) && userData.roles.length > 0) {
                userData.roles.forEach(role => {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border ' + (role.badge_class || 'bg-gray-100 text-gray-700 border-gray-200');
                    badge.innerText = role.label;
                    rolesListEl.appendChild(badge);
                });
            } else {
                rolesListEl.innerHTML = '<span class="text-xs text-gray-400">Belum memiliki peran jurnal aktif</span>';
            }
        }

        // Expertise keywords (reviewer)
        if (expSection && expListEl) {
            expListEl.innerHTML = '';
            if (Array.isArray(userData.expertise) && userData.expertise.length > 0) {
                userData.expertise.forEach(exp => {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700 border border-gray-200';
                    chip.innerText = exp;
                    expListEl.appendChild(chip);
                });
                expSection.classList.remove('hidden');
            } else {
                expSection.classList.add('hidden');
            }
        }

        // Active assignments count
        if (assignCountEl) {
            const count = parseInt(userData.active_assignments_count || 0, 10);
            assignCountEl.innerText = count > 0 ? `${count} Naskah Sedang Ditangani` : 'Tidak Ada Naskah Aktif (Siap Ditugaskan)';
        }

        if (modal) modal.classList.remove('hidden');
    }

    function closeUserDetailModal() {
        const modal = document.getElementById('modal-user-detail');
        if (modal) modal.classList.add('hidden');
        activeDetailUser = null;
    }

    function copyUserEmailFromDetail() {
        if (activeDetailUser && activeDetailUser.email) {
            copyUserEmail(activeDetailUser.email);
        }
    }

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
     * Buka modal tetapkan / ubah peran pengguna
     * @param {string|number} [userId]
     */
    function openAssignRoleModal(userId = '') {
        const modal = document.getElementById('modal-assign-role');
        const titleEl = document.getElementById('modal-assign-title');
        const subtitleEl = document.getElementById('modal-assign-subtitle');
        const idInput = document.getElementById('assign_user_id');
        const emailInput = document.getElementById('assign_user_email');
        const emailGroup = document.getElementById('assign_user_email_group');
        const infoCard = document.getElementById('assign_user_info_card');
        const nameDisplay = document.getElementById('assign_user_name_display');
        const affilDisplay = document.getElementById('assign_user_affiliation_display');
        const emailDisplay = document.getElementById('assign_user_email_display');
        const rolesBadges = document.getElementById('assign_user_current_roles_badges');

        const userData = userId ? USERS_CACHE[String(userId)] : null;

        if (idInput) idInput.value = userId || '';
        
        if (userData) {
            // Mode Ubah Peran untuk pengguna terdaftar
            if (emailInput) emailInput.value = userData.email || '';
            if (titleEl) titleEl.innerText = 'Ubah Peran Pengguna';
            if (subtitleEl) subtitleEl.innerText = 'Kelola penugasan peran jurnal untuk akun ini';
            if (nameDisplay) nameDisplay.innerText = userData.name || '-';
            if (affilDisplay) affilDisplay.innerText = userData.affiliation || '-';
            if (emailDisplay) emailDisplay.innerText = userData.email || '-';

            // Render badge peran saat ini
            if (rolesBadges) {
                rolesBadges.innerHTML = '';
                if (Array.isArray(userData.roles) && userData.roles.length > 0) {
                    userData.roles.forEach(role => {
                        const badge = document.createElement('span');
                        badge.className = 'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-200/80 text-gray-700';
                        badge.innerText = role.label;
                        rolesBadges.appendChild(badge);
                    });
                } else {
                    rolesBadges.innerHTML = '<span class="text-[10px] text-gray-400">Belum memiliki peran</span>';
                }
            }

            if (infoCard) infoCard.classList.remove('hidden');
            if (emailGroup) emailGroup.classList.add('hidden');
        } else {
            // Mode Tetapkan Peran Baru
            if (emailInput) emailInput.value = '';
            if (titleEl) titleEl.innerText = 'Tetapkan Peran Jurnal';
            if (subtitleEl) subtitleEl.innerText = 'Pemberian wewenang peran dewan redaksi atau mitra bestari';
            if (infoCard) infoCard.classList.add('hidden');
            if (emailGroup) emailGroup.classList.remove('hidden');
        }

        if (modal) modal.classList.remove('hidden');
    }

    function closeAssignRoleModal() {
        const modal = document.getElementById('modal-assign-role');
        if (modal) modal.classList.add('hidden');
    }

    /**
     * Buka modal reset kata sandi
     */
    function openResetPasswordModal(email, name) {
        const modal = document.getElementById('modal-reset-password');
        const emailInput = document.getElementById('reset_user_email');
        const namePreview = document.getElementById('reset_user_name_preview');
        const emailPreview = document.getElementById('reset_user_email_preview');
        
        if (emailInput) emailInput.value = email;
        if (namePreview) namePreview.innerText = name;
        if (emailPreview) emailPreview.innerText = email;
        if (modal) modal.classList.remove('hidden');
    }

    function closeResetPasswordModal() {
        const modal = document.getElementById('modal-reset-password');
        if (modal) modal.classList.add('hidden');
    }

    /**
     * Salin alamat email pengguna ke clipboard dan tampilkan feedback toast
     */
    function copyUserEmail(email, buttonEl) {
        if (!email) return;
        
        navigator.clipboard.writeText(email).then(() => {
            if (window.toast && typeof window.toast.success === 'function') {
                window.toast.success(`Alamat email ${email} berhasil disalin ke papan klip.`, 'Email Disalin');
            } else if (window.showToast) {
                window.showToast({
                    type: 'success',
                    title: 'Email Disalin',
                    message: `Alamat email ${email} berhasil disalin ke papan klip.`,
                    duration: 3000
                });
            } else if (buttonEl) {
                // Visual feedback pada button
                const originalHtml = buttonEl.innerHTML;
                buttonEl.innerHTML = `<svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`;
                setTimeout(() => {
                    buttonEl.innerHTML = originalHtml;
                }, 1500);
            }
        }).catch(() => {
            // Fallback manual jika izin clipboard dibatasi
            const tempInput = document.createElement('input');
            tempInput.value = email;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            if (window.toast) {
                window.toast.info(`Email ${email} telah disalin.`);
            }
        });
    }

    // Keyboard navigation: Tekan tombol ESC untuk menutup modal yang terbuka
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeUserDetailModal();
            closeAssignRoleModal();
            closeResetPasswordModal();
        }
    });
</script>
@endsection
