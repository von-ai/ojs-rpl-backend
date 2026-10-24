<!-- Header Naskah & Tombol Kembali -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <a 
        href="{{ route('copyeditor.submissions.index') }}" 
        class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 hover:text-brand-maroon transition w-fit"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali ke Daftar Penugasan</span>
    </a>

    <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $submission['status_badge_class'] }}">
            {{ $submission['status_label'] }}
        </span>
    </div>
</div>

<!-- Kartu Informasi Utama Naskah -->
<div class="bg-white rounded-2xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="space-y-2 min-w-0 flex-1">
            <div class="flex items-center gap-2 text-xs font-mono font-bold text-gray-400">
                <span>{{ $submission['number'] }}</span>
                <span>•</span>
                <span class="text-gray-500 font-sans font-medium">{{ $submission['section_title'] }}</span>
            </div>

            <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900 leading-snug">
                {{ $submission['title'] }}
            </h1>

            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-gray-500 pt-1">
                <div>
                    <span class="text-gray-400">Penulis Korespondensi:</span> 
                    <strong class="text-gray-800 font-semibold">{{ $submission['corresponding_author']['name'] }}</strong>
                    <span class="text-gray-400">({{ $submission['corresponding_author']['email'] }})</span>
                </div>
                <div>
                    <span class="text-gray-400">Batas Waktu:</span> 
                    <strong class="{{ $submission['is_urgent'] ? 'text-amber-700' : 'text-gray-700' }} font-semibold">{{ $submission['due_at'] }}</strong>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Penting: Unduh Naskah Acuan Asli -->
        <div class="shrink-0 pt-1 lg:pt-0">
            <a 
                href="{{ $submission['source_file']['download_url'] }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-800 border border-gray-300 rounded-xl text-xs font-semibold shadow-2xs transition"
                title="Unduh naskah mentah asli sebelum disunting"
            >
                <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Naskah Asli (.docx)</span>
            </a>
        </div>
    </div>
</div>
