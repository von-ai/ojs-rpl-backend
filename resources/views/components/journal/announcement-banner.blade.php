@props(['announcement' => null])

<div class="bg-gray-50 border-b border-gray-200/90 px-4 sm:px-8 py-2 text-xs text-gray-700">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5">
        <div class="flex items-center gap-2.5 overflow-hidden">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-brand-maroon text-white tracking-normal uppercase shrink-0">
                Warta
            </span>
            <p class="truncate text-gray-700 font-normal">
                {{ $announcement['title'] ?? 'Volume 6 Nomor 2 (Edisi Desember 2026): Inovasi Kebijakan Publik & Ketahanan Perkotaan' }}
                <span class="font-medium text-brand-maroon ml-1">Batas Akhir: {{ $announcement['deadline'] ?? '15 November 2026' }}</span>
            </p>
        </div>
        <a href="{{ route('journal.announcements.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-brand-maroon hover:text-brand-maroon-hover shrink-0 hover:underline">
            <span>Detail</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
