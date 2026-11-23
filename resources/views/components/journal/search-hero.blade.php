@props(['categories' => []])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 items-stretch">
    <!-- Left 2-Column Catalog Search Panel -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 lg:p-6 flex flex-col justify-between">
        <div>
            <!-- Top Subhead Row -->
            <div class="flex items-center justify-between gap-2 mb-1">
                <span class="text-[11px] sm:text-xs font-bold text-brand-maroon uppercase tracking-wider">
                    Katalog Riset
                </span>
                <span class="text-[10px] sm:text-[11px] text-gray-500 font-medium">
                    Terbuka untuk Umum
                </span>
            </div>

            <!-- Big Main Heading -->
            <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-snug [text-wrap:balance]">
                Temukan Riset & Inovasi Daerah
            </h1>

            <!-- Search Form -->
            <form id="katalog-cari" action="{{ route('journal.index') }}" method="GET" class="mb-3.5">
                <div class="flex flex-col sm:flex-row gap-2">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-0">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari judul artikel, kata kunci, atau penulis..."
                            class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-gray-50/90 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon focus:bg-white transition-all placeholder:text-gray-400"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        @if(request('q'))
                            <a href="{{ route('journal.index', array_filter(['category' => request('category'), 'scope' => request('scope')])) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5" title="Hapus pencarian">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                '' => 'Semua Ruang Lingkup',
                                'judul' => 'Judul Artikel',
                                'penulis' => 'Nama Penulis',
                                'abstrak' => 'Abstrak & Kata Kunci',
                            ]"
                            container-class="flex-1 sm:w-auto shrink-0 min-w-0"
                            button-class="py-2 px-3 text-xs sm:text-sm bg-gray-50 border-gray-300 text-gray-700 rounded-lg w-full sm:w-auto sm:min-w-44 text-left"
                            panel-class="w-full sm:w-auto sm:min-w-48 rounded-lg"
                        />

                        <button
                            type="submit"
                            class="px-4 sm:px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:scale-[0.98] rounded-lg border border-brand-maroon transition-all shrink-0 cursor-pointer inline-flex items-center justify-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-brand-maroon/20"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Telusuri</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Category Filter Pills (Responsive Horizontal Scroll with Right Fade Mask on Mobile, Wrapped on Desktop) -->
        <div class="pt-3 border-t border-gray-100">
            <div class="flex items-center justify-between gap-2 mb-1.5">
                <span class="text-xs font-medium text-gray-500">
                    Bidang Kajian:
                </span>
                @if(request('category'))
                    <a href="{{ route('journal.index', array_filter(['q' => request('q'), 'scope' => request('scope')])) }}" class="text-[11px] font-semibold text-brand-maroon hover:underline flex items-center gap-0.5">
                        <span>Reset Filter</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </div>

            <!-- Scrollable Track with Pure CSS Right Fade Hint -->
            <div class="relative">
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth pb-1 pr-8 -mx-0.5 px-0.5 sm:pr-0 sm:flex-wrap sm:pb-0 sm:overflow-visible">
                    <a
                        href="{{ route('journal.index', array_filter(['q' => request('q'), 'scope' => request('scope')])) }}"
                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium shrink-0 transition-all active:scale-[0.97] {{ empty(request('category')) ? 'bg-brand-maroon text-white font-semibold' : 'bg-gray-100/90 text-gray-700 hover:bg-gray-200 hover:text-gray-900' }}"
                    >
                        Semua
                    </a>
                    @forelse ($categories as $cat)
                        <a
                            href="{{ route('journal.index', array_filter(['category' => $cat['name'], 'q' => request('q'), 'scope' => request('scope')])) }}"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium shrink-0 transition-all active:scale-[0.97] {{ request('category') === $cat['name'] ? 'bg-brand-maroon text-white font-semibold' : 'bg-gray-100/90 text-gray-700 hover:bg-gray-200 hover:text-gray-900' }}"
                        >
                            <span>{{ $cat['name'] }}</span>
                            <span class="text-[10px] opacity-75">({{ $cat['count'] }})</span>
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
