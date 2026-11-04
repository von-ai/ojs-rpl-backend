@extends('layouts.journalmanager')

@section('title', 'Ruang Kerja Edisi ' . $issue['volume'] . '.' . $issue['number'] . ' — Portal Journal Manager')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- 1. Header Metadata Edisi & Tombol Navigasi -->
    <div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <a
                href="{{ route('journalmanager.issues.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-brand-maroon transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Katalog Terbitan</span>
            </a>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $issue['status_badge_class'] }}">
                    {{ $issue['status_label'] }}
                </span>
                @if($issue['is_current'])
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        <span>Current Issue</span>
                    </span>
                @endif
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 text-xs font-mono font-bold text-brand-maroon mb-1">
                <span>Vol. {{ $issue['volume'] }} No. {{ $issue['number'] }} ({{ $issue['year'] }})</span>
                <span>•</span>
                <span class="text-gray-400 font-sans font-normal">DOI Prefix: {{ $issue['doi_prefix'] }}</span>
            </div>
            <h1 class="text-lg sm:text-2xl font-sans font-bold text-gray-900 leading-snug tracking-tight">
                {{ $issue['title'] }}
            </h1>
            @if(!empty($issue['description']))
                <p class="text-xs sm:text-sm text-gray-600 mt-1.5 leading-relaxed">
                    {{ $issue['description'] }}
                </p>
            @endif
        </div>
    </div>

    <!-- 2. Tata Letak 2 Kolom: Daftar Isi & Otorisasi Terbit -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Daftar Isi & Pemilihan Naskah (2 Kolom Lebar) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Panel 1: Daftar Isi Terbitan (Table of Contents) -->
            <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-gray-200/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-brand-maroon text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            {{ count($issue['articles']) }}
                        </span>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-gray-900">
                                Daftar Isi Edisi (Table of Contents)
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Susunan urutan naskah resmi dan rentang penomoran halaman artikel.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    @if(count($issue['articles']) > 0)
                        <div class="space-y-3">
                            @foreach($issue['articles'] as $art)
                                <div class="p-3.5 rounded-lg border border-gray-200 hover:border-gray-300 bg-white shadow-2xs transition">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-gray-100 text-gray-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            #{{ $art['position'] }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[10px] font-mono font-bold text-gray-400 block mb-0.5">
                                                {{ $art['submission_number'] }} • {{ $art['section_title'] }}
                                            </span>
                                            <h3 class="text-xs sm:text-sm font-sans font-bold text-gray-900 leading-snug">
                                                {{ $art['title'] }}
                                            </h3>
                                            <span class="text-[11px] text-gray-600 block mt-1">
                                                Penulis: {{ $art['authors_string'] }}
                                            </span>
                                            <div class="flex flex-wrap items-center justify-between gap-3 text-[11px] text-gray-500 mt-1.5 pt-1.5 border-t border-gray-100">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold text-gray-700">Halaman: {{ $art['pages'] }}</span>
                                                    <span>•</span>
                                                    <span class="font-mono text-gray-400">DOI: {{ $art['doi'] }}</span>
                                                    <span>•</span>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $art['payment_badge'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                                        {{ $art['payment_label'] ?? 'Bebas Biaya' }}
                                                    </span>
                                                    @if(!empty($art['is_paid_journal']))
                                                        <button
                                                            type="button"
                                                            onclick="document.getElementById('modal-pay-art-{{ $art['id'] }}').classList.remove('hidden'); document.getElementById('modal-pay-art-{{ $art['id'] }}').classList.add('flex');"
                                                            class="text-brand-maroon hover:underline font-semibold cursor-pointer"
                                                        >
                                                            Verifikasi Pembayaran
                                                        </button>
                                                    @endif
                                                </div>
                                                <a
                                                    href="{{ $art['galley_pdf_url'] }}"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-brand-maroon hover:underline"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span>Unduh Galai PDF</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($art['is_paid_journal']))
                                    @include('journalmanager.issues.partials.modal-payment', ['modalId' => 'modal-pay-art-'.$art['id'], 'item' => $art])
                                @endif
                            @endforeach
                        </div>
                    @else
                        <!-- State Belum Ada Artikel -->
                        <div class="p-6 text-center border-2 border-dashed border-gray-200 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-2.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-800">Belum ada artikel di dalam edisi ini</h3>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                                Masukkan artikel yang telah disetujui EIC dari antrean di bawah ini untuk menyusun daftar isi.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Panel 2: Antrean Artikel Siap Terbit (Ready for Issue Queue) -->
            @if(count($issue['ready_articles']) > 0)
                <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-gray-200/90 flex items-center justify-between bg-blue-50/40">
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-gray-900">
                                Antrean Naskah Siap Terbit ({{ count($issue['ready_articles']) }})
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Naskah yang telah memperoleh persetujuan akhir galai EIC dan siap dijadwalkan ke edisi ini.
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            Disetujui EIC
                        </span>
                    </div>

                    <div class="p-4 sm:p-5 space-y-3">
                        @foreach($issue['ready_articles'] as $ready)
                            <div class="p-3.5 rounded-lg border border-gray-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                                <div class="min-w-0">
                                    <span class="text-[10px] font-mono font-bold text-gray-400 block mb-0.5">
                                        {{ $ready['number'] }} • {{ $ready['section_title'] }}
                                    </span>
                                    <h3 class="text-xs font-sans font-bold text-gray-900 leading-snug">
                                        {{ $ready['title'] }}
                                    </h3>
                                    <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-1">
                                        <span>Penulis: {{ $ready['author_name'] }}</span>
                                        <span>•</span>
                                        <span class="text-emerald-700 font-semibold">{{ $ready['galley_format'] }}</span>
                                        <span>•</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $ready['payment_badge'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                            {{ $ready['payment_label'] ?? 'Bebas Biaya' }}
                                        </span>
                                        @if(!empty($ready['is_paid_journal']))
                                            <button
                                                type="button"
                                                onclick="document.getElementById('modal-pay-ready-{{ $ready['id'] }}').classList.remove('hidden'); document.getElementById('modal-pay-ready-{{ $ready['id'] }}').classList.add('flex');"
                                                class="text-brand-maroon hover:underline font-semibold cursor-pointer"
                                            >
                                                {{ !empty($ready['has_proof']) ? 'Bukti Bayar & Verifikasi' : 'Status Tagihan' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <form action="{{ route('journalmanager.issues.add-article', $issue['id']) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="article_id" value="{{ $ready['id'] }}">
                                    <input type="hidden" name="article_title" value="{{ $ready['title'] }}">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition whitespace-nowrap"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Masukkan ke Edisi</span>
                                    </button>
                                </form>
                            </div>
                            @if(!empty($ready['is_paid_journal']))
                                @include('journalmanager.issues.partials.modal-payment', ['modalId' => 'modal-pay-ready-'.$ready['id'], 'item' => $ready])
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Kolom Kanan: Otorisasi Publikasi, Metadata & Linimasa (1 Kolom) -->
        <div class="space-y-6">

            <!-- Panel Otorisasi Publikasi -->
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                    Otorisasi Publikasi Edisi
                </h3>

                @if($issue['status'] === 'published')
                    <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-2">
                        <div class="flex items-center gap-2 font-bold text-emerald-800">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Edisi Telah Terbit Resmi</span>
                        </div>
                        <p class="text-[11px] text-gray-600">
                            Dipublikasikan pada: <strong class="text-gray-800">{{ $issue['published_at'] }}</strong>
                        </p>
                    </div>

                    @if(!$issue['is_current'])
                        <form action="{{ route('journalmanager.issues.set-current', $issue['id']) }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Tetapkan sebagai Current Issue</span>
                            </button>
                        </form>
                    @endif
                @else
                    <!-- Status Draf / Terjadwal -->
                    @if($issue['can_publish'])
                        @php
                            $unpaidCount = collect($issue['articles'])->filter(function ($a) {
                                return !empty($a['is_paid_journal']) && !in_array($a['payment_status'] ?? '', ['verified', 'waived', 'free'], true);
                            })->count();
                        @endphp

                        @if($unpaidCount > 0)
                            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-900 space-y-1">
                                <div class="flex items-center gap-1.5 font-bold text-amber-800">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Peringatan Pembayaran APC ({{ $unpaidCount }} Naskah)</span>
                                </div>
                                <p class="text-[11px] text-amber-800">
                                    Terdapat {{ $unpaidCount }} naskah berbayar yang belum diverifikasi atau belum lunas.
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('journalmanager.issues.publish', $issue['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mempublikasikan edisi terbitan ini ke publik?');">
                            @csrf
                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Publikasikan Edisi Sekarang</span>
                            </button>
                        </form>
                    @else
                        <!-- Guard Invariant Reminder: Empty issue -->
                        <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800">
                            <span class="font-bold block mb-1">Peringatan Publikasi:</span>
                            Edisi wajib memiliki minimal 1 naskah sah sebelum dapat dipublikasikan.
                        </div>
                    @endif
                @endif
            </div>

            <!-- Panel Pengindeksan & DOI Crossref -->
            <div class="bg-gray-50/70 rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-3 text-xs">
                <h4 class="font-bold text-gray-800">Pengindeksan & Metadata DOI</h4>
                <div class="space-y-2 text-gray-600 text-[11px]">
                    <div class="flex justify-between">
                        <span>Prefix DOI:</span>
                        <span class="font-mono font-bold text-gray-800">{{ $issue['doi_prefix'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Penyedia Registri:</span>
                        <span class="font-semibold text-gray-800">Crossref via BRIDA</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Format Metadata:</span>
                        <span class="font-semibold text-gray-800">Dublin Core & Crossref XML</span>
                    </div>
                </div>
            </div>

            <!-- Panel Jejak Aktivitas Linimasa -->
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                    Linimasa Edisi Terbitan
                </h4>

                <div class="relative pl-4 space-y-3 border-l-2 border-gray-100 ml-1">
                    @foreach($issue['activity_timeline'] as $activity)
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $activity['badge_class'] }}"></div>
                            <span class="text-[11px] font-bold text-gray-900 block leading-tight">{{ $activity['title'] }}</span>
                            <span class="text-[10px] text-gray-500 block mt-0.5">{{ $activity['actor'] }} • {{ $activity['date'] }}</span>
                            <p class="text-[11px] text-gray-600 mt-1 leading-snug">{{ $activity['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
