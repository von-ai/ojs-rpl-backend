@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Arsip Edisi Terbitan — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <x-journal.breadcrumb :crumbs="[['label' => 'Arsip Edisi Terbitan']]" />

    <!-- Header Arsip Edisi -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-normal block mb-1">
                Pustaka Berkala Ilmiah
            </span>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Arsip Seluruh Edisi Terbitan
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                Koleksi publikasi berkala naskah hasil penelitian dan kajian inovasi daerah {{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }} sejak volume pertama.
            </p>
        </div>

        <a href="{{ route('journal.issues.current') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-maroon text-white font-medium text-xs hover:bg-brand-maroon-hover transition shrink-0 self-end sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Buka Edisi Terkini</span>
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Kolom Arsip (2/3 Kiri) -->
        <div class="lg:col-span-2 min-w-0 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($issues as $arc)
                    <x-journal.issue-card :issue="$arc" />
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
