@extends('layouts.eic')

@section('title', 'Antrean Naskah Editorial — Portal Pemimpin Redaksi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header & Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Ruang Kerja Pemimpin Redaksi
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Pengelolaan alur editorial naskah masuk, mulai dari skrining awal, pendelegasian editor bagian, pemantauan telaah sejawat, hingga keputusan akhir penerbitan.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a 
                href="{{ route('eic.guidelines') }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
            >
                <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Standar Skrining & Etika Redaksi</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Metrik Ringkasan (Konsisten dengan Portal Penulis) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($viewModel->metricCards() as $card)
            @php
                $isCardActive = $viewModel->activeTab === $card['key'];
            @endphp
            <a 
                href="{{ route('eic.submissions.index', ['tab' => $card['key'], 'q' => request('q')]) }}" 
                class="bg-white rounded-lg border {{ $isCardActive ? 'border-brand-maroon ring-1 ring-brand-maroon' : 'border-gray-200 hover:border-gray-300' }} p-3 sm:p-5 flex flex-col justify-between transition group"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] sm:text-xs font-bold text-gray-500 group-hover:text-brand-maroon uppercase tracking-wider leading-tight">
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
            </a>
        @endforeach
    </div>

    <!-- Filter Tabs & Bar Pencarian (Konsisten dengan Portal Penulis) -->
    <div class="space-y-4">
        
        <!-- Tab Navigasi Status -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-1 sm:space-x-2 overflow-x-auto overflow-y-hidden no-scrollbar" aria-label="Tab Filter Status Naskah">
                @foreach($viewModel->tabs() as $tab)
                    <a 
                        href="{{ route('eic.submissions.index', ['tab' => $tab['key'], 'q' => request('q')]) }}"
                        class="group inline-flex items-center gap-2 py-2.5 sm:py-3 px-3 sm:px-4 border-b-2 text-xs sm:text-sm font-semibold transition duration-150 whitespace-nowrap shrink-0 cursor-pointer {{ $tab['active'] ? 'border-brand-maroon text-brand-maroon' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}"
                        aria-current="{{ $tab['active'] ? 'page' : 'false' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full tabular-nums {{ $tab['active'] ? 'bg-brand-maroon text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Bar Pencarian -->
        <form action="{{ route('eic.submissions.index') }}" method="GET" class="bg-white p-3 sm:p-4 rounded-lg border border-gray-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <input type="hidden" name="tab" value="{{ $viewModel->activeTab }}">

            <div class="relative flex-1 min-w-0">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="search" 
                    name="q" 
                    value="{{ request('q') }}" 
                    placeholder="Cari berdasarkan ID naskah, judul artikel, atau nama penulis..."
                    class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-gray-50/70 border border-gray-300 rounded-lg text-gray-900 placeholder:text-gray-400 focus:outline-hidden focus:bg-white focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition duration-150"
                >
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if(request('q'))
                    <a 
                        href="{{ route('eic.submissions.index', ['tab' => $viewModel->activeTab]) }}" 
                        class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                    >
                        Reset
                    </a>
                @endif
                <button type="submit" class="px-4 py-2 bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs sm:text-sm font-semibold rounded-lg transition shrink-0 cursor-pointer">
                    Cari Naskah
                </button>
            </div>
        </form>
    </div>

    <!-- Kontainer Tabel Daftar Naskah -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        @if($submissions->isEmpty())
            <div class="py-12 px-4 text-center">
                <div class="max-w-sm mx-auto space-y-3">
                    <div class="w-10 h-10 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">Belum Ada Naskah Ditemukan</h3>
                    <p class="text-xs text-gray-500">
                        Tidak ada naskah ilmiah yang cocok dengan filter tahapan atau kata kunci pencarian saat ini.
                    </p>
                    @if(request('q') || $viewModel->activeTab !== 'all')
                        <div class="pt-1 flex items-center justify-center">
                            <a 
                                href="{{ route('eic.submissions.index') }}" 
                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-md transition"
                            >
                                Reset Filter
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Tampilan Desktop & Tablet (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">ID NASKAH</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-70">JUDUL ARTIKEL</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">PENULIS</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">SEKSI & SKRINING</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">SECTION EDITOR & REVIEW</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">STATUS NASKAH</th>
                            <th scope="col" class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($submissions as $sub)
                            @php
                                $rowNumber = ($submissions->firstItem() ?? 1) + $loop->index;
                                $meta = $viewModel->statusMeta($sub->status);
                                $assignedSe = $sub->activeEditorAssignment?->editor;
                                $latestRound = $sub->reviewRounds->sortByDesc('round')->first();
                                $latestDesk = $sub->deskReviews->sortByDesc('created_at')->first();
                                $completedReviews = $latestRound ? $latestRound->assignments->where('status', 'completed')->count() : 0;
                                $minReviews = $latestRound ? max(1, (int) $latestRound->minimum_completed_reviews) : 2;
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition duration-150">
                                <!-- Kolom 0: Nomor Urut -->
                                <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-semibold text-gray-400 tabular-nums">
                                    {{ $rowNumber }}
                                </td>

                                <!-- Kolom 1: ID Naskah & Tanggal -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                    <div class="text-sm font-normal text-gray-700 tabular-nums">
                                        {{ $sub->submission_number }}
                                    </div>
                                    <div class="text-xs text-gray-400 tabular-nums mt-0.5">
                                        {{ ($sub->submitted_at ?? $sub->created_at)?->format('d M Y') ?? '-' }}
                                    </div>
                                </td>

                                <!-- Kolom 2: Judul Artikel -->
                                <td class="py-4 px-4 sm:px-6 align-top min-w-70">
                                    <div class="relative group block max-w-full">
                                        <a 
                                            href="{{ route('eic.submissions.show', $sub->id) }}" 
                                            title="{{ $sub->title }}"
                                            class="block font-normal text-gray-900 leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon hover:underline decoration-brand-maroon/40 underline-offset-2 transition duration-150 cursor-pointer"
                                        >
                                            {{ $sub->title }}
                                        </a>
                                    </div>
                                </td>

                                <!-- Kolom 3: Penulis -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                    <div class="text-xs font-medium text-gray-800">
                                        {{ $sub->author?->name ?? 'Penulis Naskah' }}
                                    </div>
                                </td>

                                <!-- Kolom 3: Seksi Jurnal & Hasil Skrining -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200 shrink-0">
                                        {{ $sub->section?->name ?? 'Artikel Riset' }}
                                    </span>
                                    @if($latestDesk)
                                        <div class="text-xs text-gray-500 tabular-nums">
                                            Kemiripan: <span class="font-semibold {{ ($latestDesk->similarity_score ?? 0) <= 20 ? 'text-emerald-700' : 'text-rose-600' }}">{{ number_format((float) ($latestDesk->similarity_score ?? 0), 1) }}%</span>
                                        </div>
                                    @else
                                        <div class="text-xs text-amber-700 font-medium">
                                            Belum diskrining
                                        </div>
                                    @endif
                                </td>

                                <!-- Kolom 4: Section Editor & Review (Tanpa dot dan tanpa tag rekomendasi) -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap space-y-1">
                                    @if($assignedSe)
                                        <div class="text-xs font-medium text-gray-800">
                                            {{ $assignedSe->name }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400">
                                            Belum ditugaskan
                                        </div>
                                    @endif

                                    @if($latestRound)
                                        <div class="text-xs text-gray-500 tabular-nums">
                                            Putaran {{ $latestRound->round }}: <span class="font-semibold {{ $completedReviews >= $minReviews ? 'text-emerald-700' : 'text-sky-700' }}">{{ $completedReviews }}/{{ $minReviews }} Review</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Kolom 5: Status Naskah (Hanya badge status tanpa keterangan tambahan tahap) -->
                                <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $meta['badge_class'] }} shrink-0">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>

                                <!-- Kolom 6: Aksi -->
                                <td class="py-4 px-4 sm:px-6 align-top text-right whitespace-nowrap">
                                    <a 
                                        href="{{ route('eic.submissions.show', $sub->id) }}" 
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

            <!-- Tampilan Mobile (< 640px) -->
            <div class="block sm:hidden divide-y divide-gray-100">
                @foreach($submissions as $sub)
                    @php
                        $rowNumber = ($submissions->firstItem() ?? 1) + $loop->index;
                        $meta = $viewModel->statusMeta($sub->status);
                        $assignedSe = $sub->activeEditorAssignment?->editor;
                    @endphp
                    <div class="p-4 space-y-3 hover:bg-gray-50/70 transition duration-150">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-xs font-semibold text-gray-400 tabular-nums">#{{ $rowNumber }}</span>
                                <span class="font-normal text-gray-600 tabular-nums text-xs">
                                    {{ $sub->submission_number }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700 border border-gray-200 shrink-0">
                                    {{ $sub->section?->name ?? 'Artikel Riset' }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-500 tabular-nums shrink-0">
                                {{ ($sub->submitted_at ?? $sub->created_at)?->format('d M Y') ?? '-' }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <a 
                                href="{{ route('eic.submissions.show', $sub->id) }}" 
                                class="block font-normal text-gray-900 text-sm leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon transition duration-150"
                            >
                                {{ $sub->title }}
                            </a>
                            <p class="text-xs text-gray-500 mt-1">
                                Penulis: <span class="font-medium text-gray-700">{{ $sub->author?->name ?? 'Penulis Naskah' }}</span>
                            </p>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[11px] font-semibold uppercase tracking-wider">Status Naskah</span>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $meta['badge_class'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-gray-400 block text-[11px] font-semibold uppercase tracking-wider">Editor Bagian</span>
                                <span class="mt-1 block font-medium text-gray-700">
                                    {{ $assignedSe?->name ?? 'Belum ditugaskan' }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-end border-t border-gray-100">
                            <a 
                                href="{{ route('eic.submissions.show', $sub->id) }}" 
                                class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                            >
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bagian Bawah Tabel / Paginasi -->
            <div class="px-4 sm:px-6 py-3.5 bg-gray-50 border-t border-gray-200">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
