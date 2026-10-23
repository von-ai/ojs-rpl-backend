<!-- ========================================== -->
<!-- NAVIGASI BAWAH WIZARD -->
<!-- ========================================== -->
<div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-2xs flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
    <div>
        <button 
            type="button" 
            id="btn-prev-step" 
            class="hidden not-[.hidden]:inline-flex w-full sm:w-auto items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 border border-gray-300 rounded-lg transition"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span>Sebelumnya</span>
        </button>
    </div>

    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
        <!-- Tombol Simpan Draf (Tersedia di setiap langkah) -->
        <button 
            type="button" 
            id="btn-save-draft" 
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 active:bg-gray-100 border border-gray-300 rounded-lg transition shadow-2xs"
            title="Simpan data ke daftar naskah draf tanpa mengirim ke redaksi"
        >
            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            <span>Simpan Draf</span>
        </button>

        <!-- Tombol Selanjutnya (Langkah 1-4) -->
        <button 
            type="button" 
            id="btn-next-step" 
            class="flex-1 sm:flex-none not-[.hidden]:inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg transition shadow-2xs"
        >
            <span>Selanjutnya</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Tombol Kirim Naskah Final (Hanya Muncul di Langkah 5) -->
        <button 
            type="submit" 
            id="btn-final-submit" 
            class="hidden not-[.hidden]:inline-flex flex-1 sm:flex-none items-center justify-center gap-2 px-6 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg transition shadow-2xs disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <svg id="submit-spinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Kirim Naskah</span>
        </button>
    </div>
</div>
