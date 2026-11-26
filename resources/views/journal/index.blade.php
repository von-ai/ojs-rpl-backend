@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar') . ' — Beranda')

@section('content')
    <!-- Call For Papers Announcement Banner -->
    <x-journal.announcement-banner :announcement="$viewModel->announcement()" />

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-4 sm:space-y-6">

        <!-- 1. Hero Catalog Search & Submit CTA Card -->
        <section>
            <x-journal.search-hero :categories="$viewModel->categories()" />
        </section>

        <!-- 2. Main Content Grid: 2-Cols (Left: Issues & Articles, Right: Sidebar) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-start">
            
            <!-- Left Main Content Column (Span 2) -->
            <div class="lg:col-span-2 min-w-0 space-y-5">
                
                <!-- Current Issue Highlight (Dari Database) -->
                @if (!empty($viewModel->currentIssue()))
                    <x-journal.current-issue :issue="$viewModel->currentIssue()" />
                @endif

                <!-- Search/Category Filter Active State Banner (If filtering) -->
                @if ($viewModel->searchQuery || $viewModel->selectedCategory)
                    <div class="bg-amber-50 border border-amber-200 rounded-md p-3.5 sm:p-4 flex flex-wrap items-center justify-between gap-2.5 text-xs text-amber-900">
                        <div class="flex flex-wrap items-center gap-2 min-w-0">
                            <span class="font-bold">Filter Aktif:</span>
                            @if ($viewModel->searchQuery)
                                <span class="bg-amber-100 px-2 py-0.5 rounded border border-amber-300 truncate max-w-full">Pencarian: "{{ $viewModel->searchQuery }}"</span>
                            @endif
                            @if ($viewModel->selectedCategory)
                                <span class="bg-amber-100 px-2 py-0.5 rounded border border-amber-300 truncate max-w-full">Kategori: {{ $viewModel->selectedCategory }}</span>
                            @endif
                        </div>
                        <a href="{{ route('journal.index') }}" class="font-bold text-brand-maroon hover:underline shrink-0">
                            Reset Filter ✕
                        </a>
                    </div>
                @endif

                <!-- Articles List Section -->
                <section id="artikel-terbit" class="space-y-4 scroll-mt-6">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <span>Artikel Terbit</span>
                            <span class="text-xs font-normal text-gray-500">({{ count($viewModel->articles()) }})</span>
                        </h2>
                        <span class="text-xs text-gray-500 font-normal">Terbaru</span>
                    </div>

                    <div class="space-y-4">
                        @forelse ($viewModel->articles() as $article)
                            <x-journal.article-card :article="$article" />
                        @empty
                            <div class="bg-white rounded-xl border border-gray-200/90 p-8 sm:p-10 text-center text-xs">
                                <div class="w-12 h-12 rounded-full bg-red-50 text-brand-maroon mx-auto flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-gray-900 text-sm">Belum Ada Artikel Diterbitkan</h3>
                                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                                    Tidak ada artikel yang cocok dengan filter atau kata kunci pencarian yang dipilih.
                                </p>
                                <a href="{{ route('journal.index') }}" class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-lg border border-red-200/80 transition-all active:scale-[0.98]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Tampilkan Seluruh Artikel</span>
                                </a>
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- Articles In Press (Terbitan Mendatang) -->
                <section>
                    <x-journal.articles-in-press :inPress="$viewModel->articlesInPress()" />
                </section>

                <!-- Issue Archives (Arsip Edisi Terdahulu) -->
                <section>
                    <x-journal.issue-archives :archives="$viewModel->archives()" />
                </section>

                <!-- Direktori / Jurnal Lain dalam Portal BRIDA -->
                @if(count($viewModel->availableJournals()) > 1)
                    <section class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 space-y-4 shadow-2xs">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <h2 class="text-sm sm:text-base font-bold text-gray-900">Jurnal Ilmiah Terkait dalam Portal BRIDA</h2>
                            </div>
                            <a href="{{ route('journal.journals.index') }}" class="text-xs font-semibold text-brand-maroon hover:underline">
                                Buka Katalog Lengkap &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($viewModel->availableJournals() as $aj)
                                <a href="{{ $aj['url'] }}" class="p-3 rounded-lg border border-gray-200 hover:border-brand-maroon/40 hover:bg-red-50/20 transition flex items-start gap-3 group">
                                    <div class="w-8 h-8 rounded-lg bg-red-50 border border-red-200 text-brand-maroon flex items-center justify-center font-bold text-xs shrink-0 group-hover:scale-105 transition-transform">
                                        {{ substr($aj['initials'], 0, 3) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="text-xs font-bold text-gray-900 group-hover:text-brand-maroon transition-colors truncate">{{ $aj['name'] }}</h3>
                                            @if($aj['is_current'])
                                                <span class="inline-flex px-1.5 py-0.2 rounded text-[9px] font-bold bg-brand-maroon text-white shrink-0">Aktif</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Inisial: <span class="font-mono font-medium text-gray-700">{{ $aj['initials'] }}</span></p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>

            <!-- Right Sidebar Column (Span 1) -->
            <aside class="lg:col-span-1 min-w-0">
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
