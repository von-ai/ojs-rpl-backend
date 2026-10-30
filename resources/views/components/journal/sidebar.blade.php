@props([
    'publisherInfo' => null,
    'indexingData' => null,
    'impactMetrics' => null,
    'announcements' => [],
])

<div class="space-y-6">
    <!-- Panel 1: Informasi Penerbitan -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b border-gray-100 flex items-center justify-between">
            <span>INFORMASI PENERBITAN</span>
        </h3>
        <div class="space-y-2.5 text-xs"> 
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Inisial Jurnal</span>
                <span class="font-bold text-gray-900 font-mono">{{ $publisherInfo['journal_code'] ?? 'JRID-MKS' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Model Penelaahan</span>
                <span class="font-semibold text-gray-800">{{ $publisherInfo['peer_review_model'] ?? 'Double-blind Peer Review' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Frekuensi Terbit</span>
                <span class="font-semibold text-gray-800">{{ $publisherInfo['frequency'] ?? '2 Kali / Tahun (Juni & Des)' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Biaya Naskah (APC)</span>
                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">{{ $publisherInfo['apc_status'] ?? 'Gratis / Rp 0 (Subsidized)' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Penerbit</span>
                <span class="font-semibold text-gray-800">{{ $publisherInfo['publisher'] ?? 'BRIDA Kota Makassar' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Bahasa Naskah</span>
                <span class="font-semibold text-gray-800">{{ $publisherInfo['languages'] ?? 'Indonesia & Inggris' }}</span>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100">
            <a href="{{ $publisherInfo['ethics_policy_url'] ?? '#etika-publikasi' }}" class="text-xs font-bold text-brand-maroon hover:underline flex items-center justify-between">
                <span>Baca Etika Publikasi & Standar Integritas</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Panel 2: Pengindeksan Ilmiah (Grid 2-Column) -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">PENGINDEKSAN ILMIAH</h3>
            <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded border border-amber-300">
                {{ $indexingData['accreditation'] ?? 'AKREDITASI S2' }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-center text-xs">
            @if (!empty($indexingData['items']))
                @foreach ($indexingData['items'] as $item)
                    <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-200/70 hover:border-brand-maroon/30 transition-colors">
                        <span class="font-bold {{ $item['color'] ?? 'text-gray-900' }} block text-[11px]">{{ $item['name'] }}</span>
                        <span class="text-[10px] font-semibold text-gray-800 block">{{ $item['provider'] }}</span>
                        <span class="text-[9px] text-gray-400 block">{{ $item['desc'] }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Panel 3: Statistik & Dampak (Metrics Grid) -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">STATISTIK & DAMPAK</h3>
            <span class="text-[10px] text-gray-400 font-medium">Per Maret 2026</span>
        </div>
        <div class="grid grid-cols-2 gap-3 text-xs mb-3">
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200/70">
                <span class="text-[10px] text-gray-500 block">Total Sitasi</span>
                <span class="text-xl font-extrabold text-gray-900 block">{{ $impactMetrics['total_citations'] ?? 842 }}</span>
                <span class="text-[10px] font-semibold text-emerald-600 block">{{ $impactMetrics['citations_growth'] ?? '▲ 24% dari 2024' }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200/70">
                <span class="text-[10px] text-gray-500 block">h5-Index</span>
                <span class="text-xl font-extrabold text-gray-900 block">{{ $impactMetrics['h5_index'] ?? 14 }}</span>
                <span class="text-[10px] text-gray-400 block">h5-median: {{ $impactMetrics['h5_median'] ?? 19 }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200/70">
                <span class="text-[10px] text-gray-500 block">Unduhan PDF</span>
                <span class="text-xl font-extrabold text-gray-900 block">{{ $impactMetrics['pdf_downloads'] ?? '38.9k' }}</span>
                <span class="text-[10px] text-gray-400 block">Sejak Vol 1 (2023)</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200/70">
                <span class="text-[10px] text-gray-500 block">Tingkat Penerimaan</span>
                <span class="text-xl font-extrabold text-gray-900 block">{{ $impactMetrics['acceptance_rate'] ?? '28.4%' }}</span>
                <span class="text-[10px] text-gray-400 block">Seleksi Ketat Reviewer</span>
            </div>
        </div>

        <!-- Trend Bar Indicator -->
        <div class="pt-2 border-t border-gray-100">
            <div class="flex items-center justify-between text-[10px] text-gray-500 mb-1">
                <span>Tren Kunjungan & Unduhan Bulanan</span>
                <span class="font-bold text-gray-700">{{ $impactMetrics['monthly_views_avg'] ?? 'Rata-rata 3.2k/bln' }}</span>
            </div>
            <div class="flex items-end gap-1 h-8 bg-gray-50 p-1 rounded border border-gray-200/50">
                <div class="bg-red-200 h-2/5 flex-1 rounded-xs"></div>
                <div class="bg-red-300 h-3/5 flex-1 rounded-xs"></div>
                <div class="bg-brand-maroon h-full flex-1 rounded-xs"></div>
                <div class="bg-red-200 h-4/5 flex-1 rounded-xs"></div>
                <div class="bg-red-200 h-3/4 flex-1 rounded-xs"></div>
                <div class="bg-brand-maroon h-full flex-1 rounded-xs"></div>
            </div>
        </div>
    </div>

    <!-- Panel 4: Pengumuman Redaksi -->
    <div id="pengumuman" class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">PENGUMUMAN REDAKSI</h3>
            <a href="#" class="text-[10px] font-bold text-brand-maroon hover:underline">Semua</a>
        </div>
        <div class="space-y-3 text-xs divide-y divide-gray-100">
            @forelse ($announcements as $ann)
                <div class="pt-2.5 first:pt-0 space-y-1">
                    <span class="text-[10px] text-gray-400 font-medium block">{{ $ann['date'] }}</span>
                    <a href="{{ $ann['url'] ?? '#' }}" class="font-bold text-gray-800 hover:text-brand-maroon transition-colors line-clamp-2 block leading-snug">
                        {{ $ann['title'] }}
                    </a>
                </div>
            @empty
                <div class="text-xs text-gray-400 italic">Belum ada pengumuman redaksi.</div>
            @endforelse
        </div>
    </div>
</div>
