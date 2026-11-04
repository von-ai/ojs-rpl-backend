@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', "Vol. {$issue->volume} No. {$issue->number} ({$issue->year}): {$issue->title} — " . ($journal->name ?? 'SIRIDA BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.archives') }}" class="hover:text-brand-maroon">Arsip Edisi</a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate max-w-md">Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }})</span>
    </nav>

    <!-- Header Edisi Terbitan -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">

            <!-- Book Cover Badge Mockup -->
            <div class="md:col-span-1 bg-gradient-to-b from-[#ED1C24] via-[#DE131B] to-[#BA0E16] text-white p-5 rounded-md flex flex-col justify-between aspect-3/4 relative overflow-hidden border border-[#A80B12] shadow-sm">
                <div class="text-[9px] font-bold text-white/80 uppercase tracking-widest border-b border-white/20 pb-1.5">
                    PUBLIKASI ILMIAH RESMI
                </div>
                <div class="my-auto py-2">
                    <h3 class="text-sm font-extrabold leading-tight text-white mb-1">
                        {{ $journal->name ?? 'Jurnal Riset & Inovasi Daerah' }}
                    </h3>
                    <p class="text-[10px] font-medium text-white/85">{{ $journal->publisher ?? 'BRIDA Kota Makassar' }}</p>

                    <div class="mt-3 p-2 bg-black/15 rounded border border-white/20">
                        <span class="text-[11px] font-bold text-white block">
                            Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }})
                        </span>
                        <span class="text-[9px] text-white/80 block">
                            {{ $issue->title }}
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-white/20 flex items-center justify-between text-[8px] text-white/80">
                    <span>p-ISSN {{ $journal->issn ?? '2746-8194' }}</span>
                    <span>e-ISSN {{ $journal->eissn ?? '2746-8208' }}</span>
                </div>
            </div>

            <!-- Detail Informasi Edisi -->
            <div class="md:col-span-3 space-y-3.5">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @if($isCurrent ?? false)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-300/60">
                            Edisi Terkini (Current Issue)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 uppercase tracking-wider border border-gray-300/60">
                            Arsip Terbitan
                        </span>
                    @endif
                    <span class="text-gray-500 text-xs">Terbit Resmi: <strong>{{ $issue->published_at ? $issue->published_at->format('d F Y') : ($issue->publication_date ? $issue->publication_date->format('d F Y') : date('d F Y')) }}</strong></span>
                    @if($issue->doi_prefix)
                        <span class="text-gray-300">•</span>
                        <span class="text-xs text-gray-600 font-mono">DOI Prefix: {{ $issue->doi_prefix }}</span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug tracking-tight">
                    Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }}): {{ $issue->title }}
                </h1>

                @if($issue->description)
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        {{ $issue->description }}
                    </p>
                @endif

                <div class="bg-gray-50 rounded-xl border border-gray-200 p-3.5 flex flex-wrap items-center gap-4 text-xs text-gray-700">
                    <div>
                        <strong class="text-brand-maroon font-bold text-sm">{{ $issue->articles->count() }}</strong> Naskah Artikel Terbit
                    </div>
                    <span class="text-gray-300">•</span>
                    <div>
                        Terindeks: <strong>SINTA 2, Garuda, DOAJ, CrossRef</strong>
                    </div>
                    <span class="text-gray-300">•</span>
                    <div>
                        Akses: <strong class="text-emerald-700">{{ $journal->license ?? 'Open Access (CC BY 4.0)' }}</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <!-- Daftar Artikel (2 Kolom Kiri) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h2 class="text-base sm:text-lg font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Daftar Isi Naskah Ilmiah (Table of Contents)</span>
                </h2>
                <span class="text-xs text-gray-500 font-medium">{{ $issue->articles->count() }} Artikel</span>
            </div>

            @php
                // Kelompokkan artikel berdasarkan Section / Bidang Kajian
                $groupedArticles = $issue->articles->groupBy(function($article) {
                    return $article->section?->name ?? 'Artikel Riset Orisinil';
                });
            @endphp

            @forelse($groupedArticles as $sectionName => $articles)
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-100/80 px-3.5 py-2 rounded-lg border-l-4 border-brand-maroon">
                        <span>{{ $sectionName }}</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($articles as $article)
                            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand-maroon/40 transition-all space-y-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="space-y-1">
                                        <h3 class="text-sm sm:text-base font-bold text-gray-900 hover:text-brand-maroon leading-snug">
                                            <a href="{{ route('journal.articles.show', $article->slug) }}">
                                                {{ $article->title }}
                                            </a>
                                        </h3>
                                        <p class="text-xs text-gray-600 font-medium">
                                            {{ $article->contributors->pluck('full_name')->join(', ') ?: 'Peneliti BRIDA' }}
                                        </p>
                                    </div>
                                    @if($article->pivot?->pages)
                                        <span class="text-[11px] text-gray-400 font-mono shrink-0">
                                            Hal. {{ $article->pivot->pages }}
                                        </span>
                                    @endif
                                </div>

                                @if($article->abstract)
                                    <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                        {{ Str::limit(strip_tags($article->abstract), 220) }}
                                    </p>
                                @endif

                                <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <div class="flex items-center gap-3 text-gray-400 text-[11px]">
                                        @if($article->doi)
                                            <span class="font-mono text-gray-600">DOI: {{ $article->doi }}</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('journal.articles.show', $article->slug) }}" class="font-bold text-brand-maroon hover:underline">
                                            Lihat Abstrak &rarr;
                                        </a>

                                        @if($article->galleys->isNotEmpty())
                                            @foreach($article->galleys as $g)
                                                <a href="{{ route('journal.articles.galleys.download', ['slugOrId' => $article->slug, 'galleyId' => $g->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-brand-maroon text-white font-bold text-[11px] hover:bg-brand-maroon-hover transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <span>{{ $g->label ?: 'PDF' }}</span>
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-xs">
                    <p class="font-bold text-gray-700">Belum ada naskah artikel yang terdaftar pada edisi ini.</p>
                </div>
            @endforelse

        </div>

        <!-- Kolom Samping (1 Kolom Kanan) -->
        <aside class="lg:col-span-1 space-y-6">
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
