@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Panduan Penulis — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
    <x-journal.page-banner
        eyebrow="Petunjuk Penyiapan Naskah"
        title="Panduan Penulis"
        :description="'Pedoman format, sistematika penulisan, dan tata cara pengajuan naskah ilmiah hasil riset ke ' . ($journal->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA) BRIDA Kota Makassar') . '.'"
        :crumbs="[['label' => 'Tentang Jurnal', 'url' => route('journal.about')], ['label' => 'Panduan Penulis']]"
    >
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-white hover:bg-gray-100 text-brand-maroon-hover font-bold text-sm transition cursor-pointer shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Kirim Naskah Baru</span>
        </a>
    </x-journal.page-banner>

    <section class="bg-white">
        <div class="max-w-5xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">
            <x-journal.about-subnav />

            <!-- Box Unduh Template Naskah -->
            @php
                $templateUrl = ! empty($journal?->slug)
                    ? route('journal.scoped.about.submissions.template', ['journalSlug' => $journal->slug])
                    : route('journal.about.submissions.template');
                $hasCustomTemplate = $journal && $journal->hasAuthorGuidelineTemplate();
            @endphp
            <div class="p-5 rounded-md bg-white border border-gray-200 border-l-4 border-l-brand-maroon shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <svg class="w-7 h-7 text-brand-maroon shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">
                            Template Resmi Naskah {{ $journal->initials ?? 'SIRIDA' }}
                        </h3>
                        <p class="text-xs text-gray-600 mt-0.5">
                            @if($hasCustomTemplate)
                                Berkas: <span class="font-semibold text-gray-800">{{ $journal->author_guideline_filename }}</span>
                                @if($journal->author_guideline_updated_at)
                                    <span class="text-gray-400">·</span> Diperbarui {{ $journal->author_guideline_updated_at->translatedFormat('d M Y') }}
                                @endif
                            @else
                                Format siap pakai berisi gaya penulisan, heading, dan layout gambar/tabel standar.
                            @endif
                        </p>
                    </div>
                </div>

                <a href="{{ $templateUrl }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs font-bold transition shrink-0 self-start sm:self-auto shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Unduh Template</span>
                </a>
            </div>

            <!-- 1. Sistematika Struktur Naskah (IMRAD) -->
            <section class="space-y-4">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Sistematika Penulisan Naskah</span>
                </h2>

                <div class="space-y-3 text-xs text-gray-600 leading-relaxed">
                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">1. Judul Artikel</strong>
                        <p class="text-[11px] text-gray-500">Lugas, spesifik, mencerminkan esensi temuan riset, maksimal 15 kata dalam Bahasa Indonesia atau Bahasa Inggris. Hindari akronim atau singkatan yang tidak umum.</p>
                    </div>

                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">2. Abstrak & Kata Kunci</strong>
                        <p class="text-[11px] text-gray-500">Ditulis dalam Bahasa Indonesia dan Bahasa Inggris, panjang 150–250 kata. Memuat ringkasan latar belakang, tujuan, metode empiris, hasil utama, serta implikasi praktis. Dilengkapi 3–5 kata kunci dipisahkan tanda koma.</p>
                    </div>

                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">3. Pendahuluan</strong>
                        <p class="text-[11px] text-gray-500">Menguraikan urgensi isu pembangunan, kajian literatur terkini, kesenjangan penelitian, hipotesis, dan kontribusi baru yang ditawarkan naskah.</p>
                    </div>

                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">4. Metode Penelitian</strong>
                        <p class="text-[11px] text-gray-500">Menjelaskan pendekatan penelitian, waktu dan lokasi studi (khususnya di wilayah Kota Makassar), teknik pengumpulan data, populasi/sampel, instrumen kalibrasi, serta tahapan analisis data empiris agar dapat direplikasi.</p>
                    </div>

                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">5. Hasil dan Pembahasan</strong>
                        <p class="text-[11px] text-gray-500">Disajikan secara sistematis dengan dukungan tabel dan gambar informatif. Pembahasan tidak sekadar mengulang angka, melainkan menginterpretasikan temuan, menghubungkan dengan teori, dan mendiskusikan implikasi bagi kebijakan daerah.</p>
                    </div>

                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">6. Kesimpulan</strong>
                        <p class="text-[11px] text-gray-500">Merangkum jawaban atas tujuan penelitian secara ringkas tanpa mengutip angka mentah statistik. Berikan rekomendasi kebijakan konkret atau agenda riset lanjutan.</p>
                    </div>

                    <div class="p-3.5 rounded border border-gray-100 bg-gray-50/70 space-y-1">
                        <strong class="text-gray-900 font-bold block">7. Daftar Pustaka</strong>
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
                    <div class="p-3 rounded border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Format Berkas Naskah:</span>
                        <strong class="text-gray-900">Microsoft Word (.doc / .docx)</strong>
                    </div>
                    <div class="p-3 rounded border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Panjang Naskah Total:</span>
                        <strong class="text-gray-900">5.000 – 8.000 kata (10–18 halaman)</strong>
                    </div>
                    <div class="p-3 rounded border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Format Halaman & Margin:</span>
                        <strong class="text-gray-900">Ukuran A4, Margin 2.5 cm di setiap sisi</strong>
                    </div>
                    <div class="p-3 rounded border border-gray-200">
                        <span class="text-gray-500 block text-[11px]">Resolusi Gambar & Ilustrasi:</span>
                        <strong class="text-gray-900">Minimal 300 DPI (Format JPEG / PNG)</strong>
                    </div>
                </div>
            </section>

            <!-- 3. Submission Checklist -->
            <section class="space-y-3 p-4 rounded-md bg-gray-50 border border-gray-200 text-xs">
                <h3 class="font-bold text-gray-900 text-sm">Daftar Periksa Pengajuan Naskah</h3>
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
    </section>
@endsection
