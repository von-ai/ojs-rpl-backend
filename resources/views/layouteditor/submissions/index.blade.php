@extends('layouts.layouteditor')

@section('title', 'Penugasan Galai & Tata Letak — Portal Layout Editor')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    <!-- Header Halaman Simpel & Jelas -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Penugasan Galai & Tata Letak Publikasi
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Kelola naskah yang diteruskan dari copyediting untuk perancangan format terbitan resmi (PDF, HTML).
            </p>
        </div>
    </div>

    <!-- 4 Metrik Ringkas (Kompak & Elegan) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 truncate">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border tabular-nums {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-gray-900 tabular-nums tracking-tight">
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
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none" aria-label="Filter Status">
                @php
                    $tabs = $viewModel->tabs();
                @endphp
                @foreach ($tabs['items'] as $tabKey => $tab)
                    @php
                        $isActive = $tabs['active'] === $tabKey;
                    @endphp
                    <a 
                        href="{{ route('layouteditor.submissions.index', ['tab' => $tabKey, 'q' => request('q')]) }}"
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
            <form action="{{ route('layouteditor.submissions.index') }}" method="GET" class="relative w-full md:w-72">
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
                    placeholder="Cari judul naskah atau penulis..."
                    class="w-full pl-9 pr-8 py-1.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                @if(request('q'))
                    <a 
                        href="{{ route('layouteditor.submissions.index', ['tab' => $tabs['active']]) }}" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600"
                        title="Hapus pencarian"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Daftar Naskah Layout -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-gray-500 font-semibold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3 px-4 sm:px-5">Informasi Naskah</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Format Galai</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Proofreader & Koreksi</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 whitespace-nowrap">Batas Waktu</th>
                        <th scope="col" class="py-3 px-4 sm:px-5">Status</th>
                        <th scope="col" class="py-3 px-4 sm:px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($viewModel->submissions() as $item)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <!-- Kolom Naskah: Kode & Judul -->
                            <td class="py-3.5 px-4 sm:px-5 max-w-sm sm:max-w-md">
                                <span class="text-[10px] font-mono font-bold text-gray-400 block mb-0.5">
                                    {{ $item['number'] }} • {{ $item['section_title'] }}
                                </span>
                                <a 
                                    href="{{ $item['url_workspace'] }}" 
                                    class="font-serif font-bold text-gray-900 group-hover:text-brand-maroon transition leading-snug line-clamp-2"
                                    title="{{ $item['title'] }}"
                                >
                                    {{ $item['title'] }}
                                </a>
                                <span class="text-[11px] text-gray-500 block mt-1 truncate">
                                    Penulis: {{ $item['corresponding_author']['name'] }}
                                </span>
                            </td>

                            <!-- Kolom Format Galai -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs font-medium text-gray-700">
                                        {{ $item['galley_format'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Kolom Proofreader & Koreksi -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="font-medium text-gray-800 block text-xs truncate max-w-44">
                                    {{ $item['proofreader'] }}
                                </span>
                                @if($item['unresolved_notes_count'] > 0)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 mt-0.5">
                                        <span>{{ $item['unresolved_notes_count'] }} Catatan Belum Selesai</span>
                                    </span>
                                @else
                                    <span class="text-[11px] text-gray-400 block mt-0.5">
                                        Tidak ada catatan tertunda
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Batas Waktu -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 {{ $item['is_urgent'] ? 'text-amber-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-xs font-medium {{ $item['is_urgent'] ? 'text-amber-700 font-semibold' : 'text-gray-600' }}">
                                        {{ $item['due_at'] }}
                                    </span>
                                </div>
                                @if($item['is_urgent'])
                                    <span class="text-[10px] font-semibold text-amber-600 block mt-0.5">Mendekati Batas</span>
                                @endif
                            </td>

                            <!-- Kolom Status -->
                            <td class="py-3.5 px-4 sm:px-5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $item['status_badge_class'] }}">
                                    {{ $item['status_label'] }}
                                </span>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-3.5 px-4 sm:px-5 text-right whitespace-nowrap">
                                <a 
                                    href="{{ $item['url_workspace'] }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-brand-maroon bg-brand-maroon/5 hover:bg-brand-maroon hover:text-white rounded-lg transition duration-150 border border-brand-maroon/20"
                                    title="Buka Ruang Kerja Galai"
                                >
                                    <span>Buka Galai</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-800">Tidak ada penugasan galai</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Tidak ada naskah yang cocok dengan filter atau kata kunci pencarian ini.
                                    </p>
                                    @if(request('q') || request('tab') !== 'all')
                                        <a href="{{ route('layouteditor.submissions.index') }}" class="mt-3 text-xs font-semibold text-brand-maroon hover:underline">
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

        <!-- Pagination Simpel -->
        @php
            $pagination = $viewModel->pagination();
        @endphp
        <div class="px-5 py-3 border-t border-gray-200 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
            <span>
                Menampilkan <strong class="text-gray-800">{{ $pagination['from'] }}</strong> - <strong class="text-gray-800">{{ $pagination['to'] }}</strong> dari <strong class="text-gray-800">{{ $pagination['total'] }}</strong> penugasan
            </span>

            <div class="flex items-center gap-1">
                <button type="button" disabled class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-400 cursor-not-allowed">
                    Sebelumnya
                </button>
                <button type="button" disabled class="px-2.5 py-1 rounded border border-gray-200 bg-white text-gray-400 cursor-not-allowed">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
