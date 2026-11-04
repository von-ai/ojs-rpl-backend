@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Kebijakan & Etika Publikasi — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.about') }}" class="hover:text-brand-maroon">Tentang Jurnal</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Kebijakan & Etika Publikasi</span>
    </nav>

    <!-- Header Halaman -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            INTEGRITAS & TATA KELOLA ILMIAH
        </span>
        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
            Kebijakan Redaksi & Etika Publikasi Ilmiah
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
            Pernyataan kepatuhan integritas publikasi mengacu pada pedoman standar internasional Committee on Publication Ethics (COPE) dan peraturan penerbitan berkala ilmiah nasional.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

        <!-- Konten Utama (2 Kolom Kiri) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 sm:p-8 space-y-8">

            <x-journal.about-subnav />

            <!-- 1. Kebijakan Akses Terbuka (Open Access Policy - Dari Database) -->
            <section class="space-y-3">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                    <span>Kebijakan Akses Terbuka (Open Access Policy)</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    {{ $journal->open_access_policy ?: 'Jurnal ini menyediakan akses terbuka langsung (Gold Open Access) ke seluruh isi naskah dengan prinsip bahwa membuat hasil penelitian dapat diakses secara bebas oleh publik demi mendukung pertukaran pengetahuan global.' }}
                </p>
                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    Pembaca dapat mengunduh, membaca, menyalin, mendistribusikan, dan menyitir naskah lengkap setiap artikel terbit tanpa hambatan biaya (paywall) maupun keharusan berlangganan.
                </p>
            </section>

            <!-- 2. Protokol Penelaahan (Peer Review Process - Dari Database) -->
            <section class="space-y-3">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Protokol Telaah Sejawat (Peer Review Policy)</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    {{ $journal->peer_review_policy ?: 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan selama proses penelaahan).' }}
                </p>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    Setiap naskah yang lolos tahap evaluasi awal (Desk Review / Screening) oleh Dewan Redaksi akan ditugaskan kepada sekurang-kurangnya 2 (dua) orang Mitra Bestari independen. Penelaah mengevaluasi kelayakan saintifik secara objektif dan bebas dari benturan kepentingan.
                </p>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs space-y-2">
                    <h3 class="font-bold text-gray-900">Kriteria Evaluasi Penelaahan Naskah:</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 leading-relaxed">
                        <li>Kebaruan (novelty) dan kontribusi substantif terhadap penyelesaian masalah kebijakan daerah.</li>
                        <li>Ketepatan metodologi penelitian, validitas analisis empiris, dan reliabilitas data.</li>
                        <li>Kepatuhan terhadap kode etik penelitian dan perlindungan hak subjek riset.</li>
                        <li>Kejelasan penulisan, sistematika argumentasi, serta kemutakhiran rujukan ilmiah (minimal 80% jurnal bereputasi 10 tahun terakhir).</li>
                    </ul>
                </div>
            </section>

            <!-- 3. Etika Publikasi & Anti Plagiarisme -->
            <section class="space-y-3">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Integritas Akademik & Pencegahan Plagiarisme</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    Redaksi menjunjung tinggi standar etika publikasi internasional Committee on Publication Ethics (COPE). Setiap naskah yang masuk diperiksa menggunakan sistem deteksi kesamaan (Turnitin / iThenticate). Batas maksimal tingkat kemiripan (similarity index) adalah <strong>20%</strong> (tidak termasuk bibliografi dan kutipan yang diapit tanda kutip resmi).
                </p>
                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    Pelanggaran etika berupa pemalsuan data (falsification), fabrikasi temuan, publikasi ganda (redundant publication), atau manipulasi kepenulisan (ghost authorship) akan berakibat pada penolakan naskah atau penarikan artikel (retraction).
                </p>
            </section>

            <!-- 4. Biaya Pemrosesan Naskah (APC - Dari Database) -->
            <section class="space-y-3">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Biaya Pengajuan & Publikasi Naskah (Author Fees / APC)</span>
                </h2>

                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 text-xs space-y-1.5">
                    <strong class="text-emerald-950 font-bold block text-sm">
                        {{ $journal->apc_fee ?: 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah - Didanai Penuh oleh APBD BRIDA Kota Makassar)' }}
                    </strong>
                    <p class="text-emerald-900 leading-relaxed">
                        Penulis tidak dikenakan biaya pengiriman (submission fee), biaya penelaahan (peer-review fee), maupun biaya penerbitan (article processing charges). Seluruh proses disubsidi penuh oleh {{ $journal->publisher ?: 'Pemerintah Kota Makassar' }}.
                    </p>
                </div>
            </section>

            <!-- 5. Hak Cipta dan Lisensi (Copyright & License - Dari Database) -->
            <section class="space-y-3">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Pernyataan Hak Cipta & Lisensi (Copyright Notice)</span>
                </h2>

                <p class="text-xs text-gray-600 leading-relaxed text-left">
                    Penulis memegang hak cipta penuh atas naskah yang diterbitkan (authors retain copyright). Hak penerbitan pertama diberikan kepada {{ $journal->name ?: 'Jurnal Riset dan Inovasi Daerah (SIRIDA)' }} dengan lisensi distribusi:
                </p>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex items-start gap-3">
                    <img src="https://licensebuttons.net/l/by-sa/4.0/88x31.png" alt="CC License" class="h-8 object-contain shrink-0 mt-0.5">
                    <div class="text-xs text-gray-700 space-y-1">
                        <strong class="text-gray-900 block">{{ $journal->license ?: 'Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)' }} (CC BY-SA 4.0)</strong>
                        <p class="text-[11px] text-gray-500 leading-relaxed">
                            Karya ini dilisensikan di bawah lisensi resmi Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0) yang mengizinkan siapa saja untuk menyalin, menyebarluaskan, dan mengadaptasi materi untuk kepentingan apapun, dengan syarat memberikan atribusi kredit yang sesuai kepada penulis dan penerbit.
                        </p>
                    </div>
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
