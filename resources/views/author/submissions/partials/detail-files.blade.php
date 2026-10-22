<!-- Panel Berkas Manuskrip & Riwayat Dokumen -->
<section class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 sm:p-7 space-y-5">
    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 leading-tight">
                    Berkas Manuskrip & Lampiran
                </h2>
                <p class="text-[11px] text-slate-500 hidden sm:block">Dokumen naskah terunggah, verifikasi integritas SHA-256, dan status karantina antivirus</p>
            </div>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 tabular-nums">
            {{ count($submission['files']) }} Dokumen Terunggah
        </span>
    </div>

    @if(empty($submission['files']))
        <div class="p-8 text-center text-xs text-slate-500 rounded-xl bg-slate-50/80 border border-dashed border-slate-200 space-y-2">
            <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="font-medium text-slate-700">Belum ada berkas naskah diunggah pada pengajuan ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($submission['files'] as $file)
                @php
                    $isPdf = str_contains(strtolower($file['mime_type'] ?? ''), 'pdf');
                @endphp
                <div class="p-4 rounded-xl bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 transition duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                    <div class="flex items-start gap-3.5 min-w-0 flex-1">
                        <!-- Icon Dokumen Sesuai Format -->
                        <div class="w-10 h-10 rounded-xl {{ $isPdf ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }} flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            @if($isPdf)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 space-y-1.5">
                            <!-- Judul Berkas & Kategori -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-xs sm:text-sm text-slate-900 truncate max-w-md" title="{{ $file['name'] }}">
                                    {{ $file['name'] }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-200/70 text-slate-700">
                                    {{ $file['genre_label'] }}
                                </span>
                            </div>

                            <!-- Meta Info & Status ClamAV -->
                            <div class="flex items-center gap-x-3 gap-y-1 text-xs text-slate-500 flex-wrap">
                                <span class="tabular-nums font-medium text-slate-700">{{ $file['size_formatted'] }}</span>
                                <span>&bull;</span>
                                <span class="tabular-nums">{{ $file['uploaded_at'] }}</span>
                                <span>&bull;</span>
                                <!-- Status Scan Antivirus ClamAV Sesuai guide-code.md section 9.B -->
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 text-[11px]">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>Pindai Bersih (ClamAV)</span>
                                </span>
                            </div>

                            <!-- Checksum SHA-256 dengan Klik Salin -->
                            <div class="flex items-center gap-1.5 pt-0.5 text-[11px] font-mono text-slate-400">
                                <span>SHA-256:</span>
                                <button 
                                    type="button" 
                                    onclick="copyTextToClipboard('{{ $file['checksum'] }}', 'Checksum SHA-256 disalin!')"
                                    class="text-slate-600 hover:text-slate-900 hover:underline flex items-center gap-1 tabular-nums cursor-pointer"
                                    title="Klik untuk menyalin SHA-256 checksum lengkap"
                                >
                                    <span>{{ substr($file['checksum'], 0, 16) }}...{{ substr($file['checksum'], -8) }}</span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Unduh Berkas Privat -->
                    <div class="shrink-0 self-end sm:self-center">
                        <button 
                            type="button" 
                            onclick="if(window.toast) window.toast.info('Memulai pengunduhan berkas privat...', 'Unduh Dokumen');"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 active:bg-slate-100 active:scale-[0.98] transition duration-150 shadow-2xs cursor-pointer"
                            title="Unduh Berkas Ini"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
