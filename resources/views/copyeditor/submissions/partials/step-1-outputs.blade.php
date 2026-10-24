<!-- LANGKAH 1: Berkas Hasil Suntingan (Output Set) -->
<div class="bg-white rounded-2xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
    <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg {{ $submission['is_completed'] ? 'bg-gray-400' : 'bg-brand-maroon' }} text-white font-bold text-xs flex items-center justify-center shrink-0">
                1
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 leading-tight">
                    Berkas Hasil Suntingan
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    @if ($submission['is_completed'])
                        Berkas final yang telah selesai disunting dan diserahterimakan (Mode Arsip).
                    @else
                        Unggah naskah yang telah Anda perbaiki tata bahasa, format sitasi, dan gaya selingkungnya.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Banner Mode Arsip jika Naskah Telah Selesai -->
    @if ($submission['is_completed'])
        <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200 flex items-center gap-2.5 text-xs text-gray-700">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>
                Naskah ini telah berstatus <strong>Selesai</strong> dan diteruskan ke Layout Editor. Seluruh berkas keluaran di bawah kini bersifat <strong>arsip terkunci</strong>.
            </span>
        </div>
    @endif

    <!-- Daftar Berkas Suntingan yang Sudah Diunggah -->
    <div class="space-y-2.5">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">
            Berkas Terunggah Saat Ini:
        </span>

        @forelse ($submission['output_files'] as $file)
            <div class="p-3.5 rounded-xl border border-gray-200/90 bg-gray-50/50 flex items-center justify-between gap-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-gray-800 block truncate" title="{{ $file['name'] }}">
                            {{ $file['name'] }}
                        </span>
                        <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-0.5">
                            <span class="font-medium text-gray-700">{{ $file['type'] }}</span>
                            <span>•</span>
                            <span>{{ $file['size_formatted'] }}</span>
                            <span>•</span>
                            <span>{{ $file['uploaded_at'] }}</span>
                        </div>
                    </div>
                </div>

                <a 
                    href="{{ $file['download_url'] }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-brand-maroon hover:bg-white rounded-lg border border-transparent hover:border-gray-200 transition shrink-0"
                    title="Unduh berkas ini"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden sm:inline">Unduh</span>
                </a>
            </div>
        @empty
            <div class="p-6 rounded-xl border border-dashed border-gray-300 text-center space-y-1">
                <p class="text-xs font-semibold text-gray-700">Belum ada berkas suntingan yang diunggah</p>
                <p class="text-[11px] text-gray-500">Silakan pilih berkas Word hasil suntingan Anda pada formulir di bawah.</p>
            </div>
        @endforelse
    </div>

    <!-- Form Upload Berkas Baru (Hanya Muncul Jika Naskah Belum Selesai) -->
    @if (!$submission['is_completed'])
        <form action="{{ route('copyeditor.upload') }}" method="POST" enctype="multipart/form-data" class="pt-2 border-t border-gray-100 space-y-3">
            @csrf
            <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

            <label class="block text-xs font-semibold text-gray-700">
                Unggah / Ganti Berkas Suntingan Baru:
            </label>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <input 
                    type="file" 
                    name="copyedit_file" 
                    accept=".docx,.doc"
                    required
                    class="flex-1 text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer border border-gray-200 rounded-xl p-1"
                >

                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs font-semibold rounded-xl shadow-2xs transition shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Unggah Berkas</span>
                </button>
            </div>

            <p class="text-[11px] text-gray-400 leading-normal">
                Format yang didukung: <strong>.docx</strong> atau <strong>.doc</strong> (Maksimal 20 MB). Mengganti berkas akan meminta persetujuan ulang dari penulis demi integritas data.
            </p>
        </form>
    @endif
</div>
