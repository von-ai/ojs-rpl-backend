<!-- ========================================== -->
<!-- LANGKAH 2: UNGGAH BERKAS MANUSKRIP -->
<!-- ========================================== -->
@php
    $existingManuscript = ($draft && $draft->files) ? $draft->files->where('genre', 'manuscript')->last()?->file : null;
@endphp
<div id="step-panel-2" class="wizard-step-panel hidden bg-white rounded-xl border border-gray-200 p-5 sm:p-7 shadow-2xs space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-maroon">Langkah 2 dari 5</span>
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mt-1">Unggah Berkas Naskah</h2>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
            Unggah dokumen naskah artikel ilmiah Anda untuk proses telaah.
        </p>
    </div>

    <!-- Area Drag-and-Drop Naskah Utama -->
    <div class="space-y-2">
        <label class="block text-xs sm:text-sm font-bold text-gray-900">
            Berkas Naskah Utama <span class="text-red-600">*</span>
        </label>
        
        <input type="hidden" name="existing_manuscript_id" id="existing_manuscript_id" value="{{ $existingManuscript?->id ?? '' }}">
        <input type="hidden" name="remove_manuscript" id="remove_manuscript" value="0">

        <div class="relative">
            <!-- State 1: Tampilan Dropzone Sebelum Berkas Dipilih -->
            <div 
                id="dropzone-prompt" 
                class="{{ $existingManuscript ? 'hidden ' : '' }}group relative rounded-xl border-2 border-dashed border-gray-300 hover:border-brand-maroon/70 transition-all duration-200 bg-gray-50/50 hover:bg-red-50/10 text-center py-5 px-4 cursor-pointer"
            >
                <input 
                    type="file" 
                    name="manuscript_file" 
                    id="manuscript_file" 
                    accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" 
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                >

                <div class="space-y-2 pointer-events-none">
                    <div class="w-9 h-9 mx-auto rounded-lg bg-red-50 text-brand-maroon ring-1 ring-red-200/60 flex items-center justify-center shadow-2xs group-hover:scale-105 transition-transform">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-semibold text-gray-800">
                            Seret & lepas berkas naskah di sini, atau <span class="text-brand-maroon underline underline-offset-2 font-bold">pilih dari komputer</span>
                        </p>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            Format didukung: PDF, DOCX, atau DOC &bull; Maksimal 20 MB
                        </p>
                    </div>
                </div>
            </div>

            <!-- State 2: Kartu Berkas Terpilih -->
            <div 
                id="dropzone-selected" 
                class="{{ $existingManuscript ? 'flex ' : 'hidden ' }}rounded-xl border border-gray-200 bg-white py-3 px-4 sm:px-5 items-center justify-between gap-3.5 shadow-2xs transition-all duration-200"
            >
                <div class="flex items-center gap-3 min-w-0 flex-1 text-left">
                    <div class="w-9 h-9 rounded-lg bg-red-50 text-brand-maroon ring-1 ring-red-200/70 flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p id="selected-file-name" class="text-xs sm:text-sm font-bold text-gray-900 truncate">{{ $existingManuscript?->original_name ?? 'naskah-ilmiah.pdf' }}</p>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-semibold rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span id="file-status-text">{{ $existingManuscript ? 'Tersimpan di Draf' : 'Siap Diunggah' }}</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 text-gray-500 text-[11px]">
                            <span id="selected-file-size" class="tabular-nums font-medium">{{ $existingManuscript ? number_format(($existingManuscript->byte_size ?? 0) / 1024, 1) . ' KB' : '0 KB' }}</span>
                            <span class="text-gray-300">&bull;</span>
                            <span class="text-gray-400">Berkas Naskah Utama</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Berkas -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <button 
                        type="button" 
                        id="btn-reselect-file" 
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.25 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition active:scale-95 cursor-pointer shadow-2xs"
                        title="Pilih berkas lain dari komputer"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Ganti Berkas</span>
                    </button>
                    <button 
                        type="button" 
                        id="btn-remove-file" 
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition active:scale-95 cursor-pointer" 
                        title="Hapus berkas ini"
                        aria-label="Hapus berkas"
                    >
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <p id="file-error-msg" class="hidden text-xs text-red-600 font-semibold"></p>
    </div>
</div>
