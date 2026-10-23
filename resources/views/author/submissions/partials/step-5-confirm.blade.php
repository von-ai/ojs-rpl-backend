<!-- ========================================== -->
<!-- LANGKAH 5: KONFIRMASI & KIRIM NASKAH -->
<!-- ========================================== -->
<div id="step-panel-5" class="wizard-step-panel hidden bg-white rounded-xl border border-gray-200 p-5 sm:p-7 shadow-2xs space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-maroon">Langkah 5 dari 5</span>
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mt-1">Konfirmasi & Pengiriman Naskah</h2>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
            Tinjau kembali seluruh data yang telah Anda isikan sebelum mengirimkan naskah secara resmi ke dewan redaksi.
        </p>
    </div>

    <!-- Ringkasan Tinjauan -->
    <div class="space-y-4">
        <!-- 1. Ketentuan & Seksi Jurnal -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 text-xs font-bold flex items-center justify-center">1</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Ketentuan & Seksi Jurnal</h3>
                </div>
                <button type="button" class="btn-jump-step inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:text-brand-maroon bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition cursor-pointer" data-target-step="1">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    <span>Ubah</span>
                </button>
            </div>
            <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                <div>
                    <span class="text-gray-400 text-[11px] font-medium uppercase tracking-wider">Seksi yang Dipilih</span>
                    <p id="review-section-title" class="font-bold text-gray-900 text-sm mt-0.5">-</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-[11px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-md font-medium self-start sm:self-auto" title="Seluruh butir checklist persyaratan pengajuan dan etika publikasi telah disetujui oleh penulis">
                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Checklist Persyaratan Pengajuan Telah Disetujui</span>
                </span>
            </div>
        </div>

        <!-- 2. Berkas Naskah Utama -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 text-xs font-bold flex items-center justify-center">2</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Berkas Naskah Utama</h3>
                </div>
                <button type="button" class="btn-jump-step inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:text-brand-maroon bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition cursor-pointer" data-target-step="2">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    <span>Ubah</span>
                </button>
            </div>
            <div class="mt-3">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200/80">
                    <div class="w-10 h-10 rounded-lg bg-red-100 text-brand-maroon flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-gray-400 text-[10px] font-medium uppercase tracking-wider">Berkas Naskah Terpilih</span>
                        <p id="review-file-name" class="font-bold text-gray-900 text-xs sm:text-sm truncate mt-0.5">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Metadata Naskah -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 text-xs font-bold flex items-center justify-center">3</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Metadata Naskah</h3>
                </div>
                <button type="button" class="btn-jump-step inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:text-brand-maroon bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition cursor-pointer" data-target-step="3">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    <span>Ubah</span>
                </button>
            </div>
            <div class="mt-3 space-y-3.5 text-xs">
                <div>
                    <span class="text-gray-400 text-[11px] font-medium uppercase tracking-wider">Judul Artikel</span>
                    <p id="review-title" class="font-bold text-gray-900 text-sm sm:text-base mt-1 leading-snug wrap-break-word overflow-wrap-anywhere">-</p>
                </div>
                <div>
                    <span class="text-gray-400 text-[11px] font-medium uppercase tracking-wider">Kata Kunci</span>
                    <div id="review-keywords" class="flex flex-wrap gap-1.5 mt-1.5"></div>
                </div>
                <div>
                    <span class="text-gray-400 text-[11px] font-medium uppercase tracking-wider">Ringkasan Abstrak</span>
                    <div class="mt-1.5 p-3.5 bg-gray-50 rounded-lg border border-gray-100">
                        <p id="review-abstract" class="text-gray-700 text-xs sm:text-sm line-clamp-4 leading-relaxed font-sans wrap-break-word overflow-wrap-anywhere">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Penulis & Penulis Korespondensi -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-2xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 text-xs font-bold flex items-center justify-center">4</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">Penulis & Korespondensi</h3>
                </div>
                <button type="button" class="btn-jump-step inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-600 hover:text-brand-maroon bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition cursor-pointer" data-target-step="4">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    </svg>
                    <span>Ubah</span>
                </button>
            </div>
            <div id="review-contributors-list" class="mt-3 space-y-2.5">
                <!-- Populated via JavaScript -->
            </div>
        </div>

        <!-- Pernyataan Integritas Final -->
        <label class="flex items-start gap-3 p-4 sm:p-5 rounded-xl bg-red-50/50 border border-red-200 cursor-pointer hover:bg-red-50/70 transition shadow-2xs">
            <input type="checkbox" id="confirm-declaration" required class="mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon shrink-0 cursor-pointer">
            <div class="text-xs leading-relaxed text-gray-700">
                <span class="font-bold text-gray-900 block mb-0.5">Pernyataan Integritas Ilmiah</span>
                Saya menyatakan bahwa naskah yang diajukan adalah karya orisinil, bebas dari fabrikasi data dan plagiarisme, serta seluruh penulis yang dicantumkan telah menyetujui versi naskah ini untuk diproses di Jurnal BRIDA Kota Makassar.
            </div>
        </label>
    </div>
</div>
