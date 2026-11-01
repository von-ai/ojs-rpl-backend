@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', $announcement->title . ' — Warta ' . ($journal->name ?? 'SIRIDA BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.announcements.index') }}" class="hover:text-brand-maroon">Warta & Pengumuman</a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate max-w-md">{{ $announcement->title }}</span>
    </nav>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Konten Pengumuman (2 Kolom Kiri) -->
        <article class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-6">
            
            <div class="border-b border-gray-100 pb-5 space-y-3">
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="px-2.5 py-0.5 rounded-full font-bold bg-brand-maroon/10 text-brand-maroon text-[10px] uppercase tracking-wider">
                        Pengumuman Resmi
                    </span>
                    <time class="text-gray-400 font-medium">
                        {{ $announcement->published_at ? $announcement->published_at->format('d F Y') : $announcement->created_at->format('d F Y') }}
                    </time>
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug tracking-tight">
                    {{ $announcement->title }}
                </h1>
            </div>

            <!-- Isi Pengumuman -->
            <div class="text-xs sm:text-sm text-gray-700 leading-relaxed whitespace-pre-line space-y-4">
                {!! nl2br(e($announcement->body)) !!}
            </div>

            <div class="pt-6 border-t border-gray-100 flex items-center justify-between text-xs">
                <a href="{{ route('journal.announcements.index') }}" class="font-bold text-gray-600 hover:text-brand-maroon flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar Pengumuman</span>
                </a>

                <a href="{{ route('login') }}" class="font-bold text-brand-maroon hover:underline">
                    Masuk ke Akun Penulis &rarr;
                </a>
            </div>

        </article>

        <!-- Kolom Samping (1 Kolom Kanan) -->
        <aside class="lg:col-span-1 space-y-6">
            <x-journal.sidebar
                :publisherInfo="$viewModel->publisherInfo()"
                :indexingData="$viewModel->indexingData()"
                :impactMetrics="$viewModel->impactMetrics()"
                :announcements="$viewModel->editorialAnnouncements()"
            />
        </aside>

    </div>

</div>
@endsection
