@extends('layouts.sysadmin')

@section('title', 'Manajemen Instansi Jurnal Ilmiah — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Header Halaman & Tombol Tambah Jurnal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-serif">
                Manajemen Instansi Jurnal Ilmiah
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Tata kelola multi-tenancy portal jurnal, penetapan nomor ISSN/E-ISSN, dan status operasional publikasi BRIDA Kota Makassar.
            </p>
        </div>

        <div class="shrink-0">
            <button 
                type="button" 
                onclick="document.getElementById('modal-add-journal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 min-h-11 bg-brand-maroon hover:bg-brand-maroon-dark text-white text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer"
                title="Daftarkan Instansi Jurnal Baru"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Daftarkan Jurnal Baru</span>
            </button>
        </div>
    </div>

    <!-- 4 Metrik Ringkas Multi-Jurnal -->
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
                    <span class="text-[11px] text-gray-400">jurnal</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Container: Filter Tabs, Pencarian, & Tabel Jurnal -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
        
        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Status -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Status Jurnal">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a 
                        href="{{ route('sysadmin.journals.index', ['status' => $tabKey, 'q' => request('q')]) }}"
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
            <form action="{{ route('sysadmin.journals.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="status" value="{{ $tabs['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="search" 
                    name="q" 
                    value="{{ request('q') }}"
                    placeholder="Cari nama jurnal, slug, atau ISSN..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a 
                        href="{{ route('sysadmin.journals.index', ['status' => $tabs['active']]) }}" 
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

        <!-- Tabel Daftar Jurnal Ilmiah -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 sm:px-5">Instansi Jurnal</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Identitas &amp; ISSN</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Kontak Utama / PIC</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Edisi Berjalan</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Status</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->journals() as $journal)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- Kolom Jurnal -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-sm sm:max-w-md">
                                <span class="font-serif font-bold text-gray-900 group-hover:text-brand-maroon transition block leading-snug">
                                    {{ $journal['name'] }}
                                </span>
                                <span class="text-[11px] text-gray-500 block mt-1 line-clamp-1">
                                    {{ $journal['description'] }}
                                </span>
                                <span class="text-[10px] font-mono text-gray-400 block mt-0.5">
                                    Slug: /{{ $journal['slug'] }} • Total Naskah: {{ $journal['total_submissions'] }}
                                </span>
                            </td>

                            <!-- Kolom Identitas & ISSN -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400 text-[10px] font-semibold">P-ISSN:</span>
                                        <span class="font-mono text-gray-800 font-medium">{{ $journal['issn'] ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400 text-[10px] font-semibold">E-ISSN:</span>
                                        <span class="font-mono text-gray-800 font-medium">{{ $journal['eissn'] ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Kontak Utama -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="font-medium text-gray-800 block text-xs truncate max-w-48">
                                    {{ $journal['primary_contact'] }}
                                </span>
                                <span class="text-[10px] text-gray-400 block">Penanggung Jawab</span>
                            </td>

                            <!-- Kolom Edisi Berjalan -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="text-xs font-semibold text-gray-800 block">
                                    {{ $journal['current_issue_title'] ?? 'Belum ada terbitan' }}
                                </span>
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $journal['status_badge_class'] }}">
                                    {{ $journal['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-3.5 px-4 sm:px-5 text-right whitespace-nowrap">
                                <form action="{{ route('sysadmin.journals.update-status', $journal['id']) }}" method="POST" class="inline">
                                    @csrf
                                    @if($journal['status'] === 'active')
                                        <input type="hidden" name="status" value="archived">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 min-h-9 text-xs font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                                            title="Arsipkan instansi jurnal ini"
                                        >
                                            <span>Arsipkan</span>
                                        </button>
                                    @else
                                        <input type="hidden" name="status" value="active">
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 min-h-9 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition border border-emerald-200"
                                            title="Aktifkan kembali publikasi jurnal"
                                        >
                                            <span>Aktifkan</span>
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada instansi jurnal</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ditemukan jurnal yang cocok dengan filter atau kata kunci pencarian.
                                    </p>
                                    @if(request('q') || request('status') !== 'all')
                                        <a href="{{ route('sysadmin.journals.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
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
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> instansi jurnal
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
</div>

<!-- Modal Dialog Tambah Jurnal Baru -->
<div id="modal-add-journal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden" role="dialog" aria-modal="true">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-bold text-gray-900 tracking-tight font-serif">
                Daftarkan Instansi Jurnal Ilmiah Baru
            </h3>
            <button 
                type="button" 
                onclick="document.getElementById('modal-add-journal').classList.add('hidden')"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 flex items-center justify-center"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('sysadmin.journals.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-xs font-semibold text-gray-700 mb-1">
                    Nama Lengkap Jurnal <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    required 
                    placeholder="Contoh: Jurnal Kebijakan Publik Makassar"
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="slug" class="block text-xs font-semibold text-gray-700 mb-1">
                        URL Slug <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="slug" 
                        name="slug" 
                        required 
                        placeholder="jkpm-makassar"
                        class="w-full text-xs font-mono text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
                <div>
                    <label for="primary_contact" class="block text-xs font-semibold text-gray-700 mb-1">
                        Kontak Utama / PIC <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="primary_contact" 
                        name="primary_contact" 
                        required 
                        placeholder="Nama Pejabat / Pengelola"
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="issn" class="block text-xs font-semibold text-gray-700 mb-1">
                        Print ISSN (Opsional)
                    </label>
                    <input 
                        type="text" 
                        id="issn" 
                        name="issn" 
                        placeholder="2655-xxxx"
                        class="w-full text-xs font-mono text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
                <div>
                    <label for="eissn" class="block text-xs font-semibold text-gray-700 mb-1">
                        Electronic E-ISSN (Opsional)
                    </label>
                    <input 
                        type="text" 
                        id="eissn" 
                        name="eissn" 
                        placeholder="2722-xxxx"
                        class="w-full text-xs font-mono text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-gray-700 mb-1">
                    Deskripsi Ringkas Jurnal
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="2" 
                    placeholder="Fokus dan ruang lingkup jurnal ilmiah..."
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button 
                    type="button" 
                    onclick="document.getElementById('modal-add-journal').classList.add('hidden')"
                    class="px-4 py-2 min-h-11 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 min-h-11 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition shadow-2xs"
                >
                    Simpan &amp; Inisialisasi Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
