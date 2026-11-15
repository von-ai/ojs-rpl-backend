@extends('layouts.journalmanager')

@section('title', 'Katalog Edisi & Terbitan — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman Simpel & Tombol Aksi Utama -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Edisi & Terbitan Berkala
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola nomor terbitan jurnal berdasarkan volume, nomor, dan tahun, penyusunan daftar isi naskah, serta otorisasi publikasi.
            </p>
        </div>

        <button
            type="button"
            onclick="openCreateIssueModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition self-start sm:self-center"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Edisi Baru</span>
        </button>
    </div>

    @if(isset($journal) && $journal)
        <!-- Tenant Workspace Active Context Bar -->
        <div class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200/90 text-xs">
            <div class="flex items-center gap-2 min-w-0">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-brand-maroon/10 text-brand-maroon border border-brand-maroon/20 shrink-0 uppercase tracking-wider">
                    Ruang Kerja Jurnal
                </span>
                <span class="font-semibold text-gray-900 truncate" title="{{ $journal->name }}">
                    {{ $journal->name }}
                </span>
                <span class="text-gray-400 hidden sm:inline">•</span>
                <span class="text-gray-500 font-mono text-[11px] hidden sm:inline">/{{ $journal->slug }}</span>
            </div>
        </div>
    @endif

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
                    <span class="text-2xl font-semibold text-gray-900 tabular-nums">
                        {{ $metric['value'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">edisi</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Container: Filter Tabs, Pencarian & Tabel Edisi -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">

        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Status -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Status Edisi">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a
                        href="{{ route('journalmanager.issues.index', ['tab' => $tabKey, 'q' => request('q')]) }}"
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
            <form action="{{ route('journalmanager.issues.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="tab" value="{{ $tabs['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari volume, nomor, atau judul edisi..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a
                        href="{{ route('journalmanager.issues.index', ['tab' => $tabs['active']]) }}"
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

        <!-- Tabel Daftar Edisi Terbitan -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase">
                        <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">NOMOR EDISI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-64">JUDUL EDISI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">TANGGAL TERBIT</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">NASKAH</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">STATUS</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->issues() as $issue)
                        <tr class="hover:bg-gray-50/70 transition duration-150">
                            <!-- Kolom 0: NO (Nomor Urut) -->
                            <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-normal text-gray-400 tabular-nums">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Kolom 1: NOMOR EDISI -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <div class="text-sm font-normal text-gray-700 tabular-nums">
                                    Vol. {{ $issue['volume'] }} No. {{ $issue['number'] }} ({{ $issue['year'] }})
                                </div>
                                @if($issue['is_current'])
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            Edisi Terkini
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Kolom 2: JUDUL EDISI (Dengan Tooltip Hover Rapi Seperti Role Penulis) -->
                            <td class="py-4 px-4 sm:px-6 align-top min-w-64 max-w-md">
                                <div class="relative group block max-w-full">
                                    <a
                                        href="{{ $issue['url_workspace'] }}"
                                        aria-describedby="tooltip-issue-{{ $issue['id'] }}"
                                        class="block font-normal text-gray-900 leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon hover:underline decoration-brand-maroon/40 underline-offset-2 transition duration-150 cursor-pointer"
                                    >
                                        {{ $issue['title'] }}
                                    </a>

                                    <!-- Tooltip Judul Lengkap saat Hover -->
                                    <div
                                        id="tooltip-issue-{{ $issue['id'] }}"
                                        role="tooltip"
                                        class="absolute left-0 top-full mt-1.5 z-30 hidden group-hover:block w-72 sm:w-80 p-2.5 bg-gray-900 text-white text-xs font-normal rounded-md pointer-events-none border border-gray-700 leading-relaxed whitespace-normal wrap-break-word shadow-lg"
                                    >
                                        {{ $issue['title'] }}
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 3: TANGGAL TERBIT (Tanpa Tulisan 'Jadwal' & Tanpa Ikon) -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap text-sm text-gray-700">
                                @if($issue['published_at'])
                                    <span class="font-medium text-gray-800 tabular-nums">{{ $issue['published_at'] }}</span>
                                @elseif($issue['scheduled_at'])
                                    <span class="font-medium text-gray-800 tabular-nums">{{ $issue['scheduled_at'] }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Kolom 4: NASKAH -->
                            <td class="py-4 px-4 sm:px-6 align-top text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 tabular-nums">
                                    {{ $issue['articles_count'] }} Artikel
                                </span>
                            </td>

                            <!-- Kolom 5: STATUS -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $issue['status_badge_class'] }}">
                                    {{ $issue['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom 6: AKSI -->
                            <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                <a
                                    href="{{ $issue['url_workspace'] }}"
                                    class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                                    title="Buka Ruang Kerja Edisi"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-800">Tidak ada edisi terbitan</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada edisi nomor terbitan yang cocok dengan filter atau kata kunci ini.
                                    </p>
                                    @if(request('q') || request('tab') !== 'all')
                                        <a href="{{ route('journalmanager.issues.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
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

        <!-- Bagian Bawah Tabel / Paginasi Seperti Role Penulis -->
        @php
            $pagination = $viewModel->pagination();
        @endphp
        <div class="px-4 sm:px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
            <div id="issues-pagination-info">
                Menampilkan <span class="font-bold text-gray-900 tabular-nums">{{ $pagination['from'] }}</span> - <span class="font-bold text-gray-900 tabular-nums">{{ $pagination['to'] }}</span> dari <span class="font-bold text-gray-900 tabular-nums">{{ $pagination['total'] }}</span> edisi terdaftar
            </div>

            <div class="flex items-center gap-1">
                <button type="button" disabled class="min-w-9 min-h-9 p-1.5 flex items-center justify-center rounded border border-gray-200 bg-white text-gray-300 cursor-not-allowed" aria-label="Halaman sebelumnya">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="min-w-9 min-h-9 flex items-center justify-center px-2.5 py-1 text-xs font-bold text-white bg-brand-maroon rounded tabular-nums">1</span>
                <button type="button" disabled class="min-w-9 min-h-9 p-1.5 flex items-center justify-center rounded border border-gray-200 bg-white text-gray-300 cursor-not-allowed" aria-label="Halaman selanjutnya">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dialog: Buat Edisi Baru -->
<div id="modal-create-issue" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-create-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeCreateIssueModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-gray-900" id="modal-create-title">
                            Buat Edisi Nomor Terbitan Baru
                        </h3>
                        <p class="text-[11px] text-gray-500">Buka edisi baru untuk kurasi naskah publikasi</p>
                    </div>
                </div>

                <button type="button" onclick="closeCreateIssueModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('journalmanager.issues.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label for="volume" class="block text-xs font-semibold text-gray-700 mb-1">Volume <span class="text-red-500">*</span></label>
                        <input type="text" name="volume" id="volume" value="6" required class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="number" class="block text-xs font-semibold text-gray-700 mb-1">Nomor <span class="text-red-500">*</span></label>
                        <input type="text" name="number" id="number" value="3" required class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="year" class="block text-xs font-semibold text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                        <input type="number" name="year" id="year" value="2026" required class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                </div>

                <div>
                    <label for="title" class="block text-xs font-semibold text-gray-700 mb-1">Judul Khusus Edisi</label>
                    <input type="text" name="title" id="title" placeholder="Contoh: Edisi Khusus Riset Inovasi Maritim" class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div class="space-y-1">
                    <label for="issue_doi_prefix" class="block text-xs font-semibold text-gray-700">Prefiks DOI Edisi</label>
                    <x-input id="issue_doi_prefix" name="doi_prefix" maxlength="191" placeholder="10.12345/jurnal.v1i1" />
                    <p class="text-xs text-gray-500">Kosongkan jika DOI belum dialokasikan.</p>
                </div>
                <div>
                    <label for="description" class="block text-xs font-semibold text-gray-700 mb-1">Kata Pengantar / Deskripsi Edisi</label>
                    <textarea name="description" id="description" rows="3" placeholder="Deskripsi ringkas fokus dan pengantar dewan redaksi untuk edisi ini..." class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCreateIssueModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition">
                        Simpan Draf Edisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCreateIssueModal() {
        const modal = document.getElementById('modal-create-issue');
        if (modal) modal.classList.remove('hidden');
    }
    function closeCreateIssueModal() {
        const modal = document.getElementById('modal-create-issue');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
