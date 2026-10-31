@props(['inPress' => []])

<div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div>
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-0.5">
                TERBITAN MENDATANG
            </span>
            <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">
                Artikel Menunggu Terbit
            </h2>
        </div>
        <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md hidden sm:inline">
            Peer-Reviewed & Accepted
        </span>
    </div>

    <!-- Dynamic Items List -->
    <div class="space-y-3.5">
        @forelse ($inPress as $item)
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200/80 hover:border-brand-maroon/30 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center gap-2 text-[11px] text-gray-500">
                        <span>Online First: <strong>{{ $item['online_first_date'] ?? 'Online First' }}</strong></span>
                        <span class="text-gray-300">•</span>
                        <span class="font-semibold text-brand-maroon">{{ $item['target_volume'] ?? 'Terbitan Mendatang' }}</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm hover:text-brand-maroon transition-colors">
                        <a href="{{ $item['preview_url'] ?? '#' }}">{{ $item['title'] }}</a>
                    </h3>
                    <p class="text-gray-600 text-xs">Penulis: {{ implode(', ', $item['authors'] ?? []) }}</p>
                </div>

                <a href="{{ $item['preview_url'] ?? '#' }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 hover:text-brand-maroon bg-white hover:bg-red-50 border border-gray-300 hover:border-brand-maroon/40 rounded-lg shrink-0 transition-all">
                    <span>Pratinjau Naskah</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
            </div>
        @empty
            <div class="p-4 bg-gray-50 rounded-lg text-center text-xs text-gray-400 italic">
                Belum ada artikel yang menunggu terbit.
            </div>
        @endforelse
    </div>
</div>
