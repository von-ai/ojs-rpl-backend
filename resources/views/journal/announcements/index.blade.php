@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Warta & Pengumuman — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Warta & Pengumuman</span>
    </nav>

    <!-- Header Halaman -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            INFORMASI REDAKSI RESMI
        </span>
        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
            Warta, Berita & Pengumuman Jurnal
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
            Panggilan pengajuan naskah (Call for Papers), agenda lokakarya penulisan artikel ilmiah, dan kabar pembaruan tata kelola berkala ilmiah {{ $journal->name ?? 'BRIDA Kota Makassar' }}.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Kolom Pengumuman (2 Kolom Kiri) -->
        <div class="lg:col-span-2 space-y-5">
            
            @forelse($announcements as $ann)
                <article class="bg-white rounded-2xl border border-gray-200 p-6 hover:border-brand-maroon/40 transition-all space-y-3">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="px-2.5 py-0.5 rounded-full font-bold bg-brand-maroon/10 text-brand-maroon text-[10px] uppercase tracking-wider">
                            Pengumuman Resmi
                        </span>
                        <time class="text-gray-400 font-medium">
                            {{ $ann->published_at ? $ann->published_at->format('d F Y') : $ann->created_at->format('d F Y') }}
                        </time>
                    </div>

                    <h2 class="text-base sm:text-lg font-bold text-gray-900 hover:text-brand-maroon transition-colors leading-snug">
                        <a href="{{ route('journal.announcements.show', $ann->id) }}">
                            {{ $ann->title }}
                        </a>
                    </h2>

                    <div class="text-xs text-gray-600 leading-relaxed line-clamp-3">
                        {{ Str::limit(strip_tags($ann->body), 250) }}
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <a href="{{ route('journal.announcements.show', $ann->id) }}" class="font-bold text-brand-maroon hover:underline flex items-center gap-1">
                            <span>Baca Selengkapnya</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-500 text-xs">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="font-bold text-gray-700">Belum ada warta atau pengumuman yang diterbitkan saat ini.</p>
                </div>
            @endforelse

            <!-- Pagination -->
            @if(method_exists($announcements, 'links'))
                <div class="pt-4">
                    {{ $announcements->links() }}
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
