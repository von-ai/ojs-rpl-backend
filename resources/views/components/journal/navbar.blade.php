@props(['navLinks' => null, 'journal' => null])

<div class="px-3.5 sm:px-6 lg:px-8 py-2 sm:py-3 bg-white w-full max-w-full">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-2.5 sm:gap-4">
        <!-- Logo & Journal Brand Info -->
        <a href="{{ $journal && request()->route()?->parameter('journalSlug') ? url('/' . $journal->slug) : url('/') }}" class="flex items-center gap-2 sm:gap-3 group min-w-0 max-w-[75%] sm:max-w-md md:max-w-lg lg:max-w-none">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 object-contain shrink-0 transition-transform group-hover:scale-105">
            <div class="flex flex-col min-w-0">
                <span class="text-sm sm:text-base md:text-lg font-bold text-brand-maroon leading-snug line-clamp-2">
                    {{ $journal?->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar' }}
                </span>
                <span class="text-[11px] sm:text-xs font-medium text-gray-600 uppercase tracking-tight leading-tight mt-0.5 truncate">
                    {{ $journal?->publisher ?? 'BADAN RISET DAN INOVASI DAERAH KOTA MAKASSAR' }}
                </span>
                <span class="sr-only">Jurnal Riset & Inovasi BRIDA Makassar</span>
            </div>
        </a>

        <!-- Header Actions: Search, Profile, Burger -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Quick Search Input (Tablet & Desktop) -->
            <form action="{{ route('journal.index') }}" method="GET" class="relative hidden sm:block w-44 md:w-56 lg:w-64">
                <input
                    type="text"
                    name="q"
                    aria-label="Cari artikel cepat"
                    placeholder="Cari artikel..."
                    value="{{ request('q') }}"
                    class="w-full min-h-10 pl-9 pr-3 py-2 text-sm bg-gray-50 border border-outline rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon placeholder:text-gray-500 transition-all"
                >
                <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>

            <!-- User Avatar Icon / Mobile Quick Search -->
            @auth
                <a
                    href="{{ auth()->user()->isSysAdmin() ? route('sysadmin.journals.index', absolute: false) : route('dashboard', absolute: false) }}"
                    class="inline-flex w-9 h-9 sm:w-10 sm:h-10 items-center justify-center rounded-full bg-brand-maroon hover:bg-brand-maroon-hover text-white uppercase tracking-wider shadow-2xs transition shrink-0 font-bold text-xs cursor-pointer active:scale-95"
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
                    class="sm:hidden inline-flex w-9 h-9 items-center justify-center text-gray-700 hover:text-brand-maroon bg-white hover:bg-red-50 rounded-lg border border-outline-variant transition-colors shrink-0 cursor-pointer active:scale-95"
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
                class="lg:hidden inline-flex items-center justify-center w-9 h-9 sm:w-9.5 sm:h-9.5 rounded-lg text-gray-700 hover:text-brand-maroon bg-white hover:bg-red-50 border border-outline-variant transition-all cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 active:scale-95"
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
<nav class="desktop-navbar hidden lg:block border-t border-divider bg-white px-3 sm:px-8 w-full max-w-full" aria-label="Navigasi utama jurnal">
    <div class="max-w-7xl mx-auto section-menu text-sm font-medium text-gray-700 py-1">
        @if (!empty($navLinks))
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}" class="px-3.5 py-2.5 rounded-md transition-colors text-nowrap {{ $link['active'] ? 'text-brand-maroon font-bold border-b-2 border-brand-maroon rounded-b-none' : 'text-gray-700 hover:text-brand-maroon hover:bg-gray-50' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        @else
            <a href="{{ route('journal.index') }}" class="px-3.5 py-2.5 rounded-md hover:text-brand-maroon transition-colors text-nowrap font-bold text-brand-maroon border-b-2 border-brand-maroon rounded-b-none">
                Beranda
            </a>
            <a href="{{ route('journal.issues.current') }}" class="px-3.5 py-2.5 rounded-md hover:text-brand-maroon hover:bg-gray-50 transition-colors text-nowrap">
                Edisi Terkini
            </a>
            <a href="{{ route('journal.archives') }}" class="px-3.5 py-2.5 rounded-md hover:text-brand-maroon hover:bg-gray-50 transition-colors text-nowrap">
                Arsip Edisi
            </a>
        @endif
    </div>
</nav>
