@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Pencarian Artikel Ilmiah Terbit — ' . ($journal->name ?? 'SIRIDA BRIDA Kota Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Header & Hero Search Box -->
    <div class="bg-white rounded-md border border-gray-200 p-6 sm:p-8 space-y-6">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Pencarian Artikel
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 max-w-2xl">
                Telusuri artikel penelitian dan inovasi daerah yang telah terbit.
            </p>
        </div>

        <!-- Form Pencarian Publik (UC-04) -->
        <form action="{{ route('journal.articles.search') }}" method="GET" class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ request('q') }}"
                        placeholder="Cari berdasarkan judul naskah, abstrak, kata kunci, atau nama penulis..."
                        class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon text-gray-900"
                    >
                </div>

                <div class="flex gap-2">
                    @php
                        $categoryOptions = array_merge(['' => 'Semua Seksi / Kategori'], collect($viewModel->categories())->pluck('name', 'name')->all());
                    @endphp
                    <x-custom-dropdown
                        name="category"
                        :value="request('category')"
                        :options="$categoryOptions"
                        button-class="py-2.5 px-3 text-xs bg-white border-gray-300 text-gray-700 rounded-md"
                        panel-class="rounded-md min-w-56"
                    />

                    <button 
                        type="submit" 
                        class="px-5 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white text-xs font-bold rounded-md transition duration-150 flex items-center gap-2 cursor-pointer border border-brand-maroon"
                    >
                        <span>Cari</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Hasil Pencarian -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <span>Hasil Pencarian</span>
                @if(isset($results))
                    <span class="text-xs font-normal text-gray-500">({{ $results->total() }} Artikel Ditemukan)</span>
                @endif
            </h2>
            @if(request('q') || request('category'))
                <a href="{{ route('journal.articles.search') }}" class="text-xs font-bold text-brand-maroon hover:underline">
                    Reset Filter Pencarian ✕
                </a>
            @endif
        </div>

        @if(isset($results) && $results->isNotEmpty())
            <div class="space-y-4">
                @foreach($results as $article)
                    @php
                        $authors = $article->contributors->map(fn($c) => $c->full_name)->all();
                        $firstGalley = $article->galleys->first();
                        $downloadUrl = $firstGalley 
                            ? route('journal.articles.galleys.download', ['slugOrId' => $article->slug, 'galleyId' => $firstGalley->id])
                            : null;
                    @endphp
                    <div class="bg-white rounded-md border border-gray-200 p-5 sm:p-6 space-y-3 hover:border-gray-300 transition duration-150">
                        <div class="flex items-center gap-2 text-xs flex-wrap">
                            <span class="px-2 py-0.5 rounded font-semibold bg-brand-maroon/10 text-brand-maroon">
                                {{ $article->section?->name ?? 'Artikel Riset Orisinil' }}
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="text-gray-500">
                                Diterbitkan: {{ $article->published_at ? $article->published_at->format('d M Y') : date('d M Y') }}
                            </span>
                            <span class="text-gray-300">•</span>
                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px] font-semibold border border-emerald-200">
                                Open Access
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-gray-900 hover:text-brand-maroon transition duration-150">
                            <a href="{{ route('journal.articles.show', $article->slug) }}">
                                {{ $article->title }}
                            </a>
                        </h3>

                        <p class="text-xs font-medium text-gray-600">
                            Penulis: {{ !empty($authors) ? implode(', ', $authors) : 'Penulis BRIDA' }}
                        </p>

                        <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">
                            {{ $article->abstract }}
                        </p>

                        <div class="pt-2 flex items-center justify-between border-t border-gray-100 flex-wrap gap-3">
                            <div class="flex items-center gap-2">
                                <a 
                                    href="{{ route('journal.articles.show', $article->slug) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                                >
                                    <span>Baca Abstrak & Sitasi</span>
                                </a>

                                @if($downloadUrl)
                                    <a 
                                        href="{{ $downloadUrl }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>Unduh PDF</span>
                                    </a>
                                @endif
                            </div>

                            <span class="text-[11px] font-mono text-gray-400">
                                https://doi.org/10.55981/sirida.{{ $article->slug }}
                            </span>
                        </div>
                    </div>
                @endforeach

                <!-- Link Pagination Aman -->
                <div class="pt-4">
                    {{ $results->links() }}
                </div>
            </div>
        @else
            <!-- Empty State Informatif -->
            <div class="bg-white rounded-md border border-gray-200 p-12 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-gray-900">Tidak ada artikel ilmiah yang sesuai</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto leading-relaxed">
                        Coba gunakan kata kunci yang lebih umum, periksa ejaan judul, atau hapus filter kategori untuk melihat seluruh terbitan resmi.
                    </p>
                </div>
                <div>
                    <a 
                        href="{{ route('journal.articles.search') }}" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                    >
                        <span>Tampilkan Seluruh Terbitan Resmi</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
