@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Katalog & Direktori Jurnal Ilmiah — Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar')

@section('content')
    <!-- Header Banner Editorial Konsisten dengan Beranda -->
    <x-journal.page-banner
        eyebrow="Portal Penerbitan Berkala Ilmiah"
        title="Direktori Jurnal Ilmiah BRIDA Kota Makassar"
        description="Pusat pangkalan terbitan berkala ilmiah resmi Pemerintah Kota Makassar. Jelajahi ragam jurnal hasil riset empiris, kajian kebijakan publik, teknologi, tata kelola lingkungan, dan inovasi daerah."
        :crumbs="[['label' => 'Katalog Jurnal Ilmiah']]"
    >
        <form action="{{ route('journal.journals.index') }}" method="GET" role="search" class="max-w-2xl flex flex-col sm:flex-row items-stretch gap-2 bg-black/25 p-1.5 rounded-xl border border-white/45 hover:border-white/70 focus-within:border-white focus-within:bg-black/35 transition">
            <div class="relative flex-1 min-w-0">
                <svg class="w-5 h-5 text-white/80 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    aria-label="Cari nama jurnal, inisial, ISSN, atau fokus kajian"
                    placeholder="Cari nama jurnal, inisial, ISSN, atau fokus kajian..."
                    class="w-full min-h-11 pl-11 pr-8 py-2 text-sm bg-transparent border-0 rounded-lg focus:outline-none focus:ring-0 text-white placeholder:text-white/70"
                >
                @if(!empty($search))
                    <a href="{{ route('journal.journals.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-white/75 hover:text-white p-1 text-xs font-bold" title="Hapus Pencarian" aria-label="Hapus Pencarian">
                        ✕
                    </a>
                @endif
            </div>
            <button
                type="submit"
                class="min-h-11 px-6 py-2 rounded-lg bg-brand-maroon hover:bg-brand-maroon-hover text-sm font-semibold text-white shadow-sm transition cursor-pointer shrink-0"
            >
                Cari Jurnal
            </button>
        </form>
    </x-journal.page-banner>

    <!-- Daftar Jurnal Terbuka (Material 3 Flat Surface dengan Garis Pemisah Halus, Tanpa Sidebar) -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-divider">
                <p class="text-sm font-semibold text-gray-900 tabular-nums">
                    {{ count($journals) }} Jurnal Ilmiah Aktif
                    @if(!empty($search))
                        <span class="font-normal text-gray-600">untuk pencarian "{{ $search }}"</span>
                    @endif
                </p>
                @if(!empty($search))
                    <a href="{{ route('journal.journals.index') }}" class="text-xs font-semibold text-brand-maroon hover:underline">
                        Reset Pencarian ✕
                    </a>
                @endif
            </div>

            <div class="divide-y divide-divider">
                @forelse($journals as $j)
                    <article class="py-7 sm:py-8 flex flex-col sm:flex-row items-start gap-6 sm:gap-8">
                        <!-- Sampul Jurnal (3:4 Portrait) -->
                        <a href="{{ $j['url'] }}" class="shrink-0 transition-transform duration-200 hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-maroon rounded-md">
                            <x-journal.cover-card :journal="$j" size="md" />
                        </a>

                        <!-- Konten & Metadata Jurnal -->
                        <div class="min-w-0 flex-1 space-y-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-mono font-bold uppercase tracking-wider text-brand-maroon-hover">
                                    {{ $j['initials'] }}
                                </span>
                                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                                <span class="font-semibold {{ $j['is_paid'] ? 'text-amber-800' : 'text-emerald-700' }}">
                                    {{ $j['apc_label'] }}
                                </span>
                                @if($j['is_current'])
                                    <span class="text-gray-300" aria-hidden="true">&middot;</span>
                                    <span class="font-semibold text-gray-900">Jurnal yang Sedang Dibuka</span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 leading-snug tracking-tight">
                                    <a href="{{ $j['url'] }}" class="hover:text-brand-maroon transition-colors">
                                        {{ $j['name'] }}
                                    </a>
                                </h2>
                                <p class="text-sm text-gray-600 font-medium">
                                    {{ $j['publisher'] }}
                                </p>
                            </div>

                            <!-- Baris Metadata Teknis Rata Kiri -->
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-gray-600 tabular-nums">
                                @if($j['issn'])
                                    <span class="font-mono">p-ISSN <strong class="text-gray-900 font-semibold">{{ $j['issn'] }}</strong></span>
                                @endif
                                @if($j['eissn'])
                                    <span class="font-mono">e-ISSN <strong class="text-gray-900 font-semibold">{{ $j['eissn'] }}</strong></span>
                                @endif
                                <span>Frekuensi: <strong class="text-gray-900 font-medium">{{ $j['frequency'] }}</strong></span>
                                <span>Koleksi: <strong class="text-gray-900 font-semibold">{{ $j['published_issues_count'] }} Edisi</strong> ({{ $j['published_articles_count'] }} Artikel)</span>
                            </div>

                            @if(!empty($j['description']))
                                <p class="text-sm text-gray-700 leading-relaxed line-clamp-2 max-w-3xl text-left">
                                    {{ $j['description'] }}
                                </p>
                            @endif

                            @if(!empty($j['current_issue']))
                                <p class="text-xs text-gray-600">
                                    Edisi terkini:
                                    <a href="{{ $j['current_issue_url'] }}" class="font-semibold text-brand-navy hover:text-brand-maroon hover:underline">
                                        Vol. {{ $j['current_issue']['volume'] }} No. {{ $j['current_issue']['number'] }} ({{ $j['current_issue']['year'] }}) — {{ $j['current_issue']['title'] }}
                                    </a>
                                </p>
                            @endif

                            <!-- Aksi -->
                            <div class="pt-1 flex flex-wrap items-center gap-4">
                                <a
                                    href="{{ $j['url'] }}"
                                    class="min-h-10 px-5 py-2 rounded-lg bg-brand-maroon hover:bg-brand-maroon-hover text-xs sm:text-sm font-semibold text-white inline-flex items-center gap-1.5 transition cursor-pointer"
                                >
                                    <span>Kunjungi Portal Jurnal</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>

                                <a href="{{ $j['archives_url'] }}" class="text-xs sm:text-sm font-semibold text-gray-800 hover:text-brand-maroon transition">
                                    Lihat Arsip Edisi
                                </a>

                                <a href="{{ $j['about_url'] }}" class="text-xs sm:text-sm font-medium text-gray-600 hover:text-brand-maroon transition">
                                    Tentang Jurnal
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="py-16 text-center space-y-3">
                        <p class="text-base font-bold text-gray-900">Tidak ada jurnal yang sesuai dengan pencarian Anda.</p>
                        <p class="text-sm text-gray-600 max-w-md mx-auto">Coba kata kunci lain atau tampilkan seluruh jurnal ilmiah aktif di portal BRIDA Kota Makassar.</p>
                        <div class="pt-2">
                            <a href="{{ route('journal.journals.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-brand-maroon hover:bg-brand-maroon-hover text-sm font-semibold text-white transition">
                                Tampilkan Semua Jurnal
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
