@extends('layouts.eic')

@section('title', 'Dashboard Pemimpin Redaksi — Portal Pemimpin Redaksi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Page Header & Pintasan Cepat -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Dashboard Pemimpin Redaksi
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Ringkasan eksekutif alur editorial naskah ilmiah, prioritas skrining awal, progres penelaahan sejawat, dan riwayat keputusan penerbitan.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a 
                href="{{ route('eic.guidelines') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
            >
                <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Pedoman Redaksi</span>
            </a>

            <a 
                href="{{ route('eic.submissions.index') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg transition cursor-pointer"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Buka Antrean Editorial</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik Ringkasan (Konsisten & Rapi) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($viewModel->metricCards() as $card)
            <x-kpi-card
                :title="$card['title']"
                :value="$card['count']"
                :icon="$card['icon'] ?? 'clipboard'"
            />
        @endforeach
    </div>

    <!-- Grid Utama 2 Kolom (2/3 Prioritas Naskah, 1/3 Riwayat Keputusan & Rubrik) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Kolom Kiri (2/3): Naskah Prioritas Menunggu Tindakan -->
        <div class="lg:col-span-2 min-w-0 space-y-6">
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div 
                        class="flex items-center gap-2.5 min-w-0 cursor-help"
                        data-tooltip="Naskah Prioritas Menunggu Tindakan"
                        data-tooltip-desc="Daftar naskah aktif yang memerlukan skrining awal, pendelegasian editor bagian, atau keputusan akhir"
                    >
                        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
                            Naskah Prioritas Menunggu Tindakan
                        </h2>
                    </div>
                    <a 
                        href="{{ route('eic.submissions.index') }}" 
                        class="text-xs font-semibold text-brand-maroon hover:underline shrink-0"
                    >
                        Lihat Semua
                    </a>
                </div>

                @if($prioritySubmissions->isEmpty())
                    <div class="p-8 text-center text-xs text-gray-500">
                        Seluruh naskah dalam antrean aktif telah ditindaklanjuti.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($prioritySubmissions as $sub)
                            @php
                                $meta = $viewModel->statusMeta($sub->status);
                                $assignedSe = $sub->activeEditorAssignment?->editor;
                            @endphp
                            <div class="p-4 sm:p-5 hover:bg-gray-50/70 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <div class="flex items-center gap-2 flex-wrap text-xs">
                                        <span class="font-semibold text-gray-700 tabular-nums">{{ $sub->submission_number }}</span>
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 border border-gray-200 text-[11px] font-medium">
                                            {{ $sub->section?->name ?? 'Artikel Riset' }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $meta['badge_class'] }}">
                                            {{ $meta['label'] }}
                                        </span>
                                    </div>

                                    <a 
                                        href="{{ route('eic.submissions.show', $sub->id) }}" 
                                        class="block text-sm font-medium text-gray-900 hover:text-brand-maroon transition line-clamp-1"
                                    >
                                        {{ $sub->title }}
                                    </a>

                                    <div class="flex items-center gap-x-3 gap-y-1 flex-wrap text-xs text-gray-500">
                                        <span>Penulis: <strong class="font-medium text-gray-700">{{ $sub->author?->name ?? 'Penulis Naskah' }}</strong></span>
                                        <span class="text-gray-300">/</span>
                                        <span>Editor Bagian: <strong class="font-medium text-gray-700">{{ $assignedSe?->name ?? 'Belum ditugaskan' }}</strong></span>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a 
                                        href="{{ route('eic.submissions.show', $sub->id) }}" 
                                        class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <!-- Kolom Kanan (1/3): Keputusan Terbaru & Distribusi Rubrik -->
        <aside class="lg:col-span-1 min-w-0 space-y-6">
            <!-- Kartu Keputusan Editorial Terbaru -->
            <section class="bg-white rounded-xl border border-gray-200/90 shadow-2xs p-5 space-y-4 text-xs">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div 
                        class="flex items-center gap-2.5 min-w-0 cursor-help"
                        data-tooltip="Keputusan Editorial Terbaru"
                        data-tooltip-desc="Rekaman surat keputusan resmi terakhir yang diterbitkan Pemimpin Redaksi"
                    >
                        <svg class="w-4 h-4 text-gray-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900 leading-tight truncate">
                            Keputusan Editorial Terbaru
                        </h3>
                    </div>
                </div>

                @if($recentDecisions->isEmpty())
                    <p class="text-gray-400 py-2">Belum ada surat keputusan editorial yang diterbitkan.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($recentDecisions as $dec)
                            @php
                                $decBadge = match($dec->decision) {
                                    'accept' => 'bg-emerald-600 text-white border-emerald-600',
                                    'revisions_required', 'resubmit' => 'bg-amber-600 text-white border-amber-600',
                                    'decline' => 'bg-brand-maroon text-white border-brand-maroon',
                                    default => 'bg-gray-600 text-white border-gray-600',
                                };
                                $decLabel = match($dec->decision) {
                                    'accept' => 'Diterima',
                                    'revisions_required' => 'Perlu Revisi',
                                    'resubmit' => 'Telaah Ulang',
                                    'decline' => 'Ditolak',
                                    default => 'Diputuskan',
                                };
                            @endphp
                            <div class="py-3 first:pt-0 last:pb-0 space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-gray-800 tabular-nums">{{ $dec->submission?->submission_number ?? '-' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $decBadge }}">
                                        {{ $decLabel }}
                                    </span>
                                </div>
                                @if($dec->submission)
                                    <a 
                                        href="{{ route('eic.submissions.show', $dec->submission->id) }}" 
                                        class="block text-gray-700 hover:text-brand-maroon font-medium truncate transition"
                                    >
                                        {{ $dec->submission->title }}
                                    </a>
                                @endif
                                <span class="block text-[11px] text-gray-400 tabular-nums">
                                    {{ $dec->sent_at?->format('d M Y, H:i') ?? '-' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Kartu Distribusi Rubrik Jurnal -->
            <section class="bg-white rounded-xl border border-gray-200/90 shadow-2xs p-5 space-y-4 text-xs">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div 
                        class="flex items-center gap-2.5 min-w-0 cursor-help"
                        data-tooltip="Distribusi Rubrik Jurnal"
                        data-tooltip-desc="Sebaran jumlah naskah masuk berdasarkan kategori seksi ilmiah"
                    >
                        <svg class="w-4 h-4 text-gray-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900 leading-tight">
                            Distribusi Rubrik Jurnal
                        </h3>
                    </div>
                    <span class="text-[11px] font-medium text-gray-500 tabular-nums shrink-0">
                        Total {{ $sections->sum('submissions_count') }} Naskah
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($sections as $sec)
                        <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3 text-xs">
                            <span class="text-gray-700 font-medium leading-snug line-clamp-2" title="{{ $sec->name }}">
                                {{ $sec->name }}
                            </span>
                            <span class="inline-flex items-center justify-center min-w-6 px-2 py-0.5 rounded-md text-[11px] font-semibold tabular-nums shrink-0 {{ $sec->submissions_count > 0 ? 'bg-brand-maroon text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ $sec->submissions_count }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>

    </div>

</div>
@endsection
