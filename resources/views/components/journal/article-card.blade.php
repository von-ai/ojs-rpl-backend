@props(['article'])

<div class="bg-white rounded-md border border-gray-200 p-5 hover:border-brand-maroon/40 transition-all space-y-3">
    <!-- Top Metadata Badges & Dates -->
    <div class="flex flex-wrap items-center justify-between gap-2 text-xs border-b border-gray-100 pb-2.5">
        <div class="flex items-center flex-wrap gap-2">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                Publik
            </span>
            <span class="text-gray-300">•</span>
            <span class="font-bold text-gray-700 text-xs">{{ $article['section'] ?? 'Artikel Riset Asli' }}</span>
            <span class="text-gray-300">•</span>
            <span class="text-gray-500 font-medium text-xs">Hal. {{ $article['pages'] ?? '101–114' }}</span>
        </div>

        <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
            <span>Terbit: <strong>{{ $article['published_at'] ?? '15 Des 2025' }}</strong></span>
        </div>
    </div>

    <!-- Article Title -->
    <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-snug hover:text-brand-maroon transition-colors">
        <a href="{{ $article['read_url'] ?? '#' }}">
            {{ $article['title'] }}
        </a>
    </h3>

    <!-- Authors & Affiliations -->
    <div class="space-y-1 text-xs">
        <div class="font-bold text-gray-800">
            {{ implode(', ', $article['authors'] ?? ['Penulis Utama']) }}
        </div>
        @if (!empty($article['affiliations']))
            <div class="text-gray-500 italic text-[11px]">
                {{ implode('; ', $article['affiliations']) }}
            </div>
        @endif
    </div>

    <!-- Abstract Preview -->
    <p class="text-xs text-gray-600 leading-relaxed line-clamp-3">
        {{ $article['abstract'] }}
    </p>

    <!-- Bottom Footer Row: DOI, Metrics, Buttons -->
    <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
        <!-- Left: DOI & Metrics -->
        <div class="flex items-center flex-wrap gap-3 text-gray-500">
            <a href="https://doi.org/{{ $article['doi'] }}" target="_blank" class="font-mono text-[11px] text-gray-700 hover:text-brand-maroon underline">
                doi:{{ $article['doi'] }}
            </a>
            <span class="text-gray-300">•</span>
            <span class="flex items-center gap-1 text-[11px]">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{ $article['views_count'] ?? ($article['views'] ?? 0) }} Dilihat
            </span>
            <span class="text-gray-300">•</span>
            <span class="flex items-center gap-1 text-[11px]">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ $article['downloads_count'] ?? ($article['downloads'] ?? 0) }} Unduhan
            </span>
        </div>

        <!-- Right: Action Buttons (Sitasi, PDF Full-text) -->
        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
            <a href="{{ $article['read_url'] ?? '#' }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-md transition-colors">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <span>Detail</span>
            </a>

            @if(!empty($article['download_url']) && $article['download_url'] !== '#')
                <a href="{{ $article['download_url'] }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-brand-maroon hover:text-white bg-red-50 hover:bg-brand-maroon border border-brand-maroon/30 hover:border-brand-maroon rounded-md transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>PDF</span>
                </a>
            @endif
        </div>
    </div>
</div>
