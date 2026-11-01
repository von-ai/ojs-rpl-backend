@props(['archives' => []])

<div id="arsip-edisi" class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest block mb-0.5">
                PUSTAKA TERBITAN
            </span>
            <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">
                Arsip Edisi Terdahulu
            </h2>
        </div>
        <a href="{{ route('journal.archives') }}" class="text-xs font-bold text-brand-maroon hover:underline flex items-center gap-1">
            <span>Lihat Seluruh Arsip</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <!-- Archives Cards 3-Column Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse ($archives as $arc)
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:border-brand-maroon/40 transition-all flex flex-col justify-between space-y-3">
                <div class="space-y-2">
                    <div class="bg-gray-100 p-4 rounded-lg text-center border border-gray-200/80">
                        <svg class="w-8 h-8 text-brand-maroon mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="font-extrabold text-gray-900 text-sm block">{{ $arc['volume_no'] }}</span>
                        <span class="text-xs text-gray-500 font-medium block">{{ $arc['year_month'] }}</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm hover:text-brand-maroon transition-colors line-clamp-2">
                        <a href="{{ $arc['url'] ?? '#' }}">{{ $arc['title'] }}</a>
                    </h3>
                    <span class="text-xs text-gray-500 block">{{ $arc['total_articles'] }} Artikel Terpublikasi</span>
                </div>
                <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                    <a href="{{ $arc['url'] ?? '#' }}" class="font-bold text-gray-700 hover:text-brand-maroon flex items-center gap-1">
                        <span>Daftar Isi Edisi</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-4 bg-gray-50 rounded-lg text-center text-xs text-gray-400 italic">
                Belum ada arsip edisi terdahulu.
            </div>
        @endforelse
    </div>
</div>
