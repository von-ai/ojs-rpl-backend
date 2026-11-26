@props(['categories' => []])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 items-stretch">
    <!-- Left 2-Column Catalog Search Panel -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant p-4 sm:p-5 lg:p-6 flex flex-col justify-between">
        <div>
            <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-snug [text-wrap:balance]">
                Cari artikel jurnal
            </h1>

            <!-- Search Form -->
            <form id="katalog-cari" action="{{ route('journal.index') }}" method="GET" class="mb-3.5">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="flex flex-col sm:flex-row gap-2">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-0">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            aria-label="Cari artikel"
                            placeholder="Ketik judul, penulis, atau kata kunci..."
                            class="w-full min-h-12 pl-10 pr-8 py-2.5 text-sm sm:text-base bg-gray-50/90 border border-outline rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon focus:bg-white transition-all placeholder:text-gray-500 text-gray-900"
                        >
                        <svg class="w-4.5 h-4.5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        @if(request('q'))
                            <a href="{{ route('journal.index', array_filter(['category' => request('category'), 'scope' => request('scope')])) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-800 p-1" title="Hapus pencarian" aria-label="Hapus pencarian">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    <!-- Scope Dropdown & Submit Button Row -->
                    <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                        <x-custom-dropdown
                            name="scope"
                            :value="request('scope')"
                            :options="[
                                '' => 'Semua bagian',
                                'judul' => 'Judul Artikel',
                                'penulis' => 'Nama Penulis',
                                'abstrak' => 'Abstrak & Kata Kunci',
                            ]"
                            container-class="flex-1 sm:w-auto shrink-0 min-w-0"
                            button-class="min-h-12 py-2.5 px-3.5 text-sm bg-gray-50 border-gray-300 text-gray-800 rounded-lg w-full sm:w-auto sm:min-w-44 text-left"
                            panel-class="w-full sm:w-auto sm:min-w-48 rounded-lg"
                        />

                        <button
                            type="submit"
                            class="min-h-12 px-5 sm:px-6 py-2.5 text-sm sm:text-base font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:scale-[0.98] rounded-lg border border-brand-maroon transition-all shrink-0 cursor-pointer inline-flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Cari</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Category Filter Pills (Responsive Horizontal Scroll with Right Fade Mask on Mobile, Wrapped on Desktop) -->
        <div class="pt-3.5 border-t border-divider">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-xs sm:text-sm font-semibold text-gray-700">
                    Topik
                </span>
                @if(request('category'))
                    <a href="{{ route('journal.index', array_filter(['q' => request('q'), 'scope' => request('scope')])) }}" class="text-xs font-semibold text-brand-maroon hover:underline flex items-center gap-0.5">
                        <span>Hapus filter</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>

            <!-- Scrollable Track with Pure CSS Right Fade Hint -->
            <div class="relative">
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar scroll-smooth pb-1 pr-8 -mx-0.5 px-0.5 sm:pr-0 sm:flex-wrap sm:pb-0 sm:overflow-visible">
                    <a
                        href="{{ route('journal.index', array_filter(['q' => request('q'), 'scope' => request('scope')])) }}"
                        class="inline-flex items-center px-3 py-1.5 rounded-md text-xs sm:text-sm font-medium shrink-0 transition-all active:scale-[0.97] {{ empty(request('category')) ? 'bg-brand-maroon text-white font-semibold' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 hover:text-gray-900' }}"
                    >
                        Semua
                    </a>
                    @forelse ($categories as $cat)
                        <a
                            href="{{ route('journal.index', array_filter(['category' => $cat['name'], 'q' => request('q'), 'scope' => request('scope')])) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs sm:text-sm font-medium shrink-0 transition-all active:scale-[0.97] {{ request('category') === $cat['name'] ? 'bg-brand-maroon text-white font-semibold' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 hover:text-gray-900' }}"
                        >
                            <span>{{ $cat['name'] }}</span>
                            <span class="text-xs opacity-80">({{ $cat['count'] }})</span>
                        </a>
                    @empty
                        <span class="text-xs text-gray-400 italic">Belum ada kategori bidang kajian.</span>
                    @endforelse
                </div>

                <!-- Right Gradient Fade Indicator (Mobile Only, Shows List Can Be Scrolled) -->
                <div aria-hidden="true" class="sm:hidden pointer-events-none absolute inset-y-0 right-0 w-8 bg-linear-to-l from-white via-white/80 to-transparent"></div>
            </div>
        </div>
    </div>

    <!-- Right Dark Maroon Hero Card: Pengajuan Naskah Online -->
    <x-journal.submit-card />
</div>
