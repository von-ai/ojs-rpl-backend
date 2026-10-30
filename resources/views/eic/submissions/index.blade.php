@extends('layouts.eic')

@section('title', 'Antrean Naskah Editorial — Portal Editor-in-Chief')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Ruang Kerja Pemimpin Redaksi (Editor-in-Chief)
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Pengelolaan alur editorial naskah masuk: skrining awal (*desk review*), penugasan Section Editor, dan keputusan akhir penerbitan.
            </p>
        </div>
    </div>

    <!-- 4 Kartu Metrik Antrean Editorial -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($viewModel->metricCards() as $card)
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $card['title'] }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-bold border tabular-nums {{ $card['badge_class'] }}">
                        {{ $card['count'] }}
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-gray-900 tabular-nums">
                    {{ $card['count'] }} <span class="text-xs font-normal text-gray-400">Naskah</span>
                </div>
                <p class="text-[11px] text-gray-500 leading-tight">{{ $card['subtitle'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Panel Filter Tab & Pencarian -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 space-y-4 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Tab Filter Status Antrean -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 text-xs">
                @foreach($viewModel->tabs() as $tab)
                    <a 
                        href="{{ route('eic.submissions.index', ['tab' => $tab['key'], 'q' => request('q')]) }}" 
                        class="px-3.5 py-2 rounded-lg font-bold whitespace-nowrap transition cursor-pointer {{ $tab['active'] ? 'bg-brand-maroon text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            <!-- Form Pencarian Naskah -->
            <form action="{{ route('eic.submissions.index') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $viewModel->activeTab }}">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Cari ID naskah, judul, penulis..."
                        class="pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon text-gray-900 w-64"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button type="submit" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    Cari
                </button>
            </form>
        </div>

        <!-- Tabel Antrean Naskah Masuk -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 divide-y divide-gray-100">
                <thead class="bg-gray-50/70 text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-3.5">ID Naskah</th>
                        <th class="py-3 px-3.5">Judul Artikel & Penulis</th>
                        <th class="py-3 px-3.5">Seksi Jurnal</th>
                        <th class="py-3 px-3.5">Status Alur</th>
                        <th class="py-3 px-3.5">Section Editor</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($submissions as $sub)
                        @php
                            $statusBadge = match($sub->status) {
                                'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'in_review' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'revisions_requested' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'accepted', 'in_production' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'declined' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200',
                            };
                            $statusLabel = match($sub->status) {
                                'submitted' => 'Butuh Desk Review',
                                'in_review' => 'Dalam Penelaahan',
                                'revisions_requested' => 'Revisi Penulis',
                                'accepted', 'in_production' => 'Diterima',
                                'declined' => 'Ditolak',
                                default => ucfirst($sub->status),
                            };
                            $assignedSe = $sub->activeEditorAssignment?->editor;
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="py-3.5 px-3.5 font-mono font-bold text-gray-900 whitespace-nowrap">
                                {{ $sub->submission_number }}
                            </td>
                            <td class="py-3.5 px-3.5 max-w-md">
                                <a href="{{ route('eic.submissions.show', $sub->id) }}" class="font-bold text-gray-900 hover:text-brand-maroon line-clamp-1">
                                    {{ $sub->title }}
                                </a>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    Diajukan oleh: <strong class="text-gray-700">{{ $sub->author?->name ?? 'Penulis Naskah' }}</strong>
                                    • {{ $sub->created_at ? $sub->created_at->format('d M Y') : '-' }}
                                </p>
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap text-gray-600">
                                {{ $sub->section?->name ?? 'Artikel Riset' }}
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 whitespace-nowrap text-[11px]">
                                @if($assignedSe)
                                    <span class="font-semibold text-gray-800">{{ $assignedSe->name }}</span>
                                @else
                                    <span class="text-gray-400 italic">Belum Ditugaskan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5 text-right whitespace-nowrap">
                                <a 
                                    href="{{ route('eic.submissions.show', $sub->id) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-brand-maroon bg-red-50 hover:bg-brand-maroon hover:text-white border border-brand-maroon/20 rounded-lg transition cursor-pointer"
                                >
                                    <span>Buka Ruang Kerja</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400 italic">
                                Tidak ada naskah yang sesuai dalam antrean editorial ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-2 border-t border-gray-100">
            {{ $submissions->links() }}
        </div>
    </div>

</div>
@endsection
