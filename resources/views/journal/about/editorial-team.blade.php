@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Dewan Redaksi & Mitra Bestari — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.about') }}" class="hover:text-brand-maroon">Tentang Jurnal</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Dewan Redaksi</span>
    </nav>

    <!-- Header Halaman -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            TIM PENGELOLA ILMIAH
        </span>
        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
            Dewan Redaksi & Mitra Bestari
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
            Susunan dewan redaksi, editor bagian, dan direktori mitra bestari penjamin mutu saintifik naskah publikasi {{ $journal->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA)' }}.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Konten Utama (2 Kolom Kiri) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 sm:p-8 space-y-8">
            
            <x-journal.about-subnav />

            <!-- 1. Pemimpin Redaksi (Editor in Chief) -->
            <section class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-brand-maroon text-white uppercase tracking-wider">
                        Dewan Redaksi
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900">
                        Pemimpin Redaksi (Editor-in-Chief)
                    </h2>
                </div>

                <div class="space-y-3">
                    @forelse($team['editor_in_chief'] as $member)
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-gray-900">{{ $member['name'] }}</h3>
                                <p class="text-xs text-gray-600 font-medium">{{ $member['affiliation'] }}</p>
                                <span class="text-[11px] text-gray-400 block">{{ $member['country'] }}</span>
                            </div>
                            @if(!empty($member['expertise']))
                                <div class="flex flex-wrap gap-1 max-w-xs justify-start sm:justify-end">
                                    @foreach($member['expertise'] as $kw)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-white border border-gray-200 text-gray-700">
                                            {{ $kw }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Data pemimpin redaksi belum dikonfigurasi.</p>
                    @endforelse
                </div>
            </section>

            <!-- 2. Redaktur Pelaksana (Managing Editor) -->
            @if(!empty($team['managing_editor']))
                <section class="space-y-4">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2">
                        Redaktur Pelaksana (Managing Editor)
                    </h2>

                    <div class="space-y-3">
                        @foreach($team['managing_editor'] as $member)
                            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <h3 class="text-sm font-bold text-gray-900">{{ $member['name'] }}</h3>
                                    <p class="text-xs text-gray-600 font-medium">{{ $member['affiliation'] }}</p>
                                    <span class="text-[11px] text-gray-400 block">{{ $member['country'] }}</span>
                                </div>
                                @if(!empty($member['expertise']))
                                    <div class="flex flex-wrap gap-1 max-w-xs justify-start sm:justify-end">
                                        @foreach($member['expertise'] as $kw)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-white border border-gray-200 text-gray-700">
                                                {{ $kw }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 3. Dewan Editor Bagian (Section Editors) -->
            <section class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider border border-amber-300">
                        Editor Bagian
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900">
                        Editor Bidang Kajian (Section Editors)
                    </h2>
                </div>

                <div class="space-y-3">
                    @forelse($team['section_editors'] as $member)
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-gray-900">{{ $member['name'] }}</h3>
                                <p class="text-xs text-gray-600 font-medium">{{ $member['affiliation'] }}</p>
                                <span class="text-[11px] text-brand-maroon font-semibold">{{ $member['role'] }}</span>
                            </div>
                            @if(!empty($member['expertise']))
                                <div class="flex flex-wrap gap-1 max-w-xs justify-start sm:justify-end">
                                    @foreach($member['expertise'] as $kw)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-white border border-gray-200 text-gray-700">
                                            {{ $kw }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic">Data editor bagian belum dikonfigurasi.</p>
                    @endforelse
                </div>
            </section>

            <!-- 4. Mitra Bestari / Reviewer Directory -->
            <section class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider border border-emerald-300">
                        Mitra Bestari
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900">
                        Direktori Penelaah Sejawat (Peer Reviewers)
                    </h2>
                </div>

                <p class="text-xs text-gray-600 leading-relaxed">
                    Penelaah sejawat (Peer Reviewers) bertugas mengevaluasi kelayakan saintifik dan kepatuhan etis setiap naskah secara independen melalui mekanisme Double-Blind Peer Review.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @forelse($team['reviewers'] as $reviewer)
                        <div class="p-4 rounded-xl border border-gray-200 bg-white hover:border-brand-maroon/30 transition space-y-2">
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">{{ $reviewer['name'] }}</h3>
                                <p class="text-[11px] text-gray-600 font-medium">{{ $reviewer['affiliation'] }}</p>
                                <span class="text-[10px] text-gray-400">{{ $reviewer['country'] }}</span>
                            </div>

                            @if(!empty($reviewer['expertise']))
                                <div class="flex flex-wrap gap-1 pt-1 border-t border-gray-100">
                                    @foreach($reviewer['expertise'] as $kw)
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-gray-100 text-gray-700">
                                            {{ $kw }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="col-span-full text-xs text-gray-400 italic">Data mitra bestari belum tersedia.</p>
                    @endforelse
                </div>
            </section>

        </div>

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
