@props(['announcement' => null])

<div class="bg-red-50/80 border-y border-red-200/80 px-4 sm:px-8 py-2.5 text-xs text-red-900">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 overflow-hidden">
            <p class="truncate text-red-950 font-medium">
                {{ $announcement['title'] ?? 'Volume 4 Nomor 1 (Edisi Juni 2026): Fokus Inovasi Ekosistem Digital Maritim dan Kebijakan Publik Berkelanjutan.' }}
                <span class="font-bold text-brand-maroon ml-1">Batas Akhir: {{ $announcement['deadline'] ?? '30 April 2026' }}</span>
            </p>
        </div>
        <a href="#pengumuman" class="inline-flex items-center gap-1 text-xs font-bold text-brand-maroon hover:text-brand-maroon-hover shrink-0 hover:underline">
            <span>Detail</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
