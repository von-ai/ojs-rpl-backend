@extends('layouts.section-editor')

@section('title', 'Dashboard Editor Bagian — Portal Editor Bagian')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Page Header & Pintasan Cepat -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Dashboard Editor Bagian
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Ringkasan penugasan naskah ilmiah, pemantauan putaran telaah sejawat (peer review), dan rekomendasi editorial untuk Pemimpin Redaksi.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a 
                href="{{ route('section-editor.guidelines') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
            >
                <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Pedoman Telaah</span>
            </a>

            <a 
                href="{{ route('section-editor.submissions.index') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg transition cursor-pointer"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Buka Antrean Telaah</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($viewModel->metricCards() as $card)
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 flex flex-col justify-between select-none">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider leading-tight truncate">
                        {{ $card['title'] }}
                    </span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg {{ $card['icon_bg'] }} {{ $card['icon_text'] }} flex items-center justify-center shrink-0">
                        @if($card['icon'] === 'clipboard')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        @elseif($card['icon'] === 'users')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @elseif($card['icon'] === 'refresh')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        @else
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                </div>
                <div class="mt-2 sm:mt-4">
                    <span class="text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums">
                        {{ $card['count'] }}
                    </span>
                    <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1 truncate">{{ $card['subtitle'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Grid Utama 2 Kolom (2/3 Prioritas Naskah, 1/3 Permohonan & Rekomendasi) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Kolom Kiri (2/3): Naskah Prioritas Menunggu Tindakan -->
        <div class="lg:col-span-2 min-w-0 space-y-6">
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div 
                        class="flex items-center gap-2.5 min-w-0 cursor-help"
                        data-tooltip="Naskah Prioritas Menunggu Tindakan"
                        data-tooltip-desc="Daftar naskah aktif yang memerlukan penugasan reviewer, peninjauan perpanjangan, atau pengiriman rekomendasi"
                    >
                        <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
                            Naskah Prioritas Menunggu Tindakan
                        </h2>
                    </div>
                    <a 
                        href="{{ route('section-editor.submissions.index') }}" 
                        class="text-xs font-semibold text-brand-maroon hover:underline shrink-0"
                    >
                        Lihat Semua
                    </a>
                </div>

                @if($prioritySubmissions->isEmpty())
                    <div class="p-8 text-center text-xs text-gray-500">
                        Seluruh naskah dalam antrean penugasan Anda telah ditindaklanjuti.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($prioritySubmissions as $sub)
                            @php
                                $meta = $viewModel->statusMeta($sub->status);
                                $round = $sub->currentReviewRound;
                                $completedCount = $round ? $round->assignments->where('status', 'completed')->count() : 0;
                                $minReq = $round ? max(1, (int) $round->minimum_completed_reviews) : 2;
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
                                        @if($round)
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200 tabular-nums">
                                                Putaran {{ $round->round }}: {{ $completedCount }}/{{ $minReq }} Selesai
                                            </span>
                                        @endif
                                    </div>

                                    <a 
                                        href="{{ route('section-editor.submissions.show', $sub->id) }}" 
                                        class="block text-sm font-semibold text-gray-900 hover:text-brand-maroon transition line-clamp-1"
                                    >
                                        {{ $sub->title }}
                                    </a>

                                    <div class="flex items-center gap-x-3 gap-y-1 flex-wrap text-xs text-gray-500">
                                        <span>Penulis: <strong class="font-medium text-gray-700">{{ $sub->author?->name ?? 'Penulis Naskah' }}</strong></span>
                                        <span class="text-gray-300">/</span>
                                        <span>Diajukan: <strong class="font-medium text-gray-700 tabular-nums">{{ ($sub->submitted_at ?? $sub->created_at)?->format('d M Y') ?? '-' }}</strong></span>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <a 
                                        href="{{ route('section-editor.submissions.show', $sub->id) }}" 
                                        class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition"
                                    >
                                        Ruang Telaah
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <!-- Kolom Kanan (1/3): Permohonan Perpanjangan & Rekomendasi Terkini -->
        <aside class="lg:col-span-1 min-w-0 space-y-6">
            <!-- Permohonan Perpanjangan Waktu Penelaah -->
            <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div 
                        class="flex items-center gap-2 min-w-0 cursor-help"
                        data-tooltip="Permohonan Perpanjangan Waktu"
                        data-tooltip-desc="Daftar permintaan perpanjangan tenggat telaah dari mitra bestari yang perlu diputuskan"
                    >
                        <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                            Permohonan Perpanjangan
                        </h3>
                    </div>
                </div>

                @if($pendingExtensions->isEmpty())
                    <p class="text-gray-400 py-2">Tidak ada permohonan perpanjangan waktu yang sedang pending.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($pendingExtensions as $ext)
                            <div class="py-3 first:pt-0 last:pb-0 space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-gray-800 truncate">{{ $ext->assignment?->reviewer?->name ?? 'Mitra Bestari' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                                        Pending
                                    </span>
                                </div>
                                <p class="text-gray-600 line-clamp-1 italic text-[11px]">
                                    "{{ $ext->reason }}"
                                </p>
                                <div class="flex items-center justify-between gap-2 text-[11px] pt-1">
                                    <span class="text-gray-500 tabular-nums">
                                        Sampai: <strong>{{ $ext->requested_due_at?->format('d M Y') }}</strong>
                                    </span>
                                    <a 
                                        href="{{ route('section-editor.submissions.show', $ext->assignment->reviewRound->submission_id) }}" 
                                        class="text-brand-maroon font-semibold hover:underline"
                                    >
                                        Tindak Lanjut &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Rekomendasi Editorial Terbaru ke EIC -->
            <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
                <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div 
                        class="flex items-center gap-2 min-w-0 cursor-help"
                        data-tooltip="Rekomendasi Editorial ke EIC"
                        data-tooltip-desc="Rekaman rekomendasi keputusan yang telah Anda serahkan kepada Pemimpin Redaksi"
                    >
                        <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                            Rekomendasi Terkini ke EIC
                        </h3>
                    </div>
                </div>

                @if($recentRecommendations->isEmpty())
                    <p class="text-gray-400 py-2">Belum ada rekomendasi editorial yang dikirimkan ke Pemimpin Redaksi.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($recentRecommendations as $recRound)
                            @php
                                $recBadge = match($recRound->se_recommendation) {
                                    'accept' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'revisions_required' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'resubmit' => 'bg-orange-50 text-orange-800 border-orange-200',
                                    'decline' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                                };
                                $recLabel = match($recRound->se_recommendation) {
                                    'accept' => 'Terima Naskah',
                                    'revisions_required' => 'Perlu Revisi',
                                    'resubmit' => 'Telaah Ulang',
                                    'decline' => 'Tolak Naskah',
                                    default => 'Rekomendasi',
                                };
                            @endphp
                            <div class="py-3 first:pt-0 last:pb-0 space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-gray-800 tabular-nums">{{ $recRound->submission?->submission_number ?? '-' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $recBadge }}">
                                        {{ $recLabel }}
                                    </span>
                                </div>
                                @if($recRound->submission)
                                    <a 
                                        href="{{ route('section-editor.submissions.show', $recRound->submission->id) }}" 
                                        class="block text-gray-700 hover:text-brand-maroon font-medium truncate transition"
                                    >
                                        {{ $recRound->submission->title }}
                                    </a>
                                @endif
                                <span class="block text-[11px] text-gray-400 tabular-nums">
                                    Diserahkan: {{ $recRound->se_recommended_at?->format('d M Y, H:i') ?? '-' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>

    </div>

</div>
@endsection
