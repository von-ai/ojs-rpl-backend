@props(['navLinks' => null])

<div class="px-4 sm:px-8 py-3.5 bg-white">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Logo & Journal Brand Info -->
        <a href="/" class="flex items-center gap-3.5 group">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-10 h-10 object-contain shrink-0 transition-transform group-hover:scale-105">
            <div class="flex flex-col">
                <span class="text-base sm:text-lg font-extrabold text-brand-maroon leading-tight tracking-tight">
                    Jurnal Riset & Inovasi BRIDA Makassar
                </span>
                <span class="text-[10px] sm:text-[11px] font-bold text-gray-500 uppercase tracking-wider leading-tight">
                    BADAN RISET DAN INOVASI DAERAH KOTA MAKASSAR
                </span>
            </div>
        </a>

        <!-- Header Actions: Search, Kirim Naskah, Profile -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <!-- Quick Search Input -->
            <form action="{{ route('journal.index') }}" method="GET" class="relative max-w-xs shrink">
                <input
                    type="text"
                    name="q"
                    placeholder="Cari Cepat..."
                    value="{{ request('q') }}"
                    class="w-36 sm:w-48 pl-8 pr-3 py-1.5 text-xs bg-gray-100/80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>

            <!-- Kirim Naskah CTA Button -->
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-sm transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Kirim Naskah</span>
            </a>

            <!-- User Avatar Icon -->
            <a href="{{ route('login') }}" class="p-1.5 text-gray-600 hover:text-brand-maroon bg-gray-100 hover:bg-red-50 rounded-full transition-colors shrink-0" title="Akun Pengguna">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- Main Navigation Link Bar -->
<nav class="border-t border-gray-200 bg-white px-4 sm:px-8">
    <div class="max-w-7xl mx-auto flex items-center gap-1 overflow-x-auto text-xs font-semibold text-gray-700 py-1 no-scrollbar">
        @if (!empty($navLinks))
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap {{ $link['active'] && request()->routeIs('journal.index') && !request('q') && !request('filter') ? 'text-brand-maroon font-bold border-b-2 border-brand-maroon rounded-b-none' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        @else
            <a href="{{ route('journal.index') }}" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap font-bold text-brand-maroon border-b-2 border-brand-maroon rounded-b-none">
                Beranda
            </a>
            <a href="#edisi-terkini" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap">
                Edisi Terkini
            </a>
            <a href="#arsip-edisi" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap">
                Arsip Edisi
            </a>
        @endif
    </div>
</nav>
