@props(['categories' => []])

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
    <!-- Left 2-Column Catalog Search Panel -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 sm:p-6 flex flex-col justify-between">
        <div>
            <!-- Top Subhead Row -->
            <div class="flex items-center justify-between gap-2 mb-2 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                <span class="text-brand-maroon">KATALOG INDEKS ILMIAH</span>
                <span class="text-gray-400 font-medium hidden sm:inline">Terindeks SINTA 2 & DOAJ • Akses Terbuka Penuh</span>
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
                            placeholder="Cari berdasarkan judul naskah, kata kunci, nama peneliti, atau DOI..."
                            class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <select name="scope" class="py-2.5 px-3 text-xs bg-gray-50 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-maroon">
                        <option value="">Semua Ruang Lingkup</option>
                        <option value="judul">Judul Artikel</option>
                        <option value="penulis">Nama Penulis</option>
                        <option value="abstrak">Abstrak & Kata Kunci</option>
                    </select>

                    <button type="submit" class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg border border-brand-maroon transition-all shrink-0">
                        Telusuri
                    </button>
                </div>
            </form>
        </div>

        <!-- Category Filter Pills -->
        <div class="pt-4 border-t border-gray-100">
            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-2.5">
                BIDANG KAJIAN UTAMA:
            </span>
            <div class="flex flex-wrap gap-2">
                @forelse ($categories as $cat)
                    <a
                        href="{{ route('journal.index', ['category' => $cat['name']]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ request('category') === $cat['name'] ? 'bg-brand-maroon text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
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
