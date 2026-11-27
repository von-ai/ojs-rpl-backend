@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Warta & Pengumuman — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
    <!-- Header Banner Pengumuman Konsisten dengan Beranda -->
    <x-journal.page-banner
        eyebrow="Informasi Redaksi Resmi"
        description="Panggilan pengajuan naskah, agenda lokakarya penulisan artikel ilmiah, dan kabar pembaruan tata kelola berkala ilmiah."
        :crumbs="[['label' => 'Warta & Pengumuman']]"
    >
        <x-slot:heading>Warta, Berita & Pengumuman Jurnal</x-slot:heading>
    </x-journal.page-banner>

    <!-- Daftar Pengumuman Bersih (Single-Column Editorial, Tanpa Sidebar) -->
    <section class="bg-white">
        <div class="max-w-5xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="divide-y divide-divider">
                @forelse($announcements as $ann)
                    <article class="py-6 sm:py-7 first:pt-0 space-y-2.5">
                        <div class="flex flex-wrap items-center gap-2.5 text-xs">
                            <time class="font-mono font-medium text-gray-600 tabular-nums">
                                {{ $ann->published_at ? $ann->published_at->format('d F Y') : $ann->created_at->format('d F Y') }}
                            </time>
                            <span class="text-gray-300" aria-hidden="true">&middot;</span>
                            <span class="font-semibold text-brand-maroon-hover uppercase tracking-wider text-[11px]">
                                Pengumuman Resmi
                            </span>
                            @if($ann->journal)
                                <span class="text-gray-300" aria-hidden="true">&middot;</span>
                                <span class="font-semibold text-gray-700">
                                    {{ $ann->journal->initials ?: $ann->journal->name }}
                                </span>
                            @endif
                        </div>

                        <h2 class="text-lg sm:text-xl font-bold text-gray-900 leading-snug">
                            <a href="{{ route('journal.announcements.show', $ann->id) }}" class="hover:text-brand-maroon transition-colors">
                                {{ $ann->title }}
                            </a>
                        </h2>

                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3 text-left">
                            {{ Str::limit(strip_tags($ann->body), 260) }}
                        </p>

                        <div class="pt-1">
                            <a href="{{ route('journal.announcements.show', $ann->id) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-maroon hover:text-brand-maroon-hover hover:underline">
                                <span>Baca Selengkapnya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="py-16 text-center space-y-2">
                        <svg class="w-10 h-10 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p class="text-base font-bold text-gray-900">Belum ada warta atau pengumuman yang diterbitkan saat ini.</p>
                        <p class="text-sm text-gray-600">Informasi panggilan naskah dan kabar redaksi terbaru akan tampil di halaman ini.</p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($announcements, 'links'))
                <div class="pt-6 border-t border-divider">
                    {{ $announcements->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
