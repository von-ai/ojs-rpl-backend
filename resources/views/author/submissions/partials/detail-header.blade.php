<!-- Breadcrumb & Navigasi Cepat -->
<div class="flex items-center justify-between gap-3 mb-1">
    <nav class="flex items-center gap-2 text-xs text-gray-500 min-w-0" aria-label="Breadcrumb">
        <a href="{{ route('author.submissions.index') }}" class="hover:text-brand-maroon transition duration-150 font-medium flex items-center gap-1.5 focus:outline-hidden">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Portal Penulis</span>
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('author.submissions.index') }}" class="hover:text-brand-maroon transition duration-150 font-medium truncate" title="Daftar Naskah Saya">
            Daftar Naskah Saya
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold tabular-nums truncate" aria-current="page" title="{{ $submission['submission_number'] }}">
            {{ $submission['submission_number'] }}
        </span>
    </nav>

    <!-- Tombol Kembali Minimalis -->
    <a 
        href="{{ route('author.submissions.index') }}" 
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-brand-maroon transition duration-150 shrink-0"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali</span>
    </a>
</div>

<!-- Header Kartu Utama Naskah -->
<header class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-4">
    <!-- Baris Status, Tag Kategori, dan Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Badges Kategori & Status -->
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <!-- Badge Nomor Submisi dengan Klik Salin -->
            <button 
                type="button" 
                onclick="copyTextToClipboard('{{ $submission['submission_number'] }}', 'Nomor naskah {{ $submission['submission_number'] }} disalin!')"
                class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md font-sans text-xs font-semibold text-gray-800 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition duration-150 tabular-nums cursor-pointer"
                title="Salin nomor pengajuan"
            >
                <span>{{ $submission['submission_number'] }}</span>
                <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-gray-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </button>

            <!-- Rubrik / Seksi -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-md font-medium text-gray-700 bg-gray-50 border border-gray-200 shrink-0" title="Kategori Rubrik / Seksi: {{ $submission['section'] }}">
                {{ $submission['section'] }}
            </span>

            <!-- Status Alur Kerja -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-md font-semibold {{ $submission['status_badge_class'] }} border shrink-0" title="Status Naskah: {{ $submission['status_label'] }}">
                <span>{{ $submission['status_label'] }}</span>
            </span>

            <!-- Indikator Tahap Alur -->
            @if($submission['status'] === 'declined')
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 shrink-0">
                    Alur Terhenti (Ditolak)
                </span>
            @elseif(in_array($submission['status'], ['archived', 'withdrawn'], true))
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-700 bg-zinc-100 border border-zinc-200 shrink-0">
                    {{ $submission['status'] === 'archived' ? 'Naskah Diarsipkan' : 'Naskah Ditarik' }}
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 tabular-nums shrink-0">
                    Tahap {{ $submission['stage_number'] }} dari 6
                </span>
            @endif
        </div>

        <!-- Tombol Aksi Header -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Salin Tautan Halaman -->
            <button 
                type="button" 
                onclick="copyTextToClipboard(window.location.href, 'Tautan naskah berhasil disalin!')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg transition duration-150 cursor-pointer"
                title="Salin tautan ke naskah ini"
            >
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span>Tautan</span>
            </button>

            <!-- Aksi Kontekstual Primer -->
            @if($submission['status'] === 'draft')
                <a 
                    href="{{ route('author.submissions.create', ['draft_id' => $submission['id']]) }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg transition duration-150"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Lanjutkan Draf</span>
                </a>
            @elseif($submission['status'] === 'published')
                <a 
                    href="#panel-publikasi" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 rounded-lg transition duration-150"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Lihat Terbitan</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Judul Manuskrip Akademik (Font Sans Inter Bersih & Konsisten) -->
    <h1 class="font-sans text-2xl sm:text-[26px] lg:text-[28px] font-bold text-gray-900 leading-tight tracking-tight break-words [overflow-wrap:anywhere] pt-1" title="{{ $submission['title'] }}">
        {{ $submission['title'] }}
    </h1>

    <!-- Baris Metadata Publikasi & Waktu Pengajuan -->
    <div class="flex items-center gap-x-5 gap-y-2 text-xs text-gray-500 flex-wrap pt-2 border-t border-gray-100">
        <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Diajukan: <strong class="text-gray-700 font-medium tabular-nums">{{ $submission['submitted_at'] }}</strong></span>
        </div>

        <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Diperbarui: <strong class="text-gray-700 font-medium tabular-nums">{{ $submission['updated_at'] }}</strong></span>
        </div>

        <div class="hidden sm:flex items-center gap-1.5 text-gray-400">
            <span class="text-gray-300">/</span>
            <span class="text-gray-600 font-medium truncate max-w-sm" title="{{ $submission['journal_name'] }}">{{ $submission['journal_name'] }}</span>
        </div>
    </div>
</header>
