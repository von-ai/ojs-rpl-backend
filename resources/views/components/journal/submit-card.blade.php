<div class="bg-brand-maroon text-white rounded-xl p-5 sm:p-6 border border-red-900 flex flex-col justify-between relative overflow-hidden">
    <!-- Subtle Background Accent Graphic -->
    <div class="absolute -right-8 -bottom-8 w-40 h-40 rounded-full bg-white/5 pointer-events-none"></div>

    <div>
        <span class="text-[10px] font-bold text-red-200 uppercase tracking-widest block mb-1.5">
            KIRIMKAN NASKAH ANDA
        </span>
        <h2 class="text-xl sm:text-2xl font-extrabold leading-tight tracking-tight mb-2.5">
            Pengajuan Naskah Online
        </h2>
        <p class="text-xs text-red-100/90 leading-relaxed mb-6">
            Dewan Redaksi mengundang peneliti, akademisi, dan praktisi kebijakan daerah untuk mempublikasikan manuskrip orisinal.
        </p>

        <!-- CTA Buttons -->
        <div class="space-y-2.5 mb-6">
            <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-bold text-brand-maroon bg-white hover:bg-red-50 rounded-lg transition-all text-center">
                <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Kirim Naskah Baru (Submit)</span>
            </a>

            <a href="{{ route('journal.about.submissions') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg transition-all text-center">
                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Panduan Penulis (.DOCX)</span>
            </a>
        </div>
    </div>

    <!-- Card Footer Info -->
    <div class="pt-4 border-t border-white/15 flex items-center justify-between text-[11px] text-red-200/90 font-medium">
        <span>Proses Review: <strong>3-4 Minggu</strong></span>
        <span class="text-white/30">•</span>
        <span><strong>Bebas Biaya Publikasi</strong></span>
    </div>
</div>
