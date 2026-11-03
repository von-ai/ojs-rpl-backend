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

        <!-- Filter & Search Bar -->
        <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form action="{{ route('reviewer.assignments.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <input type="hidden" name="status" value="{{ $viewModel->currentStatus() }}">

                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="search"
                        value="{{ $viewModel->searchQuery() }}"
                        placeholder="Cari nomor naskah atau kata kunci judul..."
                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon transition"
                    >
                </div>

                <button
                    type="submit"
                    class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition shrink-0"
                >
                    Cari
                </button>

                @if($viewModel->searchQuery())
                    <a
                        href="{{ route('reviewer.assignments.index', ['status' => $viewModel->currentStatus()]) }}"
                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold px-2 py-1 transition shrink-0"
                    >
                        Reset
                    </a>
                @endif
            </form>

            <div class="text-[11px] text-gray-500 self-end sm:self-auto">
                Menampilkan <strong class="text-gray-800 tabular-nums">{{ $viewModel->assignments->total() }}</strong> penugasan
            </div>
        </div>

    </div>

    <!-- Assignments List Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
        @if($viewModel->assignments->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm">Tidak ada tugas telaah ditemukan</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto text-center">
                    @if($viewModel->searchQuery())
                        Tidak ada naskah yang cocok dengan kata kunci pencarian "{{ $viewModel->searchQuery() }}".
                    @else
                        Saat ini belum ada tugas penelaahan naskah ilmiah pada kategori filter ini.
                    @endif
                </p>
                @if($viewModel->searchQuery() || $viewModel->currentStatus() !== 'all')
                    <div class="pt-2">
                        <a
                            href="{{ route('reviewer.assignments.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                        >
                            Tampilkan Semua Penugasan
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- Desktop Table View (Hidden on mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3.5 px-6">Informasi Manuskrip Anonim</th>
                            <th class="py-3.5 px-4">Bidang Kajian</th>
                            <th class="py-3.5 px-4">Batas Konfirmasi</th>
                            <th class="py-3.5 px-4">Batas Telaah</th>
                            <th class="py-3.5 px-4">Status Penugasan</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($viewModel->assignments as $assignment)
                            @php
                                $round = $assignment->reviewRound;
                                $submission = $round?->submission;
                                $title = $round?->reviewer_title ?? 'Judul anonim belum disiapkan';
                                $subNumber = $submission?->submission_number ?? 'SUB-'.$submission?->id;
                                $meta = $viewModel->statusMeta($assignment->status);
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="py-4 px-6 space-y-1 max-w-md">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10px] text-gray-600 font-bold bg-gray-100 px-1.5 py-0.5 rounded">
                                            {{ $subNumber }}
                                        </span>
                                        <span class="text-[10px] text-indigo-700 bg-indigo-50 font-semibold px-1.5 py-0.5 rounded border border-indigo-100">
                                            Putaran {{ $round?->round ?? 1 }}
                                        </span>
                                    </div>
                                    <a
                                        href="{{ route('reviewer.assignments.show', $assignment->id) }}"
                                        class="font-semibold text-gray-900 hover:text-brand-maroon text-sm block leading-snug line-clamp-2 transition"
                                    >
                                        {{ $title }}
                                    </a>
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-800">
                                        {{ $submission?->section?->name ?? $submission?->section?->title ?? 'Umum' }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    @if($assignment->response_due_at)
                                        <span class="font-medium text-gray-900 block tabular-nums">
                                            {{ $assignment->response_due_at->format('d M Y') }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 block">
                                            {{ $assignment->response_due_at->format('H:i') }} UTC
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    @if($assignment->review_due_at)
                                        <span class="font-medium text-gray-900 block tabular-nums">
                                            {{ $assignment->review_due_at->format('d M Y') }}
                                        </span>
                                        @php
                                            $days = (int) now()->diffInDays($assignment->review_due_at, false);
                                        @endphp
                                        @if(in_array($assignment->status, ['accepted', 'overdue'], true))
                                            @if($days < 0)
                                                <span class="text-[10px] font-semibold text-rose-600 block">
                                                    Lewat {{ abs($days) }} hari
                                                </span>
                                            @elseif($days <= 3)
                                                <span class="text-[10px] font-semibold text-amber-600 block">
                                                    Sisa {{ $days }} hari
                                                </span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $meta['badge_class'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <a
                                        href="{{ route('reviewer.assignments.show', $assignment->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $assignment->status === 'invited' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-brand-maroon hover:bg-brand-maroon-hover' }} text-white text-xs font-semibold rounded-lg shadow-2xs transition"
                                    >
                                        <span>{{ $assignment->status === 'invited' ? 'Tanggapi' : 'Ruang Telaah' }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View (Shown only on small screens) -->
            <div class="md:hidden divide-y divide-gray-100">
                @foreach($viewModel->assignments as $assignment)
                    @php
                        $round = $assignment->reviewRound;
                        $submission = $round?->submission;
                        $title = $round?->reviewer_title ?? 'Judul anonim belum disiapkan';
                        $subNumber = $submission?->submission_number ?? 'SUB-'.$submission?->id;
                        $meta = $viewModel->statusMeta($assignment->status);
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-[10px] text-gray-600 font-bold bg-gray-100 px-1.5 py-0.5 rounded">
                                    {{ $subNumber }}
                                </span>
                                <span class="text-[10px] text-indigo-700 bg-indigo-50 font-semibold px-1.5 py-0.5 rounded border border-indigo-100">
                                    P{{ $round?->round ?? 1 }}
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $meta['badge_class'] }}">
                                {{ $meta['label'] }}
                            </span>
                        </div>

                        <a
                            href="{{ route('reviewer.assignments.show', $assignment->id) }}"
                            class="font-semibold text-gray-900 text-sm block leading-snug"
                        >
                            {{ $title }}
                        </a>

                        <div class="text-xs text-gray-500 space-y-1 pt-1 border-t border-gray-100">
                            <div class="flex justify-between">
                                <span>Bidang Kajian:</span>
                                <span class="font-medium text-gray-800">{{ $submission?->section?->name ?? 'Umum' }}</span>
                            </div>
                            @if($assignment->review_due_at)
                                <div class="flex justify-between">
                                    <span>Tenggat Telaah:</span>
                                    <span class="font-medium text-gray-800">{{ $assignment->review_due_at->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="pt-2">
                            <a
                                href="{{ route('reviewer.assignments.show', $assignment->id) }}"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 {{ $assignment->status === 'invited' ? 'bg-amber-600' : 'bg-brand-maroon' }} text-white text-xs font-semibold rounded-lg shadow-2xs transition"
                            >
                                <span>{{ $assignment->status === 'invited' ? 'Tanggapi Undangan' : 'Buka Ruang Telaah' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginasi -->
            @if($viewModel->assignments->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $viewModel->assignments->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
