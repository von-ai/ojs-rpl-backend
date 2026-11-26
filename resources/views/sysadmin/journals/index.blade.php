@extends('layouts.sysadmin')

@section('title', 'Kelola jurnal — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman & Tombol Tambah Jurnal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Kelola jurnal
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Tambah jurnal, ubah ISSN, atau atur status penerbitan.
            </p>
        </div>

        <button
            type="button"
            onclick="openAddJournalModal()"
            class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-none transition self-start sm:self-center cursor-pointer"
            title="Tambah jurnal"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Daftarkan Jurnal Baru</span>
        </button>
    </div>

    <!-- 4 Metrik Ringkas Multi-Jurnal -->
    @php
        $journalIcons = [
            0 => 'collection',
            1 => 'check-circle',
            2 => 'calendar',
            3 => 'archive',
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $idx => $metric)
            <x-kpi-card
                :title="$metric['title']"
                :value="$metric['value']"
                :icon="$journalIcons[$idx] ?? 'collection'"
            />
        @endforeach
    </div>

    <!-- Main Container: Filter Tabs, Pencarian, & Tabel Jurnal -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-none overflow-hidden">

        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-3.5 sm:p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
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
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold shadow-none' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
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
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600">
                        <th scope="col" class="py-2.5 px-2 text-center whitespace-nowrap w-8 text-[11px] uppercase tracking-wider font-semibold">NO</th>
                        <th scope="col" class="py-2.5 px-2.5 text-[11px] uppercase tracking-wider font-semibold">INSTANSI JURNAL</th>
                        <th scope="col" class="py-2.5 px-2.5 whitespace-nowrap text-[11px] uppercase tracking-wider font-semibold">ISSN</th>
                        <th scope="col" class="py-2.5 px-2.5 text-[11px] uppercase tracking-wider font-semibold">PIC</th>
                        <th scope="col" class="py-2.5 px-2 text-center whitespace-nowrap text-[11px] uppercase tracking-wider font-semibold">NASKAH</th>
                        <th scope="col" class="py-2.5 px-2.5 text-[11px] uppercase tracking-wider font-semibold">EDISI TERKINI</th>
                        <th scope="col" class="py-2.5 px-2.5 text-center whitespace-nowrap text-[11px] uppercase tracking-wider font-semibold">STATUS</th>
                        <th scope="col" class="py-2.5 px-2.5 text-center whitespace-nowrap text-[11px] uppercase tracking-wider font-semibold">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->journals() as $journal)
                        <tr class="hover:bg-gray-50/70 transition duration-150">
                            <!-- Kolom NO -->
                            <td class="py-2.5 px-2 text-center whitespace-nowrap text-xs font-normal text-gray-500 tabular-nums">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Kolom Instansi Jurnal -->
                            <td class="py-2.5 px-2.5 max-w-60">
                                <div>
                                    <button
                                        type="button"
                                        onclick='openEditJournalModal(@json($journal))'
                                        class="text-left font-semibold text-gray-900 hover:text-brand-maroon transition block leading-snug text-xs sm:text-[13px] cursor-pointer"
                                        title="Kelola / sunting identitas {{ $journal['name'] }}"
                                    >
                                        {{ $journal['name'] }}
                                    </button>
                                    <a
                                        href="{{ $journal['url_public'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-[11px] text-gray-500 hover:text-brand-maroon font-normal mt-0.5 hover:underline"
                                        title="Buka halaman publik /{{ $journal['slug'] }} di tab baru"
                                    >
                                        <span>/{{ $journal['slug'] }}</span>
                                        <span aria-hidden="true">↗</span>
                                    </a>
                                </div>
                            </td>

                            <!-- Kolom Identitas & ISSN -->
                            <td class="py-2.5 px-2.5 whitespace-nowrap">
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500 text-[10px] font-semibold uppercase">P:</span>
                                        <span class="text-gray-800 font-medium tabular-nums">{{ $journal['issn'] ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500 text-[10px] font-semibold uppercase">E:</span>
                                        <span class="text-gray-800 font-medium tabular-nums">{{ $journal['eissn'] ?? '—' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Penanggung Jawab -->
                            <td class="py-2.5 px-2.5 max-w-36">
                                <span class="font-medium text-gray-800 block text-xs leading-snug line-clamp-2" title="{{ $journal['primary_contact'] }}">
                                    {{ $journal['primary_contact'] }}
                                </span>
                            </td>

                            <!-- Kolom Total Naskah -->
                            <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-200 text-gray-900 tabular-nums">
                                    {{ number_format($journal['total_submissions'], 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Kolom Edisi Terkini -->
                            <td class="py-2.5 px-2.5 max-w-40">
                                <div>
                                    @if(!empty($journal['current_issue_title']) && $journal['current_issue_title'] !== 'Belum ada terbitan')
                                        <span class="text-xs font-medium text-gray-800 block line-clamp-1 leading-snug" title="{{ $journal['current_issue_title'] }}">
                                            {{ $journal['current_issue_title'] }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 text-white mt-0.5">
                                            Terpublikasi
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500 italic block">
                                            Belum terbit
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-2.5 px-2.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $journal['status_badge_class'] }}">
                                    {{ $journal['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-2.5 px-2.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-center">
                                    <!-- Tombol Edit Metadata Jurnal (Ikon Saja) -->
                                    <button
                                        type="button"
                                        onclick='openEditJournalModal(@json($journal))'
                                        class="w-8 h-8 rounded-md inline-flex items-center justify-center text-gray-700 hover:text-brand-maroon hover:bg-gray-100 transition cursor-pointer"
                                        data-tooltip="Sunting Identitas Jurnal"
                                        title="Sunting Identitas Jurnal"
                                        aria-label="Sunting Identitas Jurnal"
                                    >
                                        <svg class="w-4 h-4 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Lihat Website Publik (Ikon Saja) -->
                                    <a
                                        href="{{ $journal['url_public'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="w-8 h-8 rounded-md inline-flex items-center justify-center text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition cursor-pointer"
                                        data-tooltip="Kunjungi Website Publik"
                                        title="Kunjungi Website Publik"
                                        aria-label="Kunjungi Website Publik"
                                    >
                                        <svg class="w-4 h-4 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>

                                    <!-- Tombol Ubah Status Jurnal dengan Modal Konfirmasi -->
                                    @if($journal['status'] === 'active')
                                        <form
                                            action="{{ route('sysadmin.journals.update-status', $journal['id']) }}"
                                            method="POST"
                                            class="inline"
                                            data-confirm="Apakah Anda betul ingin mengarsipkan jurnal {{ $journal['name'] }}? Jurnal yang diarsipkan tidak lagi menerima pengajuan naskah baru."
                                            data-confirm-title="Arsipkan Jurnal?"
                                            data-confirm-btn="Ya, Arsipkan"
                                            data-confirm-cancel="Batal"
                                            data-confirm-type="danger"
                                        >
                                            @csrf
                                            <input type="hidden" name="status" value="archived">
                                            <button
                                                type="submit"
                                                class="min-h-7 px-2.5 py-1 rounded-md inline-flex items-center justify-center text-xs font-semibold text-gray-800 bg-white hover:bg-gray-800 hover:text-white hover:border-gray-800 border border-gray-300 transition shadow-2xs cursor-pointer"
                                                title="Arsipkan Publikasi Jurnal"
                                                aria-label="Arsipkan Publikasi Jurnal"
                                            >
                                                <span>Arsipkan</span>
                                            </button>
                                        </form>
                                    @else
                                        <form
                                            action="{{ route('sysadmin.journals.update-status', $journal['id']) }}"
                                            method="POST"
                                            class="inline"
                                            data-confirm="Apakah Anda ingin mengaktifkan kembali publikasi jurnal {{ $journal['name'] }}?"
                                            data-confirm-title="Aktifkan Kembali Jurnal?"
                                            data-confirm-btn="Ya, Aktifkan"
                                            data-confirm-cancel="Batal"
                                            data-confirm-type="primary"
                                        >
                                            @csrf
                                            <input type="hidden" name="status" value="active">
                                            <button
                                                type="submit"
                                                class="min-h-7 px-2.5 py-1 rounded-md inline-flex items-center justify-center text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 border border-emerald-600 transition shadow-2xs cursor-pointer"
                                                title="Aktifkan Publikasi Jurnal"
                                                aria-label="Aktifkan Publikasi Jurnal"
                                            >
                                                <span>Aktifkan</span>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
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
    class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-add-journal-title"
>
    <!-- Click outside backdrop to close -->
    <div class="fixed inset-0 cursor-pointer" onclick="closeAddJournalModal()"></div>

    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl shadow-gray-950/20 border border-gray-100 space-y-4 z-10 my-auto">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 id="modal-add-journal-title" class="text-base font-semibold text-gray-900">
                    Tambah jurnal
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Isi nama dan alamat jurnal.
                </p>
            </div>
            <button
                type="button"
                onclick="closeAddJournalModal()"
                class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
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
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
                >
                    <option value="">Pilih Akun Pengguna Terdaftar (Opsional)</option>
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-none transition cursor-pointer"
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
    class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/60 backdrop-blur-sm flex items-center justify-center p-4 hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-edit-journal-title"
>
    <!-- Click outside backdrop to close -->
    <div class="fixed inset-0 cursor-pointer" onclick="closeEditJournalModal()"></div>

    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl shadow-gray-950/20 border border-gray-100 space-y-4 z-10 my-auto">
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
                class="w-8 h-8 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
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
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-none"
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
                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-none transition cursor-pointer"
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
