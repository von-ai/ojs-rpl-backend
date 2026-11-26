@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Katalog & Direktori Jurnal Ilmiah — Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <x-journal.breadcrumb :crumbs="[['label' => 'Katalog Jurnal Ilmiah']]" />

    <!-- Header Direktori -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
        <div class="space-y-1.5 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-maroon uppercase tracking-normal">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Portal Penerbitan Berkala Ilmiah
            </span>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                Direktori Jurnal Ilmiah BRIDA Kota Makassar
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                Pusat pangkalan terbitan berkala ilmiah resmi Pemerintah Kota Makassar. Jelajahi ragam jurnal hasil riset empiris, kajian kebijakan publik, teknologi, tata kelola lingkungan, dan inovasi daerah.
            </p>
        </div>

        <!-- Pencarian Jurnal Cepat -->
        <form action="{{ route('journal.journals.index') }}" method="GET" class="w-full md:w-80 shrink-0">
            <div class="relative">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari nama jurnal atau fokus..."
                    class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                @if(!empty($search))
                    <a href="{{ route('journal.journals.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs font-bold" title="Hapus Pencarian">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Grid: Daftar Jurnal (2/3 Kiri) & Sidebar (1/3 Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <!-- Kolom Daftar Jurnal -->
        <div class="lg:col-span-2 min-w-0 space-y-6">

            @if(!empty($search))
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-900 flex items-center justify-between gap-2">
                    <span>Menampilkan hasil pencarian untuk: <strong>"{{ $search }}"</strong></span>
                    <a href="{{ route('journal.journals.index') }}" class="font-semibold text-brand-maroon hover:underline">Reset ✕</a>
                </div>
            @endif

            <div class="space-y-5">
                @forelse($journals as $j)
                    <div class="bg-white rounded-xl border {{ $j['is_current'] ? 'border-brand-maroon/40 ring-1 ring-brand-maroon/20' : 'border-gray-200' }} p-5 sm:p-6 shadow-2xs hover:shadow-md transition-all space-y-4">
                        
                        <!-- Header Kartu Jurnal -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <!-- Badge Inisial Jurnal -->
                                <div class="w-12 h-12 rounded-xl bg-red-50 border border-red-200 text-brand-maroon flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                                    {{ substr($j['initials'], 0, 4) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="text-base sm:text-lg font-bold text-gray-900 leading-snug">
                                            <a href="{{ $j['url'] }}" class="hover:text-brand-maroon transition-colors">
                                                {{ $j['name'] }}
                                            </a>
                                        </h2>
                                        @if($j['is_current'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-brand-maroon text-white shrink-0">
                                                Jurnal yang Sedang Dibuka
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">
                                        {{ $j['publisher'] }}
                                    </p>
                                </div>
                            </div>

                            <!-- Label APC & Frekuensi -->
                            <div class="flex items-center gap-1.5 shrink-0 sm:self-start">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium {{ $j['is_paid'] ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                    {{ $j['apc_label'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Metadata ISSN & Statistik -->
                        <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs text-gray-600 bg-gray-50/80 rounded-lg p-2.5 sm:px-3 border border-gray-100">
                            @if($j['issn'])
                                <div>
                                    <span class="text-gray-400 font-medium">p-ISSN:</span>
                                    <strong class="font-mono text-gray-800">{{ $j['issn'] }}</strong>
                                </div>
                                <span class="text-gray-300 hidden sm:inline">•</span>
                            @endif

                            @if($j['eissn'])
                                <div>
                                    <span class="text-gray-400 font-medium">e-ISSN:</span>
                                    <strong class="font-mono text-gray-800">{{ $j['eissn'] }}</strong>
                                </div>
                                <span class="text-gray-300 hidden sm:inline">•</span>
                            @endif

                            <div>
                                <span class="text-gray-400 font-medium">Frekuensi:</span>
                                <span class="text-gray-800">{{ $j['frequency'] }}</span>
                            </div>

                            <span class="text-gray-300 hidden sm:inline">•</span>

                            <div>
                                <span class="text-gray-400 font-medium">Koleksi:</span>
                                <span class="font-semibold text-gray-900 tabular-nums">{{ $j['published_issues_count'] }}</span> Edisi
                                (<span class="font-semibold text-gray-900 tabular-nums">{{ $j['published_articles_count'] }}</span> Artikel)
                            </div>
                        </div>

                        <!-- Fokus & Deskripsi Singkat -->
                        @if(!empty($j['description']))
                            <p class="text-xs text-gray-600 leading-relaxed line-clamp-2">
                                {{ $j['description'] }}
                            </p>
                        @endif

                        <!-- Cuplikan Edisi Terkini Jika Ada -->
                        @if($j['current_issue'])
                            <div class="text-[11px] text-gray-600 flex items-center gap-1.5 pt-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="font-medium text-gray-500">Terbitan Terbaru:</span>
                                <a href="{{ $j['current_issue_url'] }}" class="font-semibold text-gray-900 hover:text-brand-maroon transition-colors hover:underline truncate">
                                    Vol. {{ $j['current_issue']['volume'] }} No. {{ $j['current_issue']['number'] }} ({{ $j['current_issue']['year'] }}) — {{ $j['current_issue']['title'] }}
                                </a>
                            </div>
                        @endif

                        <!-- Aksi Tombol Tautan -->
                        <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a
                                    href="{{ $j['archives_url'] }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition"
                                >
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span>Lihat Arsip Edisi</span>
                                </a>

                                <a
                                    href="{{ $j['about_url'] }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition"
                                >
                                    <span>Tentang Jurnal</span>
                                </a>
                            </div>

                            <a
                                href="{{ $j['url'] }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-brand-maroon text-white hover:bg-brand-maroon-hover transition shadow-2xs group"
                            >
                                <span>Kunjungi Portal Jurnal</span>
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                    </div>
                @empty
                    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-xs space-y-3">
                        <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <p class="font-bold text-gray-800 text-sm">Tidak Ada Jurnal yang Ditemukan</p>
                        <p class="max-w-md mx-auto text-gray-500">
                            Tidak ada jurnal yang sesuai dengan kata kunci pencarian Anda. Silakan coba kata kunci lain atau reset filter pencarian.
                        </p>
                        <a href="{{ route('journal.journals.index') }}" class="inline-flex items-center gap-1 text-brand-maroon font-semibold hover:underline">
                            Reset Pencarian ✕
                        </a>
                    </div>
                @endforelse
            </div>

        </div>

        <!-- Kolom Samping (1 Kolom Kanan) -->
        <aside class="lg:col-span-1 min-w-0 space-y-6">
            <x-journal.sidebar
                :publisherInfo="$viewModel->publisherInfo()"
                :indexingData="$viewModel->indexingData()"
                :impactMetrics="$viewModel->impactMetrics()"
                :announcements="$viewModel->editorialAnnouncements()"
            />
        </aside>

    </div>

</div>
@endsection
