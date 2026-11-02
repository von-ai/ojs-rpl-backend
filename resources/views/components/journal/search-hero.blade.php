@props(['categories' => []])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
    <!-- Left 2-Column Catalog Search Panel -->
    <div class="lg:col-span-2 bg-white rounded-md border border-gray-200 p-5 sm:p-6 flex flex-col justify-between">
        <div>
            <!-- Top Subhead Row -->
            <div class="mb-2 text-[11px] font-bold text-brand-maroon uppercase tracking-wider">
                Katalog Riset
            </div>

            <!-- Big Main Heading -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-4">
                Temukan Riset & Inovasi Daerah
            </h1>

            <!-- Search Form -->
            <form action="{{ route('journal.index') }}" method="GET" class="mb-5">
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari judul artikel, kata kunci, atau penulis..."
                            class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <x-custom-dropdown
                        name="scope"
                        :value="request('scope')"
                        :options="[
                            '' => 'Semua Ruang Lingkup',
                            'judul' => 'Judul Artikel',
                            'penulis' => 'Nama Penulis',
                            'abstrak' => 'Abstrak & Kata Kunci',
                        ]"
                        container-class="w-full sm:w-auto shrink-0"
                        button-class="py-2.5 px-3 text-xs sm:text-sm bg-gray-50 border-gray-300 text-gray-700 rounded-md min-w-44"
                        panel-class="min-w-48 rounded-md"
                    />

                    <button type="submit" class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-md border border-brand-maroon transition-all shrink-0">
                        Telusuri
                    </button>
                </div>
            </form>
        </div>

        <!-- Category Filter Pills -->
        <div class="pt-4 border-t border-gray-100">
            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-2.5">
                Bidang Kajian:
            </span>
            <div class="flex flex-wrap gap-2">
                @forelse ($categories as $cat)
                    <a
                        href="{{ route('journal.index', ['category' => $cat['name']]) }}"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-semibold transition-all {{ request('category') === $cat['name'] ? 'bg-brand-maroon text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    >
                        <span>{{ $cat['name'] }}</span>
                        <span class="text-[10px] opacity-75">({{ $cat['count'] }})</span>
                    </a>
                @empty
                    <span class="text-xs text-gray-400 italic">Belum ada kategori bidang kajian.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Dark Maroon Hero Card: Pengajuan Naskah Online -->
    <x-journal.submit-card />
</div>
