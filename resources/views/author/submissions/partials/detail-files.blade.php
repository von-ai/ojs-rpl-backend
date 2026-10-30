<!-- Panel Berkas Manuskrip & Lampiran Dokumen -->
<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-5">
    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900 leading-tight">
                    Berkas Manuskrip & Lampiran
                </h2>
                <p class="text-[11px] text-gray-500 hidden sm:block">Dokumen naskah dan lampiran yang diunggah</p>
            </div>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 tabular-nums">
            {{ count($submission['files']) }} Dokumen Terunggah
        </span>
    </div>

    @if(empty($submission['files']))
        <div class="p-8 text-center text-xs text-gray-500 rounded-lg bg-gray-50 border border-dashed border-gray-200 space-y-2">
            <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="font-medium text-gray-700">Belum ada berkas naskah diunggah pada pengajuan ini.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($submission['files'] as $file)
                @php
                    $isPdf = str_contains(strtolower($file['mime_type'] ?? ''), 'pdf');
                @endphp
                <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <!-- Icon Dokumen Sesuai Format -->
                        <div class="w-9 h-9 rounded-lg {{ $isPdf ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }} flex items-center justify-center shrink-0 mt-0.5">
                            @if($isPdf)
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            @else
                                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1 space-y-1">
                            <!-- Judul Berkas & Kategori -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-xs sm:text-sm text-gray-900 truncate max-w-xs sm:max-w-md" title="{{ $file['name'] }}">
                                    {{ $file['name'] }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700 shrink-0">
                                    {{ $file['genre_label'] }}
                                </span>
                            </div>

                            <!-- Meta Info Berkas -->
                            <div class="flex items-center gap-x-2 text-xs text-gray-500 flex-wrap">
                                <span class="tabular-nums font-medium text-gray-700 shrink-0">{{ $file['size_formatted'] }}</span>
                                <span class="text-gray-300">/</span>
                                <span class="tabular-nums shrink-0">{{ $file['uploaded_at'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Unduh Berkas Privat -->
                    <div class="shrink-0 self-end sm:self-center">
                        @if(!empty($file['download_url']) && $file['download_url'] !== '#download-file')
                            <a 
                                href="{{ $file['download_url'] }}" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:scale-[0.98] transition duration-150 cursor-pointer shadow-xs"
                                title="Unduh berkas {{ $file['name'] }}"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Unduh</span>
                            </a>
                        @else
                            <button 
                                type="button" 
                                onclick="if(window.toast) window.toast.info('Memulai simulasi pengunduhan berkas privat...', 'Unduh Dokumen');"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:scale-[0.98] transition duration-150 cursor-pointer"
                                title="Unduh berkas manuskrip"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Unduh</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
