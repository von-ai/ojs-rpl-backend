@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@php
    $rawTitle = $issue->title ?? '';
    // Bersihkan duplikasi "Vol. X No. Y (YEAR):" jika sudah ada di awal title
    $cleanIssueTitle = trim(preg_replace('/^Vol\.\s*\d+\s*No\.\s*\d+\s*\(\d{4}\)\s*:\s*/i', '', $rawTitle));
    $issueDisplayTitle = $cleanIssueTitle ?: $rawTitle;
@endphp

@section('title', "Vol. {$issue->volume} No. {$issue->number} ({$issue->year})" . ($issueDisplayTitle ? ": {$issueDisplayTitle}" : '') . ' — ' . ($journal->name ?? 'SIRIDA BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-8 py-5 sm:py-8 space-y-5 sm:space-y-8">

    <!-- Breadcrumb Navigasi -->
    <x-journal.breadcrumb :crumbs="[
        ['label' => 'Arsip Edisi', 'url' => route('journal.archives')],
        ['label' => 'Vol. ' . $issue->volume . ' No. ' . $issue->number . ' (' . $issue->year . ')']
    ]" />

    <!-- Header Edisi Terbitan -->
    <div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-6 lg:p-7">
        <div class="block md:grid md:grid-cols-4 md:gap-6 md:items-center">

            <!-- Book Cover Badge Mockup (Tampil di tablet/desktop, tersembunyi di mobile agar hemat ruang) -->
            <div class="hidden md:flex md:col-span-1 w-full max-w-[170px] sm:max-w-44 md:max-w-none mx-auto bg-linear-to-b from-white via-gray-50 to-gray-100 text-gray-800 p-4 rounded-xl flex-col justify-between min-h-[220px] md:min-h-[240px] h-full relative overflow-hidden border border-gray-200">
                <!-- Spine crease effect on left side -->
                <div class="absolute inset-y-0 left-0 w-2.5 bg-linear-to-r from-gray-300/40 via-gray-200/15 to-transparent pointer-events-none"></div>

                <!-- Top Monograph Header -->
                <div class="flex items-center gap-1.5 border-b border-gray-200/80 pb-2 shrink-0">
                    <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA" class="w-4 h-4 object-contain">
                    <span class="text-[9.5px] font-bold text-gray-700 uppercase tracking-wider truncate">
                        BRIDA MAKASSAR
                    </span>
                </div>

                <!-- Monograph Center Emblem & Volume -->
                <div class="my-auto py-2.5 text-center">
                    <div class="w-12 h-12 rounded-xl bg-white border border-gray-200/90 text-brand-maroon mx-auto flex items-center justify-center mb-2.5">
                        <svg class="w-7 h-7 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-[13px] font-bold text-gray-900 block leading-tight">
                        Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }})
                    </span>
                    <span class="text-[9.5px] text-gray-500 font-semibold block mt-1 tracking-wider uppercase">
                        {{ ($isCurrent ?? false) ? 'Edisi Terkini' : 'Arsip Terbitan' }}
                    </span>
                </div>

                <!-- Bottom ISSN Bar -->
                <div class="pt-2 border-t border-gray-200/80 flex items-center justify-between text-[8px] sm:text-[9px] text-gray-500 shrink-0 font-mono">
                    <span>p:{{ $journal->issn ?? '2746-8194' }}</span>
                    <span>e:{{ $journal->eissn ?? '2746-8208' }}</span>
                </div>
            </div>

            <!-- Detail Informasi Edisi -->
            <div class="md:col-span-3 min-w-0 space-y-3 sm:space-y-3.5">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs">
                    @if($isCurrent ?? false)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] sm:text-[11px] font-bold bg-emerald-50 text-emerald-800 tracking-normal border border-emerald-200">
                            Edisi Terkini
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] sm:text-[11px] font-bold bg-gray-100 text-gray-700 tracking-normal border border-gray-200">
                            Arsip Terbitan
                        </span>
                    @endif
                    <span class="text-gray-500">
                        Terbit Resmi: <strong class="font-medium text-gray-700">{{ $issue->published_at ? $issue->published_at->locale('id')->translatedFormat('d F Y') : ($issue->publication_date ? $issue->publication_date->locale('id')->translatedFormat('d F Y') : date('d F Y')) }}</strong>
                    </span>
                    @if($issue->doi_prefix)
                        <span class="text-gray-300 hidden sm:inline">•</span>
                        <span class="text-gray-600 font-mono text-[11px] sm:text-xs">DOI Prefix: {{ $issue->doi_prefix }}</span>
                    @endif
                </div>

                <h1 class="text-base sm:text-xl lg:text-2xl font-bold text-gray-900 leading-snug break-words [text-wrap:balance]">
                    Vol. {{ $issue->volume }} No. {{ $issue->number }} ({{ $issue->year }}){{ $issueDisplayTitle ? ': ' . $issueDisplayTitle : '' }}
                </h1>

                @if($issue->description)
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed [text-wrap:pretty]">
                        {{ $issue->description }}
                    </p>
                @endif

                <div class="bg-gray-50/90 rounded-xl border border-gray-200/80 p-3 sm:p-3.5 flex flex-wrap items-center justify-between sm:justify-start gap-2.5 sm:gap-4 text-xs text-gray-700">
                    <div>
                        <strong class="text-brand-maroon font-bold text-sm tabular-nums">{{ $issue->articles->count() }}</strong>
                        <span class="text-[11px] sm:text-xs">Naskah Artikel Terbit</span>
                    </div>
                    <span class="text-gray-300 hidden sm:inline">•</span>
                    <div>
                        <span class="text-gray-500 text-[11px] sm:text-xs">Akses:</span>
                        <strong class="text-emerald-700 text-[11px] sm:text-xs">Public (CC BY 4.0)</strong>
                    </div>
                    <span class="text-gray-300 hidden sm:inline">•</span>
                    <div class="text-[11px] sm:text-xs text-gray-500">
                        Terindeks: <strong class="text-gray-700 font-medium">SINTA 2, Garuda, DOAJ</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-start">

        <!-- Daftar Artikel (2 Kolom Kiri) -->
        <div class="lg:col-span-2 min-w-0 space-y-5 sm:space-y-6">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Daftar Isi Naskah Ilmiah</span>
                </h2>
                <span class="text-xs text-gray-500 font-normal">{{ $issue->articles->count() }} Artikel</span>
            </div>

            @php
                // Kelompokkan artikel berdasarkan Section / Bidang Kajian
                $groupedArticles = $issue->articles->groupBy(function($article) {
                    return $article->section?->name ?? 'Artikel Riset Orisinil';
                });
            @endphp

            @forelse($groupedArticles as $sectionName => $articles)
                <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-center justify-between text-xs font-bold text-gray-800 uppercase tracking-wider bg-gray-100/90 px-3.5 py-2.5 rounded-lg border border-gray-200/80">
                        <span>{{ $sectionName }}</span>
                        <span class="text-[11px] text-gray-500 font-normal lowercase">({{ $articles->count() }} artikel)</span>
                    </div>

                    <div class="space-y-3 sm:space-y-4">
                        @foreach($articles as $article)
                            <x-journal.article-card :article="$article" />
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200/90 p-8 sm:p-10 text-center text-xs">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-brand-maroon mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Belum Ada Naskah Terdaftar</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto leading-relaxed">
                        Belum ada naskah artikel yang terdaftar pada edisi terbitan ini.
                    </p>
                </div>
            @endforelse

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
