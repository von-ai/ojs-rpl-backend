@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Tentang Jurnal — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
    @php
        $aboutTitle = ($journal && $journal->slug !== 'sirida-makassar')
            ? 'Tentang ' . $journal->name
            : 'Tentang Jurnal Riset & Inovasi Daerah (SIRIDA)';
    @endphp

    <!-- Header Banner Konsisten dengan Beranda -->
    <section data-hero-banner class="relative bg-[#1E1214] text-white border-b border-black/15 overflow-hidden">
        <img src="{{ asset('images/hero-sirida-bg.jpg') }}" alt="" aria-hidden="true" class="pointer-events-none absolute inset-0 w-full h-full object-cover object-center">
        <div class="pointer-events-none absolute inset-0 bg-linear-to-r from-[#2B1014]/88 via-[#5B141D]/65 to-[#1F1316]/35" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-[#1A0C0F]/50 via-transparent to-[#1A0C0F]/20" aria-hidden="true"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-7 sm:py-9 space-y-4">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-white/80 flex-wrap">
                <a href="{{ route('journal.index') }}" class="hover:text-white transition">Beranda</a>
                <span class="text-white/40" aria-hidden="true">/</span>
                <span class="text-white font-semibold">Tentang Jurnal</span>
            </nav>
            <div class="max-w-3xl space-y-2">
                <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-white/85">Informasi Resmi Penerbitan</p>
                <h1 class="font-sans text-2xl sm:text-3xl font-extrabold text-white leading-tight tracking-tight text-left">
                    @if($journal && $journal->slug !== 'sirida-makassar')
                        Tentang {{ $journal->name }}
                    @else
                        Tentang Jurnal Riset & Inovasi Daerah (SIRIDA)
                    @endif
                </h1>
                <p class="text-sm sm:text-[15px] text-white/85 leading-relaxed text-left">
                    Media publikasi berkala ilmiah resmi {{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }} yang berfokus pada diseminasi hasil riset terapan, inovasi daerah, dan kajian kebijakan publik strategis.
                </p>
            </div>
        </div>
    </section>

    <!-- Konten Editorial Terbuka (Material 3 Flat Surface, Tanpa Sidebar & Kotak Berlapis) -->
    <section class="bg-white">
        <div class="max-w-5xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-10">
            <x-journal.about-subnav />

            <!-- Seksi 1: Profil & Identitas Jurnal -->
            <section class="space-y-5">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 pb-2.5 border-b border-divider">
                    Profil dan Identitas Jurnal
                </h2>

                <p class="text-sm text-gray-700 leading-relaxed text-left">
                    <strong>{{ $journal->name ?? 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)' }}</strong> diterbitkan oleh {{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }} sebagai wadah ilmiah bagi para peneliti, akademisi, perekayasa, dan praktisi untuk mendiseminasikan naskah ilmiah orisinal yang berimplikasi langsung terhadap perumusan kebijakan pembangunan berkelanjutan dan ketahanan perkotaan.
                </p>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 pt-2 border-t border-divider text-sm">
                    <div class="py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Terbitan Berkala</dt>
                        <dd class="font-semibold text-gray-900 mt-1">{{ $journal->name ?? 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar' }}</dd>
                    </div>
                    <div class="py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Inisial / Singkatan Resmi</dt>
                        <dd class="font-mono font-semibold text-gray-900 mt-1">{{ $journal->initials ?? 'SIRIDA' }}</dd>
                    </div>
                    <div class="py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">p-ISSN / e-ISSN</dt>
                        <dd class="font-mono font-semibold text-gray-900 mt-1 tabular-nums">{{ $journal->issn ?? '2746-8194' }} / {{ $journal->eissn ?? '2746-8208' }}</dd>
                    </div>
                    <div class="py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">DOI Prefix</dt>
                        <dd class="font-mono font-semibold text-gray-900 mt-1">{{ $journal?->currentIssue?->doi_prefix ?: '10.55981/sirida' }}</dd>
                    </div>
                    <div class="py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Penerbit</dt>
                        <dd class="font-semibold text-gray-900 mt-1">{{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar' }}</dd>
                    </div>
                    <div class="py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Email &amp; Kontak Redaksi</dt>
                        <dd class="font-semibold text-gray-900 mt-1">{{ $journal->editorial_email ?? 'jurnal.brida@makassar.go.id' }}</dd>
                    </div>
                    <div class="sm:col-span-2 py-1">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Lisensi Publikasi</dt>
                        <dd class="font-semibold text-emerald-800 mt-1">{{ $viewModel->licenseLabel() }}</dd>
                    </div>
                </dl>
            </section>

            <!-- Seksi 2: Fokus dan Ruang Lingkup -->
            <section class="space-y-5">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 pb-2.5 border-b border-divider">
                    Fokus dan Ruang Lingkup
                </h2>

                <p class="text-sm text-gray-700 leading-relaxed text-left">
                    {{ $journal->focus_scope ?? 'SIRIDA menerima naskah hasil penelitian empiris, studi komparatif, dan analisis kebijakan berbasis data yang relevan dengan dinamika pembangunan daerah dan perkotaan.' }}
                </p>

                @if(isset($journal->sections) && $journal->sections->isNotEmpty())
                    <div class="divide-y divide-divider border-y border-divider">
                        @foreach($journal->sections as $sec)
                            <div class="py-4 space-y-1">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900">{{ $sec->name }}</h3>
                                @if($sec->description)
                                    <p class="text-sm text-gray-600 leading-relaxed text-left">{{ $sec->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="divide-y divide-divider border-y border-divider">
                        <div class="py-4 space-y-1">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900">Kebijakan Publik &amp; Tata Kelola Daerah</h3>
                            <p class="text-sm text-gray-600 leading-relaxed text-left">Inovasi pelayanan publik, reformasi birokrasi, desentralisasi fiskal, efektivitas regulasi daerah, serta partisipasi masyarakat.</p>
                        </div>
                        <div class="py-4 space-y-1">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900">Makassar Smart City &amp; Rekayasa IoT</h3>
                            <p class="text-sm text-gray-600 leading-relaxed text-left">Transformasi digital pemerintahan, jaringan sensor cerdas mikroklimat lorong, sistem kecerdasan artifisial, dan analisis data perkotaan.</p>
                        </div>
                        <div class="py-4 space-y-1">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900">Resiliensi Pesisir &amp; Lingkungan Hidup</h3>
                            <p class="text-sm text-gray-600 leading-relaxed text-left">Mitigasi banjir rob, manajemen tata ruang pulau-pulau Sangkarrang, pengelolaan sampah terpadu, dan adaptasi perubahan iklim.</p>
                        </div>
                        <div class="py-4 space-y-1">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900">Ekonomi Sirkular &amp; Pengembangan UMKM</h3>
                            <p class="text-sm text-gray-600 leading-relaxed text-left">Pemberdayaan ekonomi lorong wisata, inkubasi bisnis rintisan daerah, pemanfaatan biokonversi limbah, dan ketahanan pangan perkotaan.</p>
                        </div>
                    </div>
                @endif
            </section>

            <!-- Seksi 3: Frekuensi Penerbitan -->
            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 pb-2.5 border-b border-divider">
                    Frekuensi Penerbitan
                </h2>
                <p class="text-sm text-gray-700 leading-relaxed text-left">
                    {{ $journal->frequency ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA) diterbitkan secara berkala sebanyak 2 (dua) kali per tahun, yaitu pada bulan Juni dan Desember. Setiap edisi memuat minimal 8 hingga 15 naskah ilmiah hasil telaah sejawat.' }}
                </p>
            </section>
        </div>
    </section>
@endsection
