@extends('layouts.reviewer')

@section('title', 'Tugas Penelaahan Ilmiah (Peer Reviewer) — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Tugas Penelaahan Ilmiah</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                Selamat datang di portal telaah ganda tertutup (*Double-Blind Peer Review*). Identitas penulis dan penelaah dirahasiakan untuk menjamin objektivitas ilmiah.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Mitra Bestari Terakreditasi
            </span>
        </div>
    </div>

    <!-- Statistik Ringkas Penugasan -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('reviewer.assignments.index', ['status' => 'all']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'all' ? 'border-brand-maroon ring-1 ring-brand-maroon' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Penugasan</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ $viewModel->totalCount() }}</span>
        </a>

        <a href="{{ route('reviewer.assignments.index', ['status' => 'invited']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'invited' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Undangan Masuk</span>
            <span class="text-2xl font-black text-amber-700 mt-1 block">{{ $viewModel->invitedCount() }}</span>
        </a>

        <a href="{{ route('reviewer.assignments.index', ['status' => 'active']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'active' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-blue-600 uppercase tracking-wider">Sedang Ditelaah</span>
            <span class="text-2xl font-black text-blue-700 mt-1 block">{{ $viewModel->activeCount() }}</span>
        </a>

        <a href="{{ route('reviewer.assignments.index', ['status' => 'completed']) }}" class="bg-white p-4 rounded-xl border {{ $viewModel->currentStatus() === 'completed' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-200' }} shadow-2xs hover:border-gray-300 transition-all">
            <span class="block text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Telaah Selesai</span>
            <span class="text-2xl font-black text-emerald-700 mt-1 block">{{ $viewModel->completedCount() }}</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold">
            <a href="{{ route('reviewer.assignments.index', ['status' => 'all', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'all' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Semua ({{ $viewModel->totalCount() }})
            </a>
            <a href="{{ route('reviewer.assignments.index', ['status' => 'invited', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'invited' ? 'bg-amber-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Undangan Masuk ({{ $viewModel->invitedCount() }})
            </a>
            <a href="{{ route('reviewer.assignments.index', ['status' => 'active', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'active' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Sedang Ditelaah ({{ $viewModel->activeCount() }})
            </a>
            <a href="{{ route('reviewer.assignments.index', ['status' => 'completed', 'search' => $viewModel->searchQuery()]) }}" class="px-3 py-1.5 rounded-lg shrink-0 transition-colors {{ $viewModel->currentStatus() === 'completed' ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                Telaah Selesai ({{ $viewModel->completedCount() }})
            </a>
        </div>

        <!-- Search Form -->
        <form action="{{ route('reviewer.assignments.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $viewModel->currentStatus() }}">
            <div class="relative w-full sm:w-64">
                <input type="text" name="search" value="{{ $viewModel->searchQuery() }}" placeholder="Cari judul atau nomor naskah..." class="w-full text-xs rounded-lg border border-gray-300 pl-8 pr-3 py-2 focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg shrink-0 cursor-pointer">
                Cari
            </button>
            @if($viewModel->searchQuery())
                <a href="{{ route('reviewer.assignments.index', ['status' => $viewModel->currentStatus()]) }}" class="text-xs text-rose-600 hover:underline shrink-0">Reset</a>
            @endif
        </form>
    </div>

    <!-- Assignments Table Card -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
        @if($viewModel->assignments->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-sm">Tidak ada tugas telaah ditemukan</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    Saat ini belum ada tugas penelaahan naskah ilmiah pada kategori filter ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 sm:px-6">Naskah (Blind Projection)</th>
                            <th class="py-3 px-4">Bidang Kajian</th>
                            <th class="py-3 px-4">Batas Konfirmasi</th>
                            <th class="py-3 px-4">Batas Selesai Telaah</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @foreach($viewModel->assignments as $assignment)
                            @php
                                $round = $assignment->reviewRound;
                                $submission = $round?->submission;
                                $title = $round?->reviewer_title ?? $submission?->title ?? 'Naskah Tanpa Judul';
                                $subNumber = $submission?->submission_number ?? 'SUB-'.$submission?->id;
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-4 sm:px-6 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10px] text-gray-500 font-bold bg-gray-100 px-1.5 py-0.5 rounded">
                                            {{ $subNumber }}
                                        </span>
                                        <span class="text-[10px] text-indigo-700 bg-indigo-50 font-semibold px-1.5 py-0.5 rounded border border-indigo-100">
                                            Putaran {{ $round?->round ?? 1 }}
                                        </span>
                                    </div>
                                    <a href="{{ route('reviewer.assignments.show', $assignment->id) }}" class="font-serif font-bold text-gray-900 hover:text-brand-maroon text-sm block line-clamp-1">
                                        {{ $title }}
                                    </a>
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-800">
                                        {{ $submission?->section?->name ?? $submission?->section?->title ?? 'Umum' }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    <span class="font-medium text-gray-900 block">
                                        {{ $assignment->response_due_at ? $assignment->response_due_at->format('d M Y') : '-' }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-gray-600">
                                    <span class="font-medium text-gray-900 block">
                                        {{ $assignment->review_due_at ? $assignment->review_due_at->format('d M Y') : '-' }}
                                    </span>
                                </td>

                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $assignment->status_badge_class }}">
                                        {{ $assignment->status_label }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 sm:px-6 text-right">
                                    <a href="{{ route('reviewer.assignments.show', $assignment->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-maroon text-white hover:bg-brand-maroon/90 text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer">
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
            @if($viewModel->assignments->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $viewModel->assignments->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
