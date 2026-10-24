{{-- resources/views/layouteditor/submissions/partials/step-1-galleys.blade.php --}}
<div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
    <!-- Header Langkah 1 -->
    <div class="p-4 sm:p-5 border-b border-gray-200/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
        <div class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full bg-brand-maroon text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                1
            </span>
            <div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Berkas Galai Publikasi (PDF / HTML)
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Format terbitan resmi jurnal. Setiap revisi galai wajib diberi penomoran versi baru secara berurutan.
                </p>
            </div>
        </div>

        @if(!$submission['is_completed'])
            <button 
                type="button" 
                onclick="openUploadGalleyModal()"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg transition duration-150 shadow-2xs whitespace-nowrap self-start sm:self-center"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ count($submission['galleys']) > 0 ? 'Unggah Revisi Galai' : 'Unggah Draf Galai PDF' }}</span>
            </button>
        @endif
    </div>

    <!-- Konten Berkas Galai -->
    <div class="p-4 sm:p-5">
        @if(count($submission['galleys']) > 0)
            <div class="space-y-3">
                @foreach($submission['galleys'] as $galley)
                    <div class="p-3.5 rounded-lg border {{ $galley['is_active'] ? 'bg-white border-gray-300 shadow-2xs' : 'bg-gray-50/70 border-gray-200 opacity-80' }} transition">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg {{ $galley['format'] === 'pdf' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-blue-50 text-blue-700 border-blue-200' }} border flex items-center justify-center shrink-0">
                                    @if($galley['format'] === 'pdf')
                                        <span class="text-[10px] font-bold">PDF</span>
                                    @else
                                        <span class="text-[10px] font-bold">HTML</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-bold text-gray-900">{{ $galley['label'] }}</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $galley['stage_badge_class'] }}">
                                            {{ $galley['stage_label'] }}
                                        </span>
                                        @if($galley['is_active'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                                Versi Lama
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $galley['file_name'] }}</p>
                                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-400 mt-1">
                                        <span>{{ $galley['size_formatted'] }}</span>
                                        <span>•</span>
                                        <span>Diunggah: {{ $galley['uploaded_at'] }}</span>
                                        <span>•</span>
                                        <span class="font-mono text-[10px]" title="Checksum: {{ $galley['checksum_sha256'] }}">
                                            SHA-256: {{ substr($galley['checksum_sha256'], 0, 12) }}...
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <a 
                                href="{{ $galley['download_url'] }}" 
                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 rounded-lg border border-gray-200 shadow-2xs transition whitespace-nowrap self-start sm:self-center"
                                title="Unduh berkas galai"
                            >
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Unduh Galai</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- State Belum Ada Galai -->
            <div class="p-6 text-center border-2 border-dashed border-gray-200 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-2.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-800">Belum ada berkas galai publikasi</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    Unduh berkas Word naskah acuan di atas, susun tata letak 2-kolom sesuai panduan, lalu unggah berkas PDF draf pertama di sini.
                </p>
                <button 
                    type="button" 
                    onclick="openUploadGalleyModal()"
                    class="mt-3.5 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg transition duration-150 shadow-2xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Unggah Draf Galai PDF Pertama</span>
                </button>
            </div>
        @endif
    </div>
</div>
