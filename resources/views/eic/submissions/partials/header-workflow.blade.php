{{-- resources/views/eic/submissions/partials/header-workflow.blade.php --}}
@php
    $statusInfo = $viewModel->statusInfo();
    $steps = $viewModel->workflowSteps();
@endphp

<!-- Breadcrumb & Navigasi Cepat (Konsisten dengan Portal Penulis) -->
<div class="flex items-center justify-between gap-3 mb-1">
    <nav class="flex items-center gap-2 text-xs text-gray-500 min-w-0" aria-label="Breadcrumb">
        <a href="{{ route('eic.submissions.index') }}" class="hover:text-brand-maroon transition duration-150 font-medium flex items-center gap-1.5 focus:outline-hidden">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Portal Pemimpin Redaksi</span>
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('eic.submissions.index') }}" class="hover:text-brand-maroon transition duration-150 font-medium truncate">
            Antrean Editorial
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold tabular-nums truncate" aria-current="page">
            {{ $submission->submission_number }}
        </span>
    </nav>

    <!-- Tombol Kembali Minimalis -->
    <a 
        href="{{ route('eic.submissions.index') }}" 
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-brand-maroon transition duration-150 shrink-0"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali</span>
    </a>
</div>

<!-- Header Kartu Utama Naskah (Konsisten dengan Portal Penulis) -->
<header class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-4">
    <!-- Baris Status, Tag Kategori, dan Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <!-- Nomor Submisi -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-md font-sans text-xs font-semibold text-gray-800 bg-gray-50 border border-gray-200 tabular-nums shrink-0">
                {{ $submission->submission_number }}
            </span>

            <!-- Rubrik / Seksi -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-md font-medium text-gray-700 bg-gray-50 border border-gray-200 shrink-0">
                {{ $submission->section?->name ?? 'Artikel Riset' }}
            </span>

            <!-- Status Alur Kerja -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-md font-semibold {{ $statusInfo['badge_class'] }} border shrink-0">
                {{ $statusInfo['label'] }}
            </span>

            <!-- Indikator Tahap -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 tabular-nums shrink-0">
                {{ $statusInfo['stage_name'] }}
            </span>
        </div>

        <!-- Tombol Cetak Ringkasan -->
        <div class="flex items-center gap-2 shrink-0">
            <button 
                type="button" 
                onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg transition duration-150 cursor-pointer"
                title="Cetak lembar ringkasan naskah"
            >
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Judul Manuskrip Akademik (Font Sans Inter Bersih & Konsisten) -->
    <h1 class="font-sans text-2xl sm:text-[26px] lg:text-[28px] font-bold text-gray-900 leading-tight tracking-tight break-words [overflow-wrap:anywhere] pt-1">
        {{ $submission->title }}
    </h1>

    <!-- Baris Metadata Publikasi & Waktu Pengajuan -->
    <div class="flex items-center gap-x-5 gap-y-2 text-xs text-gray-500 flex-wrap pt-2 border-t border-gray-100">
        <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Diajukan: <strong class="text-gray-700 font-medium tabular-nums">{{ ($submission->submitted_at ?? $submission->created_at)?->format('d M Y, H:i') ?? '-' }}</strong></span>
        </div>

        <div class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Penulis Utama: <strong class="text-gray-700 font-medium">{{ $submission->author?->name ?? 'Penulis Naskah' }}</strong></span>
        </div>

        @if($submission->journal?->name)
            <div class="hidden sm:flex items-center gap-1.5 text-gray-400">
                <span class="text-gray-300">/</span>
                <span class="text-gray-600 font-medium truncate max-w-sm">{{ $submission->journal->name }}</span>
            </div>
        @endif
    </div>

    <!-- Stepper Progres Alur Kerja Editorial 5 Tahap -->
    <div class="pt-3 border-t border-gray-100">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-2.5">
            @foreach($steps as $step)
                @php
                    $boxClass = match($step['state']) {
                        'completed' => 'bg-emerald-50/70 border-emerald-200 text-emerald-900',
                        'current' => 'bg-red-50/70 border-brand-maroon text-gray-900',
                        'terminated' => 'bg-rose-50/70 border-rose-200 text-rose-900',
                        default => 'bg-gray-50 border-gray-200 text-gray-500',
                    };
                    $badgeStepClass = match($step['state']) {
                        'completed' => 'bg-emerald-600 text-white',
                        'current' => 'bg-brand-maroon text-white',
                        'terminated' => 'bg-rose-600 text-white',
                        default => 'bg-gray-200 text-gray-600',
                    };
                @endphp
                <div 
                    class="p-3 rounded-lg border {{ $boxClass }} space-y-1 min-w-0 cursor-help"
                    data-tooltip="Tahap {{ $step['step'] }}: {{ $step['title'] }}"
                    data-tooltip-desc="{{ $step['subtitle'] }}"
                >
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-5 h-5 rounded {{ $badgeStepClass }} flex items-center justify-center text-[10px] font-bold shrink-0 tabular-nums">
                            {{ $step['step'] }}
                        </div>
                        <span class="text-xs font-bold truncate">{{ $step['title'] }}</span>
                    </div>
                    <p class="text-[11px] opacity-80 truncate">
                        {{ $step['subtitle'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</header>
