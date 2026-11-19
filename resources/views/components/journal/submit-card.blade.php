<div class="bg-brand-maroon text-white rounded-md p-5 border border-brand-maroon-hover/40 shadow-xs flex flex-col justify-between relative overflow-hidden h-full">
    <!-- Subtle Background Graphic Accent -->
    <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>

    <div>
        <span class="text-xs font-semibold text-white/90 uppercase tracking-normal block mb-1">
            Kirimkan Naskah Anda
        </span>
        <h2 class="text-xl sm:text-2xl font-semibold leading-tight text-white mb-1.5">
            Pengajuan Naskah Online
        </h2>
        <p class="text-xs text-white/90 leading-relaxed mb-3">
            Dewan Redaksi mengundang peneliti, akademisi, dan praktisi kebijakan daerah untuk mempublikasikan manuskrip orisinal.
        </p>

        <!-- CTA Buttons -->
        <div class="space-y-2 mb-3.5">
            <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-medium text-gray-900 bg-white hover:bg-gray-50 active:bg-gray-100 rounded-md transition-all shadow-xs text-center cursor-pointer">
                <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>

                <span>Kirim Naskah Baru</span>
            </a>

            <a href="{{ route('journal.about.submissions') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-medium text-white bg-white/10 hover:bg-white/20 active:bg-white/25 border border-white/30 rounded-md transition-all text-center">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Panduan Penulis</span>
            </a>
        </div>
    </div>

    <!-- Card Footer Info -->
    <div class="pt-2.5 border-t border-white/20 flex flex-wrap items-center justify-between gap-1 text-[11px] sm:text-xs text-white/90 font-normal">
        <span>Proses Review: <strong class="text-white font-medium">3-4 Minggu</strong></span>
        <span class="text-white/40 hidden sm:inline">•</span>
        <span class="text-white font-medium">Bebas Biaya Publikasi</span>
    </div>
</div>
