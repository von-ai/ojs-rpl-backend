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

    <!-- 4 Kartu Metrik Ringkasan (Konsisten & Rapi) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($viewModel->metricCards() as $card)
            <x-kpi-card
                :title="$card['title']"
                :value="$card['count']"
                :icon="$card['icon'] ?? 'clipboard'"
                :description="$card['subtitle'] ?? null"
            />
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
                        <span class="py-0.5 px-2 rounded-full text-xs font-bold tabular-nums {{ $tab['active'] ? 'bg-brand-maroon text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200' }}">
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
            <!-- Tabel Daftar Penugasan (Selalu Tampil & Responsif via overflow-x-auto) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                            <th scope="col" class="py-3 px-2.5 text-center whitespace-nowrap w-10">NO</th>
                            <th scope="col" class="py-3 px-3 whitespace-nowrap">ID NASKAH</th>
                            <th scope="col" class="py-3 px-3">JUDUL ARTIKEL</th>
                            <th scope="col" class="py-3 px-3 whitespace-nowrap">BIDANG KAJIAN</th>
                            <th scope="col" class="py-3 px-3 whitespace-nowrap">TENGGAT WAKTU</th>
                            <th scope="col" class="py-3 px-3 whitespace-nowrap">STATUS PENUGASAN</th>
                            <th scope="col" class="py-3 px-3 text-right whitespace-nowrap">AKSI</th>
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
                                <td class="py-3 px-2.5 align-top text-center whitespace-nowrap text-xs font-semibold text-gray-500 tabular-nums">
                                    {{ $rowNumber }}
                                </td>

                                <!-- Kolom 1: ID Naskah & Putaran -->
                                <td class="py-3 px-3 align-top whitespace-nowrap">
                                    <div class="text-xs sm:text-sm font-semibold text-gray-900 tabular-nums">
                                        {{ $subNumber }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-600 text-white tabular-nums">
                                            Putaran {{ $round?->round ?? 1 }}
                                        </span>
                                        <span class="text-xs text-gray-500 tabular-nums">
                                            {{ ($assignment->invited_at ?? $assignment->created_at)?->format('d M Y') ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Kolom 2: Judul Artikel (Double-Blind) -->
                                <td class="py-3 px-3 align-top max-w-sm">
                                    <div class="relative group block max-w-full">
                                        <a
                                            href="{{ route('reviewer.assignments.show', $assignment->id) }}"
                                            title="{{ $title }}"
                                            class="block font-semibold text-gray-900 leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon hover:underline decoration-brand-maroon/40 underline-offset-2 transition duration-150 cursor-pointer"
                                        >
                                            {{ $title }}
                                        </a>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Penelaahan Anonim Ganda</span>
                                    </div>
                                </td>

                                <!-- Kolom 3: Bidang Kajian -->
                                <td class="py-3 px-3 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-200 text-gray-800 shrink-0">
                                        {{ $submission?->section?->name ?? 'Artikel Riset' }}
                                    </span>
                                </td>

                                <!-- Kolom 4: Tenggat Waktu (Respons / Telaah) -->
                                <td class="py-3 px-3 align-top whitespace-nowrap space-y-1">
                                    @if($assignment->status === 'invited')
                                        <div>
                                            <span class="text-[11px] text-gray-500 block">Batas Respons:</span>
                                            <strong class="text-xs text-gray-900 tabular-nums">
                                                {{ $assignment->response_due_at ? $assignment->response_due_at->format('d M Y') : '-' }}
                                            </strong>
                                        </div>
                                    @else
                                        <div>
                                            <span class="text-[11px] text-gray-500 block">Batas Telaah:</span>
                                            <strong class="text-xs text-gray-900 tabular-nums">
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
                                <td class="py-3 px-3 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $meta['badge_class'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>

                                <!-- Kolom 6: Aksi -->
                                <td class="py-3 px-3 align-top text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('reviewer.assignments.show', $assignment->id) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-md border border-brand-maroon bg-brand-maroon text-white hover:bg-brand-maroon-hover transition duration-150 shrink-0 shadow-2xs"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
