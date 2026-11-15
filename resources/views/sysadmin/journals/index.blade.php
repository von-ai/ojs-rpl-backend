@extends('layouts.sysadmin')

@section('title', 'Manajemen Instansi Jurnal Ilmiah — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman & Tombol Tambah Jurnal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Manajemen Instansi Jurnal Ilmiah
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Tata kelola multi-tenancy portal jurnal, penetapan nomor ISSN/E-ISSN, dan status operasional publikasi BRIDA Kota Makassar.
            </p>
        </div>

        <button
            type="button"
            onclick="openAddJournalModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition self-start sm:self-center cursor-pointer"
            title="Daftarkan Instansi Jurnal Baru"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Daftarkan Jurnal Baru</span>
        </button>
    </div>

    <!-- 4 Metrik Ringkas Multi-Jurnal -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 truncate" title="{{ $metric['title'] }}">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border tabular-nums shrink-0 {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-semibold text-gray-900 tabular-nums">
                        {{ $metric['value'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">{{ $metric['unit'] ?? 'jurnal' }}</span>
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
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase">
                        <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-64 max-w-md">INSTANSI JURNAL</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">IDENTITAS &amp; ISSN</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">PENANGGUNG JAWAB</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-48 max-w-xs">EDISI TERKINI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">STATUS</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->journals() as $journal)
                        <tr class="hover:bg-gray-50/70 transition duration-150">
                            <!-- Kolom NO -->
                            <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-normal text-gray-400 tabular-nums">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Kolom Instansi Jurnal -->
                            <td class="py-4 px-4 sm:px-6 align-top min-w-64 max-w-md">
                                <div class="space-y-1">
                                    <a
                                        href="{{ $journal['url_public'] }}"
                                        target="_blank"
                                        class="font-semibold text-gray-900 hover:text-brand-maroon transition block leading-snug text-sm sm:text-[15px]"
                                        title="Buka website publik {{ $journal['name'] }}"
                                    >
                                        {{ $journal['name'] }}
                                    </a>
                                    @if (!empty($journal['description']))
                                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2" title="{{ $journal['description'] }}">
                                            {{ $journal['description'] }}
                                        </p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-gray-400 pt-0.5">
                                        <span class="inline-flex items-center gap-1 text-gray-500 bg-gray-100 px-2 py-0.5 rounded text-[11px]">
                                            <span class="text-gray-400">slug:</span> /{{ $journal['slug'] }}
                                        </span>
                                        <span class="text-gray-300">•</span>
                                        <span class="inline-flex items-center gap-1 text-gray-500 text-[11px]">
                                            <span>Total Naskah:</span>
                                            <strong class="font-semibold text-gray-700 tabular-nums">{{ $journal['total_submissions'] }}</strong>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Identitas & ISSN -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <div class="space-y-1 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400 text-[10px] font-semibold uppercase">P-ISSN</span>
                                        <span class="text-gray-800 font-medium tabular-nums">{{ $journal['issn'] ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400 text-[10px] font-semibold uppercase">E-ISSN</span>
                                        <span class="text-gray-800 font-medium tabular-nums">{{ $journal['eissn'] ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Kontak Utama -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <div>
                                    <span class="font-medium text-gray-800 block text-xs truncate max-w-48" title="{{ $journal['primary_contact'] }}">
                                        {{ $journal['primary_contact'] }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 block mt-0.5">Kontak Utama / PIC</span>
                                </div>
                            </td>

                            <!-- Kolom Edisi Terkini -->
                            <td class="py-4 px-4 sm:px-6 align-top min-w-48 max-w-xs">
                                <div>
                                    @if(!empty($journal['current_issue_title']) && $journal['current_issue_title'] !== 'Belum ada terbitan')
                                        <span class="text-xs font-normal text-gray-800 block line-clamp-2 leading-relaxed" title="{{ $journal['current_issue_title'] }}">
                                            {{ $journal['current_issue_title'] }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 mt-1">
                                            Edisi Aktif Terpublikasi
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic block">
                                            Belum ada terbitan
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-4 px-4 sm:px-6 align-top text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $journal['status_badge_class'] }}">
                                    {{ $journal['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Tombol Kelola Ruang Kerja Jurnal -->
                                    <a
                                        href="{{ route('sysadmin.journals.manage', $journal['id']) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-md text-white bg-brand-maroon hover:bg-brand-maroon/90 active:bg-brand-maroon transition shadow-2xs"
                                        title="Masuk ke ruang kerja pengelolaan jurnal ini"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Kelola</span>
                                    </a>

                                    <!-- Tombol Edit Metadata Jurnal -->
                                    <button
                                        type="button"
                                        onclick='openEditJournalModal(@json($journal))'
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition shadow-2xs cursor-pointer"
                                        title="Sunting identitas dan informasi jurnal"
                                    >
                                        <span>Edit</span>
                                    </button>

                                    <!-- Tombol Lihat Website Publik -->
                                    <a
                                        href="{{ $journal['url_public'] }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition shadow-2xs"
                                        title="Buka website publik jurnal (tab baru)"
                                    >
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>Web</span>
                                    </a>

                                    <!-- Tombol Status -->
                                    <form action="{{ route('sysadmin.journals.update-status', $journal['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @if($journal['status'] === 'active')
                                            <input type="hidden" name="status" value="archived">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-md transition cursor-pointer"
                                                title="Arsipkan instansi jurnal ini"
                                            >
                                                Arsipkan
                                            </button>
                                        @else
                                            <input type="hidden" name="status" value="active">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition cursor-pointer"
                                                title="Aktifkan kembali publikasi jurnal"
                                            >
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-800">Tidak ada instansi jurnal</h3>
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
<div
    id="modal-add-journal"
    class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-add-journal-title"
>
    <!-- Click outside backdrop to close -->
    <div class="fixed inset-0" onclick="closeAddJournalModal()"></div>

    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-4 z-10">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 id="modal-add-journal-title" class="text-base font-semibold text-gray-900">
                    Daftarkan Instansi Jurnal Ilmiah Baru
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Buat entitas portal jurnal baru di bawah naungan BRIDA Kota Makassar.
                </p>
            </div>
            <button
                type="button"
                onclick="closeAddJournalModal()"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                aria-label="Tutup dialog"
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
                    oninput="handleJournalNameInput(this.value)"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                    <p class="text-[11px] text-gray-400 mt-0.5">Akses via: domain/slug</p>
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

            <div>
                <label for="manager_user_id" class="block text-xs font-semibold text-gray-700 mb-1">
                    Akun PIC / Journal Manager <span class="text-xs font-normal text-emerald-700">(Otomatis diberi hak akses)</span>
                </label>
                <select
                    id="manager_user_id"
                    name="manager_user_id"
                    onchange="if(this.options[this.selectedIndex].dataset.name) { document.getElementById('primary_contact').value = this.options[this.selectedIndex].dataset.name; }"
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                    <option value="">-- Pilih Akun Pengguna Terdaftar (Opsional) --</option>
                    @foreach ($viewModel->selectableUsers() as $userOption)
                        <option value="{{ $userOption['id'] }}" data-name="{{ $userOption['name'] }}">
                            {{ $userOption['name'] }} ({{ $userOption['email'] }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-500 mt-1">
                    Pengguna terpilih akan langsung didaftarkan sebagai Manajer Jurnal pada portal yang dibuat.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="issn" class="block text-xs font-semibold text-gray-700 mb-1">
                        Print ISSN (P-ISSN)
                    </label>
                    <input
                        type="text"
                        id="issn"
                        name="issn"
                        placeholder="2655-xxxx"
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
                <div>
                    <label for="eissn" class="block text-xs font-semibold text-gray-700 mb-1">
                        Electronic ISSN (E-ISSN)
                    </label>
                    <input
                        type="text"
                        id="eissn"
                        name="eissn"
                        placeholder="2722-xxxx"
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-gray-700 mb-1">
                    Fokus &amp; Ruang Lingkup (Deskripsi Jurnal)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="2"
                    placeholder="Tuliskan fokus, ruang lingkup, dan tujuan penerbitan jurnal ilmiah..."
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button
                    type="button"
                    onclick="closeAddJournalModal()"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer"
                >
                    Simpan &amp; Inisialisasi Jurnal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dialog Edit Metadata Jurnal -->
<div
    id="modal-edit-journal"
    class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-edit-journal-title"
>
    <!-- Click outside backdrop to close -->
    <div class="fixed inset-0" onclick="closeEditJournalModal()"></div>

    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-4 z-10">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 id="modal-edit-journal-title" class="text-base font-semibold text-gray-900">
                    Sunting Identitas Instansi Jurnal
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Perbarui nama, URL slug, ISSN, dan informasi penanggung jawab jurnal.
                </p>
            </div>
            <button
                type="button"
                onclick="closeEditJournalModal()"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                aria-label="Tutup dialog"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-edit-journal" action="" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="edit_name" class="block text-xs font-semibold text-gray-700 mb-1">
                    Nama Lengkap Jurnal <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="edit_name"
                    name="name"
                    required
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="edit_slug" class="block text-xs font-semibold text-gray-700 mb-1">
                        URL Slug <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_slug"
                        name="slug"
                        required
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                    <p class="text-[11px] text-gray-400 mt-0.5">Akses via: domain/slug</p>
                </div>
                <div>
                    <label for="edit_primary_contact" class="block text-xs font-semibold text-gray-700 mb-1">
                        Kontak Utama / PIC <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_primary_contact"
                        name="primary_contact"
                        required
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="edit_issn" class="block text-xs font-semibold text-gray-700 mb-1">
                        Print ISSN (P-ISSN)
                    </label>
                    <input
                        type="text"
                        id="edit_issn"
                        name="issn"
                        placeholder="2655-xxxx"
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
                <div>
                    <label for="edit_eissn" class="block text-xs font-semibold text-gray-700 mb-1">
                        Electronic ISSN (E-ISSN)
                    </label>
                    <input
                        type="text"
                        id="edit_eissn"
                        name="eissn"
                        placeholder="2722-xxxx"
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                    >
                </div>
            </div>

            <div>
                <label for="edit_description" class="block text-xs font-semibold text-gray-700 mb-1">
                    Fokus &amp; Ruang Lingkup (Deskripsi Jurnal)
                </label>
                <textarea
                    id="edit_description"
                    name="description"
                    rows="2"
                    placeholder="Tuliskan fokus, ruang lingkup, dan tujuan penerbitan jurnal ilmiah..."
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button
                    type="button"
                    onclick="closeEditJournalModal()"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let slugIsManuallyEdited = false;

    function openAddJournalModal() {
        document.getElementById('modal-add-journal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeAddJournalModal() {
        document.getElementById('modal-add-journal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function openEditJournalModal(journal) {
        const form = document.getElementById('form-edit-journal');
        form.action = `/sysadmin/journals/${journal.id}`;
        document.getElementById('edit_name').value = journal.name || '';
        document.getElementById('edit_slug').value = journal.slug || '';
        document.getElementById('edit_primary_contact').value = journal.primary_contact || '';
        document.getElementById('edit_issn').value = journal.issn || '';
        document.getElementById('edit_eissn').value = journal.eissn || '';
        document.getElementById('edit_description').value = journal.description || '';

        document.getElementById('modal-edit-journal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeEditJournalModal() {
        document.getElementById('modal-edit-journal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function handleJournalNameInput(val) {
        const slugInput = document.getElementById('slug');
        if (!slugIsManuallyEdited && slugInput) {
            slugInput.value = val
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const slugInput = document.getElementById('slug');
        if (slugInput) {
            slugInput.addEventListener('input', function () {
                slugIsManuallyEdited = true;
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const addModal = document.getElementById('modal-add-journal');
                if (addModal && !addModal.classList.contains('hidden')) {
                    closeAddJournalModal();
                }

                const editModal = document.getElementById('modal-edit-journal');
                if (editModal && !editModal.classList.contains('hidden')) {
                    closeEditJournalModal();
                }
            }
        });
    });
</script>
@endpush
@endsection
