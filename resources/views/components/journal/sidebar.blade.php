@props([
    'publisherInfo' => null,
    'indexingData' => null,
    'impactMetrics' => null,
    'announcements' => [],
])

<div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-1 sm:gap-4 lg:space-y-5 lg:gap-0">
    <!-- Panel 1: Informasi Penerbitan -->
    <div class="bg-white rounded-lg sm:rounded-xl border border-outline-variant p-4 sm:p-5 space-y-4">
        <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-normal pb-2 border-b border-divider flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <span>Informasi Penerbitan</span>
        </h3>

        <dl class="space-y-2.5 text-xs divide-y divide-divider">
            <div class="pt-1 first:pt-0 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-medium text-gray-500 uppercase tracking-normal shrink-0">Inisial</dt>
                <dd class="font-medium text-gray-900 text-right">{{ $publisherInfo['journal_code'] ?? 'SIRIDA' }}</dd>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-medium text-gray-500 uppercase tracking-normal shrink-0">Penelaahan</dt>
                <dd class="font-medium text-gray-800 text-right truncate">{{ \Illuminate\Support\Str::limit($publisherInfo['peer_review_model'] ?? 'Double-Blind Peer Review', 34) }}</dd>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-medium text-gray-500 uppercase tracking-normal shrink-0">Frekuensi</dt>
                <dd class="font-medium text-gray-800 text-right truncate">{{ \Illuminate\Support\Str::limit($publisherInfo['frequency'] ?? '2 Kali / Tahun', 34) }}</dd>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-medium text-gray-500 uppercase tracking-normal shrink-0">Biaya APC</dt>
                <dd class="font-medium text-emerald-800 text-right truncate">{{ \Illuminate\Support\Str::limit($publisherInfo['apc_status'] ?? 'Rp 0', 34) }}</dd>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-medium text-gray-500 uppercase tracking-normal shrink-0">Penerbit</dt>
                <dd class="font-medium text-gray-800 text-right truncate">{{ $publisherInfo['publisher'] ?? 'BRIDA Kota Makassar' }}</dd>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-medium text-gray-500 uppercase tracking-normal shrink-0">Bahasa</dt>
                <dd class="font-medium text-gray-800 text-right">{{ $publisherInfo['languages'] ?? 'Indonesia & Inggris' }}</dd>
            </div>
        </dl>

        <div class="pt-2.5 border-t border-divider">
            <a href="{{ $publisherInfo['ethics_policy_url'] ?? route('journal.about.policies') }}" class="text-xs font-medium text-brand-maroon hover:underline flex items-center justify-between group">
                <span>Etika Publikasi & Kebijakan</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Panel 2: Pengindeksan Ilmiah -->
    <div class="bg-white rounded-lg sm:rounded-xl border border-outline-variant p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-divider">
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-normal">Pengindeksan</h3>
            <span class="text-[10px] font-medium text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                {{ $indexingData['accreditation'] ?? 'AKREDITASI S2' }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-center text-xs">
            @if (!empty($indexingData['items']))
                @foreach ($indexingData['items'] as $item)
                    <div class="bg-gray-50 p-2 rounded border border-outline-variant hover:border-brand-maroon/30 transition-colors">
                        <span class="font-medium {{ $item['color'] ?? 'text-gray-900' }} block text-xs">{{ $item['name'] }}</span>
                        <span class="text-[10px] text-gray-500 block">{{ $item['provider'] }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Panel 3: Statistik Portal Jurnal -->
    <div class="bg-white rounded-lg sm:rounded-xl border border-outline-variant p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-divider">
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-normal flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Statistik</span>
            </h3>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-gray-50 p-2.5 rounded border border-outline-variant text-center">
                <span class="text-[10px] font-medium text-gray-500 uppercase tracking-normal block">Artikel</span>
                <span class="text-lg font-semibold text-gray-900 block mt-0.5 tabular-nums">{{ $impactMetrics['total_articles'] ?? 0 }}</span>
            </div>
            <div class="bg-gray-50 p-2.5 rounded border border-outline-variant text-center">
                <span class="text-[10px] font-medium text-gray-500 uppercase tracking-normal block">Edisi</span>
                <span class="text-lg font-semibold text-gray-900 block mt-0.5 tabular-nums">{{ $impactMetrics['total_issues'] ?? 0 }}</span>
            </div>
            <div class="bg-gray-50 p-2.5 rounded border border-outline-variant text-center">
                <span class="text-[10px] font-medium text-gray-500 uppercase tracking-normal block">Unduhan</span>
                <span class="text-lg font-semibold text-brand-maroon block mt-0.5 tabular-nums">{{ number_format($impactMetrics['pdf_downloads'] ?? 0) }}</span>
            </div>
            <div class="bg-gray-50 p-2.5 rounded border border-outline-variant text-center">
                <span class="text-[10px] font-medium text-gray-500 uppercase tracking-normal block">Kunjungan</span>
                <span class="text-lg font-semibold text-gray-900 block mt-0.5 tabular-nums">{{ number_format($impactMetrics['abstract_views'] ?? 0) }}</span>
            </div>
        </div>

        <div class="pt-2 border-t border-divider flex items-center justify-between text-[11px] text-gray-600">
            <span>Penerimaan:</span>
            <strong class="text-gray-900 font-medium">{{ $impactMetrics['acceptance_rate'] ?? '0%' }}</strong>
        </div>
    </div>

    <!-- Panel 4: Pengumuman Redaksi (Data Riil Database) -->
    <div id="pengumuman" class="bg-white rounded-lg sm:rounded-xl border border-outline-variant p-4 sm:p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-divider">
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-normal">Pengumuman Redaksi</h3>
            <a href="{{ route('journal.announcements.index') }}" class="text-[10px] font-medium text-brand-maroon hover:underline">Semua</a>
        </div>
        <div class="space-y-3 text-xs divide-y divide-divider">
            @forelse ($announcements as $ann)
                <div class="pt-2.5 first:pt-0 space-y-1">
                    <span class="text-[10px] text-gray-400 font-normal block">{{ $ann['date'] }}</span>
                    <a href="{{ $ann['url'] ?? '#' }}" class="font-medium text-gray-800 hover:text-brand-maroon transition-colors line-clamp-2 block leading-snug">
                        {{ $ann['title'] }}
                    </a>
                </div>
            @empty
                <div class="text-xs text-gray-400 italic py-2">Belum ada pengumuman redaksi yang diterbitkan.</div>
            @endforelse
        </div>
    </div>
</div>
