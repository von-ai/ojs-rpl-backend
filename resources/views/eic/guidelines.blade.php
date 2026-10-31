@extends('layouts.eic')

@section('title', 'Pedoman Keputusan Redaksi — Portal Pemimpin Redaksi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
                Pedoman Keputusan Redaksi & Etika Publikasi Ilmiah
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Standar operasional baku Pemimpin Redaksi Jurnal SIRIDA BRIDA Kota Makassar dalam menjalankan skrining awal, pendelegasian telaah, dan keputusan penerbitan.
            </p>
        </div>

        <a 
            href="{{ route('eic.submissions.index') }}" 
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition shrink-0 self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Antrean Editorial</span>
        </a>
    </div>

    <!-- 3 Pilar Kewenangan Pemimpin Redaksi -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Tahap 1 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    Tahap 1
                </span>
                <span class="text-xs text-gray-400 tabular-nums">Target 3 Hari Kerja</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Skrining Awal Naskah</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Kesesuaian Fokus & Ruang Lingkup:</strong> Naskah wajib membahas riset dan inovasi kebijakan publik, lingkungan, atau pembangunan daerah.</p>
                <p><strong>Ambang Indeks Kemiripan:</strong> Maksimal <strong>20%</strong> di luar daftar pustaka dan kutipan langsung. Skor melebihi 35% ditolak pada tahap awal.</p>
                <p><strong>Anonimitas Naskah:</strong> Pastikan berkas utama bebas dari identitas penulis sebelum diteruskan kepada editor bagian.</p>
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
            <h2 class="text-sm font-bold text-gray-900">Delegasi Editor Bagian & Telaah</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Penunjukan Editor Bagian:</strong> Pilih editor yang sesuai dengan rumpun keilmuan naskah dan bebas dari konflik kepentingan afiliasi.</p>
                <p><strong>Kuorum Evaluasi:</strong> Setiap putaran telaah mewajibkan minimal <strong>2 laporan mitra bestari selesai</strong> dengan penilaian skala 1 sampai 5.</p>
                <p><strong>Rekomendasi Editor Bagian:</strong> Editor bagian menyusun sintesis hasil telaah sebagai bahan pertimbangan Pemimpin Redaksi.</p>
            </div>
        </div>

        <!-- Tahap 3 -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-red-50 text-brand-maroon border border-red-200">
                    Tahap 3
                </span>
                <span class="text-xs text-gray-400">Rekaman Permanen</span>
            </div>
            <h2 class="text-sm font-bold text-gray-900">Keputusan Editorial Resmi</h2>
            <div class="text-xs text-gray-600 space-y-2 leading-relaxed text-left">
                <p><strong>Integritas Keputusan:</strong> Setiap surat keputusan yang telah diterbitkan tercatat secara permanen pada riwayat audit naskah.</p>
                <p><strong>Kewajiban Tenggat Revisi:</strong> Keputusan permintaan revisi wajib menetapkan tanggal batas pengumpulan revisi antara 14 hingga 30 hari.</p>
                <p><strong>Kerahasiaan Catatan:</strong> Catatan internal dewan redaksi dan komentar rahasia penelaah tidak pernah ditampilkan kepada penulis.</p>
            </div>
        </div>
    </div>

    <!-- Matriks Keputusan Editorial -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-sm font-bold text-gray-900">Matriks Standar Pengambilan Keputusan Editorial Akhir</h2>
            <p class="text-xs text-gray-500 mt-0.5">Panduan penentuan status naskah berdasarkan kombinasi hasil evaluasi mitra bestari.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700 divide-y divide-gray-200">
                <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-600 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Jenis Keputusan</th>
                        <th class="py-3.5 px-4 sm:px-6">Kondisi Hasil Telaah</th>
                        <th class="py-3.5 px-4 sm:px-6">Status Naskah Baru</th>
                        <th class="py-3.5 px-4 sm:px-6">Tindak Lanjut Alur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-emerald-800 whitespace-nowrap">Terima Naskah</td>
                        <td class="py-3.5 px-4 sm:px-6">Kedua penelaah merekomendasikan penerimaan atau revisi minor telah dipenuhi dengan baik.</td>
                        <td class="py-3.5 px-4 sm:px-6 font-semibold text-emerald-700">Diterima</td>
                        <td class="py-3.5 px-4 sm:px-6">Masuk ke antrean penyuntingan naskah dan pembuatan galai terbitan.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-amber-800 whitespace-nowrap">Perlu Revisi Penulis</td>
                        <td class="py-3.5 px-4 sm:px-6">Perlu perbaikan substansi atau metodologi yang dapat diselesaikan dalam 14 hari tanpa putaran telaah baru.</td>
                        <td class="py-3.5 px-4 sm:px-6 font-semibold text-amber-700">Perlu Revisi</td>
                        <td class="py-3.5 px-4 sm:px-6">Penulis mengunggah naskah perbaikan beserta surat tanggapan penulis.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-orange-800 whitespace-nowrap">Kirim Ulang untuk Telaah</td>
                        <td class="py-3.5 px-4 sm:px-6">Perombakan besar yang wajib dinilai ulang oleh mitra bestari pada putaran telaah berikutnya.</td>
                        <td class="py-3.5 px-4 sm:px-6 font-semibold text-orange-700">Perlu Revisi</td>
                        <td class="py-3.5 px-4 sm:px-6">Setelah penulis mengunggah revisi, editor bagian membuka putaran telaah baru.</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-4 sm:px-6 font-bold text-rose-800 whitespace-nowrap">Tolak Naskah</td>
                        <td class="py-3.5 px-4 sm:px-6">Kelemahan metodologi mendasar, di luar ruang lingkup jurnal, indeks kemiripan melebihi 20%, atau mayoritas penelaah menolak.</td>
                        <td class="py-3.5 px-4 sm:px-6 font-semibold text-rose-700">Ditolak</td>
                        <td class="py-3.5 px-4 sm:px-6">Alur naskah dihentikan secara permanen.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
