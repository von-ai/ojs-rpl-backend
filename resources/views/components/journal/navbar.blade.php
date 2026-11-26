@props(['navLinks' => null, 'journal' => null])

<div class="px-3.5 sm:px-6 lg:px-8 py-2 sm:py-3 bg-white w-full max-w-full">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-2.5 sm:gap-4">
        <!-- Logo & Journal Brand Info -->
        <a href="{{ $journal && request()->route()?->parameter('journalSlug') ? url('/' . $journal->slug) : url('/') }}" class="flex items-center gap-2 sm:gap-3 group min-w-0 max-w-[75%] sm:max-w-md md:max-w-lg lg:max-w-none">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 object-contain shrink-0 transition-transform group-hover:scale-105">
            <div class="flex flex-col min-w-0">
                <span class="text-[11.5px] sm:text-sm md:text-base lg:text-lg font-bold text-brand-maroon leading-tight line-clamp-2">
                    {{ $journal?->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar' }}
                </span>
                <span class="text-[8.5px] sm:text-[10px] md:text-[11px] font-medium text-gray-500 uppercase tracking-tight leading-tight mt-0.5 truncate">
                    {{ $journal?->publisher ?? 'BADAN RISET DAN INOVASI DAERAH KOTA MAKASSAR' }}
                </span>
                <span class="sr-only">Jurnal Riset & Inovasi BRIDA Makassar</span>
            </div>
        </a>

        <!-- Header Actions: Search, Profile, Burger -->
        <div class="flex items-center gap-1.5 sm:gap-2 md:gap-3 shrink-0">
            <!-- Quick Search Input (Tablet & Desktop) -->
            <form action="{{ route('journal.index') }}" method="GET" class="relative hidden sm:block w-36 md:w-48 lg:w-56">
                <input
                    type="text"
                    name="q"
                    placeholder="Cari artikel..."
                    value="{{ request('q') }}"
                    class="w-full pl-8 pr-2.5 py-1.5 text-xs bg-gray-100/80 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
                >
                <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>

            <!-- User Avatar Icon / Mobile Quick Search -->
            @auth
                <a
                    href="{{ auth()->user()->isSysAdmin() ? route('sysadmin.journals.index', absolute: false) : route('dashboard', absolute: false) }}"
                    class="inline-flex w-9 h-9 sm:w-9.5 sm:h-9.5 items-center justify-center text-brand-maroon bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-colors shrink-0 font-bold text-xs cursor-pointer active:scale-95"
                    title="Dashboard {{ auth()->user()->name }}"
                    aria-label="Dashboard Pengguna"
                >
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </a>
            @else
                <!-- Mobile Quick Search Button (Hanya tampil di mobile ketika guest) -->
                <button
                    type="button"
                    onclick="const el = document.querySelector('input[name=q]'); if (el) { el.focus(); el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }"
                    class="sm:hidden inline-flex w-9 h-9 items-center justify-center text-gray-700 hover:text-brand-maroon bg-white hover:bg-red-50 rounded-lg border border-gray-200 transition-colors shrink-0 cursor-pointer active:scale-95"
                    title="Cari Artikel"
                    aria-label="Cari Artikel"
                >
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            @endauth

            <!-- Burger Menu Trigger Button (Visible on screens < lg) -->
            <button
                id="mobile-nav-toggle-btn"
                type="button"
                onclick="window.toggleMobileNav(event)"
                class="lg:hidden inline-flex items-center justify-center w-9 h-9 sm:w-9.5 sm:h-9.5 rounded-lg text-gray-700 hover:text-brand-maroon bg-white hover:bg-red-50 border border-gray-200 transition-all cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 active:scale-95"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
                aria-controls="mobile-nav-drawer"
            >
                <svg id="burger-icon" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="burger-close-icon" class="w-5 h-5 hidden transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Desktop Navigation Link Bar (Hidden on < lg, Visible on lg+) -->
<nav class="desktop-navbar hidden lg:block border-t border-gray-200 bg-white px-3 sm:px-8 w-full max-w-full">
    <div class="max-w-7xl mx-auto flex items-center gap-0.5 sm:gap-1 text-xs font-medium text-gray-600 py-1">
        @if (!empty($navLinks))
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}" class="px-3 py-2 rounded-md transition-colors text-nowrap {{ $link['active'] ? 'text-brand-maroon font-semibold border-b-2 border-brand-maroon rounded-b-none' : 'text-gray-600 hover:text-brand-maroon' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        @else
            <a href="{{ route('journal.index') }}" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap font-semibold text-brand-maroon border-b-2 border-brand-maroon rounded-b-none">
                Beranda
            </a>
            <a href="{{ route('journal.issues.current') }}" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap">
                Edisi Terkini
            </a>
            <a href="{{ route('journal.archives') }}" class="px-3 py-2 rounded-md hover:text-brand-maroon transition-colors text-nowrap">
                Arsip Edisi
            </a>
        @endif
    </div>
</nav>
