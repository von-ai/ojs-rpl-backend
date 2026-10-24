{{-- resources/views/layouteditor/submissions/partials/header-card.blade.php --}}
<div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
    <!-- Navigasi Kembali & Status Utama -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
        <a 
            href="{{ route('layouteditor.submissions.index') }}" 
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-brand-maroon transition"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Penugasan Galai</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $submission['status_badge_class'] }}">
                {{ $submission['status_label'] }}
            </span>
            @if($submission['is_urgent'])
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Mendekati Batas</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Metadata Pokok Naskah -->
    <div>
        <div class="flex items-center gap-2 text-xs font-mono font-bold text-gray-400 mb-1">
            <span>{{ $submission['number'] }}</span>
            <span>•</span>
            <span class="text-gray-500 font-sans font-medium">{{ $submission['section_title'] }}</span>
            <span>•</span>
            <span class="text-gray-400">{{ $submission['assignment_code'] }}</span>
        </div>
        <h1 class="text-lg sm:text-2xl font-serif font-bold text-gray-900 leading-snug tracking-tight">
            {{ $submission['title'] }}
        </h1>
        <p class="text-xs sm:text-sm text-gray-600 mt-1">
            Penulis Korespondensi: <strong class="text-gray-900">{{ $submission['corresponding_author']['name'] }}</strong> 
            <span class="text-gray-400 font-normal">({{ $submission['corresponding_author']['affiliation'] }})</span>
        </p>
    </div>

    <!-- Card Naskah Acuan Final dari Copyeditor -->
    <div class="bg-gray-50/80 rounded-lg border border-gray-200/80 p-3.5 sm:p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-100/80 border border-blue-200 text-blue-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 block">Naskah Acuan Final Copyeditor (.docx)</span>
                    <span class="text-xs font-semibold text-gray-900 break-all">{{ $submission['copyedit_final_file']['name'] }}</span>
                    <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-0.5">
                        <span>Ukuran: {{ $submission['copyedit_final_file']['size_formatted'] }}</span>
                        <span>•</span>
                        <span class="font-mono text-[10px] text-gray-400 truncate max-w-48 sm:max-w-xs" title="SHA-256: {{ $submission['copyedit_final_file']['checksum_sha256'] }}">
                            SHA: {{ substr($submission['copyedit_final_file']['checksum_sha256'], 0, 16) }}...
                        </span>
                    </div>
                </div>
            </div>

            <a 
                href="{{ $submission['copyedit_final_file']['download_url'] }}" 
                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-lg transition border border-gray-300 shadow-2xs whitespace-nowrap self-start sm:self-center"
                title="Unduh berkas Word acuan susun layout"
            >
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Naskah Final (.docx)</span>
            </a>
        </div>

        @if(!empty($submission['copyedit_final_file']['notes_from_copyeditor']))
            <div class="mt-2.5 pt-2.5 border-t border-gray-200/70 text-xs text-gray-600 flex items-start gap-1.5">
                <span class="font-semibold text-gray-700 shrink-0">Catatan Copyeditor:</span>
                <span class="italic text-gray-600">"{{ $submission['copyedit_final_file']['notes_from_copyeditor'] }}"</span>
            </div>
        @endif
    </div>
</div>
