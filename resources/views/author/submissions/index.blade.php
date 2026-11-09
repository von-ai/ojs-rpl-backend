@extends('layouts.author')

@section('title', 'Naskah Saya — Portal Penulis BRIDA Kota Makassar')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Header Halaman & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                Naskah Saya
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola dan pantau seluruh pengajuan naskah ilmiah Anda.
            </p>
        </div>

        <div class="flex items-center gap-2.5 sm:gap-3 shrink-0 w-full sm:w-auto">
            <!-- Tombol Unduh Rekap CSV -->
            <a
                id="export-csv-btn"
                href="{{ route('author.submissions.export', request()->query()) }}"
                class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 border border-gray-300 rounded-lg transition duration-150 shrink-0"
                title="Unduh seluruh rekap riwayat naskah dalam format CSV"
            >
                <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Rekap CSV</span>
            </a>

            <!-- Tombol Tambah Naskah -->
            <a
                href="{{ route('author.submissions.create') }}"
                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg transition duration-150 shadow-2xs"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span class="whitespace-nowrap">Tambah Naskah</span>
            </a>
        </div>
    </div>

    <!-- Navigasi Tab Status Naskah -->
    <div class="border-b border-gray-200">
        <nav id="submissions-tabs-nav" class="flex space-x-1 sm:space-x-2 overflow-x-auto pb-px no-scrollbar" aria-label="Tab Kategori Naskah">
            @foreach($viewModel->tabs() as $tab)
                @php
                    $isActive = ($filters['tab'] ?? 'semua') === $tab['key'];
                @endphp
                <a
                    href="{{ route('author.submissions.index', array_merge(request()->query(), ['tab' => $tab['key']])) }}"
                    data-tab="{{ $tab['key'] }}"
                    class="submission-tab-link whitespace-nowrap py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 sm:gap-2 transition duration-150 shrink-0 {{ $isActive ? 'border-brand-maroon text-brand-maroon is-active' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <span class="submission-tab-badge inline-flex items-center justify-center px-1.5 sm:px-2 py-0.5 text-[11px] sm:text-xs font-bold rounded-full tabular-nums transition duration-150 {{ $isActive ? 'bg-brand-maroon text-white' : 'bg-gray-100 text-gray-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Kontrol Filter & Bilah Pencarian -->
    <form id="submissions-filter-form" action="{{ route('author.submissions.index') }}" method="GET" class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200 space-y-2.5 sm:space-y-0 sm:flex sm:items-center sm:gap-3">
        <!-- Parameter Tab Aktif -->
        <input type="hidden" name="tab" id="filter_tab" value="{{ $filters['tab'] ?? 'semua' }}">

        <!-- Kolom Input Pencarian dengan Debounce Live Search & Tombol Clear -->
        <div class="relative flex-1">
            <label for="filter_search" class="block mb-1 text-xs font-medium text-gray-700">Cari naskah</label>
            <div class="absolute top-6 bottom-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg id="search-icon" class="w-4 h-4 transition-transform duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <svg id="search-spinner" class="hidden w-4 h-4 animate-spin text-brand-maroon" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <input
                type="text"
                name="search"
                id="filter_search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Cari judul naskah atau ID pengajuan..."
                autocomplete="off"
                class="w-full pl-9 pr-9 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition duration-150"
            >
            <button
                type="button"
                id="search-clear-btn"
                class="{{ empty($filters['search']) ? 'hidden' : '' }} absolute top-6 bottom-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-hidden cursor-pointer transition"
                title="Hapus pencarian"
                aria-label="Hapus kata kunci pencarian"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-2.5">
            <!-- Custom Dropdown Filter Tahun -->
            <div class="relative custom-dropdown-container" data-filter="year" data-native-dropdown data-dropdown="Tahun">
                <label for="filter_year" class="block mb-1 text-xs font-medium text-gray-700">Tahun</label>
                <select name="year" id="filter_year" class="custom-dropdown-native w-full min-h-11 px-3 rounded-lg border border-gray-300 bg-white text-sm">
                    <option value="semua">Semua Tahun</option>
                    @foreach($viewModel->availableYears() as $year)
                        <option value="{{ $year }}" {{ ($filters['year'] ?? '') === $year ? 'selected' : '' }}>
                            Tahun {{ $year }}
                        </option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="custom-dropdown-btn w-full min-h-11 text-xs sm:text-sm bg-gray-50 hover:bg-gray-100 border border-gray-200 hover:border-gray-300 rounded-lg px-2.5 sm:px-3 py-2 text-gray-700 font-medium flex items-center justify-between gap-2 transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                >
                    <span class="custom-dropdown-label truncate">
                        {{ !empty($filters['year']) && $filters['year'] !== 'semua' ? 'Tahun ' . $filters['year'] : 'Semua Tahun' }}
                    </span>
                    <svg class="custom-dropdown-chevron w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top-left absolute left-0 top-full mt-1.5 min-w-35 w-full sm:w-auto bg-white rounded-lg border border-gray-200 shadow-lg p-1.5 z-30 space-y-0.5">
                    <div
                        class="custom-dropdown-item flex items-center justify-between gap-2 px-2.5 py-1.5 text-xs sm:text-sm rounded-lg cursor-pointer transition {{ ($filters['year'] ?? 'semua') === 'semua' ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                        data-value="semua"
                        data-label="Semua Tahun"
                        role="option"
                    >
                        <span>Semua Tahun</span>
                        <svg class="custom-dropdown-check w-3.5 h-3.5 text-brand-maroon {{ ($filters['year'] ?? 'semua') === 'semua' ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    @foreach($viewModel->availableYears() as $year)
                        <div
                            class="custom-dropdown-item flex items-center justify-between gap-2 px-2.5 py-1.5 text-xs sm:text-sm rounded-lg cursor-pointer transition {{ ($filters['year'] ?? '') === $year ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                            data-value="{{ $year }}"
                            data-label="Tahun {{ $year }}"
                            role="option"
                        >
                            <span>Tahun {{ $year }}</span>
                            <svg class="custom-dropdown-check w-3.5 h-3.5 text-brand-maroon {{ ($filters['year'] ?? '') === $year ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Custom Dropdown Filter Seksi Jurnal -->
            <div class="relative custom-dropdown-container" data-filter="type" data-native-dropdown data-dropdown="Seksi jurnal">
                <label for="filter_type" class="block mb-1 text-xs font-medium text-gray-700">Seksi jurnal</label>
                <select name="type" id="filter_type" class="custom-dropdown-native w-full min-h-11 px-3 rounded-lg border border-gray-300 bg-white text-sm">
                    <option value="semua">Semua Seksi</option>
                    @foreach($viewModel->articleTypes() as $type)
                        <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="custom-dropdown-btn w-full min-h-11 text-xs sm:text-sm bg-gray-50 hover:bg-gray-100 border border-gray-200 hover:border-gray-300 rounded-lg px-2.5 sm:px-3 py-2 text-gray-700 font-medium flex items-center justify-between gap-2 transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                >
                    <span class="custom-dropdown-label truncate">
                        {{ !empty($filters['type']) && $filters['type'] !== 'semua' ? $filters['type'] : 'Semua Seksi' }}
                    </span>
                    <svg class="custom-dropdown-chevron w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top-left absolute left-0 top-full mt-1.5 min-w-42.5 w-full sm:w-auto bg-white rounded-lg border border-gray-200 shadow-lg p-1.5 z-30 space-y-0.5">
                    <div
                        class="custom-dropdown-item flex items-center justify-between gap-2 px-2.5 py-1.5 text-xs sm:text-sm rounded-lg cursor-pointer transition {{ ($filters['type'] ?? 'semua') === 'semua' ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                        data-value="semua"
                        data-label="Semua Seksi"
                        role="option"
                    >
                        <span>Semua Seksi</span>
                        <svg class="custom-dropdown-check w-3.5 h-3.5 text-brand-maroon {{ ($filters['type'] ?? 'semua') === 'semua' ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    @foreach($viewModel->articleTypes() as $type)
                        <div
                            class="custom-dropdown-item flex items-center justify-between gap-2 px-2.5 py-1.5 text-xs sm:text-sm rounded-lg cursor-pointer transition {{ ($filters['type'] ?? '') === $type ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                            data-value="{{ $type }}"
                            data-label="{{ $type }}"
                            role="option"
                        >
                            <span>{{ $type }}</span>
                            <svg class="custom-dropdown-check w-3.5 h-3.5 text-brand-maroon {{ ($filters['type'] ?? '') === $type ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Custom Dropdown Pengurutan & Reset -->
            <div class="col-span-2 sm:col-auto flex items-center gap-2">
                <div class="relative custom-dropdown-container flex-1 sm:flex-none" data-filter="sort" data-native-dropdown data-dropdown="Urutkan">
                    <label for="filter_sort" class="block mb-1 text-xs font-medium text-gray-700">Urutkan</label>
                    <select name="sort" id="filter_sort" class="custom-dropdown-native w-full min-h-11 px-3 rounded-lg border border-gray-300 bg-white text-sm">
                        <option value="-updated_at" {{ ($filters['sort'] ?? '') === '-updated_at' ? 'selected' : '' }}>Pembaruan Terkini</option>
                        <option value="updated_at" {{ ($filters['sort'] ?? '') === 'updated_at' ? 'selected' : '' }}>Terlama</option>
                        <option value="title" {{ ($filters['sort'] ?? '') === 'title' ? 'selected' : '' }}>Judul (A-Z)</option>
                    </select>
                    <button
                        type="button"
                        class="custom-dropdown-btn w-full min-h-11 text-xs sm:text-sm bg-gray-50 hover:bg-gray-100 border border-gray-200 hover:border-gray-300 rounded-lg px-2.5 sm:px-3 py-2 text-gray-700 font-medium flex items-center justify-between gap-2 transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
                        <span class="custom-dropdown-label truncate">
                            @php
                                $sortMap = [
                                    '-updated_at' => 'Pembaruan Terkini',
                                    'updated_at' => 'Terlama',
                                    'title' => 'Judul (A-Z)'
                                ];
                                $currentSort = $filters['sort'] ?? '-updated_at';
                            @endphp
                            {{ $sortMap[$currentSort] ?? 'Pembaruan Terkini' }}
                        </span>
                        <svg class="custom-dropdown-chevron w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top-right absolute right-0 top-full mt-1.5 min-w-40 w-full sm:w-auto bg-white rounded-lg border border-gray-200 shadow-lg p-1.5 z-30 space-y-0.5">
                        @foreach(['-updated_at' => 'Pembaruan Terkini', 'updated_at' => 'Terlama', 'title' => 'Judul (A-Z)'] as $sVal => $sLabel)
                            <div
                                class="custom-dropdown-item flex items-center justify-between gap-2 px-2.5 py-1.5 text-xs sm:text-sm rounded-lg cursor-pointer transition {{ ($filters['sort'] ?? '-updated_at') === $sVal ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                data-value="{{ $sVal }}"
                                data-label="{{ $sLabel }}"
                                role="option"
                            >
                                <span>{{ $sLabel }}</span>
                                <svg class="custom-dropdown-check w-3.5 h-3.5 text-brand-maroon {{ ($filters['sort'] ?? '-updated_at') === $sVal ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tombol Reset Filter -->
                <div id="reset-filter-container" class="{{ (!empty($filters['search']) || (($filters['year'] ?? 'semua') !== 'semua') || (($filters['type'] ?? 'semua') !== 'semua')) ? '' : 'hidden' }}">
                    <a
                        href="{{ route('author.submissions.index', ['tab' => $filters['tab'] ?? 'semua']) }}"
                        id="btn-reset-filter"
                        class="p-2 min-w-11 min-h-11 flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition shrink-0 cursor-pointer"
                        title="Reset Filter"
                        aria-label="Reset Filter"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Kontainer Tabel & Kartu Responsif Naskah Penulis -->
    <div id="submissions-table-container" class="relative bg-white rounded-lg border border-gray-200 overflow-hidden">
        <!-- Top Loading Indicator Bar -->
        <div id="table-loading-bar" class="hidden absolute top-0 left-0 right-0 h-0.5 bg-brand-maroon/20 overflow-hidden z-20">
            <div class="h-full bg-brand-maroon w-full animate-pulse"></div>
        </div>

        <div id="submissions-table-content" class="transition-opacity duration-150 ease-in-out">
            @include('author.submissions.partials.table', ['submissions' => $submissions, 'filters' => $filters])
        </div>
    </div>

    <!-- 4 Kartu Metrik Ringkasan (Grid 2x2 Kompak di Mobile, 4 Kolom di Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        @foreach($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-5 flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-gray-700 leading-tight">{{ $metric['title'] }}</span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg {{ $metric['color_class'] }} flex items-center justify-center shrink-0">
                        @if($metric['icon'] === 'document-text')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        @elseif($metric['icon'] === 'exclamation-circle')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        @elseif($metric['icon'] === 'cog')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @else
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                </div>
                <div class="mt-2 sm:mt-3">
                    <span class="text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums tracking-tight">{{ $metric['value'] }}</span>
                </div>
                <p class="mt-0.5 sm:mt-1 text-xs text-gray-600" title="{{ $metric['description'] }}">{{ $metric['description'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Strip Panduan Alur Kerja Editorial (Ringkas & Bersih) -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded bg-red-50 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-900">
                    Panduan Alur Kerja Editorial & Siklus Naskah
                </h2>
            </div>
            <a href="{{ route('journal.about.submissions') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-maroon hover:text-brand-maroon-hover transition">
                <span>Buku Panduan Penulis BRIDA</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>

        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
            @foreach($viewModel->editorialWorkflowSteps() as $step)
                <div class="p-3 rounded-md bg-gray-50/80 border border-gray-200/50 flex flex-col justify-between">
                    <h3 class="text-xs font-bold text-gray-900">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-[11px] text-gray-500 leading-snug line-clamp-2" title="{{ $step['description'] }}">
                        {{ $step['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('submissions-filter-form');
    const tabInput = document.getElementById('filter_tab');
    const searchInput = document.getElementById('filter_search');
    const searchIcon = document.getElementById('search-icon');
    const searchSpinner = document.getElementById('search-spinner');
    const searchClearBtn = document.getElementById('search-clear-btn');
    const resetContainer = document.getElementById('reset-filter-container');
    const resetBtn = document.getElementById('btn-reset-filter');
    const exportBtn = document.getElementById('export-csv-btn');
    const tableContainer = document.getElementById('submissions-table-container');
    const tableContent = document.getElementById('submissions-table-content');
    const loadingBar = document.getElementById('table-loading-bar');
    const tabLinks = document.querySelectorAll('.submission-tab-link');

    let abortController = null;
    let searchDebounceTimer = null;

    document.addEventListener('dropdown:change', event => {
        if (event.target.matches('.custom-dropdown-container[data-filter]')) triggerFilter(true);
    });

    // --- Tab Navigation Clicks ---
    tabLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tabKey = link.getAttribute('data-tab');
            if (!tabKey) return;

            tabInput.value = tabKey;

            // Optimistic UI for tabs
            tabLinks.forEach(l => {
                const badge = l.querySelector('.submission-tab-badge');
                if (l === link) {
                    l.className = 'submission-tab-link whitespace-nowrap py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 sm:gap-2 transition duration-150 shrink-0 border-brand-maroon text-brand-maroon is-active';
                    if (badge) badge.className = 'submission-tab-badge inline-flex items-center justify-center px-1.5 sm:px-2 py-0.5 text-[11px] sm:text-xs font-bold rounded-full tabular-nums transition duration-150 bg-brand-maroon text-white';
                } else {
                    l.className = 'submission-tab-link whitespace-nowrap py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 sm:gap-2 transition duration-150 shrink-0 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                    if (badge) badge.className = 'submission-tab-badge inline-flex items-center justify-center px-1.5 sm:px-2 py-0.5 text-[11px] sm:text-xs font-bold rounded-full tabular-nums transition duration-150 bg-gray-100 text-gray-600';
                }
            });

            triggerFilter(true);
        });
    });

    // --- Search Input Handlers ---
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            if (searchClearBtn) {
                if (query.length > 0) {
                    searchClearBtn.classList.remove('hidden');
                } else {
                    searchClearBtn.classList.add('hidden');
                }
            }

            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                triggerFilter(false); // replaceState while typing
            }, 250);
        });

        // Prevent standard form submit on Enter
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            triggerFilter(true);
        });
    }

    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchClearBtn.classList.add('hidden');
            searchInput.focus();
            triggerFilter(true);
        });
    }

    // --- Reset Filter Handler ---
    function resetAllFilters() {
        if (searchInput) {
            searchInput.value = '';
            if (searchClearBtn) searchClearBtn.classList.add('hidden');
        }

        // Reset Year
        const yearContainer = document.querySelector('[data-filter="year"]');
        if (yearContainer) {
            const select = yearContainer.querySelector('select');
            const label = yearContainer.querySelector('.custom-dropdown-label');
            const items = yearContainer.querySelectorAll('.custom-dropdown-item');
            if (select) select.value = 'semua';
            if (label) label.textContent = 'Semua Tahun';
            items.forEach(it => {
                const check = it.querySelector('.custom-dropdown-check');
                if (it.getAttribute('data-value') === 'semua') {
                    it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.remove('hidden');
                } else {
                    it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.add('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.add('hidden');
                }
            });
        }

        // Reset Type
        const typeContainer = document.querySelector('[data-filter="type"]');
        if (typeContainer) {
            const select = typeContainer.querySelector('select');
            const label = typeContainer.querySelector('.custom-dropdown-label');
            const items = typeContainer.querySelectorAll('.custom-dropdown-item');
            if (select) select.value = 'semua';
            if (label) label.textContent = 'Semua Seksi';
            items.forEach(it => {
                const check = it.querySelector('.custom-dropdown-check');
                if (it.getAttribute('data-value') === 'semua') {
                    it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.remove('hidden');
                } else {
                    it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.add('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.add('hidden');
                }
            });
        }

        if (resetContainer) {
            resetContainer.classList.add('hidden');
        }

        triggerFilter(true);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', (e) => {
            e.preventDefault();
            resetAllFilters();
        });
    }

    // Delegated click for reset filter inside empty state
    document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-reset-filter-trigger')) {
            e.preventDefault();
            resetAllFilters();
        }
    });

    // --- Core AJAX Filter Engine ---
    function triggerFilter(isPushState = true) {
        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        const params = new URLSearchParams();
        const tabVal = tabInput ? tabInput.value : 'semua';
        const searchVal = searchInput ? searchInput.value.trim() : '';
        const yearSelect = document.getElementById('filter_year');
        const yearVal = yearSelect ? yearSelect.value : 'semua';
        const typeSelect = document.getElementById('filter_type');
        const typeVal = typeSelect ? typeSelect.value : 'semua';
        const sortSelect = document.getElementById('filter_sort');
        const sortVal = sortSelect ? sortSelect.value : '-updated_at';

        params.set('tab', tabVal);
        if (searchVal !== '') {
            params.set('search', searchVal);
        }
        params.set('year', yearVal);
        params.set('type', typeVal);
        params.set('sort', sortVal);

        const baseUrl = form.getAttribute('action') || window.location.pathname;
        const newUrl = baseUrl + '?' + params.toString();

        if (isPushState) {
            window.history.pushState({ path: newUrl }, '', newUrl);
        } else {
            window.history.replaceState({ path: newUrl }, '', newUrl);
        }

        // Soft Transition State: reduce opacity without layout shift
        if (tableContent) {
            tableContent.classList.add('opacity-40', 'pointer-events-none');
        }
        if (loadingBar) {
            loadingBar.classList.remove('hidden');
        }
        if (searchSpinner && searchIcon) {
            searchSpinner.classList.remove('hidden');
            searchIcon.classList.add('hidden');
        }

        fetch(newUrl, {
            signal: abortController.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network error: ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.table_html && tableContent) {
                tableContent.innerHTML = data.table_html;
            }

            if (data.export_url && exportBtn) {
                exportBtn.href = data.export_url;
            }

            if (resetContainer) {
                if (data.has_filters) {
                    resetContainer.classList.remove('hidden');
                } else {
                    resetContainer.classList.add('hidden');
                }
            }

            // Sync tab counts if provided
            if (data.tabs && Array.isArray(data.tabs)) {
                data.tabs.forEach(t => {
                    const tLink = document.querySelector(`.submission-tab-link[data-tab="${t.key}"]`);
                    if (tLink) {
                        const badge = tLink.querySelector('.submission-tab-badge');
                        if (badge) badge.textContent = t.count;
                    }
                });
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            console.error('Filter execution error:', err);
            // Fallback gracefully to standard form GET submission if unexpected error
            form.submit();
        })
        .finally(() => {
            if (tableContent) {
                tableContent.classList.remove('opacity-40', 'pointer-events-none');
            }
            if (loadingBar) {
                loadingBar.classList.add('hidden');
            }
            if (searchSpinner && searchIcon) {
                searchSpinner.classList.add('hidden');
                searchIcon.classList.remove('hidden');
            }
        });
    }

    // --- Browser Back/Forward (Popstate) Sync ---
    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab') || 'semua';
        const searchParam = urlParams.get('search') || '';
        const yearParam = urlParams.get('year') || 'semua';
        const typeParam = urlParams.get('type') || 'semua';
        const sortParam = urlParams.get('sort') || '-updated_at';

        if (tabInput) tabInput.value = tabParam;
        if (searchInput) {
            searchInput.value = searchParam;
            if (searchClearBtn) {
                if (searchParam.length > 0) searchClearBtn.classList.remove('hidden');
                else searchClearBtn.classList.add('hidden');
            }
        }

        // Sync tab visuals
        tabLinks.forEach(l => {
            const isMatch = (l.getAttribute('data-tab') === tabParam);
            const badge = l.querySelector('.submission-tab-badge');
            if (isMatch) {
                l.className = 'submission-tab-link whitespace-nowrap py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 sm:gap-2 transition duration-150 shrink-0 border-brand-maroon text-brand-maroon is-active';
                if (badge) badge.className = 'submission-tab-badge inline-flex items-center justify-center px-1.5 sm:px-2 py-0.5 text-[11px] sm:text-xs font-bold rounded-full tabular-nums transition duration-150 bg-brand-maroon text-white';
            } else {
                l.className = 'submission-tab-link whitespace-nowrap py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 sm:gap-2 transition duration-150 shrink-0 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                if (badge) badge.className = 'submission-tab-badge inline-flex items-center justify-center px-1.5 sm:px-2 py-0.5 text-[11px] sm:text-xs font-bold rounded-full tabular-nums transition duration-150 bg-gray-100 text-gray-600';
            }
        });

        // Sync Year dropdown
        const yearContainer = document.querySelector('[data-filter="year"]');
        if (yearContainer) {
            const select = yearContainer.querySelector('select');
            const label = yearContainer.querySelector('.custom-dropdown-label');
            const items = yearContainer.querySelectorAll('.custom-dropdown-item');
            if (select) select.value = yearParam;
            if (label) label.textContent = yearParam !== 'semua' ? 'Tahun ' + yearParam : 'Semua Tahun';
            items.forEach(it => {
                const check = it.querySelector('.custom-dropdown-check');
                if (it.getAttribute('data-value') === yearParam) {
                    it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.remove('hidden');
                } else {
                    it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.add('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.add('hidden');
                }
            });
        }

        // Sync Type dropdown
        const typeContainer = document.querySelector('[data-filter="type"]');
        if (typeContainer) {
            const select = typeContainer.querySelector('select');
            const label = typeContainer.querySelector('.custom-dropdown-label');
            const items = typeContainer.querySelectorAll('.custom-dropdown-item');
            if (select) select.value = typeParam;
            if (label) label.textContent = typeParam !== 'semua' ? typeParam : 'Semua Seksi';
            items.forEach(it => {
                const check = it.querySelector('.custom-dropdown-check');
                if (it.getAttribute('data-value') === typeParam) {
                    it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.remove('hidden');
                } else {
                    it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.add('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.add('hidden');
                }
            });
        }

        // Sync Sort dropdown
        const sortContainer = document.querySelector('[data-filter="sort"]');
        if (sortContainer) {
            const select = sortContainer.querySelector('select');
            const label = sortContainer.querySelector('.custom-dropdown-label');
            const items = sortContainer.querySelectorAll('.custom-dropdown-item');
            const sortMap = { '-updated_at': 'Pembaruan Terkini', 'updated_at': 'Terlama', 'title': 'Judul (A-Z)' };
            if (select) select.value = sortParam;
            if (label) label.textContent = sortMap[sortParam] || 'Pembaruan Terkini';
            items.forEach(it => {
                const check = it.querySelector('.custom-dropdown-check');
                if (it.getAttribute('data-value') === sortParam) {
                    it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.remove('hidden');
                } else {
                    it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                    it.classList.add('text-gray-700', 'hover:bg-gray-50');
                    if (check) check.classList.add('hidden');
                }
            });
        }

        // Execute fetch without pushState
        triggerFilter(false);
    });
});
</script>
@endpush
