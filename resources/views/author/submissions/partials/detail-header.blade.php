<!-- Breadcrumb Navigasi Halaman Detail Naskah -->
<nav class="flex items-center gap-2 text-xs text-gray-500 mb-3" aria-label="Breadcrumb">
    <a href="{{ route('author.submissions.index') }}" class="hover:text-brand-maroon transition duration-150 font-medium flex items-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 rounded px-1 py-0.5">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span>Portal Penulis</span>
    </a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('author.submissions.index') }}" class="hover:text-brand-maroon transition duration-150 font-medium focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 rounded px-1 py-0.5">
        Daftar Naskah Saya
    </a>
    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-gray-900 font-semibold tabular-nums truncate max-w-56" aria-current="page" title="{{ $submission['submission_number'] }}">
        {{ $submission['submission_number'] }}
    </span>
</nav>

<!-- Header Blok Judul & Metadata Utama Naskah -->
<header class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 sm:p-7 space-y-4">
    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
        <div class="min-w-0 flex-1 space-y-3.5">
            <!-- Badges Kategori, ID Naskah, & Status Alur -->
            <div class="flex items-center gap-2 flex-wrap text-xs">
                <!-- Badge Nomor Submisi dengan Klik Salin -->
                <button 
                    type="button" 
                    onclick="copyTextToClipboard('{{ $submission['submission_number'] }}', 'Nomor submisi {{ $submission['submission_number'] }} disalin!')"
                    class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-mono font-bold text-slate-800 bg-slate-100 hover:bg-slate-200/80 border border-slate-200/90 transition duration-150 tabular-nums cursor-pointer title-hint"
                    title="Klik untuk menyalin nomor submisi"
                >
                    <span>{{ $submission['submission_number'] }}</span>
                    <svg class="w-3 h-3 text-slate-400 group-hover:text-slate-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>

                <!-- Rubrik / Seksi -->
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-medium text-slate-700 bg-slate-50 border border-slate-200/80">
                    {{ $submission['section'] }}
                </span>

                <!-- Status Alur Kerja -->
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-semibold {{ $submission['status_badge_class'] }} border shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-80"></span>
                    <span>{{ $submission['status_label'] }}</span>
                </span>

                <!-- Indikator Tahap -->
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold text-slate-600 bg-slate-100/70 border border-slate-200 tabular-nums">
                    Tahap {{ $submission['stage_number'] }} dari 6
                </span>
            </div>

            <!-- Judul Manuskrip Akademik (Tipografi Lora Serif Sesuai guide-code.md) -->
            <h1 class="text-xl sm:text-2xl lg:text-[1.75rem] font-serif font-bold text-slate-900 leading-snug sm:leading-tight break-words [overflow-wrap:anywhere] tracking-tight">
                {{ $submission['title'] }}
            </h1>

            <!-- Baris Metadata Tanggal Submit & Pembaruan -->
            <div class="flex items-center gap-x-5 gap-y-2 text-xs text-slate-500 flex-wrap pt-0.5 border-t border-slate-100">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Diajukan: <strong class="text-slate-700 font-medium tabular-nums">{{ $submission['submitted_at'] }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Diperbarui: <strong class="text-slate-700 font-medium tabular-nums">{{ $submission['updated_at'] }}</strong></span>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 text-slate-400">
                    <span>&bull;</span>
                    <span class="text-slate-600 font-medium truncate max-w-xs">{{ $submission['journal_name'] }}</span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Kontekstual di Sisi Kanan Atas -->
        <div class="flex items-center gap-2.5 shrink-0 pt-2 lg:pt-0 w-full sm:w-auto">
            <!-- Tombol Kembali -->
            <a 
                href="{{ route('author.submissions.index') }}" 
                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 active:bg-slate-100 active:scale-[0.98] border border-slate-300 rounded-xl transition duration-150 shadow-2xs shrink-0 cursor-pointer"
                title="Kembali ke Daftar Naskah Saya"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali</span>
            </a>

            <!-- Tombol Salin Tautan Naskah -->
            <button 
                type="button" 
                onclick="copyTextToClipboard(window.location.href, 'Tautan naskah berhasil disalin ke papan klip!')"
                class="hidden sm:inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 active:bg-slate-100 active:scale-[0.98] border border-slate-300 rounded-xl transition duration-150 shadow-2xs shrink-0 cursor-pointer"
                title="Salin tautan halaman naskah ini"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                <span class="sr-only lg:not-sr-only">Tautan</span>
            </button>

            <!-- Tombol Cetak Dokumen -->
            <button 
                type="button" 
                onclick="window.print()" 
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 active:bg-slate-100 active:scale-[0.98] border border-slate-300 rounded-xl transition duration-150 shadow-2xs shrink-0 cursor-pointer"
                title="Cetak Ringkasan Naskah Ini"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span class="hidden sm:inline">Cetak</span>
            </button>

            <!-- Aksi Utama Berdasarkan Status Naskah -->
            @if($submission['status'] === 'revisions_requested')
                <a 
                    href="#panel-revisi" 
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 active:scale-[0.98] rounded-xl transition duration-150 shadow-xs"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span class="whitespace-nowrap">Unggah Revisi</span>
                </a>
            @elseif($submission['status'] === 'draft')
                <a 
                    href="{{ route('author.submissions.create') }}" 
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active active:scale-[0.98] rounded-xl transition duration-150 shadow-xs"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="whitespace-nowrap">Lanjutkan Draf</span>
                </a>
            @elseif($submission['status'] === 'published')
                <a 
                    href="#panel-publikasi" 
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 active:scale-[0.98] rounded-xl transition duration-150 shadow-xs"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="whitespace-nowrap">Lihat Publikasi</span>
                </a>
            @endif
        </div>
    </div>
</header>
