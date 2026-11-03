<div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div>
            <h2 class="text-base font-bold text-gray-900 tracking-tight">Berkas Galai &amp; Dokumen Rujukan</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Bandingkan berkas PDF galai terhadap naskah rujukan copyediting final untuk memastikan tidak ada teks yang hilang atau salah format.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- 1. Berkas Galai PDF Aktif -->
        <div class="border border-brand-maroon/20 bg-brand-maroon/5 rounded-xl p-4 flex flex-col justify-between space-y-3 relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-brand-maroon bg-white px-2 py-0.5 rounded border border-brand-maroon/20">
                                Galai PDF Aktif
                            </span>
                            <span class="text-xs font-semibold text-gray-700">
                                {{ $submission['active_galley']['version_label'] }}
                            </span>
                        </div>
                        <span class="text-xs font-bold text-gray-900 block truncate mt-1" title="{{ $submission['active_galley']['file_name'] }}">
                            {{ $submission['active_galley']['file_name'] }}
                        </span>
                        <span class="text-[11px] text-gray-500 block">
                            Ukuran: {{ $submission['active_galley']['size_formatted'] }} • Diunggah {{ $submission['active_galley']['uploaded_at'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- SHA-256 Checksum -->
            <div class="bg-white/80 border border-brand-maroon/15 rounded-lg p-2 text-[10px] text-gray-600">
                <span class="font-bold text-gray-700 block">Checksum SHA-256:</span>
                <span class="font-mono text-gray-500 break-all select-all block mt-0.5">
                    {{ $submission['active_galley']['checksum_sha256'] }}
                </span>
            </div>

            <a
                href="{{ $submission['active_galley']['download_url'] }}"
                class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition duration-150 shadow-2xs cursor-pointer min-h-11"
                title="Unduh Berkas Galai PDF untuk Diperiksa"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Galai PDF ({{ $submission['active_galley']['size_formatted'] }})</span>
            </a>
        </div>

        <!-- 2. Naskah Acuan Copyediting Final (.docx) -->
        <div class="border border-blue-200 bg-blue-50/40 rounded-xl p-4 flex flex-col justify-between space-y-3 relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-800 bg-white px-2 py-0.5 rounded border border-blue-200">
                            Naskah Acuan Copyediting
                        </span>
                        <span class="text-xs font-bold text-gray-900 block truncate mt-1" title="{{ $submission['copyedit_reference']['file_name'] }}">
                            {{ $submission['copyedit_reference']['file_name'] }}
                        </span>
                        <span class="text-[11px] text-gray-500 block">
                            Ukuran: {{ $submission['copyedit_reference']['size_formatted'] }} • Format Microsoft Word
                        </span>
                    </div>
                </div>
            </div>

            <!-- Keterangan Naskah Acuan -->
            <p class="text-xs text-gray-600 bg-white/80 border border-blue-100 rounded-lg p-2 leading-relaxed">
                {{ $submission['copyedit_reference']['description'] }}
            </p>

            @if($submission['copyedit_reference']['download_url'])
            <a
                href="{{ $submission['copyedit_reference']['download_url'] }}"
                class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 text-xs font-bold text-blue-800 bg-white hover:bg-blue-100/60 border border-blue-300 rounded-lg transition duration-150 shadow-2xs cursor-pointer min-h-11"
                title="Unduh Naskah Acuan Copyediting (.docx)"
            >
                <svg class="w-4 h-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Acuan Teks Final (.docx)</span>
            </a>
            @else
                <span class="text-xs text-gray-500">Berkas belum tersedia</span>
            @endif
        </div>
    </div>
</div>
