<div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
    <!-- Tombol Kembali & Baris Status Cepat -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3">
        <a 
            href="{{ route('proofreader.submissions.index') }}" 
            class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 hover:text-brand-maroon transition min-h-11 min-w-11"
            title="Kembali ke Daftar Penugasan Proofreading"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Penugasan Proofreading</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-gray-400 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded">
                {{ $submission['assignment_code'] }}
            </span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $submission['status_badge_class'] }}">
                {{ $submission['status_label'] }}
            </span>
        </div>
    </div>

    <!-- Judul & Bidang Naskah -->
    <div>
        <span class="text-xs font-mono font-bold text-brand-maroon uppercase tracking-wide block mb-1">
            {{ $submission['number'] }} • {{ $submission['section_title'] }}
        </span>
        <h1 class="text-xl sm:text-2xl font-bold font-serif text-gray-900 leading-snug tracking-tight">
            {{ $submission['title'] }}
        </h1>
    </div>

    <!-- Metadata Ringkas 4 Kolom -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-gray-100 text-xs">
        <div>
            <span class="text-gray-400 block font-medium">Penulis Utama:</span>
            <span class="font-bold text-gray-800 block truncate mt-0.5" title="{{ $submission['corresponding_author']['name'] }}">
                {{ $submission['corresponding_author']['name'] }}
            </span>
            <span class="text-[11px] text-gray-500 block truncate" title="{{ $submission['corresponding_author']['affiliation'] }}">
                {{ $submission['corresponding_author']['affiliation'] }}
            </span>
        </div>

        <div>
            <span class="text-gray-400 block font-medium">Layout Editor:</span>
            <span class="font-bold text-gray-800 block truncate mt-0.5" title="{{ $submission['layout_editor'] }}">
                {{ $submission['layout_editor'] }}
            </span>
            <span class="text-[11px] text-gray-500 block truncate">
                Bertanggung jawab tata letak galai
            </span>
        </div>

        <div>
            <span class="text-gray-400 block font-medium">Editor Bagian:</span>
            <span class="font-bold text-gray-800 block truncate mt-0.5" title="{{ $submission['section_editor'] }}">
                {{ $submission['section_editor'] }}
            </span>
            <span class="text-[11px] text-gray-500 block truncate">
                Redaksi penanggung jawab
            </span>
        </div>

        <div>
            <span class="text-gray-400 block font-medium">Batas Waktu Pemeriksaan:</span>
            <div class="flex items-center gap-1.5 mt-0.5">
                <svg class="w-3.5 h-3.5 {{ $submission['is_urgent'] ? 'text-amber-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-bold {{ $submission['is_urgent'] ? 'text-amber-700' : 'text-gray-800' }}">
                    {{ $submission['due_at'] }}
                </span>
            </div>
            @if($submission['is_urgent'])
                <span class="text-[10px] font-bold text-amber-600 block">Segera Diperiksa</span>
            @else
                <span class="text-[11px] text-gray-500 block">Ditugaskan {{ $submission['assigned_at'] }}</span>
            @endif
        </div>
    </div>
</div>
