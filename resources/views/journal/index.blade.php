@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar') . ' — Beranda')

@section('content')
    <!-- Call For Papers Announcement Banner -->
    <x-journal.announcement-banner :announcement="$viewModel->announcement()" />

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-5 space-y-5">

        <!-- 1. Hero Catalog Search & Submit CTA Card -->
        <section>
            <x-journal.search-hero :categories="$viewModel->categories()" />
        </section>

        <!-- 2. Main Content Grid: 2-Cols (Left: Issues & Articles, Right: Sidebar) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            
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
                <section class="space-y-4">
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
                            <div class="bg-white rounded-md border border-gray-200 p-8 text-center text-gray-500 text-xs">
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
