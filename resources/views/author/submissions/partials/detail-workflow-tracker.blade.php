@php
    $currentStage = collect($submission['workflow_stages'])->firstWhere('state', 'current') 
        ?? collect($submission['workflow_stages'])->first();
    $isDeclined = ($submission['status'] === 'declined');
    $isWithdrawn = in_array($submission['status'], ['archived', 'withdrawn'], true);
@endphp

<!-- Visual Workflow Tracker: 6 Tahapan Alur Kerja Editorial OJS 3 -->
<section class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-4">
    <!-- Header Tracker -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight">
                    Tahapan Alur Editorial
                </h2>
                <p class="text-[11px] text-gray-500 hidden sm:block">Standar Alur Penerbitan Jurnal Ilmiah OJS 3 BRIDA</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($isDeclined)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200">
                    Alur Terhenti (Ditolak)
                </span>
            @elseif($isWithdrawn)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold text-zinc-800 bg-zinc-100 border border-zinc-200">
                    {{ $submission['status'] === 'archived' ? 'Naskah Diarsipkan' : 'Naskah Ditarik' }}
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold text-brand-maroon bg-red-50 border border-red-100 tabular-nums">
                    Tahap {{ $submission['stage_number'] }} dari 6
                </span>
            @endif
        </div>
    </div>

    <!-- Timeline Stepper 6 Tahap yang Ramping & Bersih -->
    <div class="py-2 overflow-x-auto">
        <div class="min-w-[620px] sm:min-w-0">
            <div class="grid grid-cols-6 relative">
                <!-- Garis Penghubung Latar Belakang -->
                <div class="absolute top-3.5 left-[8%] right-[8%] h-0.5 bg-gray-200 -z-0" aria-hidden="true"></div>

                @foreach($submission['workflow_stages'] as $stg)
                    @php
                        $isCompleted = ($stg['state'] === 'completed');
                        $isCurrent = ($stg['state'] === 'current');
                        $isStageDeclined = ($stg['state'] === 'declined');
                        $isStageWithdrawn = ($stg['state'] === 'withdrawn');
                    @endphp
                    <div class="flex flex-col items-center text-center relative z-10 px-1">
                        <!-- Node Lingkaran Indikator -->
                        <div class="mb-2">
                            @if($isCompleted)
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center" title="Tahap Selesai">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @elseif($isStageDeclined)
                                <div class="w-7 h-7 rounded-full bg-rose-600 text-white flex items-center justify-center" title="Tahap Terhenti (Ditolak)">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            @elseif($isStageWithdrawn)
                                <div class="w-7 h-7 rounded-full bg-zinc-500 text-white flex items-center justify-center" title="Naskah Ditarik/Diarsipkan">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 12H6" />
                                    </svg>
                                </div>
                            @elseif($isCurrent)
                                <div class="w-7 h-7 rounded-full bg-brand-maroon text-white font-bold text-xs flex items-center justify-center" title="Tahap Aktif Saat Ini">
                                    <span class="tabular-nums">{{ $stg['number'] }}</span>
                                </div>
                            @else
                                <div class="w-7 h-7 rounded-full bg-white text-gray-400 border border-gray-200 flex items-center justify-center text-xs font-medium" title="Menunggu Tahapan">
                                    <span class="tabular-nums">{{ $stg['number'] }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Judul Tahap -->
                        <h3 class="text-xs font-semibold leading-snug {{ $isCurrent ? 'text-brand-maroon font-bold' : ($isCompleted ? 'text-gray-900' : 'text-gray-500') }}">
                            {{ $stg['title'] }}
                        </h3>

                        <!-- Sub-keterangan Singkat -->
                        <p class="text-[10px] text-gray-400 mt-0.5 line-clamp-1 max-w-[90px] sm:max-w-none" title="{{ $stg['desc'] }}">
                            {{ $stg['desc'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Ringkasan Status Tahap Aktif -->
    <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-gray-600 bg-gray-50 -mx-5 sm:-mx-6 -mb-5 sm:-mb-6 px-5 sm:px-6 py-3 rounded-b-xl">
        <div class="flex items-center gap-1.5 min-w-0">
            <span class="truncate" title="{{ ($currentStage['title'] ?? 'Draf Submisi') }} &mdash; {{ ($currentStage['desc'] ?? '') }}">
                <strong class="text-gray-900">Tahap Aktif:</strong> {{ $currentStage['title'] ?? 'Draf Submisi' }} &mdash; 
                <span class="text-gray-500">{{ $currentStage['desc'] ?? '' }}</span>
            </span>
        </div>
        <div class="text-[11px] text-gray-500 shrink-0">
            Status: <span class="font-semibold text-gray-800">{{ $submission['status_label'] }}</span>
        </div>
    </div>
</section>
