@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Panduan Penulis — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.about') }}" class="hover:text-brand-maroon">Tentang Jurnal</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Panduan Penulis</span>
    </nav>

    <!-- Header Halaman -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
                PETUNJUK PENYIAPAN NASKAH
            </span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Panduan Penulis (Author Guidelines)
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
                Pedoman format, sistematika penulisan, dan tata cara pengajuan naskah ilmiah hasil riset ke {{ $journal->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA) BRIDA Kota Makassar' }}.
            </p>
        </div>

        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-maroon text-white font-bold text-xs hover:bg-brand-maroon-hover transition shrink-0 self-start sm:self-auto border border-brand-maroon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Kirim Naskah Baru (Online Submission)</span>
        </a>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Konten Utama (2 Kolom Kiri) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-8">
            
            <x-journal.about-subnav />

            <!-- Box Unduh Template Naskah -->
            <div class="p-5 rounded-xl bg-red-50/70 border border-red-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-brand-maroon text-white flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Template Resmi Naskah SIRIDA (Microsoft Word)</h3>
                        <p class="text-[11px] text-gray-500">Format siap pakai (.docx) berisi gaya penulisan, heading, dan layout gambar/tabel standar.</p>
                    </div>
                </div>

                <a href="{{ asset('files/template-sirida-makassar.docx') }}" download class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs font-bold transition shrink-0 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Template .DOCX</span>
                </a>
            </div>

            <!-- 1. Sistematika Struktur Naskah (IMRAD) -->
            <section class="space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Sistematika Penulisan Naskah (IMRAD Structure)</span>
                </h2>

                <div class="space-y-3 text-xs text-gray-600 leading-relaxed">
                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">1. Judul Artikel (Title)</strong>
                        <p class="text-[11px] text-gray-500">Lugas, spesifik, mencerminkan esensi temuan riset, maksimal 15 kata dalam Bahasa Indonesia atau Bahasa Inggris. Hindari akronim atau singkatan yang tidak umum.</p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">2. Abstrak & Kata Kunci (Abstract & Keywords)</strong>
                        <p class="text-[11px] text-gray-500">Ditulis dalam Bahasa Indonesia dan Bahasa Inggris, panjang 150–250 kata. Memuat ringkasan latar belakang, tujuan, metode empiris, hasil utama, serta implikasi praktis. Dilengkapi 3–5 kata kunci dipisahkan tanda koma.</p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">3. Pendahuluan (Introduction)</strong>
                        <p class="text-[11px] text-gray-500">Menguraikan urgensi isu pembangunan, kajian literatur terkini (state of the art), kesenjangan penelitian (research gap), hipotesis, dan kontribusi baru yang ditawarkan naskah.</p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">4. Metode Penelitian (Methods)</strong>
                        <p class="text-[11px] text-gray-500">Menjelaskan pendekatan penelitian, waktu dan lokasi studi (khususnya di wilayah Kota Makassar), teknik pengumpulan data, populasi/sampel, instrumen kalibrasi, serta tahapan analisis data empiris agar dapat direplikasi (reproducible).</p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">5. Hasil dan Pembahasan (Results and Discussion)</strong>
                        <p class="text-[11px] text-gray-500">Disajikan secara sistematis dengan dukungan tabel dan gambar informatif. Pembahasan tidak sekadar mengulang angka, melainkan menginterpretasikan temuan, menghubungkan dengan teori, dan mendiskusikan implikasi bagi kebijakan daerah.</p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">6. Kesimpulan (Conclusion)</strong>
                        <p class="text-[11px] text-gray-500">Merangkum jawaban atas tujuan penelitian secara ringkas tanpa mengutip angka mentah statistik. Berikan rekomendasi kebijakan konkret atau agenda riset lanjutan.</p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">7. Daftar Pustaka (References)</strong>
                        <p class="text-[11px] text-gray-500">Disusun menggunakan standar <strong>APA Style Edisi ke-7</strong>. Minimal memuat 20 rujukan pustaka, dengan proporsi 80% berasal dari artikel jurnal bereputasi yang terbit dalam 10 tahun terakhir. Penulis diwajibkan menggunakan aplikasi manajemen referensi (Mendeley, Zotero, atau EndNote).</p>
                    </div>
                </div>
            </section>

            <!-- 2. Ketentuan Format Dokumen -->
            <section class="space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    <span>Format Dokumen Teknis</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Format Berkas Naskah:</span>
                        <strong class="text-gray-900">Microsoft Word (.doc / .docx)</strong>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Panjang Naskah Total:</span>
                        <strong class="text-gray-900">5.000 – 8.000 kata (10–18 halaman)</strong>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Format Halaman & Margin:</span>
                        <strong class="text-gray-900">Ukuran A4, Margin 2.5 cm di setiap sisi</strong>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Resolusi Gambar & Ilustrasi:</span>
                        <strong class="text-gray-900">Minimal 300 DPI (Format JPEG / PNG)</strong>
                    </div>
                </div>
            </section>

            <!-- 3. Submission Checklist -->
            <section class="space-y-3 p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                <h3 class="font-bold text-gray-900 text-sm">Daftar Periksa Pengajuan Naskah (Submission Checklist)</h3>
                <p class="text-gray-600 text-[11px]">Pastikan butir-butir kelengkapan berikut telah terpenuhi sebelum mengunggah manuskrip:</p>

                <ul class="space-y-1.5 text-gray-700">
                    <li class="flex items-start gap-2">
                        <input type="checkbox" checked disabled class="mt-0.5 text-brand-maroon rounded">
                        <span>Naskah belum pernah dipublikasikan sebelumnya dan tidak sedang diajukan ke jurnal lain.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <input type="checkbox" checked disabled class="mt-0.5 text-brand-maroon rounded">
                        <span>Format berkas adalah OpenOffice, Microsoft Word, atau RTF.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <input type="checkbox" checked disabled class="mt-0.5 text-brand-maroon rounded">
                        <span>Telah mencantumkan URL atau DOI aktif pada seluruh referensi bibliografi yang tersedia.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <input type="checkbox" checked disabled class="mt-0.5 text-brand-maroon rounded">
                        <span>Telah menghapus identitas nama penulis dan afiliasi pada berkas telaah untuk mematuhi protokol Double-Blind Review.</span>
                    </li>
                </ul>
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
