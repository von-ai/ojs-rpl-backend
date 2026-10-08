@props(['stats' => null])

<div class="space-y-3">
    <!-- Header Row -->
    <div class="flex items-center justify-between">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
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
            </div>
        </div>

        <!-- 2. Diajukan -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Diajukan</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['submitted'] ?? 1) }}</span>
            </div>
        </div>

        <!-- 3. Dalam Telaah -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Dalam Telaah</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['under_review'] ?? 1) }}</span>
            </div>
        </div>

        <!-- 4. Perlu Revisi (Urgent Red Alert Card) -->
        <div class="bg-red-50/90 rounded-xl border border-red-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-brand-maroon font-bold">Perlu Revisi</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-brand-maroon">{{ sprintf('%02d', $stats['revision_required'] ?? 1) }}</span>
            </div>
        </div>

        <!-- 5. Diterima -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Diterima</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['accepted'] ?? 1) }}</span>
            </div>
        </div>

        <!-- 6. Diterbitkan -->
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 shadow-2xs flex flex-col justify-between space-y-2">
            <span class="text-[11px] text-gray-500 font-medium">Diterbitkan</span>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-gray-900">{{ sprintf('%02d', $stats['published'] ?? 2) }}</span>
            </div>
        </div>
    </div>
</div>
