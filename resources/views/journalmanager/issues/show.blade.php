@extends('layouts.journalmanager')

@section('title', 'Ruang Kerja Edisi ' . $issue['volume'] . '.' . $issue['number'] . ' — Portal Journal Manager')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

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
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Edisi Terkini
                    </span>
                @endif
            </div>
        </div>

        <div>
            <div class="flex items-center gap-2 text-xs font-normal text-gray-600 mb-1">
                <span class="tabular-nums">Vol. {{ $issue['volume'] }} No. {{ $issue['number'] }} ({{ $issue['year'] }})</span>
                <span class="text-gray-300">•</span>
                <span class="text-gray-500 font-normal tabular-nums">Prefiks DOI: {{ $issue['doi_prefix'] }}</span>
            </div>
            <h1 class="text-lg sm:text-xl lg:text-2xl font-sans font-medium text-gray-900 leading-snug">
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
                        <span class="w-6 h-6 rounded-full bg-brand-maroon text-white font-semibold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            {{ count($issue['articles']) }}
                        </span>
                        <div>
                            <h2 class="text-sm sm:text-base font-medium text-gray-900">
                                Daftar Isi Edisi
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Susunan urutan naskah resmi dan rentang penomoran halaman artikel.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    @if(count($issue['articles']) > 0)
                        <div class="space-y-3.5">
                            @foreach($issue['articles'] as $art)
                                <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 bg-white shadow-2xs transition duration-150">
                                    <div class="flex items-start gap-3.5">
                                        <!-- Nomor Urut (Tanpa Pagar) -->
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-700 font-medium text-xs flex items-center justify-center shrink-0 tabular-nums">
                                            {{ $art['position'] }}
                                        </div>

                                        <!-- Konten Utama Naskah -->
                                        <div class="min-w-0 flex-1 space-y-1">
                                            <div class="flex items-center gap-2 text-xs text-gray-500 tabular-nums">
                                                <span class="font-medium text-gray-700">{{ $art['submission_number'] }}</span>
                                                <span>•</span>
                                                <span>{{ $art['section_title'] }}</span>
                                            </div>

                                            <h3 class="text-sm font-normal text-gray-900 leading-snug">
                                                {{ $art['title'] }}
                                            </h3>

                                            <p class="text-xs text-gray-500">
                                                Penulis: <span class="text-gray-700 font-normal">{{ $art['authors_string'] }}</span>
                                            </p>

                                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 tabular-nums pt-0.5">
                                                <span class="font-medium text-gray-700">Halaman: {{ $art['pages'] }}</span>
                                                <span class="text-gray-300">•</span>
                                                <span class="text-gray-500">DOI: {{ $art['doi'] }}</span>
                                            </div>

                                            <!-- Baris Status Bayar, Verifikasi Pembayaran & Unduh Galai (1 Baris Terpadu) -->
                                            <div class="pt-2.5 mt-2.5 border-t border-gray-100 flex flex-wrap sm:flex-nowrap items-center justify-between gap-2.5 text-xs">
                                                <!-- Kiri: Status Pembayaran & Tombol Verifikasi -->
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <!-- Badge Status Pembayaran -->
                                                    <div class="relative group inline-flex">
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold border cursor-help transition-colors {{ $art['payment_badge'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                                            @if(($art['payment_status'] ?? '') === 'verified')
                                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            @elseif(($art['payment_status'] ?? '') === 'pending_verification')
                                                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @elseif(($art['payment_status'] ?? '') === 'free')
                                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @elseif(($art['payment_status'] ?? '') === 'rejected')
                                                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @else
                                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                            @endif
                                                            <span>{{ $art['payment_label'] ?? 'Bebas Biaya' }}</span>
                                                        </span>

                                                        <!-- Tooltip Status Bayar -->
                                                        <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:flex flex-col items-start z-30 w-44">
                                                            <div class="bg-gray-900 text-white text-[11px] font-normal leading-snug px-2.5 py-1.5 rounded-md shadow-lg border border-gray-800">
                                                                @if(($art['payment_status'] ?? '') === 'verified')
                                                                    Pembayaran APC lunas
                                                                @elseif(($art['payment_status'] ?? '') === 'pending_verification')
                                                                    Menunggu verifikasi bukti transfer
                                                                @elseif(($art['payment_status'] ?? '') === 'free')
                                                                    Bebas biaya publikasi (Free APC)
                                                                @elseif(($art['payment_status'] ?? '') === 'rejected')
                                                                    Bukti transfer ditolak redaksi
                                                                @else
                                                                    Menunggu pembayaran APC dari penulis
                                                                @endif
                                                            </div>
                                                            <div class="w-2 h-2 -mt-1 ml-4 rotate-45 bg-gray-900"></div>
                                                        </div>
                                                    </div>

                                                    <!-- Tombol Verifikasi Pembayaran -->
                                                    @if(!empty($art['is_paid_journal']))
                                                        <div class="relative group inline-flex">
                                                            <button
                                                                type="button"
                                                                onclick="document.getElementById('modal-pay-art-{{ $art['id'] }}').classList.remove('hidden'); document.getElementById('modal-pay-art-{{ $art['id'] }}').classList.add('flex');"
                                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium text-brand-maroon bg-rose-50/80 hover:bg-rose-100 hover:text-rose-950 border border-rose-200/80 transition-colors cursor-pointer shrink-0 whitespace-nowrap"
                                                            >
                                                                <svg class="w-3.5 h-3.5 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                                </svg>
                                                                <span>Verifikasi Pembayaran</span>
                                                            </button>

                                                            <!-- Tooltip Verifikasi Pembayaran -->
                                                            <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 w-48">
                                                                <div class="bg-gray-900 text-white text-[11px] font-normal leading-snug px-2.5 py-1.5 rounded-md shadow-lg border border-gray-800 text-center">
                                                                    Verifikasi pembayaran & bukti transfer APC
                                                                </div>
                                                                <div class="w-2 h-2 -mt-1 rotate-45 bg-gray-900"></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Kanan: Unduh Galai PDF -->
                                                <div class="relative group inline-flex shrink-0">
                                                    <a
                                                        href="{{ $art['galley_pdf_url'] }}"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 hover:text-brand-maroon border border-gray-200/90 transition-colors shrink-0 whitespace-nowrap"
                                                    >
                                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        <span>Unduh Galai PDF</span>
                                                    </a>

                                                    <!-- Tooltip Unduh Galai PDF -->
                                                    <div class="pointer-events-none absolute bottom-full right-0 mb-2 hidden group-hover:flex flex-col items-end z-30 w-44">
                                                        <div class="bg-gray-900 text-white text-[11px] font-normal leading-snug px-2.5 py-1.5 rounded-md shadow-lg border border-gray-800 text-right">
                                                            Unduh galai PDF naskah final
                                                        </div>
                                                        <div class="w-2 h-2 -mt-1 mr-4 rotate-45 bg-gray-900"></div>
                                                    </div>
                                                </div>
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
                            <h3 class="text-xs font-semibold text-gray-800">Belum ada artikel di dalam edisi ini</h3>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                                Masukkan artikel yang telah disetujui Pemimpin Redaksi dari antrean di bawah ini untuk menyusun daftar isi.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Panel 2: Antrean Artikel Siap Terbit (Ready for Issue Queue) -->
            @if(count($issue['ready_articles']) > 0)
                <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-gray-200/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-blue-50/40">
                        <div>
                            <h2 class="text-sm sm:text-base font-medium text-gray-900">
                                Antrean Naskah Siap Terbit ({{ count($issue['ready_articles']) }})
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Naskah yang telah memperoleh persetujuan akhir galai dan siap dimasukkan ke daftar isi edisi ini.
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200 shrink-0 whitespace-nowrap self-start sm:self-center">
                            Disetujui Pemimpin Redaksi
                        </span>
                    </div>

                    @if($issue['status'] === 'published')
                        <div class="px-4 py-2.5 bg-amber-50/80 border-b border-amber-200/80 text-amber-900 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Edisi ini telah resmi terbit. Penambahan naskah baru ke daftar isi hanya dapat dilakukan pada edisi berstatus Draf atau Terjadwal.</span>
                        </div>
                    @endif

                    <div class="p-4 sm:p-5 space-y-3.5">
                        @foreach($issue['ready_articles'] as $ready)
                            <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 bg-white shadow-2xs transition duration-150 space-y-3">
                                <!-- Baris Atas: Metadata ID, Judul & Tombol Aksi -->
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div class="flex items-center gap-2 text-xs text-gray-500 tabular-nums">
                                            <span class="font-medium text-gray-700">{{ $ready['number'] }}</span>
                                            <span>•</span>
                                            <span>{{ $ready['section_title'] }}</span>
                                        </div>

                                        <h3 class="text-sm font-normal text-gray-900 leading-snug">
                                            {{ $ready['title'] }}
                                        </h3>

                                        <p class="text-xs text-gray-500">
                                            Penulis: <span class="text-gray-700 font-normal">{{ $ready['author_name'] }}</span>
                                        </p>
                                    </div>

                                    <!-- Tombol Icon Tambah Masukkan ke Edisi -->
                                    <div class="shrink-0 self-start">
                                        @if($ready['can_add_to_issue'])
                                            <form action="{{ route('journalmanager.issues.add-article', $issue['id']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="article_id" value="{{ $ready['id'] }}">
                                                <input type="hidden" name="article_title" value="{{ $ready['title'] }}">
                                                <div class="relative group/tooltip inline-block">
                                                    <button
                                                        type="submit"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 shadow-2xs transition duration-150 cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-blue-400"
                                                        aria-label="Masukkan ke Edisi"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </button>
                                                    <!-- Tooltip -->
                                                    <div class="absolute bottom-full right-0 mb-1.5 hidden group-hover/tooltip:flex group-focus/tooltip:flex flex-col items-center pointer-events-none z-30 whitespace-nowrap">
                                                        <div class="px-2.5 py-1 text-[11px] font-medium text-white bg-gray-900 rounded-md shadow-md">
                                                            Masukkan ke Edisi
                                                        </div>
                                                        <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45 mr-3"></div>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <div class="relative group/tooltip inline-block">
                                                <button
                                                    type="button"
                                                    disabled
                                                    class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed select-none"
                                                    aria-label="{{ $ready['cant_add_reason'] }}"
                                                >
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                                <!-- Tooltip Alasan Belum Bisa Ditambahkan -->
                                                <div class="absolute bottom-full right-0 mb-1.5 hidden group-hover/tooltip:flex group-focus/tooltip:flex flex-col items-end pointer-events-none z-30 max-w-xs">
                                                    <div class="px-2.5 py-1.5 text-[11px] font-normal leading-tight text-white bg-gray-900 rounded-md shadow-md text-right">
                                                        {{ $ready['cant_add_reason'] }}
                                                    </div>
                                                    <div class="w-1.5 h-1.5 -mt-0.5 bg-gray-900 rotate-45 mr-3"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Baris Bawah: Tagihan & Verifikasi Pembayaran -->
                                <div class="flex flex-wrap items-center gap-2 pt-2.5 border-t border-gray-100 text-xs">
                                    <!-- Badge Status Pembayaran -->
                                    <div class="relative group inline-flex">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold border cursor-help shrink-0 whitespace-nowrap {{ $ready['payment_badge'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                            @if(($ready['payment_status'] ?? '') === 'verified')
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @elseif(($ready['payment_status'] ?? '') === 'pending_verification')
                                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @elseif(($ready['payment_status'] ?? '') === 'free')
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @elseif(($ready['payment_status'] ?? '') === 'rejected')
                                                <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            @endif
                                            <span>{{ $ready['payment_label'] ?? 'Bebas Biaya' }}</span>
                                        </span>

                                        <!-- Tooltip Status Bayar -->
                                        <div class="pointer-events-none absolute bottom-full left-0 mb-2 hidden group-hover:flex flex-col items-start z-30 w-44">
                                            <div class="bg-gray-900 text-white text-[11px] font-normal leading-snug px-2.5 py-1.5 rounded-md shadow-lg border border-gray-800">
                                                @if(($ready['payment_status'] ?? '') === 'verified')
                                                    Pembayaran APC lunas
                                                @elseif(($ready['payment_status'] ?? '') === 'pending_verification')
                                                    Menunggu verifikasi bukti transfer
                                                @elseif(($ready['payment_status'] ?? '') === 'free')
                                                    Bebas biaya publikasi (Free APC)
                                                @elseif(($ready['payment_status'] ?? '') === 'rejected')
                                                    Bukti transfer ditolak redaksi
                                                @else
                                                    Menunggu pembayaran APC dari penulis
                                                @endif
                                            </div>
                                            <div class="w-2 h-2 -mt-1 ml-4 rotate-45 bg-gray-900"></div>
                                        </div>
                                    </div>

                                    @if(!empty($ready['is_paid_journal']))
                                        <div class="relative group inline-flex">
                                            <button
                                                type="button"
                                                onclick="document.getElementById('modal-pay-ready-{{ $ready['id'] }}').classList.remove('hidden'); document.getElementById('modal-pay-ready-{{ $ready['id'] }}').classList.add('flex');"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium text-brand-maroon bg-rose-50/80 hover:bg-rose-100 hover:text-rose-950 border border-rose-200/80 transition-colors cursor-pointer shrink-0 whitespace-nowrap ml-0.5"
                                            >
                                                <svg class="w-3.5 h-3.5 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                <span>Verifikasi Pembayaran</span>
                                            </button>

                                            <!-- Tooltip Verifikasi -->
                                            <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:flex flex-col items-center z-30 w-48">
                                                <div class="bg-gray-900 text-white text-[11px] font-normal leading-snug px-2.5 py-1.5 rounded-md shadow-lg border border-gray-800 text-center">
                                                    Verifikasi pembayaran & bukti transfer APC
                                                </div>
                                                <div class="w-2 h-2 -mt-1 rotate-45 bg-gray-900"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
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
                <h3 class="text-xs font-semibold uppercase text-gray-500">
                    Otorisasi Publikasi Edisi
                </h3>

                @if($issue['status'] === 'published')
                    <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-2">
                        <div class="flex items-center gap-2 font-semibold text-emerald-800">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Edisi Telah Terbit Resmi</span>
                        </div>
                        <p class="text-[11px] text-gray-600">
                            Dipublikasikan pada: <strong class="text-gray-800">{{ $issue['published_at'] }}</strong>
                        </p>

                        @if(!empty($issue['public_url']))
                            <div class="pt-2 flex flex-col gap-1.5 border-t border-emerald-200/80">
                                <a
                                    href="{{ $issue['public_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    <span>Buka di Halaman Publik</span>
                                </a>
                                @if(!empty($issue['archives_url']))
                                    <a
                                        href="{{ $issue['archives_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-medium text-emerald-800 bg-white hover:bg-emerald-100/60 rounded-lg border border-emerald-200 transition"
                                    >
                                        <span>Lihat Arsip Jurnal Ini</span>
                                    </a>
                                @endif
                            </div>
                        @endif
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
                                <span>Tetapkan sebagai Edisi Terkini</span>
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
                                <div class="flex items-center gap-1.5 font-semibold text-amber-800">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Peringatan Biaya Publikasi: {{ $unpaidCount }} Naskah</span>
                                </div>
                                <p class="text-[11px] text-amber-800">
                                    Terdapat {{ $unpaidCount }} naskah berbayar yang belum diverifikasi atau belum lunas.
                                </p>
                            </div>
                        @endif

                        <form action="{{ route('journalmanager.issues.publish', $issue['id']) }}" 
                              method="POST"
                              data-confirm="Apakah Anda yakin ingin mempublikasikan edisi terbitan ini ke publik?"
                              data-confirm-title="Publikasikan Edisi Terbitan?"
                              data-confirm-btn="Publikasikan Sekarang"
                              data-confirm-cancel="Batal"
                              data-confirm-type="primary">
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
                            <span class="font-semibold block mb-1">Peringatan Publikasi:</span>
                            Edisi wajib memiliki minimal 1 naskah sah sebelum dapat dipublikasikan.
                        </div>
                    @endif
                @endif
            </div>

            <!-- Panel Pengindeksan & DOI Crossref -->
            <div class="bg-gray-50/70 rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-3 text-xs">
                <h4 class="font-semibold text-gray-800">Pengindeksan & Metadata DOI</h4>
                <div class="space-y-2 text-gray-600 text-[11px]">
                    <div class="flex justify-between">
                        <span>Prefiks DOI:</span>
                        <span class="font-medium text-gray-800 tabular-nums">{{ $issue['doi_prefix'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Penyedia Registri:</span>
                        <span class="font-medium text-gray-800">Crossref via BRIDA</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Format Metadata:</span>
                        <span class="font-medium text-gray-800">Dublin Core & Crossref XML</span>
                    </div>
                </div>
            </div>

            <!-- Panel Jejak Aktivitas Linimasa -->
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-3.5">
                <h4 class="text-xs font-semibold uppercase text-gray-500">
                    Linimasa Edisi Terbitan
                </h4>

                <div class="relative pl-4 space-y-3.5 border-l-2 border-gray-100 ml-1">
                    @foreach($issue['activity_timeline'] as $activity)
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $activity['badge_class'] }} ring-4 ring-white"></div>
                            <span class="text-xs font-semibold text-gray-900 block leading-tight">{{ $activity['title'] }}</span>
                            <span class="text-[10px] text-gray-400 block mt-0.5">{{ $activity['actor'] }} • {{ $activity['date'] }}</span>
                            <p class="text-xs text-gray-600 mt-1 leading-snug">{{ $activity['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
