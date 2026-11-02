@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Arsip Edisi Terbitan — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Arsip Edisi Terbitan</span>
    </nav>

    <!-- Header Arsip Edisi -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
                PUSTAKA BERKALA ILMIAH
            </span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Arsip Seluruh Edisi Terbitan
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                Koleksi publikasi berkala naskah hasil penelitian dan kajian inovasi daerah {{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }} sejak volume pertama.
            </p>
        </div>

        <a href="{{ route('journal.issues.current') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-maroon text-white font-bold text-xs hover:bg-brand-maroon-hover transition shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Buka Edisi Terkini</span>
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Kolom Arsip (2/3 Kiri) -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($issues as $arc)
                    <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand-maroon/40 transition-all flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $arc->is_current ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $arc->is_current ? 'Edisi Terkini' : 'Arsip' }}
                                </span>
                                <span class="text-xs text-gray-400 font-mono">Tahun {{ $arc->year }}</span>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-xl text-center border border-gray-100">
                                <span class="font-extrabold text-gray-900 text-base block">
                                    Vol. {{ $arc->volume }} No. {{ $arc->number }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium block mt-0.5">
                                    {{ $arc->publication_date ? $arc->publication_date->format('F Y') : ($arc->published_at ? $arc->published_at->format('F Y') : "Tahun {$arc->year}") }}
                                </span>
                            </div>

                            <h2 class="font-bold text-gray-900 text-sm hover:text-brand-maroon transition-colors line-clamp-2">
                                <a href="{{ route('journal.issues.show', $arc->id) }}">
                                    {{ $arc->title }}
                                </a>
                            </h2>

                            @if($arc->description)
                                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ $arc->description }}
                                </p>
                            @endif

                            <div class="text-xs text-gray-500">
                                <strong>{{ $arc->articles_count ?? $arc->articles()->where('status', 'published')->count() }}</strong> Naskah Artikel Terpublikasi
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                            <a href="{{ route('journal.issues.show', $arc->id) }}" class="font-bold text-brand-maroon hover:underline flex items-center gap-1">
                                <span>Buka Daftar Isi Naskah</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-xs">
                    <p class="font-bold text-gray-700">Belum ada arsip edisi terbitan yang tersedia.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(method_exists($issues, 'links'))
            <div class="pt-4">
                {{ $issues->links() }}
            </div>
        @endif

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
