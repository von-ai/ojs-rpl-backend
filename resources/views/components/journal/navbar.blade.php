@props(['navLinks' => null, 'journal' => null])

@php
    $links = ! empty($navLinks) ? $navLinks : [
        ['label' => 'Beranda', 'url' => route('journal.index'), 'active' => request()->routeIs('journal.index')],
        ['label' => 'Katalog Jurnal', 'url' => route('journal.journals.index'), 'active' => request()->routeIs('journal.journals.*')],
        ['label' => 'Cari Artikel', 'url' => route('journal.articles.search'), 'active' => request()->routeIs('journal.articles.search')],
        ['label' => 'Pengumuman', 'url' => route('journal.announcements.index'), 'active' => request()->routeIs('journal.announcements.*')],
        ['label' => 'Tentang', 'url' => route('journal.about'), 'active' => request()->routeIs('journal.about*')],
    ];

    $submitUrl = route(auth()->user()?->hasRole('author') ? 'author.submissions.create' : (auth()->check() ? 'dashboard' : 'login'), absolute: false);
@endphp

<div id="public-navbar" class="px-3.5 sm:px-6 lg:px-8 py-2.5 sm:py-3 bg-white w-full max-w-full transition-colors duration-200">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 sm:gap-4 lg:gap-6">
        <!-- Kiri: Logo & Identitas Jurnal -->
        <a
            href="{{ $journal && request()->route()?->parameter('journalSlug') ? url('/' . $journal->slug) : url('/') }}"
            class="flex items-center gap-2.5 sm:gap-3 group min-w-0 flex-1 lg:flex-initial lg:max-w-sm xl:max-w-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon rounded-md"
        >
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-8 h-8 sm:w-9 sm:h-9 object-contain shrink-0 transition-transform group-hover:scale-105">
            <div class="flex flex-col min-w-0">
                <span class="public-brand-title text-sm sm:text-base font-bold text-brand-maroon leading-snug line-clamp-1 transition-colors">
                    {{ $journal?->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar' }}
                </span>
                <span class="public-brand-subtitle text-[11px] font-medium text-gray-600 uppercase tracking-tight leading-tight mt-0.5 truncate transition-colors">
                    {{ $journal?->publisher ?? 'BADAN RISET DAN INOVASI DAERAH KOTA MAKASSAR' }}
                </span>
                <span class="sr-only">Jurnal Riset & Inovasi BRIDA Makassar</span>
            </div>
        </a>

        <!-- Tengah-Kanan: Navigasi Utama Inline (Desktop lg+) -->
        <nav class="desktop-navbar hidden lg:flex items-center gap-1 ml-auto" aria-label="Navigasi utama jurnal">
            @foreach ($links as $link)
                <a
                    href="{{ $link['url'] }}"
                    @if($link['active']) aria-current="page" @endif
                    class="px-3 py-2 rounded-lg text-sm text-nowrap transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon {{ $link['active'] ? 'text-brand-maroon font-semibold bg-brand-maroon-light/60' : 'text-gray-700 font-medium hover:text-brand-maroon hover:bg-surface-subtle' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <!-- Kanan: Tombol Kirim Naskah + Profil & Mobile Burger -->
        <div class="flex items-center gap-2.5 shrink-0">
            <a
                href="{{ $submitUrl }}"
                class="hidden sm:inline-flex items-center justify-center px-4 py-2 rounded-lg bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-xs sm:text-sm font-semibold text-white transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon"
            >
                Kirim Naskah
            </a>

            @auth
                <a
                    href="{{ auth()->user()->isSysAdmin() ? route('sysadmin.journals.index', absolute: false) : route('dashboard', absolute: false) }}"
                    class="inline-flex w-9 h-9 sm:w-10 sm:h-10 items-center justify-center rounded-full bg-brand-maroon hover:bg-brand-maroon-hover text-white uppercase tracking-wider transition shrink-0 font-bold text-xs cursor-pointer active:scale-95 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon"
                    title="Dashboard {{ auth()->user()->name }}"
                    aria-label="Dashboard Pengguna"
                >
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </a>
            @endauth

            <!-- Burger Menu Trigger Button (Visible on screens < lg) -->
            <button
                id="mobile-nav-toggle-btn"
                type="button"
                onclick="window.toggleMobileNav(event)"
                class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-lg text-gray-700 hover:text-brand-maroon bg-white hover:bg-surface-subtle border border-outline-variant transition-all cursor-pointer shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-maroon active:scale-95"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
                aria-controls="mobile-nav-drawer"
            >
                <svg id="burger-icon" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="burger-close-icon" class="w-5 h-5 hidden transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
