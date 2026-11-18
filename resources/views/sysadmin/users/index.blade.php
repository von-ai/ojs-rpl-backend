@extends('layouts.sysadmin')

@section('title', 'Direktori Pengguna & Hak Akses Global — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Halaman & Tombol Impor -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-sans">
                Direktori Pengguna &amp; Hak Akses Global
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola akun pengguna terdaftar, pemberian hak istimewa System Administrator, dan pemantauan impor pengguna massal.
            </p>
        </div>

        <div class="shrink-0">
            <button
                type="button"
                onclick="document.getElementById('modal-import-users').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 min-h-11 bg-brand-maroon hover:bg-brand-maroon-dark text-white text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer"
                title="Impor Pengguna secara Massal melalui Berkas CSV"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Impor Pengguna CSV</span>
            </button>
        </div>
    </div>

    <!-- Container Utama 1: Direktori Pengguna Global -->
    <div class="bg-white rounded-xl border border-gray-200/90 overflow-hidden">

        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Peran Pengguna -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Peran Pengguna">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a
                        href="{{ route('sysadmin.users.index', ['role' => $tabKey, 'q' => request('q')]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] rounded-full tabular-nums {{ $isActive ? 'bg-white/20 text-white font-bold' : 'bg-gray-200/70 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Cepat -->
            <form action="{{ route('sysadmin.users.index') }}" method="GET" class="relative w-full md:w-72">
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
                    placeholder="Cari nama, surel, atau afiliasi..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
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

        <!-- Tabel Direktori Pengguna -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 sm:px-5">Informasi Pengguna</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Peran &amp; Keanggotaan</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 whitespace-nowrap">Hak Akses Sistem</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Status</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->users() as $user)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- Kolom Pengguna -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-sm sm:max-w-md">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $user['name']) ?: 'U', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-gray-900 group-hover:text-brand-maroon transition block leading-snug truncate" title="{{ $user['name'] }}">
                                            {{ $user['name'] }}
                                        </span>
                                        <span class="text-[11px] text-gray-500 block truncate" title="{{ $user['email'] }}">
                                            {{ $user['email'] }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 block truncate" title="{{ $user['affiliation'] }}">
                                            {{ $user['affiliation'] }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Peran & Keanggotaan -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="text-xs font-semibold text-gray-800 block">
                                    {{ $user['roles_formatted'] }}
                                </span>
                                <span class="text-[10px] text-gray-400 block">
                                    {{ $user['journal_memberships_count'] }} Keanggotaan Jurnal
                                </span>
                            </td>

                            <!-- Kolom Hak Akses Sistem -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                @if($user['is_sysadmin'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-800 border border-purple-200">
                                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <span>System Admin</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 font-medium">
                                        Standar (Non-Admin)
                                    </span>
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
                                        onclick="openJournalRoleModal(@js($user['id']), @js($user['name']), @js($user['email']), @js($user['journal_roles'] ?? []))"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 min-h-9 text-xs font-semibold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200 cursor-pointer"
                                        title="Kelola peran jurnal untuk pengguna ini"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span>Peran Jurnal</span>
                                    </button>

                                    @if($user['is_sysadmin'])
                                        <form action="{{ route('sysadmin.users.revoke-sysadmin') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 min-h-9 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition border border-red-200 cursor-pointer"
                                                title="Cabut hak akses System Administrator"
                                            >
                                                <span>Cabut SysAdmin</span>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('sysadmin.users.assign-sysadmin') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user['id'] }}">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 min-h-9 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg transition border border-purple-200 cursor-pointer"
                                                title="Berikan hak akses System Administrator"
                                            >
                                                <span>Jadikan SysAdmin</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada pengguna</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ditemukan akun pengguna yang cocok dengan filter atau kata kunci.
                                    </p>
                                    @if(request('q') || request('role') !== 'all')
                                        <a href="{{ route('sysadmin.users.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
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

        <!-- Pagination Simpel -->
        @php
            $pagination = $viewModel->pagination();
        @endphp
        <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span>
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> pengguna
            </span>

            <div class="flex items-center gap-1">
                <button type="button" disabled class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-400 cursor-not-allowed">
                    Sebelumnya
                </button>
                <button type="button" disabled class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-400 cursor-not-allowed">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>

    <!-- 4 Metrik Ringkas Pengguna -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 flex flex-col justify-between">
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
                    <span class="text-[11px] text-gray-400">pengguna</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Container Utama 2: Riwayat Pengimporan Massal CSV (Tabel 54 user_imports) -->
    <div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 space-y-4">
        <div class="border-b border-gray-100 pb-3">
            <h2 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                Riwayat Pengimporan Massal Pengguna via CSV
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Audit rekam jejak operasi impor akun massal, validasi baris, dan penanganan kegagalan format.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-2.5 px-3">ID &amp; Jurnal</th>
                        <th class="py-2.5 px-3">Nama Berkas CSV</th>
                        <th class="py-2.5 px-3">Total Baris</th>
                        <th class="py-2.5 px-3">Hasil Proses</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Waktu Eksekusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->recentImports() as $import)
                        <tr class="hover:bg-gray-50/50">
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-gray-800 block">{{ $import['id'] }}</span>
                                <span class="text-gray-500 block">{{ $import['journal_name'] }}</span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap font-mono text-gray-700">
                                {{ $import['file_name'] }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap font-bold text-gray-900">
                                {{ $import['total_rows'] }} baris
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="text-emerald-700 font-semibold">{{ $import['processed_rows'] }} Sukses</span>
                                @if($import['failed_rows'] > 0)
                                    <span class="text-red-600 font-semibold ml-1">({{ $import['failed_rows'] }} Gagal)</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
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

<!-- Modal Dialog Impor Pengguna CSV -->
<div id="modal-import-users" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                Impor Pengguna secara Massal (CSV)
            </h3>
            <button
                type="button"
                onclick="document.getElementById('modal-import-users').classList.add('hidden')"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 flex items-center justify-center"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('sysadmin.users.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="journal_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Jurnal Tujuan Alokasi Pengguna <span class="text-red-500">*</span>
                </label>
                <select
                    id="journal_id"
                    name="journal_id"
                    required
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
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
                    class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-maroon/10 file:text-brand-maroon hover:file:bg-brand-maroon/20 cursor-pointer border border-gray-300 rounded-lg p-1.5"
                >
                <p class="text-[11px] text-gray-400 mt-1">
                    Format kolom wajib: <code>name, email, affiliation, role</code>.
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
                    Kirim email notifikasi akun dan instruksi pengaturan kata sandi baru kepada seluruh pengguna yang diimpor.
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    onclick="document.getElementById('modal-import-users').classList.add('hidden')"
                    class="px-4 py-2 min-h-11 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 min-h-11 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition shadow-2xs"
                >
                    Unggah &amp; Proses Antrean CSV
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dialog Kelola Peran Jurnal Pengguna -->
<div id="modal-journal-roles" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                    Kelola Peran Jurnal Pengguna
                </h3>
                <p id="modal-user-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
            <button
                type="button"
                onclick="closeJournalRoleModal()"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 flex items-center justify-center cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Bagian 1: Peran Jurnal Aktif -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider text-[11px]">
                Peran Jurnal Saat Ini
            </h4>
            <div id="active-roles-list" class="space-y-2 max-h-40 overflow-y-auto">
                <!-- Diisi secara dinamis lewat JS -->
            </div>
            <template id="active-role-template">
                <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg border border-gray-200/80 text-xs">
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
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-semibold px-2 py-1 hover:bg-red-50 rounded transition cursor-pointer">
                            Cabut
                        </button>
                    </form>
                </div>
            </template>
        </div>

        <!-- Garis Pemisah -->
        <hr class="border-gray-200">

        <!-- Bagian 2: Form Tetapkan Peran Jurnal Baru -->
        <form action="{{ route('sysadmin.users.assign-journal-role') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="user_id" id="assign_user_id" value="">

            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider text-[11px]">
                Tetapkan Peran Jurnal Baru
            </h4>

            <div>
                <label for="assign_journal_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Pilih Instansi Jurnal <span class="text-red-500">*</span>
                </label>
                <select
                    id="assign_journal_id"
                    name="journal_id"
                    required
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                    <option value="">-- Pilih Instansi Jurnal --</option>
                    @foreach ($viewModel->selectableJournals() as $j)
                        <option value="{{ $j['id'] }}">{{ $j['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="assign_role_code" class="block text-xs font-semibold text-gray-700 mb-1">
                    Pilih Peran yang Diberikan <span class="text-red-500">*</span>
                </label>
                <select
                    id="assign_role_code"
                    name="role_code"
                    required
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
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
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button
                    type="button"
                    onclick="closeJournalRoleModal()"
                    class="px-4 py-2 min-h-11 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 min-h-11 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition shadow-2xs cursor-pointer"
                >
                    Simpan Peran Jurnal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openJournalRoleModal(userId, userName, userEmail, roles) {
        document.getElementById('assign_user_id').value = userId;
        document.getElementById('modal-user-subtitle').textContent = userName + ' (' + userEmail + ')';

        const listContainer = document.getElementById('active-roles-list');
        listContainer.replaceChildren();

        if (!roles || roles.length === 0) {
            listContainer.innerHTML = '<p class="text-xs text-gray-400 italic bg-gray-50 p-2.5 rounded-lg border border-gray-200/60">Pengguna belum memiliki peran pada jurnal spesifik (Pengguna Terdaftar Global).</p>';
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

        const roleSelect = document.getElementById('assign_role_code');
        const expField = document.getElementById('expertise_field');
        roleSelect.onchange = function() {
            if (this.value === 'reviewer') {
                expField.classList.remove('hidden');
            } else {
                expField.classList.add('hidden');
            }
        };

        document.getElementById('modal-journal-roles').classList.remove('hidden');
    }

    function closeJournalRoleModal() {
        document.getElementById('modal-journal-roles').classList.add('hidden');
    }
</script>
@endsection
