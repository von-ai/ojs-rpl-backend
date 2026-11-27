@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Dewan Redaksi & Mitra Bestari — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
    <x-journal.page-banner
        eyebrow="Tim Pengelola Ilmiah"
        :description="'Susunan dewan redaksi, editor bagian, dan direktori mitra bestari penjamin mutu saintifik naskah publikasi ' . ($journal->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA)') . '.'"
        :crumbs="[['label' => 'Tentang Jurnal', 'url' => route('journal.about')], ['label' => 'Dewan Redaksi']]"
    >
        <x-slot:heading>Dewan Redaksi & Mitra Bestari</x-slot:heading>
    </x-journal.page-banner>

    <section class="bg-white">
        <div class="max-w-5xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">
            <x-journal.about-subnav />

            <!-- 1. Pemimpin Redaksi -->
            <section class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-medium bg-brand-maroon text-white tracking-normal uppercase">
                        Dewan Redaksi
                    </span>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                        Pemimpin Redaksi
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

            <!-- 2. Redaktur Pelaksana -->
            @if(!empty($team['managing_editor']))
                <section class="space-y-4">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2">
                        Redaktur Pelaksana
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

            <!-- 3. Dewan Editor Bagian -->
            <section class="space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-2">
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-medium bg-amber-100 text-amber-800 tracking-normal border border-amber-300">
                        Editor Bagian
                    </span>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                        Editor Bidang Kajian
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
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-medium bg-emerald-100 text-emerald-800 tracking-normal border border-emerald-300">
                        Mitra Bestari
                    </span>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                        Direktori Penelaah Sejawat
                    </h2>
                </div>

                <p class="text-xs text-gray-600 leading-relaxed">
                    Penelaah sejawat bertugas mengevaluasi kelayakan saintifik dan kepatuhan etis setiap naskah secara independen melalui mekanisme Double-Blind Peer Review.
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
    </section>
@endsection
