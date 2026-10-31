@extends('layouts.reviewer')

@section('title', 'Dashboard Mitra Bestari — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">

    <!-- Header Selamat Datang & Profil Mitra Bestari -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Dashboard Mitra Bestari
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Portal evaluasi saintifik independen ganda tertutup (double-blind) untuk menjamin mutu dan objektivitas naskah ilmiah.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a 
                href="{{ route('reviewer.assignments.index') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg transition"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Buka Tugas Penelaahan</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik Ringkasan (Statis Murni, Non-Klik) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($viewModel->metricCards() as $card)
            <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 flex flex-col justify-between select-none shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider leading-tight truncate">
                        {{ $card['title'] }}
                    </span>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg {{ $card['icon_bg'] }} {{ $card['icon_text'] }} flex items-center justify-center shrink-0">
                        @if($card['icon'] === 'clipboard')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        @elseif($card['icon'] === 'mail')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @elseif($card['icon'] === 'document-search')
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
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

    <!-- Grid Utama 2 Kolom (2/3 Tugas Prioritas, 1/3 Ringkasan Telaah & Etika) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Kolom Kiri: Naskah Prioritas & Undangan Masuk -->
        <div class="lg:col-span-2 space-y-6">

            <!-- 1. Naskah Prioritas Sedang Ditelaah -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-2xs">
                <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">
                            Naskah Sedang Ditelaah
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Naskah aktif yang memerlukan evaluasi saintifik dan pengisian rubrik penilaian.
                        </p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 tabular-nums">
                        {{ $priorityAssignments->count() }} aktif
                    </span>
                </div>

                @if($priorityAssignments->isEmpty())
                    <div class="p-8 text-center text-gray-500 text-xs">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="font-medium text-gray-700">Tidak ada naskah yang sedang ditelaah saat ini.</p>
                        <p class="text-gray-400 mt-0.5">Undangan telaah yang disetujui akan muncul pada antrean kerja ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($priorityAssignments as $assignment)
                            @php
                                $sub = $assignment->reviewRound?->submission;
                                $displayTitle = $assignment->reviewRound?->reviewer_title ?: ($sub?->title ?? 'Naskah Ilmiah');
                                $sectionName = $sub?->section?->name ?: ($sub?->section?->title ?: 'Umum');
                                $subNumber = $sub?->submission_number ?: "SUB-{$sub?->id}";
                                $dueAt = $assignment->review_due_at;
                                $daysLeft = $dueAt ? (int) now()->diffInDays($dueAt, false) : null;
                            @endphp
                            <div class="p-4 sm:p-5 hover:bg-gray-50/80 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1.5 min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-bold text-gray-500">
                                            {{ $subNumber }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                            Putaran {{ $assignment->reviewRound?->round ?? 1 }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                            {{ $sectionName }}
                                        </span>
                                        @if($daysLeft !== null)
                                            @if($daysLeft < 0)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Terlewat {{ abs($daysLeft) }} hari
                                                </span>
                                            @elseif($daysLeft <= 3)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Sisa {{ $daysLeft }} hari
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold text-gray-600">
                                                    Tenggat: {{ $dueAt->format('d M Y') }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2">
                                        {{ $displayTitle }}
                                    </h3>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a 
                                        href="{{ route('reviewer.assignments.show', $assignment->id) }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-900 text-white hover:bg-gray-800 transition"
                                    >
                                        <span>Ruang Telaah</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 2. Undangan Masuk Menunggu Konfirmasi -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-2xs">
                <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">
                            Undangan Telaah Masuk
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Konfirmasi kesediaan Anda untuk menelaah naskah sesuai kepakaran saintifik.
                        </p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 tabular-nums">
                        {{ $pendingInvitations->count() }} undangan
                    </span>
                </div>

                @if($pendingInvitations->isEmpty())
                    <div class="p-8 text-center text-gray-500 text-xs">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="font-medium text-gray-700">Tidak ada undangan telaah baru saat ini.</p>
                        <p class="text-gray-400 mt-0.5">Undangan baru dari Editor Bagian akan muncul di sini.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($pendingInvitations as $invitation)
                            @php
                                $sub = $invitation->reviewRound?->submission;
                                $displayTitle = $invitation->reviewRound?->reviewer_title ?: ($sub?->title ?? 'Naskah Ilmiah');
                                $sectionName = $sub?->section?->name ?: ($sub?->section?->title ?: 'Umum');
                                $subNumber = $sub?->submission_number ?: "SUB-{$sub?->id}";
                            @endphp
                            <div class="p-4 sm:p-5 hover:bg-gray-50/80 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1.5 min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-bold text-gray-500">
                                            {{ $subNumber }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            Menunggu Konfirmasi
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                            {{ $sectionName }}
                                        </span>
                                        @if($invitation->response_due_at)
                                            <span class="text-xs text-gray-500">
                                                Batas Respon: {{ $invitation->response_due_at->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2">
                                        {{ $displayTitle }}
                                    </h3>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a 
                                        href="{{ route('reviewer.assignments.show', $invitation->id) }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition"
                                    >
                                        <span>Tanggapi Undangan</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Kolom Kanan: Telaah Selesai & Kotak Etika -->
        <div class="space-y-6">

            <!-- 1. Telaah Baru Diselesaikan -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-2xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h2 class="text-sm font-bold text-gray-900">
                        Telaah Selesai Terbaru
                    </h2>
                    <span class="text-xs text-gray-400 tabular-nums">Arsip</span>
                </div>

                @if($recentCompleted->isEmpty())
                    <p class="text-xs text-gray-500 text-left py-2">
                        Belum ada laporan evaluasi telaah yang diselesaikan pada periode ini.
                    </p>
                @else
                    <div class="space-y-3">
                        @foreach($recentCompleted as $comp)
                            @php
                                $sub = $comp->reviewRound?->submission;
                                $displayTitle = $comp->reviewRound?->reviewer_title ?: ($sub?->title ?? 'Naskah Ilmiah');
                                $recommendation = $comp->review?->recommendation ?? 'accept';
                                $badgeRec = match($recommendation) {
                                    'accept' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'revisions_required' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'resubmit' => 'bg-orange-50 text-orange-800 border-orange-200',
                                    'decline' => 'bg-rose-50 text-rose-800 border-rose-200',
                                    default => 'bg-gray-100 text-gray-800 border-gray-200',
                                };
                                $labelRec = match($recommendation) {
                                    'accept' => 'Terima Naskah',
                                    'revisions_required' => 'Perlu Revisi',
                                    'resubmit' => 'Kirim Ulang',
                                    'decline' => 'Tolak Naskah',
                                    default => ucfirst($recommendation),
                                };
                            @endphp
                            <div class="p-3 rounded-lg border border-gray-100 bg-gray-50/60 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] font-bold text-gray-500">
                                        {{ $sub?->submission_number }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badgeRec }}">
                                        {{ $labelRec }}
                                    </span>
                                </div>
                                <h4 class="text-xs font-semibold text-gray-800 line-clamp-2">
                                    {{ $displayTitle }}
                                </h4>
                                <div class="flex items-center justify-between text-[11px] text-gray-500 pt-1 border-t border-gray-100">
                                    <span>Terkirim: {{ $comp->updated_at->format('d M Y') }}</span>
                                    <a href="{{ route('reviewer.assignments.show', $comp->id) }}" class="text-brand-maroon hover:underline font-semibold">
                                        Lihat
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 2. Kotak Etika & Protokol Double-Blind -->
            <div class="bg-indigo-50/60 rounded-xl border border-indigo-100 p-5 space-y-3">
                <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-indigo-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Protokol Double-Blind</span>
                </div>
                <p class="text-xs text-indigo-900/80 leading-relaxed text-left">
                    Sistem secara otomatis menyaring seluruh data identitas penulis, institusi asal, dan metadata berkas untuk menjamin objektivitas saintifik.
                </p>
                <div class="pt-2">
                    <a 
                        href="{{ route('reviewer.guidelines') }}" 
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 hover:text-indigo-900 underline"
                    >
                        <span>Baca Pedoman & Etika Lengkap</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
