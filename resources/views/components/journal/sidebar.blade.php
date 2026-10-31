@props([
    'publisherInfo' => null,
    'indexingData' => null,
    'impactMetrics' => null,
    'announcements' => [],
])

<div class="space-y-6">
    <!-- Panel 1: Informasi Penerbitan (Rapi, Struktur Vertikal Bebas Tabrakan Teks) -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider pb-2 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <span>INFORMASI PENERBITAN</span>
        </h3>

        <dl class="space-y-3 text-xs divide-y divide-gray-100">
            <div class="pt-1 first:pt-0 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide shrink-0">Inisial Jurnal</dt>
                <dd class="font-bold text-gray-900 font-mono text-right">{{ $publisherInfo['journal_code'] ?? 'SIRIDA' }}</dd>
            </div>

            <div class="pt-2.5">
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide block">Model Penelaahan</dt>
                <dd class="font-medium text-gray-800 mt-1 leading-relaxed block">{{ $publisherInfo['peer_review_model'] ?? 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan).' }}</dd>
            </div>

            <div class="pt-2.5">
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide block">Frekuensi Terbit</dt>
                <dd class="font-medium text-gray-800 mt-1 leading-relaxed block">{{ $publisherInfo['frequency'] ?? '2 Kali Setahun (Nomor 1 terbit Juni, Nomor 2 terbit Desember)' }}</dd>
            </div>

            <div class="pt-2.5">
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide block mb-1">Biaya Pemrosesan Naskah (APC)</dt>
                <dd class="block">
                    <span class="inline-block text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg px-2.5 py-1.5 leading-relaxed">
                        {{ $publisherInfo['apc_status'] ?? 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi / Diamond Open Access)' }}
                    </span>
                </dd>
            </div>

            <div class="pt-2.5">
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide block">Penerbit Resmi</dt>
                <dd class="font-medium text-gray-800 mt-1 leading-relaxed block">{{ $publisherInfo['publisher'] ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar' }}</dd>
            </div>

            <div class="pt-2.5 flex items-center justify-between gap-3">
                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide shrink-0">Bahasa Naskah</dt>
                <dd class="font-medium text-gray-800 text-right">{{ $publisherInfo['languages'] ?? 'Indonesia & Inggris' }}</dd>
            </div>
        </dl>

        <div class="pt-3 border-t border-gray-100">
            <a href="{{ $publisherInfo['ethics_policy_url'] ?? route('journal.about.policies') }}" class="text-xs font-bold text-brand-maroon hover:underline flex items-center justify-between group">
                <span>Baca Etika Publikasi & Standar Integritas</span>
                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Panel 2: Pengindeksan Ilmiah (Grid 2-Kolom) -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">PENGINDEKSAN ILMIAH</h3>
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

    <!-- Panel 3: Statistik Portal Jurnal (Data Riil Backend & COUNTER Metrics) -->
    <div class="bg-white rounded-2xl border border-gray-200 p-5 space-y-3.5">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>STATISTIK PORTAL</span>
            </h3>
            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 font-semibold">Live System</span>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/70 text-center">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide block">Artikel Terbit</span>
                <span class="text-xl font-extrabold text-gray-900 block mt-0.5 tabular-nums">{{ $impactMetrics['total_articles'] ?? 0 }}</span>
                <span class="text-[10px] text-gray-400 block mt-0.5">Naskah Ilmiah</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/70 text-center">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide block">Edisi Terbit</span>
                <span class="text-xl font-extrabold text-gray-900 block mt-0.5 tabular-nums">{{ $impactMetrics['total_issues'] ?? 0 }}</span>
                <span class="text-[10px] text-gray-400 block mt-0.5">Volume Terbit</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/70 text-center">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide block">Unduhan PDF</span>
                <span class="text-xl font-extrabold text-brand-maroon block mt-0.5 tabular-nums">{{ number_format($impactMetrics['pdf_downloads'] ?? 0) }}</span>
                <span class="text-[10px] text-gray-400 block mt-0.5">Galai Naskah</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/70 text-center">
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide block">Pembaca Abstrak</span>
                <span class="text-xl font-extrabold text-gray-900 block mt-0.5 tabular-nums">{{ number_format($impactMetrics['abstract_views'] ?? 0) }}</span>
                <span class="text-[10px] text-gray-400 block mt-0.5">Kunjungan Riil</span>
            </div>
        </div>

        <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-600">
            <span>Tingkat Penerimaan Naskah:</span>
            <strong class="text-gray-900 font-bold font-mono">{{ $impactMetrics['acceptance_rate'] ?? '0%' }}</strong>
        </div>
    </div>

    <!-- Panel 4: Pengumuman Redaksi (Data Riil Database) -->
    <div id="pengumuman" class="bg-white rounded-2xl border border-gray-200 p-5 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">PENGUMUMAN REDAKSI</h3>
            <a href="{{ route('journal.announcements.index') }}" class="text-[10px] font-bold text-brand-maroon hover:underline">Semua</a>
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
                <div class="text-xs text-gray-400 italic py-2">Belum ada pengumuman redaksi yang diterbitkan.</div>
            @endforelse
        </div>
    </div>
</div>
