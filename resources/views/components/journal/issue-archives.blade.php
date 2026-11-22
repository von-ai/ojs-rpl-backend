@props(['archives' => []])

<div id="arsip-edisi" class="space-y-3.5 sm:space-y-4">
    <!-- Header with Desktop Link (Bersih, Tanpa Tombol yang Berdesakan) -->
    <div class="flex items-center justify-between gap-2">
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-normal block mb-0.5">
                Pustaka Terbitan
            </span>
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">
                Arsip Edisi Terdahulu
            </h2>
        </div>

        <a href="{{ route('journal.archives') }}" class="text-xs font-semibold text-brand-maroon hover:underline flex items-center gap-1 shrink-0 whitespace-nowrap">
            <span>Lihat Seluruh Arsip</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <!-- Responsive Container: Mobile Slider Carousel dengan Layer Arrow di Atas Card -->
    <div class="relative group/slider">
        <!-- Floating Left Arrow Layer (Sebelumnya - Mengambang di atas Card) -->
        <button
            type="button"
            onclick="document.getElementById('archives-slider').scrollBy({ left: -260, behavior: 'smooth' })"
            class="sm:hidden absolute -left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/95 hover:bg-white text-gray-700 hover:text-brand-maroon border border-gray-300 shadow-sm flex items-center justify-center transition-all cursor-pointer active:scale-90 focus:outline-none"
            aria-label="Arsip Sebelumnya"
            title="Arsip Sebelumnya"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Floating Right Arrow Layer (Selanjutnya - Mengambang di atas Card) -->
        <button
            type="button"
            onclick="document.getElementById('archives-slider').scrollBy({ left: 260, behavior: 'smooth' })"
            class="sm:hidden absolute -right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/95 hover:bg-white text-gray-700 hover:text-brand-maroon border border-gray-300 shadow-sm flex items-center justify-center transition-all cursor-pointer active:scale-90 focus:outline-none"
            aria-label="Arsip Selanjutnya"
            title="Arsip Selanjutnya"
        >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Slider Track: Berjarak Rapi dari Tepi (Tidak Mepet) -->
        <div
            id="archives-slider"
            class="flex overflow-x-auto no-scrollbar scroll-smooth snap-x snap-mandatory gap-3.5 pb-2 px-1 sm:px-0 sm:grid sm:grid-cols-2 md:grid-cols-3 sm:gap-4 sm:overflow-visible sm:pb-0"
        >
            @forelse ($archives as $arc)
                <x-journal.issue-card :issue="$arc" class="snap-start shrink-0 w-[82vw] max-w-[280px] sm:w-auto sm:max-w-none" />
            @empty
                <div class="col-span-full p-4 bg-gray-50 rounded-lg text-center text-xs text-gray-400 italic">
                    Belum ada arsip edisi terdahulu.
                </div>
            @endforelse
        </div>
    </div>
</div>
