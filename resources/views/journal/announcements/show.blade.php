@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', $announcement->title . ' — Warta ' . ($journal->name ?? 'SIRIDA BRIDA Makassar'))

@section('content')
    <x-journal.page-banner
        eyebrow="Pengumuman Resmi"
        :title="$announcement->title"
        :crumbs="[
            ['label' => 'Warta & Pengumuman', 'url' => route('journal.announcements.index')],
            ['label' => \Illuminate\Support\Str::limit($announcement->title, 56)],
        ]"
    />

    <section class="bg-white">
        <article class="max-w-4xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-6">
            <div class="flex flex-wrap items-center gap-3 pb-4 border-b border-divider text-xs text-gray-600">
                <time class="font-mono font-medium tabular-nums">
                    Diterbitkan {{ $announcement->published_at ? $announcement->published_at->format('d F Y') : $announcement->created_at->format('d F Y') }}
                </time>
                @if($announcement->journal)
                    <span class="text-gray-300" aria-hidden="true">&middot;</span>
                    <span class="font-semibold text-gray-900">{{ $announcement->journal->name }}</span>
                @endif
            </div>

            <div class="text-sm sm:text-base text-gray-800 leading-relaxed whitespace-pre-line space-y-4 text-left">
                {!! nl2br(e($announcement->body)) !!}
            </div>

            <div class="pt-6 border-t border-divider flex flex-wrap items-center justify-between gap-3 text-sm">
                <a href="{{ route('journal.announcements.index') }}" class="font-semibold text-gray-700 hover:text-brand-maroon inline-flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Daftar Pengumuman</span>
                </a>

                <a href="{{ route('login') }}" class="font-semibold text-brand-maroon hover:underline">
                    Masuk ke Akun Penulis &rarr;
                </a>
            </div>
        </article>
    </section>
@endsection
