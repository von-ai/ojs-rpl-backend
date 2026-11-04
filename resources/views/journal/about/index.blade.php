@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Tentang Jurnal — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Tentang Jurnal</span>
    </nav>

    <!-- Header Halaman -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            INFORMASI RESMI PENERBITAN
        </span>
        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
            Tentang Jurnal Riset & Inovasi Daerah (SIRIDA)
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
            Media publikasi berkala ilmiah resmi {{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }} yang berfokus pada diseminasi hasil riset terapan, inovasi daerah, dan kajian kebijakan publik strategis.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <!-- Konten Utama (2 Kolom Kiri) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 sm:p-8 space-y-8">

            <x-journal.about-subnav />

            <!-- Seksi 1: Profil & Identitas Jurnal (Data Backend & ERD) -->
            <section class="space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <span>Profil dan Identitas Jurnal</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    <strong>{{ $journal->name ?? 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)' }}</strong> diterbitkan oleh {{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }} sebagai wadah ilmiah bagi para peneliti, akademisi, perekayasa, dan praktisi untuk mendiseminasikan naskah ilmiah orisinal yang berimplikasi langsung terhadap perumusan kebijakan pembangunan berkelanjutan dan ketahanan perkotaan.
                </p>

                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-gray-500 block text-[11px]">Nama Terbitan Berkala:</span>
                        <strong class="text-gray-900">{{ $journal->name ?? 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[11px]">Inisial / Singkatan Resmi:</span>
                        <strong class="text-gray-900 font-mono">{{ $journal->initials ?? 'SIRIDA' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[11px]">p-ISSN (Cetak) / e-ISSN (Elektronik):</span>
                        <strong class="text-gray-900 font-mono">{{ $journal->issn ?? '2746-8194' }} / {{ $journal->eissn ?? '2746-8208' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[11px]">Digital Object Identifier (DOI) Prefix:</span>
                        <strong class="text-gray-900 font-mono">10.55981/sirida</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[11px]">Penerbit:</span>
                        <strong class="text-gray-900">{{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[11px]">Email & Kontak Redaksi:</span>
                        <strong class="text-gray-900">{{ $journal->editorial_email ?? 'jurnal.brida@makassar.go.id' }}</strong>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-gray-500 block text-[11px]">Lisensi Publikasi:</span>
                        <strong class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[11px] font-semibold border border-emerald-200 inline-block">
                            {{ $journal->license ?? 'Creative Commons Attribution 4.0 International (CC BY 4.0)' }}
                        </strong>
                    </div>
                </div>
            </section>

            <!-- Seksi 2: Fokus dan Ruang Lingkup (Focus & Scope dari Database) -->
            <section class="space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Fokus dan Ruang Lingkup (Focus and Scope)</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    {{ $journal->focus_scope ?? 'SIRIDA menerima naskah hasil penelitian empiris, studi komparatif, dan analisis kebijakan berbasis data (evidence-based policy) yang relevan dengan dinamika pembangunan daerah dan perkotaan.' }}
                </p>

                @if(isset($journal->sections) && $journal->sections->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        @foreach($journal->sections as $sec)
                            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                                    <span>{{ $sec->name }}</span>
                                </h3>
                                @if($sec->description)
                                    <p class="text-gray-500 text-[11px] leading-relaxed">
                                        {{ $sec->description }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                                <span>Kebijakan Publik & Tata Kelola Daerah</span>
                            </h3>
                            <p class="text-gray-500 text-[11px] leading-relaxed">
                                Inovasi pelayanan publik, reformasi birokrasi, desentralisasi fiskal, efektivitas regulasi daerah, serta partisipasi masyarakat.
                            </p>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                                <span>Makassar Smart City & Rekayasa IoT</span>
                            </h3>
                            <p class="text-gray-500 text-[11px] leading-relaxed">
                                Transformasi digital pemerintahan, jaringan sensor cerdas mikroklimat lorong, sistem kecerdasan artifisial, dan analisis data perkotaan.
                            </p>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                                <span>Resiliensi Pesisir & Lingkungan Hidup</span>
                            </h3>
                            <p class="text-gray-500 text-[11px] leading-relaxed">
                                Mitigasi banjir rob, manajemen tata ruang pulau-pulau Sangkarrang, pengelolaan sampah terpadu, dan adaptasi perubahan iklim.
                            </p>
                        </div>
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/50 space-y-1.5">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                                <span>Ekonomi Sirkular & Pengembangan UMKM</span>
                            </h3>
                            <p class="text-gray-500 text-[11px] leading-relaxed">
                                Pemberdayaan ekonomi lorong wisata, inkubasi bisnis rintisan daerah, pemanfaatan biokonversi limbah, dan ketahanan pangan perkotaan.
                            </p>
                        </div>
                    </div>
                @endif
            </section>

            <!-- Seksi 3: Frekuensi & Jadwal Penerbitan (Dari Database) -->
            <section class="space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Frekuensi Penerbitan</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    {{ $journal->frequency ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA) diterbitkan secara berkala sebanyak 2 (dua) kali per tahun, yaitu pada bulan Juni dan Desember. Setiap edisi memuat minimal 8 hingga 15 naskah ilmiah hasil telaah sejawat (double-blind peer-reviewed).' }}
                </p>
            </section>

            <!-- Seksi 4: Biaya Pemrosesan Naskah (APC dari Database) -->
            <section class="space-y-3 p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 text-xs">
                <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                    <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Bebas Biaya Publikasi (Diamond Open Access / Zero APC)</span>
                </div>
                <p class="text-emerald-800 leading-relaxed">
                    {{ $journal->apc_fee ?? 'Seluruh biaya pemrosesan naskah (Article Processing Charge - APC), biaya penelaahan (peer-review), penataan letak (layout editing), hingga penerbitan naskah sepenuhnya dibiayai oleh Pemerintah Kota Makassar melalui APBD BRIDA Kota Makassar. Penulis dan pembaca tidak dipungut biaya apapun (Gratis / Rp 0).' }}
                </p>
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
