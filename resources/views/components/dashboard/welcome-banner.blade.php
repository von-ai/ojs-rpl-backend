@props(['userProfile' => null])

<div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-2xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
    <div class="space-y-2 max-w-3xl">
        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-gray-500">
            <span class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded border border-blue-200">
                {{ $userProfile['role_label'] ?? 'PORTAL PENULIS OJS 3' }}
            </span>
            <span class="text-gray-300">•</span>
            <span>{{ $userProfile['academic_year'] ?? 'Tahun Akademik 2026/2027' }}</span>
        </div>

        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
            Selamat Datang Kembali, {{ $userProfile['name'] ?? 'Dr. Andi Muhammad Ilyas, M.Si.' }}
        </h1>

        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
            Kelola naskah penelitian Anda, tanggapi tinjauan mitra bestari, dan pantau kemajuan publikasi artikel secara transparan sesuai standar integritas ilmiah BRIDA Kota Makassar.
        </p>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0 w-full md:w-auto">
        <a href="#" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-sm transition-all text-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Kirim Naskah Baru</span>
        </a>

        <a href="#" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg transition-all text-center">
            <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Template (.DOCX) & Panduan</span>
        </a>
    </div>
</div>
