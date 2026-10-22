<!-- Panel Metadata Naskah (Abstrak, Kata Kunci, & Bidang Fokus) -->
<section class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 sm:p-7 space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 leading-tight">
                    Abstrak & Metadata Naskah
                </h2>
                <p class="text-[11px] text-slate-500 hidden sm:block">Deskripsi substansi riset dan parameter taksonomi ilmiah</p>
            </div>
        </div>

        <!-- Tombol Salin Abstrak -->
        <button 
            type="button" 
            onclick="copyTextToClipboard({{ json_encode($submission['abstract']) }}, 'Abstrak naskah berhasil disalin!')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-slate-600 bg-slate-50 hover:bg-slate-100 active:scale-[0.98] border border-slate-200 rounded-lg transition cursor-pointer"
            title="Salin isi abstrak naskah ke papan klip"
        >
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <span>Salin Abstrak</span>
        </button>
    </div>

    <!-- Teks Abstrak (Font Editorial Lora Serif Sesuai guide-code.md) -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Abstrak</h3>
            <span class="text-[11px] text-slate-400 font-medium">Bahasa Indonesia (ID)</span>
        </div>
        <div class="border-l-4 border-brand-maroon bg-stone-50/70 p-5 sm:p-6 rounded-r-2xl border-y border-r border-slate-200/80 shadow-2xs">
            <p class="font-serif text-[15px] sm:text-base text-slate-800 leading-relaxed sm:leading-loose text-justify selection:bg-red-100 selection:text-brand-maroon">
                {{ $submission['abstract'] }}
            </p>
        </div>
    </div>

    <!-- Kata Kunci (Keywords Tag Pills) -->
    <div class="space-y-2.5">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kata Kunci (Keywords)</h3>
        <div class="flex items-center gap-2 flex-wrap">
            @forelse($submission['keywords'] as $kw)
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium text-slate-800 bg-slate-100/80 hover:bg-slate-200/80 border border-slate-200/80 transition shadow-2xs">
                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>{{ $kw }}</span>
                </span>
            @empty
                <span class="text-xs text-slate-400 italic">Belum ada kata kunci ditambahkan.</span>
            @endforelse
        </div>
    </div>

    <!-- Spesifikasi Taksonomi Ilmiah & Lisensi -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 text-xs">
        @if(!empty($submission['subject_area']))
            <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-1">
                <span class="font-semibold text-slate-500 block text-[11px] uppercase tracking-wider">Bidang Fokus / Keilmuan</span>
                <span class="text-slate-900 font-bold block">{{ $submission['subject_area'] }}</span>
            </div>
        @endif

        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70 space-y-1">
            <span class="font-semibold text-slate-500 block text-[11px] uppercase tracking-wider">Lisensi Publikasi</span>
            <span class="text-slate-900 font-bold block">Creative Commons Attribution 4.0 (CC BY 4.0)</span>
        </div>
    </div>

    <!-- Daftar Pustaka / Referensi -->
    @if(!empty($submission['references']))
        <div class="pt-4 border-t border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Pustaka / Referensi</h3>
                <button 
                    type="button" 
                    onclick="copyTextToClipboard({{ json_encode($submission['references']) }}, 'Daftar pustaka berhasil disalin!')"
                    class="text-[11px] font-semibold text-brand-maroon hover:underline flex items-center gap-1 cursor-pointer"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Salin Daftar Pustaka</span>
                </button>
            </div>
            <div class="p-4 rounded-xl bg-slate-50/90 border border-slate-200/80 font-mono text-xs text-slate-700 whitespace-pre-line leading-relaxed max-h-64 overflow-y-auto selection:bg-slate-200">
                {{ $submission['references'] }}
            </div>
        </div>
    @endif
</section>
