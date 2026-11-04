@props(['issue' => null])

<div id="edisi-terkini" class="bg-white rounded-md border border-gray-200 p-5 sm:p-6">
    <!-- Header Row -->
    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-100">
        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Edisi Terkini</span>
        </h2>
        <a href="{{ route('journal.archives') }}" class="text-xs font-bold text-brand-maroon hover:underline flex items-center gap-1">
            <span>Lihat Riwayat Volume</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Featured Issue Box (Grid 2-Column: Cover + Content) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
        <!-- Left Book Cover Card Mockup -->
        <div class="md:col-span-1 bg-brand-maroon text-white p-4 rounded-md flex flex-col justify-between aspect-3/4 relative overflow-hidden border border-brand-maroon-hover shadow-xs">
            <div class="text-[9px] font-bold text-white/90 uppercase tracking-wider border-b border-white/20 pb-1.5">
                BRIDA MAKASSAR
            </div>
            <div class="my-auto py-2">
                <h3 class="text-sm font-extrabold leading-tight text-white mb-1">
                    Jurnal Riset & Inovasi
                </h3>

                <div class="mt-3 p-2 bg-black/15 rounded border border-white/20">
                    <span class="text-[11px] font-bold text-white block">
                        {{ $issue['volume_label'] ?? 'Vol. 3 No. 2 (2025)' }}
                    </span>
                    <span class="text-[9px] text-white/90 block">
                        {{ $issue['edition_label'] ?? 'Edisi Desember' }}
                    </span>
                </div>
            </div>

            <div class="pt-2 border-t border-white/20 flex items-center justify-between text-[8px] text-white/80">
                <span>p-ISSN {{ $issue['p_issn'] ?? '2746-8194' }}</span>
                <span>e-ISSN {{ $issue['e_issn'] ?? '2746-8208' }}</span>
            </div>
        </div>

        <!-- Right Issue Overview Details -->
        <div class="md:col-span-3 space-y-3.5">
            <!-- Badges & DOI -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-300/60">
                    Publik
                </span>
                <span class="text-gray-500 text-xs">Terbit: <strong>{{ $issue['published_at'] ?? date('d F Y') }}</strong></span>
                <span class="text-gray-300">•</span>
                <span class="text-xs text-gray-600 font-mono">DOI: {{ $issue['doi'] ?? '10.55981/sirida' }}</span>
            </div>

            <!-- Big Issue Title -->
            <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 leading-snug">
                {{ $issue['title'] ?? 'Edisi Terkini' }}
            </h3>

            <!-- Description -->
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                {{ $issue['description'] ?? 'Edisi berkala ilmiah resmi publikasi Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar.' }}
            </p>

            <!-- Summary Stats Bar -->
            <div class="bg-gray-50 rounded-md border border-gray-200 p-2.5 flex flex-wrap items-center justify-around gap-3 text-xs text-gray-700">
                <div class="flex items-center gap-1.5 font-semibold">
                    <strong class="text-brand-maroon font-bold text-sm">{{ $issue['total_articles'] ?? '0' }}</strong> Artikel
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-center gap-1.5 font-semibold">
                    <strong class="text-brand-maroon font-bold text-sm">{{ number_format($issue['total_downloads'] ?? 0) }}</strong> Unduhan
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-center gap-1.5 font-semibold">
                    <span class="text-emerald-700 font-bold text-xs">Open Access</span>
                </div>
            </div>

            <!-- Action Button -->
            <div>
                <a href="{{ route('journal.issues.current') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-md transition-all border border-brand-maroon shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Lihat Edisi Ini</span>
                </a>
            </div>
        </div>
    </div>
</div>
