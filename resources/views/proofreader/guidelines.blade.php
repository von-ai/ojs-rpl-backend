@extends('layouts.proofreader')

@section('title', 'Panduan Bukti Baca & Standar Koreksi — Portal Proofreader')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                <a href="{{ route('proofreader.submissions.index') }}" class="hover:text-brand-maroon transition">Proofreader</a>
                <span>/</span>
                <span class="text-gray-900">Panduan Bukti Baca</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-serif">
                Panduan Bukti Baca &amp; Standar Koreksi Galai
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Pedoman komprehensif pemeriksaan tipografi, kepatuhan ejaan EYD V, integritas tata letak, dan verifikasi sitasi jurnal BRIDA Kota Makassar.
            </p>
        </div>

        <div class="shrink-0">
            <a 
                href="{{ route('proofreader.submissions.index') }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 min-h-11 bg-brand-maroon hover:bg-brand-maroon-dark text-white text-xs font-bold rounded-xl shadow-2xs transition"
                title="Kembali ke Daftar Penugasan Proofreading"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Lihat Penugasan Proofread</span>
            </a>
        </div>
    </div>

    <!-- Konten Utama Panduan -->
    <div class="bg-white rounded-2xl border border-gray-200/90 p-6 sm:p-8 shadow-2xs space-y-8 text-sm text-gray-700 leading-relaxed">

        <!-- 1. Taksonomi Kategori Kesalahan -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    1
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Klasifikasi Kategori Catatan Koreksi
                </h2>
            </div>
            <p class="text-xs text-gray-600">
                Setiap catatan koreksi yang diberikan kepada Layout Editor harus dikelompokkan ke dalam salah satu dari empat kategori baku sistem:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                <!-- Typo -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">
                            Tipografi / Ejaan (Typo)
                        </span>
                    </div>
                    <p class="text-xs text-gray-600">
                        Kesalahan ejaan kata, pemenggalan suku kata pada ujung baris (hyphenation), spasi ganda, tanda baca yang keliru, dan kata serapan yang belum sesuai standar KBBI.
                    </p>
                </div>

                <!-- Layout -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                            Tata Letak / Format (Layout)
                        </span>
                    </div>
                    <p class="text-xs text-gray-600">
                        Penempatan gambar atau tabel yang menabrak batas margin, kolom naskah yang tidak simetris, keterangan gambar terpotong, atau baris yatim/piatu (orphan/widow lines).
                    </p>
                </div>

                <!-- Metadata -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-800">
                            Metadata &amp; Penulis
                        </span>
                    </div>
                    <p class="text-xs text-gray-600">
                        Ketidaksesuaian penulisan nama penulis, gelar akademis, urutan korespondensi, afiliasi lembaga, nomor DOI, running header, atau penomoran halaman awal-akhir.
                    </p>
                </div>

                <!-- Reference -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800">
                            Sitasi &amp; Daftar Pustaka
                        </span>
                    </div>
                    <p class="text-xs text-gray-600">
                        Sitasi dalam tubuh tulisan tidak ditemukan pada Daftar Pustaka, perbedaan ejaan nama pengarang atau tahun penerbitan, serta format rujukan APA 7th Edition yang tidak konsisten.
                    </p>
                </div>
            </div>
        </section>

        <!-- 2. Tingkat Keparahan & Guard Rail Sign-off -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Tingkat Keparahan (Severity) &amp; Guard Rails
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Minor -->
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-blue-900 text-sm">Minor</span>
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded">Rendah</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Koreksi kata tunggal, huruf kapital, atau pemiringan kata yang tidak mempengaruhi tata letak kolom secara keseluruhan.
                    </p>
                </div>

                <!-- Major -->
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-900 text-sm">Major</span>
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded">Sedang</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Pergeseran gambar atau tabel, penataan ulang kolom, atau koreksi formula matematika yang memerlukan ekspor ulang berkas PDF oleh Layout Editor.
                    </p>
                </div>

                <!-- Kritis -->
                <div class="p-4 rounded-xl border border-red-200 bg-red-50/30 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-red-900 text-sm">Kritis</span>
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-100 text-red-800 rounded">Blokir Sign-off</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        Teks substansial hilang, data tabel tertukar, atau sitasi krusial tidak ada. <strong>Sistem secara otomatis mengunci tombol sign-off</strong> sampai catatan ini selesai diverifikasi.
                    </p>
                </div>
            </div>
        </section>

        <!-- 3. Checklist Verifikasi Bukti Baca (5 Poin Kunci) -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    3
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Checklist Pemeriksaan Berkas Galai PDF
                </h2>
            </div>

            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200/80">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        ✓
                    </div>
                    <div>
                        <strong class="text-xs sm:text-sm text-gray-900 block">1. Header, Footer &amp; Running Head</strong>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Pastikan nama jurnal, volume, nomor terbitan, bulan-tahun, serta rentang nomor halaman (misal: 105–118) tercantum dengan presisi di setiap halaman.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200/80">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        ✓
                    </div>
                    <div>
                        <strong class="text-xs sm:text-sm text-gray-900 block">2. Integritas Teks terhadap Naskah Acuan Copyediting</strong>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Bandingkan terhadap berkas <code>.docx</code> copyediting final untuk memastikan tidak ada paragraf, rumus, atau catatan kaki yang terpotong saat penataan letak galai.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200/80">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        ✓
                    </div>
                    <div>
                        <strong class="text-xs sm:text-sm text-gray-900 block">3. Gambar, Bagan &amp; Tabel</strong>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Periksa ketajaman gambar grafik (minimal 300 DPI), penomoran urut (Gambar 1, 2, ...), posisi caption di bawah gambar, dan judul tabel di atas tabel.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200/80">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        ✓
                    </div>
                    <div>
                        <strong class="text-xs sm:text-sm text-gray-900 block">4. Persamaan Matematika &amp; Notasi Ilmiah</strong>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Verifikasi simbol Yunani, superskrip, dan subskrip agar tidak mengalami rendering error (seperti karakter kotak atau tanda tanya).
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200/80">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        ✓
                    </div>
                    <div>
                        <strong class="text-xs sm:text-sm text-gray-900 block">5. Korespondensi Sitasi 1:1</strong>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Setiap sitasi yang muncul di dalam naskah wajib memiliki entri pustaka yang lengkap dan sesuai abjad di Daftar Pustaka.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Ringkasan Pedoman Ejaan Bahasa Indonesia (EYD V) -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    4
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Prinsip Ringkas Ejaan Bahasa Indonesia (EYD V)
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Huruf Miring (Italic):</span>
                    <span class="text-gray-600">Digunakan untuk menuliskan istilah bahasa asing atau bahasa daerah yang belum diserap, serta nama ilmiah organisme (misal: <em>Oryza sativa</em>, <em>smart governance</em>).</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Kata Depan vs Awalan:</span>
                    <span class="text-gray-600">Kata depan <em>di</em> (tempat/arah) ditulis terpisah (misal: <em>di Makassar</em>), sedangkan awalan <em>di-</em> (kata kerja pasif) ditulis serangkai (misal: <em>dianalisis</em>).</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Penulisan Angka &amp; Bilangan:</span>
                    <span class="text-gray-600">Bilangan yang dapat dinyatakan dengan satu atau dua kata ditulis huruf (misal: <em>tiga sampel</em>), kecuali jika dipakai secara berurutan atau menunjukkan ukuran matematis.</span>
                </div>
                <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200/80 space-y-1">
                    <span class="font-bold text-gray-900 block">Huruf Kapital pada Gelar:</span>
                    <span class="text-gray-600">Huruf kapital dipakai sebagai huruf pertama unsur gelar kehormatan, keturunan, keagamaan, atau akademik yang diikuti nama orang.</span>
                </div>
            </div>
        </section>

        <!-- 5. Prosedur Eskalasi Redaksi -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-3">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    5
                </div>
                <h2 class="text-base font-bold text-gray-900">
                    Prosedur Eskalasi ke Section Editor
                </h2>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Jika ditemukan ketidaksesuaian berat yang tidak dapat diselesaikan oleh Layout Editor (misalnya pemotongan teks secara sepihak yang merusak argumen riset, atau kontradiksi antara berkas naskah dan data OJS), Proofreader harus menerbitkan <strong>Tiket Eskalasi Redaksi</strong> agar Section Editor dapat memediasi klarifikasi kepada Penulis sebelum galai disetujui.
            </p>
        </section>

    </div>

</div>
@endsection
