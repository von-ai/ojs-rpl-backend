@extends('layouts.layouteditor')

@section('title', 'Panduan Tata Letak & Template Galai — Portal Layout Editor')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                <a href="{{ route('layouteditor.submissions.index') }}" class="hover:text-brand-maroon transition">Layout Editor</a>
                <span>/</span>
                <span class="text-gray-900">Panduan Galai</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Panduan Tata Letak & Standar Format Galai
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Spesifikasi teknis tata letak berkas publikasi PDF 2-kolom, penomoran halaman, running header, dan struktur DOI resmi BRIDA Kota Makassar.
            </p>
        </div>

        <div class="shrink-0">
            <a
                href="{{ asset('docs/panduan-penulisan-jurnal-brida.pdf') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs font-semibold rounded-xl shadow-2xs transition"
                title="Unduh berkas template tata letak resmi"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Panduan Format (PDF) (.docx/.indt)</span>
            </a>
        </div>
    </div>

    <!-- Isi Panduan Terstruktur -->
    <div class="bg-white rounded-2xl border border-gray-200/90 p-6 sm:p-8 shadow-2xs space-y-8 text-sm text-gray-700 leading-relaxed">

        <!-- Poin 1: Format Geometri & Grid Halaman -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    1
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Geometri Halaman & Grid Dua Kolom
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Ukuran Kertas:</span>
                    <span class="text-gray-600">A4 Standard (210 mm x 297 mm)</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Batas Margin:</span>
                    <span class="text-gray-600">Atas: 2.5 cm, Bawah: 2.5 cm, Kiri: 2.5 cm, Kanan: 2.0 cm</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Format Kolom:</span>
                    <span class="text-gray-600">2 Kolom (Lebar 8.0 cm, Jarak Antar-kolom / Gutter: 0.8 cm)</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Judul & Abstrak:</span>
                    <span class="text-gray-600">1 Kolom Penuh (*Full-width spanning*) pada halaman pertama</span>
                </div>
            </div>
        </section>

        <!-- Poin 2: Running Header & Footer -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Header Berjalan (*Running Header*) & Penomoran DOI
                </h2>
            </div>

            <ul class="text-xs sm:text-sm space-y-2 list-disc list-inside text-gray-600 pl-1">
                <li><strong>Halaman Pertama:</strong> Memuat nama jurnal lengkap, volume, nomor terbitan, bulan/tahun, dan tautan resmi DOI Crossref di sudut kanan atas.</li>
                <li><strong>Halaman Genap:</strong> Running header memuat nama penulis pertama et al. (contoh: <em>Bahar et al. / Jurnal Riset Daerah</em>).</li>
                <li><strong>Halaman Ganjil:</strong> Running header memuat judul singkat naskah (*running title*, maksimal 50 karakter).</li>
                <li><strong>Footer:</strong> Nomor halaman diletakkan di sudut luar (*outer margin*).</li>
            </ul>
        </section>

        <!-- Poin 3: Ketentuan Ekspor PDF Publikasi -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    3
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Standar Ekspor Berkas Galai PDF
                </h2>
            </div>

            <ul class="text-xs sm:text-sm space-y-2 list-disc list-inside text-gray-600 pl-1">
                <li>Gunakan preset PDF/A-1b atau PDF/X-1a untuk memastikan kompatibilitas arsip digital jangka panjang.</li>
                <li>Seluruh fon wajib disematkan (*fully embedded fonts*).</li>
                <li>Kompresi gambar menggunakan metode bicubic downsampling ke 300 DPI (warna CMYK atau RGB standar sRGB).</li>
                <li>Ukuran berkas final galai PDF disarankan berada di bawah <strong>10 MiB</strong> per artikel untuk kecepatan unduh pembaca.</li>
            </ul>
        </section>

    </div>

</div>
@endsection
