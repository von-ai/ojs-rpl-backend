@extends('layouts.reviewer')

@section('title', 'Pedoman & Etika Telaah Sejawat — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Pedoman & Etika Telaah Sejawat
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Standar operasional baku Mitra Bestari Jurnal SIRIDA BRIDA Kota Makassar dalam penelaahan ilmiah ganda tertutup (double-blind peer review).
            </p>
        </div>

        <a 
            href="{{ route('reviewer.assignments.index') }}" 
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition shrink-0 self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Tugas Penelaahan</span>
        </a>
    </div>

    <!-- 3 Pilar Etika Penelaah Ilmiah -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Pilar 1 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200">
                    Prinsip 1
                </span>
                <span class="text-xs text-gray-400">Objektivitas Mutlak</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Kerahasiaan & Anonimitas</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Kerahasiaan Dokumen:</strong> Seluruh draf naskah yang ditelaah berstatus dokumen rahasia dan dilarang disebarluaskan atau digunakan untuk kepentingan pribadi.</p>
                <p><strong>Perlindungan Identitas:</strong> Dilarang berupaya mengidentifikasi penulis asli atau menyematkan identitas diri penelaah pada berkas catatan naskah.</p>
            </div>
        </div>

        <!-- Pilar 2 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    Prinsip 2
                </span>
                <span class="text-xs text-gray-400">Integritas Akademik</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Bebas Konflik Kepentingan</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Deklarasi Wajib:</strong> Penelaah wajib menolak undangan jika memiliki hubungan keluarga, relasi pembimbing-mahasiswa aktif, atau riset kolaboratif terkini dengan penulis.</p>
                <p><strong>Kompetisi Riset:</strong> Hindari menelaah naskah yang berada dalam kompetisi langsung dengan riset laboratorium atau proyek Anda yang sedang berjalan.</p>
            </div>
        </div>

        <!-- Pilar 3 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    Prinsip 3
                </span>
                <span class="text-xs text-gray-400">Komitmen Waktu</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Ketepatan Waktu & Mutu</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Tenggat Telaah:</strong> Selesaikan telaah sesuai batas waktu yang telah disepakati (standar: 14 hari kerja kalender).</p>
                <p><strong>Perpanjangan Waktu:</strong> Jika menghadapi kendala ilmiah yang mendesak, ajukan permohonan perpanjangan waktu melalui fitur sistem sebelum tenggat berakhir.</p>
            </div>
        </div>
    </div>

    <!-- Standar Rubrik Penilaian Saintifik -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-2xs">
        <div class="p-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-base font-bold text-gray-900">
                Standar Rubrik Evaluasi Ilmiah
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Panduan pemberian skor skala 1 hingga 5 pada formulir evaluasi rubrik telaah naskah.
            </p>
        </div>

        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700">Skor 1</span>
                        <span class="text-[10px] font-semibold text-rose-700 uppercase">Sangat Kurang</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-snug">Kelemahan fatal, tidak memenuhi standar minimal publikasi ilmiah bereputasi.</p>
                </div>

                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700">Skor 2</span>
                        <span class="text-[10px] font-semibold text-amber-700 uppercase">Kurang</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-snug">Terdapat defisiensi substantif pada metodologi atau analisis data yang memerlukan perombakan besar.</p>
                </div>

                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700">Skor 3</span>
                        <span class="text-[10px] font-semibold text-blue-700 uppercase">Cukup</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-snug">Naskah layak namun memerlukan perbaikan minor pada elaborasi temuan dan referensi pendukung.</p>
                </div>

                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700">Skor 4</span>
                        <span class="text-[10px] font-semibold text-emerald-700 uppercase">Baik</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-snug">Riset solid, metodologi tepat guna, dan temuan memberikan kontribusi nyata bagi bidang kajian.</p>
                </div>

                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-700">Skor 5</span>
                        <span class="text-[10px] font-semibold text-indigo-700 uppercase">Istimewa</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-snug">Orisinalitas sangat tinggi, temuan terobosan baru, dan penulisan terstruktur sangat sistematis.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Matriks Rekomendasi Editorial Mitra Bestari -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-2xs">
        <div class="p-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-base font-bold text-gray-900">
                Matriks Standar Rekomendasi Mitra Bestari ke Dewan Redaksi
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Kriteria ilmiah dalam menetapkan salah satu dari empat opsi rekomendasi akhir telaah sejawat.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-600 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Rekomendasi</th>
                        <th class="py-3.5 px-4 sm:px-6">Kondisi Hasil Telaah Sejawat</th>
                        <th class="py-3.5 px-4 sm:px-6">Tindak Lanjut Editorial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-emerald-800 whitespace-nowrap">Terima Naskah</td>
                        <td class="py-3.5 px-4 sm:px-6">Naskah telah memenuhi seluruh kriteria saintifik jurnal tanpa membutuhkan revisi lanjutan yang berarti.</td>
                        <td class="py-3.5 px-4 sm:px-6">Editor Bagian menyusun rekomendasi ke EIC untuk menerima naskah ke tahap copyediting.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-amber-800 whitespace-nowrap">Perlu Revisi Penulis</td>
                        <td class="py-3.5 px-4 sm:px-6">Perlu perbaikan minor seperti klarifikasi penjelasan, penambahan literatur mutakhir, atau penyesuaian format tabel/grafik.</td>
                        <td class="py-3.5 px-4 sm:px-6">Penulis diminta mengunggah naskah revisi dan matriks tanggapan penelaah.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-orange-800 whitespace-nowrap">Kirim Ulang untuk Telaah</td>
                        <td class="py-3.5 px-4 sm:px-6">Diperlukan perbaikan substantif pada metodologi atau pengujian data tambahan yang memerlukan penelaahan ulang oleh reviewer.</td>
                        <td class="py-3.5 px-4 sm:px-6">Naskah revisi akan dimasukkan ke putaran telaah baru untuk dinilai kembali.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-rose-800 whitespace-nowrap">Tolak Naskah</td>
                        <td class="py-3.5 px-4 sm:px-6">Kelemahan metodologi fatal, tidak orisinal, di luar fokus jurnal, atau mengandung indikasi pelanggaran etika saintifik.</td>
                        <td class="py-3.5 px-4 sm:px-6">Editor Bagian merekomendasikan penolakan naskah secara resmi kepada EIC.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
