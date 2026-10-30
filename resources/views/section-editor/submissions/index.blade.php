@extends('layouts.section-editor')

@section('title', 'Antrean Telaah Naskah (Section Editor) — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Antrean Telaah Naskah</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                Kelola putaran telaah sejawat (*peer review rounds*), penugasan mitra bestari, dan rekomendasi editorial untuk Pemimpin Redaksi.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                Penugasan Aktif
            </span>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('section-editor.submissions.index', ['status' => 'all']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'all' ? 'border-brand-maroon ring-1 ring-brand-maroon' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Ditugaskan</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ $viewModel->totalCount() }}</span>
        </a>

        <a href="{{ route('section-editor.submissions.index', ['status' => 'in_review']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'in_review' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Sedang Ditelaah</span>
            <span class="text-2xl font-black text-blue-700 mt-1 block">{{ $viewModel->inReviewCount() }}</span>
        </a>

        <a href="{{ route('section-editor.submissions.index', ['status' => 'revisions_requested']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'revisions_requested' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Menunggu Revisi</span>
            <span class="text-2xl font-black text-amber-700 mt-1 block">{{ $viewModel->revisionsRequestedCount() }}</span>
        </a>

        <a href="{{ route('section-editor.submissions.index', ['status' => 'reviewed']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'reviewed' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Review Selesai</span>
            <span class="text-2xl font-black text-emerald-700 mt-1 block">{{ $viewModel->reviewedCount() }}</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold">
            <a href="{{ route('section-editor.submissions.index', ['status' => 'all', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Semua ({{ $viewModel->totalCount() }})
            </a>
            <a href="{{ route('section-editor.submissions.index', ['status' => 'in_review', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'in_review' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Sedang Ditelaah ({{ $viewModel->inReviewCount() }})
            </a>
            <a href="{{ route('section-editor.submissions.index', ['status' => 'revisions_requested', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'revisions_requested' ? 'bg-amber-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Menunggu Revisi ({{ $viewModel->revisionsRequestedCount() }})
            </a>
            <a href="{{ route('section-editor.submissions.index', ['status' => 'reviewed', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'reviewed' ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Review Selesai ({{ $viewModel->reviewedCount() }})
            </a>
        </div>

        <!-- Search Form -->
        <form action="{{ route('section-editor.submissions.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $viewModel->currentStatus() }}">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ $viewModel->searchQuery() }}" placeholder="Cari judul, nomor, penulis..." class="w-full text-xs rounded-lg border border-gray-300 pl-8 pr-3 py-2 focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg shrink-0 cursor-pointer">
                Cari
            </button>
            @if($viewModel->searchQuery())
                <a href="{{ route('section-editor.submissions.index', ['status' => $viewModel->currentStatus()]) }}" class="text-xs text-rose-600 hover:underline shrink-0">Reset</a>
            @endif
        </form>
    </div>

    <!-- Submissions Table Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
        @if($viewModel->submissions->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm">Tidak ada naskah ditemukan</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    Saat ini belum ada naskah yang ditugaskan kepada Anda pada kategori filter ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 sm:px-6">Naskah & Penulis</th>
                            <th class="py-3 px-4">Bidang (Section)</th>
                            <th class="py-3 px-4">Progres Mitra Bestari</th>
                            <th class="py-3 px-4">Status Naskah</th>
                            <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($viewModel->submissions as $submission)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-4 sm:px-6 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10px] text-gray-500 font-bold bg-gray-100 px-1.5 py-0.5 rounded">
                                            {{ $submission->submission_number ?? 'SUB-'.$submission->id }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">
                                            {{ $submission->created_at ? $submission->created_at->format('d M Y') : '-' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('section-editor.submissions.show', $submission->id) }}" class="font-serif font-bold text-gray-900 hover:text-brand-maroon text-sm block line-clamp-1">
                                        {{ $submission->title }}
                                    </a>
                                    <div class="text-[11px] text-gray-500">
                                        Oleh: <span class="font-medium text-gray-700">{{ $submission->author?->name ?? 'Anonim' }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-800">
                                        {{ $submission->section?->name ?? $submission->section?->title ?? 'Umum' }}
                                    </span>
                                </td>

                                <td class="py-4 px-4">
                                    @php
                                        $currentRound = $submission->currentReviewRound;
                                        $completedCount = $currentRound ? $currentRound->assignments->where('status', 'completed')->count() : 0;
                                        $invitedCount = $currentRound ? $currentRound->assignments->count() : 0;
                                    @endphp
                                    @if($currentRound)
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-gray-800 text-xs">Putaran {{ $currentRound->round }}</span>
                                                <span class="text-[10px] text-gray-500">({{ $completedCount }}/{{ $currentRound->minimum_completed_reviews }} selesai)</span>
                                            </div>
                                            <div class="w-24 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, ($completedCount / max(1, $currentRound->minimum_completed_reviews)) * 100) }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum dimulai</span>
                                    @endif
                                </td>

                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $submission->status_badge_class }}">
                                        {{ $submission->status_label }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 sm:px-6 text-right">
                                    <a href="{{ route('section-editor.submissions.show', $submission->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-maroon text-white hover:bg-brand-maroon/90 text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer">
                                        <span>Ruang Telaah</span>
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

            <!-- Paginasi -->
            @if($viewModel->submissions->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $viewModel->submissions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
