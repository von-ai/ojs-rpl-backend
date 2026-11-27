@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar') . ' — Beranda')

@section('content')
    @php
        $summary = $viewModel->portalSummaryMetrics();
        $isPaid = $journal ? $journal->isPaidApc() : false;
        $apcLabel = $isPaid ? ($journal->apc_fee ?: 'Berbayar') : 'Rp 0';
    @endphp

    <!-- Call For Papers Announcement Banner -->
    <x-journal.announcement-banner :announcement="$viewModel->announcement()" />

    <!-- 1. Hero Editorial SIRIDA (Pencarian Besar + Carousel Sampul Semua Edisi Jurnal) -->
    <x-journal.flagship-hero
        :slides="$viewModel->heroSlides()"
        :journal="$journal"
        :currentIssue="$viewModel->currentIssue()"
        :indexingData="$viewModel->indexingData()"
    />

    <!-- 2. Pita Statistik Sederhana di Bawah Hero (Tipografi Inter, Palet SIRIDA) -->
    <section class="bg-white border-b border-divider" aria-label="Ringkasan statistik portal jurnal">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8">
            <dl class="grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 lg:divide-x divide-divider">
                <a href="{{ route('journal.journals.index') }}" class="group block py-6 sm:py-8 pr-4 lg:pr-8 hover:bg-canvas transition focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-maroon">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Jurnal Aktif
                    </dt>
                    <dd class="font-sans text-3xl sm:text-4xl font-extrabold text-gray-900 group-hover:text-brand-maroon tabular-nums mt-1.5 transition-colors">
                        {{ number_format($summary['total_journals']) }}
                    </dd>
                    <span class="text-xs text-gray-600 mt-1 block">
                        Katalog jurnal BRIDA Makassar
                    </span>
                </a>

                <a href="{{ route('journal.articles.search') }}" class="group block py-6 sm:py-8 pl-4 lg:px-8 border-l lg:border-l-0 border-divider hover:bg-canvas transition focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-maroon">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Artikel Terbit
                    </dt>
                    <dd class="font-sans text-3xl sm:text-4xl font-extrabold text-gray-900 group-hover:text-brand-maroon tabular-nums mt-1.5 transition-colors">
                        {{ number_format($summary['total_articles']) }}
                    </dd>
                    <span class="text-xs text-gray-600 mt-1 block">
                        Lolos double-blind peer review
                    </span>
                </a>

                <a href="{{ route('journal.archives') }}" class="group block py-6 sm:py-8 pr-4 lg:px-8 hover:bg-canvas transition focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-maroon">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Edisi Terbit
                    </dt>
                    <dd class="font-sans text-3xl sm:text-4xl font-extrabold text-gray-900 group-hover:text-brand-maroon tabular-nums mt-1.5 transition-colors">
                        {{ number_format($summary['total_issues']) }}
                    </dd>
                    <span class="text-xs text-gray-600 mt-1 block">
                        Volume &amp; arsip publikasi resmi
                    </span>
                </a>

                <a href="{{ route('journal.about.policies') }}" class="group block py-6 sm:py-8 pl-4 lg:pl-8 border-l lg:border-l-0 border-divider hover:bg-canvas transition focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-brand-maroon">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                        Unduhan PDF
                    </dt>
                    <dd class="font-sans text-3xl sm:text-4xl font-extrabold text-gray-900 group-hover:text-brand-maroon tabular-nums mt-1.5 transition-colors">
                        {{ number_format($summary['pdf_downloads']) }}
                    </dd>
                    <span class="text-xs text-gray-600 mt-1 block">
                        Open Access &middot; Biaya {{ $apcLabel }}
                    </span>
                </a>
            </dl>
        </div>
    </section>

    <!-- 3. Daftar 3 Pengumuman Terbaru (Editorial List, Tanpa Kotak Berlapis) -->
    @php
        $latestAnnouncements = $viewModel->latestAnnouncements(3);
        $scopedSlug = request()->route('journalSlug');
        $announcementsUrl = $scopedSlug ? url("/{$scopedSlug}/announcements") : route('journal.announcements.index');
    @endphp
    <section class="bg-canvas border-b border-divider" aria-labelledby="home-announcements-heading">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-9 sm:py-11 space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4 pb-4 border-b border-divider">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand-maroon-hover">
                        Warta &amp; Informasi Redaksi
                    </p>
                    <h2 id="home-announcements-heading" class="font-sans text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                        Pengumuman Terbaru
                    </h2>
                </div>

                <a
                    href="{{ $announcementsUrl }}"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-maroon hover:text-brand-maroon-hover hover:underline underline-offset-4 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon rounded-xs"
                >
                    <span>Selengkapnya</span>
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            @if(!empty($latestAnnouncements))
                <div class="divide-y divide-divider">
                    @foreach($latestAnnouncements as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="group py-5 first:pt-1 last:pb-1 grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-6 items-start hover:bg-white/70 -mx-3 px-3 rounded-lg transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon"
                        >
                            <div class="md:col-span-3 flex items-center gap-2 text-xs text-gray-600 pt-0.5">
                                <time class="font-mono font-medium tabular-nums">{{ $item['date'] }}</time>
                                @if(!empty($item['journal_initials']))
                                    <span class="text-gray-300" aria-hidden="true">&middot;</span>
                                    <span class="font-semibold text-gray-800 truncate">{{ $item['journal_initials'] }}</span>
                                @endif
                            </div>

                            <div class="md:col-span-8 min-w-0 space-y-1">
                                <h3 class="font-sans text-base font-bold text-gray-900 group-hover:text-brand-maroon leading-snug transition-colors text-left">
                                    {{ $item['title'] }}
                                </h3>
                                @if(!empty($item['excerpt']))
                                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-2 text-left">
                                        {{ $item['excerpt'] }}
                                    </p>
                                @endif
                            </div>

                            <div class="hidden md:flex md:col-span-1 justify-end pt-1 text-gray-400 group-hover:text-brand-maroon group-hover:translate-x-0.5 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600 py-4">
                    Belum ada pengumuman terbaru yang diterbitkan saat ini.
                </p>
            @endif
        </div>
    </section>
@endsection
