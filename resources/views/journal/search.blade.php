@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Pencarian Artikel Ilmiah Terbit — ' . ($journal->name ?? 'SIRIDA BRIDA Kota Makassar'))

@section('content')
    @php
        $categoryOptions = array_merge(['' => 'Semua Seksi / Kategori'], collect($viewModel->categories())->pluck('name', 'name')->all());
    @endphp

    <!-- Header Banner Pencarian Artikel Konsisten dengan Beranda -->
    <x-journal.page-banner
        eyebrow="Katalog Publikasi Terbuka"
        title="Pencarian Artikel"
        description="Telusuri artikel penelitian, kajian kebijakan, dan inovasi daerah yang telah lolos penelaahan sejawat."
        :crumbs="[['label' => 'Pencarian Artikel']]"
    >
        <form action="{{ route('journal.articles.search') }}" method="GET" role="search" class="w-full max-w-4xl flex flex-col sm:flex-row items-stretch gap-2 bg-black/25 p-1.5 rounded-xl border border-white/45 hover:border-white/70 focus-within:border-white focus-within:bg-black/35 transition">
            <div class="relative flex-1 min-w-0">
                <svg class="w-5 h-5 text-white/80 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    aria-label="Cari berdasarkan judul naskah, abstrak, DOI, kata kunci, atau nama penulis"
                    placeholder="Cari berdasarkan judul naskah, abstrak, DOI, kata kunci, atau nama penulis..."
                    class="w-full min-h-11 pl-11 pr-4 py-2 text-sm bg-transparent border-0 rounded-lg focus:outline-none focus:ring-0 text-white placeholder:text-white/70"
                >
            </div>

            <div class="sm:w-56 shrink-0">
                <x-custom-dropdown
                    name="category"
                    :value="request('category')"
                    :options="$categoryOptions"
                    container-class="w-full"
                    button-class="min-h-11 w-full py-2 px-3.5 text-xs sm:text-sm bg-white/10 hover:bg-white/20 border-white/35 text-white rounded-lg"
                    panel-class="rounded-lg w-full"
                />
            </div>

            <button
                type="submit"
                class="min-h-11 px-6 py-2 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white text-sm font-semibold rounded-lg shadow-sm transition cursor-pointer shrink-0"
            >
                Cari
            </button>
        </form>
    </x-journal.page-banner>

    <!-- Hasil Pencarian (Permukaan Terbuka Material 3, Tanpa Kotak Berlapis) -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-divider">
                <h2 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2 tabular-nums">
                    <span>Hasil Pencarian</span>
                    @if(isset($results))
                        <span class="text-xs sm:text-sm font-normal text-gray-600">({{ $results->total() }} Artikel Ditemukan)</span>
                    @endif
                </h2>
                @if(request('q') || request('category'))
                    <a href="{{ route('journal.articles.search') }}" class="text-xs font-semibold text-brand-maroon hover:underline">
                        Reset Filter Pencarian ✕
                    </a>
                @endif
            </div>

            @if(isset($results) && $results->isNotEmpty())
                <div class="space-y-4">
                    @foreach($results as $article)
                        <x-journal.article-card :article="$article" />
                    @endforeach

                    <div class="pt-4">
                        {{ $results->links() }}
                    </div>
                </div>
            @else
                <div class="py-16 text-center space-y-3">
                    <svg class="w-10 h-10 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-gray-900">Tidak ada artikel ilmiah yang sesuai</h3>
                        <p class="text-sm text-gray-600 max-w-md mx-auto leading-relaxed">
                            Coba gunakan kata kunci yang lebih umum, periksa ejaan judul, atau hapus filter kategori untuk melihat seluruh terbitan resmi.
                        </p>
                    </div>
                    <div class="pt-2">
                        <a
                            href="{{ route('journal.articles.search') }}"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 text-sm font-semibold rounded-lg bg-surface-subtle text-gray-900 hover:bg-gray-200 border border-outline-variant transition"
                        >
                            <span>Tampilkan Seluruh Terbitan Resmi</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
