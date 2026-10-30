@props(['issue' => null])

<div id="edisi-terkini" class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-2xs">
    <!-- Header Row -->
    <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-100">
        <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Edisi Terkini</span>
        </h2>
        <a href="#arsip-edisi" class="text-xs font-bold text-brand-maroon hover:underline flex items-center gap-1">
            <span>Lihat Riwayat Volume</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Featured Issue Box (Grid 2-Column: Cover + Content) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-center">
        <!-- Left Book Cover Card Mockup -->
        <div class="md:col-span-1 bg-brand-maroon text-white p-4 rounded-xl shadow-md flex flex-col justify-between aspect-[3/4] relative overflow-hidden border border-red-900">
            <div class="text-[9px] font-bold text-red-200 uppercase tracking-widest border-b border-white/20 pb-1.5">
                PUBLIKASI ILMIAH RESMI
            </div>
            <div class="my-auto py-2">
                <h3 class="text-sm font-extrabold leading-tight text-white mb-1">
                    Jurnal Riset & Inovasi Daerah
                </h3>
                <p class="text-[10px] font-medium text-red-200">BRIDA Kota Makassar</p>

                <div class="mt-3 p-2 bg-white/15 rounded border border-white/20 backdrop-blur-xs">
                    <span class="text-[11px] font-bold text-white block">
                        {{ $issue['volume_label'] ?? 'Vol. 3 No. 2 (2025)' }}
                    </span>
                    <span class="text-[9px] text-red-100 block">
                        {{ $issue['edition_label'] ?? 'Edisi Desember' }}
                    </span>
                </div>
            </div>

            <div class="pt-2 border-t border-white/20 flex items-center justify-between text-[8px] text-red-200">
                <span>p-ISSN 2985-789X</span>
                <span>e-ISSN 2985-8888</span>
            </div>
        </div>

        <!-- Right Issue Overview Details -->
        <div class="md:col-span-3 space-y-3.5">
            <!-- Badges & DOI -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-300/60">
                    Publik
                </span>
                <span class="text-gray-500 text-xs">Terbit: <strong>{{ $issue['published_at'] ?? '15 Desember 2025' }}</strong></span>
                <span class="text-gray-300">•</span>
                <span class="text-xs text-gray-600 font-mono">DOI: {{ $issue['doi'] ?? '10.55981/brida-mks.2025.v3i2' }}</span>
            </div>

            <!-- Big Issue Title -->
            <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 leading-snug">
                {{ $issue['title'] ?? 'Vol. 3 No. 2 (2025): Akselerasi Kebijakan Berbasis Bukti Menuju Makassar Low Carbon City' }}
            </h3>

            <!-- Description -->
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                {{ $issue['description'] ?? 'Edisi ini menghimpun sembilan naskah hasil penelitian empiris dan studi kebijakan strategis yang mengevaluasi transformasi perkotaan inklusif, digitalisasi tata kelola persampahan terpadu, integrasi sensor cerdas pemantau mikroklimat lorong wisata, serta model penguatan ekonomi sirkular komunal di Kota Makassar.' }}
            </p>

            <!-- Summary Stats Bar -->
            <div class="bg-gray-50 rounded-lg border border-gray-200/80 p-3 flex flex-wrap items-center justify-around gap-3 text-xs text-gray-700">
                <div class="flex items-center gap-1.5 font-semibold">
                    <strong class="text-brand-maroon font-bold text-sm">{{ $issue['total_articles'] ?? '9' }}</strong> Naskah Artikel
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-center gap-1.5 font-semibold">
                    <strong class="text-brand-maroon font-bold text-sm">{{ $issue['total_pages'] ?? '142' }}</strong> Halaman
                </div>
                <span class="text-gray-300">•</span>
                <div class="flex items-center gap-1.5 font-semibold">
                    <strong class="text-brand-maroon font-bold text-sm">{{ number_format($issue['total_downloads'] ?? 2410, 0, ',', '.') }}</strong> Total Unduhan
                </div>
            </div>

            <!-- Action Button -->
            <div>
                <a href="#" class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh Seluruh Edisi (PDF)</span>
                </a>
            </div>
        </div>
    </div>
</div>
