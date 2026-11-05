@extends('layouts.reviewer')

@section('title', 'Tugas Penelaahan Ilmiah — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Tugas Penelaahan Ilmiah
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Kelola konfirmasi undangan telaah dan ruang evaluasi saintifik ganda tertutup (double-blind peer review).
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a 
                href="{{ route('reviewer.guidelines') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition"
            >
                <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Pedoman Telaah</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik Ringkasan (Statis Murni, Non-Klik) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($viewModel->metricCards() as $card)
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-5 flex flex-col justify-between select-none shadow-2xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500 uppercase tracking-wider leading-tight">
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

    <!-- Filter Tabs & Bar Pencarian -->
    <div class="space-y-4">
        
        <!-- Tab Navigasi Status -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-1 sm:space-x-2 overflow-x-auto overflow-y-hidden no-scrollbar" aria-label="Tab Filter Status Tugas">
                @foreach($viewModel->tabs() as $tab)
                    <a 
                        href="{{ route('reviewer.assignments.index', ['status' => $tab['key'], 'search' => $viewModel->searchQuery()]) }}"
                        class="group inline-flex items-center gap-2 py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold transition duration-150 whitespace-nowrap shrink-0 {{ $tab['active'] ? 'border-brand-maroon text-brand-maroon' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="py-0.5 px-2 rounded-full text-xs font-bold tabular-nums {{ $tab['active'] ? 'bg-brand-maroon/10 text-brand-maroon' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Bar Pencarian Konsisten -->
        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-2xs">
            <form action="{{ route('reviewer.assignments.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <input type="hidden" name="status" value="{{ $viewModel->currentStatus() }}">

                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="search" 
                        name="search" 
                        value="{{ $viewModel->searchQuery() }}" 
                        placeholder="Cari berdasarkan ID naskah atau kata kunci judul artikel..." 
                        class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-gray-50/70 border border-gray-300 rounded-lg text-gray-900 placeholder:text-gray-400 focus:outline-hidden focus:bg-white focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition duration-150"
                    >
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($viewModel->searchQuery())
                        <a 
                            href="{{ route('reviewer.assignments.index', ['status' => $viewModel->currentStatus()]) }}" 
                            class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Reset
                        </a>
                    @endif
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs sm:text-sm font-semibold rounded-lg transition shrink-0 cursor-pointer"
                    >
                        Cari Tugas
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Kontainer Tabel Daftar Penugasan -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-2xs overflow-hidden">
        @if($viewModel->assignments->isEmpty())
            <div class="py-12 px-4 text-center">
                <div class="max-w-sm mx-auto space-y-3">
                    <div class="w-10 h-10 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Belum Ada Tugas Telaah Ditemukan</h3>
                    <p class="text-xs text-gray-500">
                        @if($viewModel->searchQuery())
                            Tidak ada penugasan naskah yang cocok dengan kata kunci pencarian "{{ $viewModel->searchQuery() }}".
                        @else
                            Saat ini belum ada tugas penelaahan naskah ilmiah pada kategori filter ini.
                        @endif
                    </p>
                    @if($viewModel->searchQuery() || $viewModel->currentStatus() !== 'all')
                        <div class="pt-1 flex items-center justify-center">
                            <a 
                                href="{{ route('reviewer.assignments.index') }}" 
                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-md transition"
                            >
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Tampilan Tabel Desktop & Tablet (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">ID NASKAH</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-70">JUDUL ARTIKEL</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">BIDANG KAJIAN</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">TENGGAT WAKTU</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">STATUS PENUGASAN</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($viewModel->assignments as $assignment)
                            @php
                                $rowNumber = ($viewModel->assignments->firstItem() ?? 1) + $loop->index;
                                $round = $assignment->reviewRound;
                                $submission = $round?->submission;
                                $title = $round?->reviewer_title ?? 'Judul anonim belum disiapkan';
                                $subNumber = $submission?->submission_number ?? 'SUB-'.$submission?->id;
                                $meta = $viewModel->statusMeta($assignment->status);
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition duration-150">
                                <!-- Kolom 0: Nomor Urut -->
                                <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-semibold text-gray-400 tabular-nums">
                                    {{ $rowNumber }}
                                </td>

                                <!-- Kolom 1: ID Naskah & Putaran -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                    <div class="text-sm font-normal text-gray-700 tabular-nums">
                                        {{ $subNumber }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 tabular-nums">
                                            Putaran {{ $round?->round ?? 1 }}
                                        </span>
                                        <span class="text-xs text-gray-400 tabular-nums">
                                            {{ ($assignment->invited_at ?? $assignment->created_at)?->format('d M Y') ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Kolom 2: Judul Artikel (Double-Blind) -->
                                <td class="py-4 px-4 sm:px-6 align-top min-w-70">
                                    <div class="relative group block max-w-full">
                                        <a 
                                            href="{{ route('reviewer.assignments.show', $assignment->id) }}" 
                                            title="{{ $title }}"
                                            class="block font-normal text-gray-900 leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon hover:underline decoration-brand-maroon/40 underline-offset-2 transition duration-150 cursor-pointer"
                                        >
                                            {{ $title }}
                                        </a>
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Penelaahan Anonim Ganda</span>
                                    </div>
                                </td>

                                <!-- Kolom 3: Bidang Kajian -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200 shrink-0">
                                        {{ $submission?->section?->name ?? 'Artikel Riset' }}
                                    </span>
                                </td>

                                <!-- Kolom 4: Tenggat Waktu (Respons / Telaah) -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap space-y-1">
                                    @if($assignment->status === 'invited')
                                        <div>
                                            <span class="text-[11px] text-gray-500 block">Batas Respons:</span>
                                            <strong class="text-xs text-gray-800 tabular-nums">
                                                {{ $assignment->response_due_at ? $assignment->response_due_at->format('d M Y') : '-' }}
                                            </strong>
                                        </div>
                                    @else
                                        <div>
                                            <span class="text-[11px] text-gray-500 block">Batas Telaah:</span>
                                            <strong class="text-xs text-gray-800 tabular-nums">
                                                {{ $assignment->review_due_at ? $assignment->review_due_at->format('d M Y') : '-' }}
                                            </strong>
                                            @php
                                                $days = (int) now()->diffInDays($assignment->review_due_at, false);
                                            @endphp
                                            @if(in_array($assignment->status, ['accepted', 'overdue'], true))
                                                @if($days < 0)
                                                    <span class="text-[10px] font-semibold text-rose-600 block mt-0.5">
                                                        Lewat {{ abs($days) }} hari
                                                    </span>
                                                @elseif($days <= 3)
                                                    <span class="text-[10px] font-semibold text-amber-600 block mt-0.5">
                                                        Sisa {{ $days }} hari
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- Kolom 5: Status Penugasan -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border {{ $meta['badge_class'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>

                                <!-- Kolom 6: Aksi -->
                                <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                    <a 
                                        href="{{ route('reviewer.assignments.show', $assignment->id) }}" 
                                        class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tampilan Kartu Responsif Mobile (< 640px) -->
            <div class="sm:hidden divide-y divide-gray-100">
                @foreach($viewModel->assignments as $assignment)
                    @php
                        $rowNumber = ($viewModel->assignments->firstItem() ?? 1) + $loop->index;
                        $round = $assignment->reviewRound;
                        $submission = $round?->submission;
                        $title = $round?->reviewer_title ?? 'Judul anonim belum disiapkan';
                        $subNumber = $submission?->submission_number ?? 'SUB-'.$submission?->id;
                        $meta = $viewModel->statusMeta($assignment->status);
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-[10px] font-bold tabular-nums">
                                    {{ $rowNumber }}
                                </span>
                                <span class="font-mono text-[11px] text-gray-700 font-semibold bg-gray-100 px-1.5 py-0.5 rounded">
                                    {{ $subNumber }}
                                </span>
                                <span class="text-[10px] text-indigo-700 bg-indigo-50 font-semibold px-1.5 py-0.5 rounded border border-indigo-100">
                                    Putaran {{ $round?->round ?? 1 }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium border {{ $meta['badge_class'] }}">
                                {{ $meta['label'] }}
                            </span>
                        </div>

                        <a 
                            href="{{ route('reviewer.assignments.show', $assignment->id) }}" 
                            class="font-semibold text-gray-900 text-sm block leading-snug line-clamp-2 hover:text-brand-maroon transition"
                        >
                            {{ $title }}
                        </a>

                        <div class="text-xs text-gray-500 space-y-1.5 pt-2 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span>Bidang Kajian:</span>
                                <span class="font-medium text-gray-800">{{ $submission?->section?->name ?? 'Artikel Riset' }}</span>
                            </div>
                            @if($assignment->status === 'invited')
                                <div class="flex justify-between items-center">
                                    <span>Batas Respons:</span>
                                    <strong class="font-medium text-gray-900 tabular-nums">{{ $assignment->response_due_at ? $assignment->response_due_at->format('d M Y') : '-' }}</strong>
                                </div>
                            @else
                                <div class="flex justify-between items-center">
                                    <span>Batas Telaah:</span>
                                    <strong class="font-medium text-gray-900 tabular-nums">{{ $assignment->review_due_at ? $assignment->review_due_at->format('d M Y') : '-' }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="pt-1">
                            <a 
                                href="{{ route('reviewer.assignments.show', $assignment->id) }}" 
                                class="w-full inline-flex items-center justify-center px-3.5 py-2 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                            >
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginasi -->
            @if($viewModel->assignments->hasPages())
                <div class="p-4 border-t border-gray-200 bg-gray-50/50">
                    {{ $viewModel->assignments->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
