<div class="bg-brand-maroon text-white rounded-xl p-3.5 sm:p-5 lg:p-6 border border-brand-maroon-hover/40 flex flex-col justify-between relative overflow-hidden h-full">
    <!-- Subtle Background Graphic Accent -->
    <div class="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-white/5 pointer-events-none"></div>

    <div>
        <span class="text-[10px] sm:text-xs font-semibold text-white/80 uppercase tracking-wider block mb-1">
            Kirimkan Naskah Anda
        </span>
        <h2 class="text-lg sm:text-xl md:text-2xl font-bold leading-tight text-white mb-1.5 sm:mb-2">
            Pengajuan Naskah Online
        </h2>
        <p class="text-[11px] sm:text-xs text-white/90 leading-relaxed mb-3 sm:mb-4 line-clamp-2 sm:line-clamp-none">
            Dewan Redaksi mengundang peneliti, akademisi, dan praktisi kebijakan daerah untuk mempublikasikan manuskrip riset orisinal.
        </p>

        <!-- CTA Buttons (1 baris di mobile & desktop) -->
        <div class="grid grid-cols-2 gap-2 sm:gap-2.5 mb-3 sm:mb-4">
            <a href="{{ route(auth()->user()?->hasRole('author') ? 'author.submissions.create' : (auth()->check() ? 'dashboard' : 'login'), absolute: false) }}" class="inline-flex items-center justify-center gap-1.5 px-2 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-gray-900 bg-white hover:bg-gray-50 active:scale-[0.98] rounded-lg transition-all text-center cursor-pointer shadow-none whitespace-nowrap">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-brand-maroon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Kirim Naskah</span>
            </a>

            <a href="{{ route('journal.about.submissions') }}" class="inline-flex items-center justify-center gap-1.5 px-2 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-white bg-white/10 hover:bg-white/20 active:scale-[0.98] border border-white/30 rounded-lg transition-all text-center shadow-none whitespace-nowrap">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Panduan</span>
            </a>
        </div>
    </div>

    <!-- Card Footer Info -->
    <div class="pt-2.5 sm:pt-3 border-t border-white/20 flex flex-wrap items-center justify-between gap-1 text-[10.5px] sm:text-xs text-white/90 font-normal">
        <span>Proses Review: <strong class="text-white font-medium">3-4 Minggu</strong></span>
        <span>Peer-Reviewed Journal</span>
    </div>
</div>
