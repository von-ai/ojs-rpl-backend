@extends('layouts.journalmanager')

@section('title', 'Manajemen Pengguna & Tim Redaksi — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Header Halaman & Tombol Aksi Utama -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Pengguna & Tim Redaksi
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola keanggotaan jurnal, hak akses peran dewan redaksi, dan direktori kepakaran penelaah (reviewer).
            </p>
        </div>

        <button 
            type="button" 
            onclick="openAssignRoleModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition self-start sm:self-center"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Tetapkan Peran Pengguna</span>
        </button>
    </div>

    <!-- 4 Metrik Ringkas & Ergonomis -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 truncate">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border tabular-nums {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-gray-900 tabular-nums tracking-tight">
                        {{ $metric['value'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">orang</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Container: Filter Peran, Pencarian & Tabel Pengguna -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
        
        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Status/Peran -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Peran Pengguna">
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
            <form action="{{ route('journalmanager.users.index') }}" method="GET" class="relative w-full md:w-72">
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
                    placeholder="Cari nama, email, afiliasi, atau kepakaran..."
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

        <!-- Tabel Pengguna Jurnal -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 sm:px-5">Identitas & Afiliasi</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Peran Jurnal</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Kepakaran & Beban Aktif</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Status</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->users() as $user)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- Kolom Identitas & Afiliasi -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-xs sm:max-w-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-maroon/10 text-brand-maroon flex items-center justify-center font-bold text-xs uppercase shrink-0 mt-0.5">
                                        {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $user['name']) ?: 'US', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-gray-900 leading-tight">
                                                {{ $user['name'] }}
                                            </span>
                                            @if($user['is_last_admin'])
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-100 text-purple-800 border border-purple-200" title="Manajer tunggal yang dilindungi aturan sistem">
                                                    Last Manager
                                                </span>
                                            @endif
                                            @if($user['is_last_eic'])
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-red-100 text-red-800 border border-red-200" title="Pemimpin redaksi tunggal yang dilindungi aturan sistem">
                                                    Last EIC
                                                </span>
                                            @endif
                                        </div>
                                        <span class="font-mono text-[11px] text-gray-500 block truncate mt-0.5">
                                            {{ $user['email'] }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 block truncate">
                                            {{ $user['affiliation'] }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Peran Jurnal -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($user['roles'] as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold border {{ $role['badge_class'] }}">
                                            {{ $role['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Kolom Kepakaran & Beban Aktif -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-xs">
                                @if(count($user['expertise']) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user['expertise'] as $exp)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                                {{ $exp }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($user['active_assignments_count'] > 0)
                                    <div class="flex items-center gap-1 mt-1 text-[11px] font-semibold text-blue-700">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>{{ $user['active_assignments_count'] }} Naskah Aktif</span>
                                    </div>
                                @else
                                    <span class="text-[11px] text-gray-400 block mt-1">Tidak ada beban naskah aktif</span>
                                @endif
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $user['status_badge_class'] }}">
                                    {{ $user['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-3.5 px-4 sm:px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openResetPasswordModal('{{ $user['email'] }}', '{{ addslashes($user['name']) }}')"
                                        class="px-2.5 py-1 text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 shadow-2xs transition"
                                        title="Kirim tautan reset kata sandi ke email pengguna"
                                    >
                                        Reset Sandi
                                    </button>

                                    <button 
                                        type="button" 
                                        onclick="openAssignRoleModal('{{ $user['id'] }}', '{{ $user['email'] }}', '{{ addslashes($user['name']) }}')"
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-brand-maroon bg-brand-maroon/5 hover:bg-brand-maroon hover:text-white rounded-lg transition border border-brand-maroon/20"
                                        title="Atur penugasan peran jurnal pengguna"
                                    >
                                        <span>Ubah Peran</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada pengguna ditemukan</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada akun anggota yang cocok dengan filter atau kata kunci ini.
                                    </p>
                                    @if(request('q') || request('role') !== 'all')
                                        <a href="{{ route('journalmanager.users.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
                                            Reset Filter
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
        @php
            $pagination = $viewModel->pagination();
        @endphp
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

<!-- Modal Dialog: Tetapkan Peran Pengguna -->
<div id="modal-assign-role" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-assign-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeAssignRoleModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-assign-title">
                            Tetapkan Peran Jurnal
                        </h3>
                        <p class="text-[11px] text-gray-500">Pemberian wewenang peran editorial atau reviewer</p>
                    </div>
                </div>

                <button type="button" onclick="closeAssignRoleModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.users.assign-role') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="user_id" id="assign_user_id" value="">
                <div>
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
                            <option value="{{ $registeredUser['email'] }}"></option>
                        @endforeach
                    </datalist>
                    <p id="assign_user_name_preview" class="text-[11px] text-gray-500 mt-1 hidden"></p>
                </div>

                <div>
                    <label for="role_code" class="block text-xs font-semibold text-gray-700 mb-1">Pilih Peran yang Diberikan <span class="text-red-500">*</span></label>
                    <select name="role_code" id="role_code" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon bg-white">
                        <option value="section_editor">Section Editor (Editor Bagian)</option>
                        <option value="reviewer">Peer Reviewer (Mitra Bestari)</option>
                        <option value="copyeditor">Copy Editor (Penyunting Bahasa)</option>
                        <option value="layout_editor">Layout Editor (Penata Letak Galai)</option>
                        <option value="proofreader">Proofreader (Korektor Akhir)</option>
                    </select>
                </div>

                <div>
                    <label for="expertise_keywords" class="block text-xs font-semibold text-gray-700 mb-1">Kata Kunci Kepakaran (Khusus Reviewer)</label>
                    <input type="text" name="expertise_keywords" id="expertise_keywords" placeholder="Contoh: Epidemiologi, Kesehatan Lingkungan (pisahkan koma)" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeAssignRoleModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition">
                        Simpan Penugasan Peran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dialog: Reset Kata Sandi Pengguna -->
<div id="modal-reset-password" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-reset-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeResetPasswordModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-reset-title">
                            Kirim Tautan Reset Sandi
                        </h3>
                        <p class="text-[11px] text-gray-500">Memicu token pemulihan akun pengguna</p>
                    </div>
                </div>

                <button type="button" onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.users.reset-password') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="email" id="reset_user_email" value="">

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs space-y-1">
                    <span class="text-gray-500 block text-[11px]">Nama Pengguna:</span>
                    <strong id="reset_user_name_preview" class="text-gray-900 text-sm block"></strong>
                    <span class="text-gray-500 block text-[11px] mt-1">Alamat Email Tujuan:</span>
                    <span id="reset_user_email_preview" class="font-mono text-gray-800 text-xs block break-all"></span>
                </div>

                <div class="p-2.5 bg-blue-50/70 rounded-lg border border-blue-200 text-[11px] text-blue-900 leading-relaxed">
                    Sistem akan mengirimkan email resmi berisi tautan aman untuk mengatur kata sandi baru. Journal Manager tidak memanipulasi kata sandi secara langsung demi menjaga integritas data akun pengguna.
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeResetPasswordModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-2xs transition">
                        Kirim Tautan Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAssignRoleModal(userId = '', userEmail = '', userName = '') {
        const modal = document.getElementById('modal-assign-role');
        const idInput = document.getElementById('assign_user_id');
        const emailInput = document.getElementById('assign_user_email');
        const namePreview = document.getElementById('assign_user_name_preview');
        if (idInput) idInput.value = userId;
        if (emailInput) emailInput.value = userEmail;
        if (namePreview) {
            if (userName) {
                namePreview.innerText = 'Pengguna: ' + userName;
                namePreview.classList.remove('hidden');
            } else {
                namePreview.innerText = '';
                namePreview.classList.add('hidden');
            }
        }
        if (modal) modal.classList.remove('hidden');
    }
    function closeAssignRoleModal() {
        const modal = document.getElementById('modal-assign-role');
        if (modal) modal.classList.add('hidden');
    }

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
</script>
@endsection

