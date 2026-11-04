@extends('layouts.copyeditor')

@section('title', 'Panduan Gaya Selingkung — Portal Copy Editor')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                <a href="{{ route('copyeditor.submissions.index') }}" class="hover:text-brand-maroon transition">Copy Editor</a>
                <span>/</span>
                <span class="text-gray-900">Panduan Selingkung</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Panduan Gaya Selingkung & Format Jurnal
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Standar tata bahasa, tipografi, penulisan sitasi, dan tata letak artikel ilmiah BRIDA Kota Makassar.
            </p>
        </div>

        <!-- Tombol Unduh Template Word -->
        <div class="shrink-0">
            <a
                href="{{ asset('docs/panduan-penulisan-jurnal-brida.pdf') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs font-semibold rounded-xl shadow-2xs transition"
                title="Unduh berkas template Word resmi jurnal BRIDA"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Template Word (.docx)</span>
            </a>
        </div>
    </div>

    <!-- Isi Panduan Terstruktur -->
    <div class="bg-white rounded-2xl border border-gray-200/90 p-6 sm:p-8 shadow-2xs space-y-8 text-sm text-gray-700 leading-relaxed">

        <!-- Poin 1: Tata Bahasa & Ejaan -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    1
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Ejaan & Tata Bahasa (EYD Edisi V)
                </h2>
            </div>

            <ul class="text-xs sm:text-sm space-y-2 list-disc list-inside text-gray-600 pl-1">
                <li>Naskah wajib ditulis dalam bahasa Indonesia baku sesuai Pedoman Umum Ejaan Bahasa Indonesia (EYD V) atau bahasa Inggris baku (*academic English*).</li>
                <li>Seluruh istilah asing, istilah daerah, atau taksonomi biologi wajib dicetak miring (<em>italic</em>).</li>
                <li>Penggunaan akronim atau singkatan harus diawali kepanjangannya pada pemunculan pertama dalam teks naskah.</li>
                <li>Hindari kalimat pasif bertingkat berlebihan; gunakan kalimat efektif dengan struktur Subjek-Predikat-Objek-Keterangan (SPOK) yang lugas.</li>
            </ul>
        </section>

        <!-- Poin 2: Tipografi & Tata Letak -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Tipografi & Format Halaman
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Huruf Utama:</span>
                    <span class="text-gray-600">Times New Roman 11pt (atau Georgia 10.5pt)</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Spasi & Paragraf:</span>
                    <span class="text-gray-600">Spasi 1.15, After 6pt, First Line Indent 1 cm</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Judul Naskah:</span>
                    <span class="text-gray-600">14pt Bold, Center, Maksimal 16 kata</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Abstrak & Kata Kunci:</span>
                    <span class="text-gray-600">10pt Italic, 150–250 kata, 3–5 kata kunci</span>
                </div>
            </div>
        </section>

        <!-- Poin 3: Sitasi & Daftar Pustaka (APA 7th) -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    3
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Format Sitasi & Daftar Pustaka (APA Style 7th Edition)
                </h2>
            </div>

            <p class="text-xs sm:text-sm text-gray-600">
                Semua rujukan yang dikutip dalam naskah wajib tercantum dalam Daftar Pustaka, dan sebaliknya. Penulisan menggunakan format <strong>American Psychological Association (APA) 7th Edition</strong>. Rujukan primer (jurnal/prosiding) minimal 80% dari terbitan 10 tahun terakhir. Wajib mencantumkan tautan DOI.
            </p>

            <div class="space-y-2 text-xs">
                <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 font-mono text-gray-800">
                    <span class="text-brand-maroon font-bold">Artikel Jurnal:</span><br>
                    Bahar, N., & Hamka, H. (2025). Analisis spasial ruang terbuka hijau terhadap urban heat island. <em>Jurnal Riset Daerah</em>, 12(2), 45–58. https://doi.org/10.1234/jrd.v12i2.106
                </div>
                <div class="p-3 rounded-lg bg-gray-50 border border-gray-200 font-mono text-gray-800">
                    <span class="text-brand-maroon font-bold">Buku Teks:</span><br>
                    Syarif, F. E. (2024). <em>Geoteknik Pesisir dan Rekayasa Mitigasi Erosi</em> (Edisi ke-2). BRIDA Press.
                </div>
            </div>
        </section>

        <!-- Poin 4: Tabel & Gambar -->
        <section class="space-y-3">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    4
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Ketentuan Penyajian Tabel & Gambar
                </h2>
            </div>

            <ul class="text-xs sm:text-sm space-y-2 list-disc list-inside text-gray-600 pl-1">
                <li>Tabel hanya menggunakan 3 garis horizontal utama (garis atas kolom, garis bawah kepala kolom, dan garis penutup bawah tabel). Garis vertikal <strong>tidak digunakan</strong>.</li>
                <li>Judul tabel diletakkan di <strong>atas</strong> tabel (10pt Bold, rata kiri).</li>
                <li>Gambar dan bagan wajib memiliki resolusi tinggi (minimal 300 DPI, format .PNG atau .JPEG tajam). Judul gambar diletakkan di <strong>bawah</strong> gambar.</li>
                <li>Setiap gambar dan tabel harus dirujuk secara eksplisit dalam teks naskah (contoh: "...sebagaimana disajikan pada Tabel 2").</li>
            </ul>
        </section>

    </div>

</div>
@endsection
