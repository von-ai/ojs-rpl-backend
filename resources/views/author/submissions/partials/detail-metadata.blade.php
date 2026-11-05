<!-- Panel Metadata Naskah (Abstrak, Kata Kunci, & Bidang Fokus) -->
<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900 leading-tight">
                    Abstrak & Metadata Naskah
                </h2>
                <p class="text-[11px] text-gray-500 hidden sm:block">Deskripsi substansi riset dan parameter taksonomi ilmiah</p>
            </div>
        </div>

        <!-- Tombol Salin Abstrak -->
        <button 
            type="button" 
            onclick="copyTextToClipboard({{ json_encode($submission['abstract']) }}, 'Abstrak naskah berhasil disalin!')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-gray-600 bg-white hover:bg-gray-50 active:scale-[0.98] border border-gray-200 rounded-lg transition duration-150 cursor-pointer"
            title="Salin isi abstrak naskah ke papan klip"
        >
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <span>Salin Abstrak</span>
        </button>
    </div>

    <!-- Teks Abstrak -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Abstrak</h3>
            <span class="text-[11px] text-gray-400 font-medium">Bahasa Indonesia (ID)</span>
        </div>
        <div class="p-4 sm:p-5 rounded-lg bg-gray-50 border border-gray-200">
            <p class="font-sans text-xs sm:text-sm text-gray-700 leading-relaxed text-left selection:bg-brand-maroon/10 selection:text-brand-maroon break-words [overflow-wrap:anywhere]">
                {{ $submission['abstract'] }}
            </p>
        </div>
    </div>

    <!-- Kata Kunci (Keywords Tag Pills) -->
    <div class="space-y-2">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kata Kunci</h3>
        <div class="flex items-center gap-2 flex-wrap">
            @forelse($submission['keywords'] as $kw)
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-700 bg-white border border-gray-200 hover:border-gray-300 transition-colors break-words">
                    {{ $kw }}
                </span>
            @empty
                <span class="text-xs text-gray-400 italic">Belum ada kata kunci ditambahkan.</span>
            @endforelse
        </div>
    </div>

    <!-- Bidang Fokus / Keilmuan (Taksonomi Ilmiah) -->
    @if(!empty($submission['subject_area']))
        <div class="pt-2 border-t border-gray-100 text-xs">
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200/70 space-y-1">
                <span class="font-semibold text-gray-500 block text-[11px] uppercase tracking-wider">Bidang Fokus / Keilmuan</span>
                <span class="text-gray-900 font-bold block">{{ $submission['subject_area'] }}</span>
            </div>
        </div>
    @endif

    <!-- Daftar Pustaka / Referensi -->
    @if(!empty($submission['references']))
        <div class="pt-3 border-t border-gray-100 space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Daftar Pustaka</h3>
                <button 
                    type="button" 
                    onclick="copyTextToClipboard({{ json_encode($submission['references']) }}, 'Daftar pustaka berhasil disalin!')"
                    class="text-xs font-semibold text-brand-maroon hover:underline flex items-center gap-1 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Salin Daftar Pustaka</span>
                </button>
            </div>
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 font-mono text-xs text-gray-700 whitespace-pre-line leading-relaxed max-h-56 overflow-y-auto">
                {{ $submission['references'] }}
            </div>
        </div>
    @endif

    <!-- Catatan Pengantar untuk Dewan Redaksi / Editor -->
    @if(!empty($submission['comments_for_editor']))
        <div class="pt-3 border-t border-gray-100 space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Catatan Pengantar untuk Dewan Redaksi</h3>
                </div>
                <span class="text-[11px] text-gray-400 font-medium">Dari Penulis saat Pengajuan</span>
            </div>
            <div class="p-3.5 sm:p-4 rounded-lg bg-amber-50/50 border border-amber-200/60">
                <p class="font-sans text-xs text-gray-800 leading-relaxed whitespace-pre-line break-words [overflow-wrap:anywhere]">
                    {{ $submission['comments_for_editor'] }}
                </p>
            </div>
        </div>
    @endif
</section>
