@props(['issue' => null])

<div id="edisi-terkini" class="bg-white rounded-xl border border-gray-200/90 overflow-hidden">
    <!-- Header Row (Responsive: Compact on Mobile, Full on Desktop) -->
    <div class="px-3.5 sm:px-6 py-2.5 sm:py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between gap-2">
        <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-bold uppercase tracking-wider bg-red-100/70 text-brand-maroon whitespace-nowrap shrink-0">
                Edisi Terkini
            </span>
            <span class="text-xs text-gray-600 font-medium truncate">
                {{ $issue['volume_label'] ?? 'Vol. 5 No. 2 (2025)' }}
            </span>
        </div>

        <a href="{{ route('journal.archives') }}" class="text-xs font-semibold text-brand-maroon hover:underline flex items-center gap-1 shrink-0 whitespace-nowrap">
            <span class="sm:hidden">Arsip</span>
            <span class="hidden sm:inline">Lihat Riwayat Volume</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Mobile View (< md): Clean, Compact & Content-First -->
    <div class="p-4 md:hidden space-y-3">
        <!-- Theme Title & Description -->
        <div>
            <h3 class="text-base font-bold text-gray-900 leading-snug tracking-tight [text-wrap:balance] break-words" title="{{ $issue['theme_title'] ?? $issue['edition_label'] ?? $issue['title'] }}">
                {{ $issue['theme_title'] ?? $issue['edition_label'] ?? $issue['title'] }}
            </h3>
            <p class="text-xs text-gray-600 mt-1 leading-relaxed break-words line-clamp-3">
                {{ $issue['description'] ?? 'Edisi berkala ilmiah resmi publikasi Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar.' }}
            </p>
        </div>

        <!-- Compact 1-Row Stats Strip -->
        <div class="grid grid-cols-3 gap-1 py-2 px-1.5 bg-gray-50/90 rounded-lg border border-gray-200/70 text-center">
            <div class="px-1">
                <strong class="text-gray-900 font-bold text-xs block tabular-nums">{{ $issue['total_articles'] ?? '0' }}</strong>
                <span class="text-[10px] text-gray-500">Artikel</span>
            </div>
            <div class="border-x border-gray-200/80 px-1">
                <strong class="text-gray-900 font-bold text-xs block tabular-nums">{{ number_format($issue['total_downloads'] ?? 0) }}</strong>
                <span class="text-[10px] text-gray-500">Unduhan</span>
            </div>
            <div class="px-1 flex flex-col justify-center items-center">
                <span class="text-emerald-700 font-bold text-[10.5px] leading-tight">Public</span>
                <span class="text-[9.5px] text-gray-400">Bebas Akses</span>
            </div>
        </div>

        <!-- Terbit & DOI Row -->
        <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-500 pt-0.5">
            <span>Terbit: <strong class="text-gray-700 font-medium">{{ $issue['published_at'] ?? date('d F Y') }}</strong></span>
            @if(!empty($issue['doi']))
                <a
                    href="https://doi.org/{{ $issue['doi'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-mono text-gray-600 hover:text-brand-maroon underline truncate max-w-[55%]"
                    title="DOI: {{ $issue['doi'] }}"
                >
                    DOI: {{ $issue['doi'] }}
                </a>
            @endif
        </div>

        <!-- Single Primary CTA on Mobile -->
        <a href="{{ route('journal.issues.current') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:scale-[0.98] rounded-lg transition-all shadow-none">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Lihat Seluruh Artikel Edisi Ini</span>
        </a>
    </div>

    <!-- Desktop View (md:grid): 2-Column Balanced Editorial Grid -->
    <div class="hidden md:grid md:grid-cols-12 md:gap-6 p-6 items-stretch">
        <!-- Left: Issue Identity Card (md:col-span-4) -->
        <div class="md:col-span-4 min-w-0 bg-gray-50/90 rounded-xl p-5 border border-gray-200/70 flex flex-col justify-between space-y-4">
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
                    TERBITAN RESMI
                </span>
                <h4 class="text-lg font-bold text-gray-900 leading-tight break-words line-clamp-2" title="{{ $issue['volume_label'] ?? 'Vol. 5 No. 2 (2025)' }}">
                    {{ $issue['volume_label'] ?? 'Vol. 5 No. 2 (2025)' }}
                </h4>
                <p class="text-xs text-gray-500 mt-1">
                    Terbit: <strong class="text-gray-700 font-medium">{{ $issue['published_at'] ?? date('d F Y') }}</strong>
                </p>
            </div>

            <div class="space-y-2 pt-3 border-t border-gray-200/70 text-xs">
                <div class="flex items-center justify-between text-gray-600 gap-2">
                    <span class="shrink-0">Jumlah Artikel</span>
                    <strong class="text-gray-900 font-bold tabular-nums truncate">{{ $issue['total_articles'] ?? '0' }} Naskah</strong>
                </div>
                <div class="flex items-center justify-between text-gray-600 gap-2">
                    <span class="shrink-0">Akses Publikasi</span>
                    <span class="text-emerald-700 font-semibold text-[11px] shrink-0">
                        Public
                    </span>
                </div>
                <div class="flex items-center justify-between text-gray-600 gap-2">
                    <span class="shrink-0">Total Unduhan</span>
                    <strong class="text-gray-900 font-bold tabular-nums truncate">{{ number_format($issue['total_downloads'] ?? 0) }} kali</strong>
                </div>
            </div>
        </div>

        <!-- Right: Editorial Theme & Action (md:col-span-8) -->
        <div class="md:col-span-8 min-w-0 flex flex-col justify-between space-y-3">
            <div class="space-y-2.5">
                @if(!empty($issue['doi']))
                    <div class="flex items-center gap-2">
                        <a
                            href="https://doi.org/{{ $issue['doi'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-gray-100 hover:bg-red-50 text-[11px] font-mono text-gray-700 hover:text-brand-maroon transition-colors group border border-gray-200/80 max-w-full"
                            title="Buka DOI Resolver (Tautan Eksternal)"
                        >
                            <span class="font-bold text-brand-maroon shrink-0">DOI</span>
                            <span class="truncate">{{ $issue['doi'] }}</span>
                            <svg class="w-3 h-3 text-gray-400 group-hover:text-brand-maroon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                @endif

                <h3 class="text-xl md:text-2xl font-bold text-gray-900 leading-snug tracking-tight break-words [text-wrap:balance]" title="{{ $issue['theme_title'] ?? $issue['edition_label'] ?? $issue['title'] }}">
                    {{ $issue['theme_title'] ?? $issue['edition_label'] ?? $issue['title'] }}
                </h3>

                <p class="text-sm text-gray-600 leading-relaxed [text-wrap:pretty] break-words line-clamp-3 md:line-clamp-4">
                    {{ $issue['description'] ?? 'Edisi berkala ilmiah resmi publikasi Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar yang memuat kajian ilmiah orisinal dan inovasi kebijakan daerah.' }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex flex-wrap items-center gap-2.5">
                <a href="{{ route('journal.issues.current') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:scale-[0.98] rounded-lg transition-all shadow-none shrink-0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Lihat Seluruh Artikel Edisi Ini</span>
                </a>

                <a href="#artikel-terbit" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:text-brand-maroon bg-white hover:bg-gray-50 border border-gray-200 rounded-lg transition-all shrink-0">
                    <span>Gulir ke Daftar Artikel</span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
