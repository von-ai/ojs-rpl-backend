@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Jurnal Riset & Inovasi BRIDA Makassar (JRID-MKS) — Beranda')

@section('content')
    <!-- Call For Papers Announcement Banner -->
    <x-journal.announcement-banner :announcement="$viewModel->announcement()" />

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-6 space-y-8">

        <!-- 1. Hero Catalog Search & Submit CTA Card -->
        <section>
            <x-journal.search-hero :categories="$viewModel->categories()" />
        </section>

        <!-- 2. Main Content Grid: 2-Cols (Left: Issues & Articles, Right: Sidebar) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Main Content Column (Span 2) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Current Issue Highlight (Vol. 3 No. 2) -->
                <x-journal.current-issue :issue="$viewModel->currentIssue()" />

                <!-- Search/Category Filter Active State Banner (If filtering) -->
                @if ($viewModel->searchQuery || $viewModel->selectedCategory)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between text-xs text-amber-900">
                        <div class="flex items-center gap-2">
                            <span class="font-bold">Filter Aktif:</span>
                            @if ($viewModel->searchQuery)
                                <span class="bg-amber-100 px-2 py-0.5 rounded border border-amber-300">Pencarian: "{{ $viewModel->searchQuery }}"</span>
                            @endif
                            @if ($viewModel->selectedCategory)
                                <span class="bg-amber-100 px-2 py-0.5 rounded border border-amber-300">Kategori: {{ $viewModel->selectedCategory }}</span>
                            @endif
                        </div>
                        <a href="{{ route('journal.index') }}" class="font-bold text-brand-maroon hover:underline">
                            Reset Filter ✕
                        </a>
                    </div>
                @endif

                <!-- Articles List Section -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <h2 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                            <span>Daftar Artikel Edisi Ini</span>
                            <span class="text-xs font-normal text-gray-500">({{ count($viewModel->articles()) }} Artikel)</span>
                        </h2>
                        <span class="text-xs text-gray-500 font-medium">Urutkan: Terbaru</span>
                    </div>

                    <div class="space-y-4">
                        @forelse ($viewModel->articles() as $article)
                            <x-journal.article-card :article="$article" />
                        @empty
                            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-xs">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="font-bold text-gray-700">Tidak ada artikel yang cocok dengan filter atau kata kunci tersebut.</p>
                                <a href="{{ route('journal.index') }}" class="mt-2 inline-block text-brand-maroon font-bold hover:underline">Tampilkan seluruh artikel</a>
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

            </div>

            <!-- Right Sidebar Column (Span 1) -->
            <aside class="lg:col-span-1">
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
