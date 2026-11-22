@props(['announcement' => null])

<div class="bg-gray-50/90 border-b border-gray-200/80 px-3.5 sm:px-6 lg:px-8 py-2 text-xs text-gray-700 w-full max-w-full">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-2.5 min-w-0 w-full">
        <div class="flex items-center gap-2 min-w-0 flex-1 overflow-hidden">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-bold bg-brand-maroon text-white uppercase tracking-wider shrink-0 shadow-2xs">
                Warta
            </span>
            <p class="truncate text-gray-700 font-normal text-xs min-w-0 flex-1">
                {{ $announcement['title'] ?? 'Volume 6 Nomor 2 (Edisi Desember 2026): Inovasi Kebijakan Publik & Ketahanan Perkotaan' }}
                @if(!empty($announcement['deadline']))
                    <span class="hidden sm:inline font-medium text-brand-maroon ml-1">• Batas: {{ $announcement['deadline'] }}</span>
                @endif
            </p>
        </div>
        <a href="{{ $announcement['detail_url'] ?? route('journal.announcements.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-maroon hover:text-brand-maroon-hover shrink-0 hover:underline py-1 px-1.5 active:scale-95 transition-transform">
            <span>Detail</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
