<!-- Visual Workflow Tracker: 6 Tahapan Alur Kerja Editorial OJS 3 -->
<section class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 sm:p-6 space-y-5">
    <!-- Header Tracker -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-red-50 text-brand-maroon flex items-center justify-center shrink-0 border border-red-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 leading-tight">
                    Tahapan Alur Editorial & Penelaahan
                </h2>
                <p class="text-[11px] text-slate-500 hidden sm:block">Standar Alur Penerbitan Jurnal Ilmiah OJS 3 BRIDA</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold text-brand-maroon bg-red-50/80 border border-red-100 tabular-nums">
                Tahap {{ $submission['stage_number'] }} dari 6
            </span>
        </div>
    </div>

    <!-- Stepper 6 Kolom dengan Konektor Garis Progres -->
    <div class="relative">
        <!-- Garis Penghubung Horizontal (Desktop) -->
        <div class="hidden lg:block absolute top-4 left-6 right-6 h-0.5 bg-slate-200/90 -z-0" aria-hidden="true"></div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 relative z-10">
            @foreach($submission['workflow_stages'] as $stg)
                @php
                    $isCompleted = ($stg['state'] === 'completed');
                    $isCurrent = ($stg['state'] === 'current');
                    $isUpcoming = ($stg['state'] === 'upcoming');
                @endphp
                <div class="p-3.5 rounded-xl border flex flex-col justify-between transition-all duration-200 {{ $isCurrent ? 'bg-red-50/70 border-brand-maroon/50 shadow-sm ring-1 ring-brand-maroon/20' : ($isCompleted ? 'bg-emerald-50/40 border-emerald-200/80 hover:bg-emerald-50/60' : 'bg-slate-50/50 border-slate-200/70 opacity-65') }}">
                    <div class="space-y-2">
                        <!-- Node Lingkaran Indikator -->
                        <div class="flex items-center justify-between gap-1.5">
                            <span class="text-[11px] font-mono font-bold tabular-nums {{ $isCurrent ? 'text-brand-maroon' : ($isCompleted ? 'text-emerald-700' : 'text-slate-400') }}">
                                0{{ $stg['number'] }}
                            </span>

                            @if($isCompleted)
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs ring-2 ring-emerald-100" title="Tahap Selesai">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            @elseif($isCurrent)
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-maroon opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-maroon ring-2 ring-red-200"></span>
                                </span>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                            @endif
                        </div>

                        <!-- Judul Tahap -->
                        <h3 class="text-xs font-bold leading-tight {{ $isCurrent ? 'text-brand-maroon' : ($isCompleted ? 'text-slate-900' : 'text-slate-500') }}">
                            {{ $stg['title'] }}
                        </h3>
                    </div>

                    <!-- Keterangan Tahap -->
                    <p class="mt-2 text-[10px] sm:text-[11px] text-slate-500 leading-snug line-clamp-2" title="{{ $stg['desc'] }}">
                        {{ $stg['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Banner Kontekstual Tahap Aktif Saat Ini -->
    @php
        $currentStage = collect($submission['workflow_stages'])->firstWhere('state', 'current') ?? $submission['workflow_stages'][0];
    @endphp
    <div class="p-3.5 rounded-xl bg-slate-50/90 border border-slate-200/90 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-brand-maroon shrink-0"></span>
            <span class="text-slate-700">
                Tahap Aktif: <strong class="text-slate-900 font-semibold">{{ $currentStage['title'] }}</strong> &mdash; {{ $currentStage['desc'] }}
            </span>
        </div>
        <span class="text-[11px] font-medium text-slate-500 shrink-0">
            Status: <span class="text-slate-800 font-semibold">{{ $submission['status_label'] }}</span>
        </span>
    </div>
</section>
