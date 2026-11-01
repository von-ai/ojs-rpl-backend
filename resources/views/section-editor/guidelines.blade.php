@extends('layouts.section-editor')

@section('title', 'Pedoman Telaah & Etika Penugasan — Portal Editor Bagian')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Pedoman Telaah Sejawat & Etika Penugasan Mitra Bestari
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Standar operasional baku Editor Bagian Jurnal SIRIDA BRIDA Kota Makassar dalam mengelola putaran telaah sejawat (peer review), penugasan penelaah independen, dan penyusunan rekomendasi editorial.
            </p>
        </div>

        <a 
            href="{{ route('section-editor.submissions.index') }}" 
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition shrink-0 self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Antrean Telaah</span>
        </a>
    </div>

    <!-- 3 Pilar Kewenangan Editor Bagian -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Tahap 1 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    Tahap 1
                </span>
                <span class="text-xs text-gray-400 tabular-nums">Bebas Konflik Kepentingan</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Seleksi & Undangan Mitra Bestari</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Kesesuaian Kepakaran:</strong> Pilih mitra bestari yang memiliki publikasi dan rekam jejak riset relevan dengan sub-topik naskah.</p>
                <p><strong>Pencegahan Konflik Kepentingan:</strong> Penulis naskah dilarang keras menelaah naskahnya sendiri atau naskah dari rekan satu institusi/laboratorium.</p>
                <p><strong>Anonimitas Dua Arah:</strong> Pastikan berkas naskah yang ditelaah adalah versi anonim (double-blind) tanpa identitas penulis.</p>
            </div>
        </div>

        <!-- Tahap 2 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-sky-50 text-sky-800 border border-sky-200">
                    Tahap 2
                </span>
                <span class="text-xs text-gray-400 tabular-nums">Minimal 2 Penelaah</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Pemantauan & Kuorum Evaluasi</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Kuorum Evaluasi:</strong> Setiap putaran telaah mewajibkan minimal <strong>2 laporan evaluasi selesai</strong> sebelum rekomendasi dapat disusun.</p>
                <p><strong>Pengingat Batas Waktu:</strong> Kirimkan surat pengingat resmi kepada penelaah yang mendekati tenggat atau telah melewati batas waktu.</p>
                <p><strong>Permohonan Perpanjangan:</strong> Evaluasi alasan permohonan perpanjangan waktu secara obyektif demi menjaga kedalaman telaah ilmiah.</p>
            </div>
        </div>

        <!-- Tahap 3 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-red-50 text-brand-maroon border border-red-200">
                    Tahap 3
                </span>
                <span class="text-xs text-gray-400">Rekomendasi Resmi</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Sintesis & Rekomendasi ke EIC</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Sintesis Independen:</strong> Rangkum poin-poin konsistensi maupun divergensi antara masukan kedua penelaah secara objektif.</p>
                <p><strong>Catatan Substantif:</strong> Berikan pertimbangan akademis yang jelas dan instruksi perbaikan yang dapat dioperasionalkan oleh penulis.</p>
                <p><strong>Keputusan Final di Tangan EIC:</strong> Rekomendasi Section Editor menjadi dasar bagi Pemimpin Redaksi untuk menerbitkan keputusan final.</p>
            </div>
        </div>
    </div>

    <!-- Matriks Rekomendasi Editor Bagian -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-sm font-bold text-gray-900">Matriks Standar Rekomendasi Editorial ke Pemimpin Redaksi</h2>
            <p class="text-xs text-gray-500 mt-0.5">Panduan penentuan jenis rekomendasi berdasarkan sintesis hasil evaluasi mitra bestari.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 divide-y divide-gray-200">
                <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-600 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Rekomendasi</th>
                        <th class="py-3.5 px-4 sm:px-6">Kondisi Hasil Telaah Sejawat</th>
                        <th class="py-3.5 px-4 sm:px-6">Tindak Lanjut Alur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-emerald-800 whitespace-nowrap">Terima Naskah</td>
                        <td class="py-3.5 px-4 sm:px-6">Mayoritas penelaah merekomendasikan penerimaan naskah atau catatan minor telah terakomodasi dengan sempurna.</td>
                        <td class="py-3.5 px-4 sm:px-6">EIC menerbitkan surat penerimaan naskah dan melanjutkannya ke tahap penyuntingan teks (copyediting).</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-amber-800 whitespace-nowrap">Perlu Revisi Penulis</td>
                        <td class="py-3.5 px-4 sm:px-6">Perlu perbaikan metodologi, data empiris, atau analisis yang dapat diselesaikan penulis dalam rentang waktu 14-21 hari.</td>
                        <td class="py-3.5 px-4 sm:px-6">Penulis mengunggah naskah revisi dan surat tanggapan untuk dievaluasi pada putaran berjalan.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-orange-800 whitespace-nowrap">Kirim Ulang untuk Telaah</td>
                        <td class="py-3.5 px-4 sm:px-6">Perombakan naskah secara mendasar (major revisions) yang mewajibkan penilaian ulang oleh penelaah pada putaran telaah baru.</td>
                        <td class="py-3.5 px-4 sm:px-6">Setelah revisi diunggah, Editor Bagian membuka Putaran Telaah berikutnya (putaran 2).</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-rose-800 whitespace-nowrap">Tolak Naskah</td>
                        <td class="py-3.5 px-4 sm:px-6">Kelemahan metodologi fatal, tidak orisinal, di luar fokus jurnal, atau seluruh penelaah merekomendasikan penolakan.</td>
                        <td class="py-3.5 px-4 sm:px-6">EIC menerbitkan surat penolakan resmi dan alur naskah dihentikan permanen.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
