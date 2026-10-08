@props(['stats' => null])

<div class="space-y-3">
    <!-- Header Row -->
    <div class="flex items-center justify-between">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>RINGKASAN ALUR SUBMISI NASKAH</span>
        </h2>
        <span class="text-xs text-gray-500 font-medium">
            Total Terdaftar: <strong class="text-gray-900 font-bold">{{ $stats['total_registered'] ?? 7 }} Naskah</strong>
        </span>
    </div>

    <!-- Stat Cards 6-Column Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- 1. Draf Tersimpan -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Draf Tersimpan</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['draft'] ?? 1) }}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
        </div>

        <!-- 2. Diajukan (Submitted) -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Diajukan (Submitted)</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['submitted'] ?? 1) }}</span>
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
        </div>

        <!-- 3. Dalam Telaah -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Dalam Telaah</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['under_review'] ?? 1) }}</span>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- 4. Perlu Revisi (Urgent Red Alert Card) -->
        <div class="bg-red-50/90 rounded-xl border border-red-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <div class="flex items-center gap-1.5 text-brand-maroon font-bold text-[11px]">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                <span>Perlu Revisi</span>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-brand-maroon">{{ sprintf('%02d', $stats['revision_required'] ?? 1) }}</span>
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-brand-maroon text-white font-bold text-xs">!</span>
            </div>
        </div>

        <!-- 5. Diterima (Accepted) -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Diterima (Accepted)</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['accepted'] ?? 1) }}</span>
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- 6. Diterbitkan -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Diterbitkan</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['published'] ?? 2) }}</span>
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>
    </div>
</div>
