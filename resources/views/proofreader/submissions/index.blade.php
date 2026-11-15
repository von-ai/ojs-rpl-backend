@extends('layouts.proofreader')

@section('title', 'Penugasan Proofreading — Portal Proofreader')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Halaman Simpel & Jelas -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                Penugasan Proofreading &amp; Pemeriksaan Bukti Galai
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Periksa ketepatan tipografi, ejaan EYD, format tata letak, dan sitasi pada draf galai publikasi sebelum terbit.
            </p>
        </div>
    </div>

    <!-- 4 Metrik Ringkas Penugasan Proofread -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 leading-tight">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border tabular-nums shrink-0 {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-semibold text-gray-900 tabular-nums">
                        {{ $metric['value'] }}
                    </span>
                    <span class="text-[11px] text-gray-400">naskah</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Container: Filter Tabs, Pencarian, & Tabel Penugasan -->
    <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">

        <!-- Filter Tabs & Search Bar Terpadu -->
        <div class="p-4 border-b border-gray-200/90 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gray-50/50">
            <!-- Tabs Status -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Status Proofreading">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a
                        href="{{ route('proofreader.submissions.index', ['tab' => $tabKey, 'q' => request('q')]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition whitespace-nowrap {{ $isActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.2 text-[10px] rounded-full tabular-nums {{ $isActive ? 'bg-white/20 text-white font-bold' : 'bg-gray-200/70 text-gray-600' }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Cepat -->
            <form action="{{ route('proofreader.submissions.index') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="tab" value="{{ $tabs['active'] }}">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari judul atau penulis..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a
                        href="{{ route('proofreader.submissions.index', ['tab' => $tabs['active']]) }}"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                        title="Hapus filter pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Daftar Naskah Penugasan Proofreader -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase">
                        <th scope="col" class="py-3.5 px-3 sm:px-4 text-center whitespace-nowrap w-12 sm:w-14">NO</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">ID NASKAH</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 min-w-56 max-w-sm">INFORMASI NASKAH</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">PENULIS</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">AFILIASI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">VERSI GALAI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">CATATAN KOREKSI</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">BATAS WAKTU</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 whitespace-nowrap">STATUS</th>
                        <th scope="col" class="py-3.5 px-4 sm:px-6 text-center whitespace-nowrap">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->submissions() as $item)
                        <tr class="hover:bg-gray-50/70 transition duration-150">
                            <!-- Kolom 0: NO -->
                            <td class="py-4 px-3 sm:px-4 align-top text-center whitespace-nowrap text-xs font-normal text-gray-400 tabular-nums">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Kolom 1: ID NASKAH -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <div class="text-xs font-normal text-gray-900 tabular-nums">
                                    {{ $item['number'] }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-normal bg-gray-100 text-gray-600 border border-gray-200">
                                        {{ $item['section_title'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Kolom 2: INFORMASI NASKAH (Hover Tooltip, Normal Font Weight) -->
                            <td class="py-4 px-4 sm:px-6 align-top min-w-56 max-w-sm">
                                <div class="relative group block max-w-full">
                                    <a
                                        href="{{ $item['url_workspace'] }}"
                                        aria-describedby="tooltip-proof-{{ $item['id'] }}"
                                        class="block font-normal text-gray-900 leading-snug line-clamp-2 wrap-break-word hover:text-brand-maroon hover:underline decoration-brand-maroon/40 underline-offset-2 transition duration-150 cursor-pointer"
                                    >
                                        {{ $item['title'] }}
                                    </a>

                                    <div
                                        id="tooltip-proof-{{ $item['id'] }}"
                                        role="tooltip"
                                        class="absolute left-0 top-full mt-1.5 z-30 hidden group-hover:block w-72 sm:w-80 p-2.5 bg-gray-900 text-white text-xs font-normal rounded-md pointer-events-none border border-gray-700 leading-relaxed whitespace-normal wrap-break-word shadow-lg"
                                    >
                                        {{ $item['title'] }}
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom 3: PENULIS -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <span class="font-normal text-gray-800 text-xs sm:text-sm">
                                    {{ $item['corresponding_author']['name'] }}
                                </span>
                            </td>

                            <!-- Kolom 4: AFILIASI -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <span class="font-normal text-gray-500 text-xs sm:text-sm">
                                    {{ $item['corresponding_author']['affiliation'] }}
                                </span>
                            </td>

                            <!-- Kolom 5: VERSI GALAI & LAYOUT EDITOR -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $item['galley_version'] }} (PDF)
                                </span>
                                <span class="text-xs font-normal text-gray-500 block mt-1 truncate max-w-44">
                                    {{ $item['layout_editor'] }}
                                </span>
                            </td>

                            <!-- Kolom 6: CATATAN KOREKSI -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                @if($item['unresolved_notes_count'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $item['unresolved_notes_count'] }} Catatan Terbuka
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Bersih / Selesai
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom 7: BATAS WAKTU (Tanpa Ikon Jam yang Cluttered) -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap text-xs font-normal text-gray-600 tabular-nums">
                                {{ $item['due_at'] }}
                                @if($item['is_urgent'])
                                    <span class="text-[10px] font-medium text-amber-600 block mt-0.5">Segera Diperiksa</span>
                                @endif
                            </td>

                            <!-- Kolom 8: STATUS -->
                            <td class="py-4 px-4 sm:px-6 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $item['status_badge_class'] }}">
                                    {{ $item['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom 9: AKSI (Detail) -->
                            <td class="py-4 px-4 sm:px-6 align-top text-center whitespace-nowrap">
                                <a
                                    href="{{ $item['url_workspace'] }}"
                                    class="inline-flex items-center justify-center px-3.5 py-1.5 text-xs font-semibold rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition duration-150 shrink-0"
                                    title="Periksa Galai"
                                >
                                    <span>Detail</span>
                                    <span class="sr-only">Periksa Galai</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-800">Tidak ada penugasan proofreading</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada naskah yang cocok dengan filter atau kata kunci pencarian ini.
                                    </p>
                                    @if(request('q') || request('tab') !== 'all')
                                        <a href="{{ route('proofreader.submissions.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
                                            Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Simpel & Konsisten -->
        @php
            $pagination = $viewModel->pagination();
        @endphp
        <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span>
                Menampilkan <strong class="text-gray-800 font-semibold tabular-nums">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800 font-semibold tabular-nums">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800 font-semibold tabular-nums">{{ $pagination['total'] }}</strong> penugasan terdaftar
            </span>

            <div class="flex items-center gap-1">
                @if($pagination['current_page'] > 1)
                    <a
                        href="{{ route('proofreader.submissions.index', ['page' => $pagination['current_page'] - 1, 'tab' => $tabs['active'], 'q' => request('q')]) }}"
                        class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-700 hover:bg-gray-100 transition"
                        title="Halaman Sebelumnya"
                    >
                        &larr;
                    </a>
                @else
                    <span class="px-2.5 py-1 rounded border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed">&larr;</span>
                @endif

                <span class="px-2.5 py-1 font-semibold text-gray-700 tabular-nums">
                    {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}
                </span>

                @if($pagination['current_page'] < $pagination['last_page'])
                    <a
                        href="{{ route('proofreader.submissions.index', ['page' => $pagination['current_page'] + 1, 'tab' => $tabs['active'], 'q' => request('q')]) }}"
                        class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-700 hover:bg-gray-100 transition"
                        title="Halaman Berikutnya"
                    >
                        &rarr;
                    </a>
                @else
                    <span class="px-2.5 py-1 rounded border border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed">&rarr;</span>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
