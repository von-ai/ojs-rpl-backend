@props(['navLinks' => null, 'journal' => null])

<!-- Mobile Drawer Backdrop Overlay -->
<div
    id="mobile-nav-backdrop"
    class="fixed inset-0 bg-gray-900/60 z-50 transition-opacity duration-300 opacity-0 pointer-events-none hidden lg:hidden"
    style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
    onclick="window.closeMobileNav()"
    aria-hidden="true"
></div>

<!-- Mobile Off-Canvas Navigation Drawer -->
<div
    id="mobile-nav-drawer"
    class="fixed inset-y-0 right-0 z-50 w-full max-w-[310px] sm:max-w-xs bg-white shadow-2xl flex flex-col transition-transform duration-300 ease-out translate-x-full lg:hidden border-l border-gray-200"
    role="dialog"
    aria-modal="true"
    aria-label="Menu Navigasi Mobile"
    aria-hidden="true"
>
    <!-- Drawer Header -->
    <div class="px-4 py-3.5 border-b border-gray-200 flex items-center justify-between gap-3 bg-gray-50/80 shrink-0">
        <a href="{{ $journal && request()->route()?->parameter('journalSlug') ? url('/' . $journal->slug) : url('/') }}" class="flex items-center gap-2.5 min-w-0" onclick="window.closeMobileNav()">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-8 h-8 object-contain shrink-0">
            <div class="flex flex-col min-w-0">
                <span class="text-xs font-bold text-brand-maroon truncate">
                    {{ $journal?->name ?? 'Jurnal BRIDA Makassar' }}
                </span>
                <span class="text-[9.5px] text-gray-500 uppercase tracking-tight truncate">
                    SIRIDA Kota Makassar
                </span>
            </div>
        </a>

        <!-- Close Button (Accessible Touch Target) -->
        <button
            id="mobile-nav-close-btn"
            type="button"
            onclick="window.closeMobileNav()"
            class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200/80 bg-white hover:bg-gray-100 active:bg-gray-200 text-gray-500 hover:text-gray-900 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-maroon/20 shadow-2xs shrink-0"
            aria-label="Tutup menu navigasi"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Drawer Body (Scrollable) -->
    <div class="flex-1 overflow-y-auto px-4 py-3.5 space-y-4 sidebar-scroll">
        <!-- Quick Search Form Inside Drawer -->
        <form action="{{ route('journal.index') }}" method="GET" class="relative w-full">
            <input
                type="text"
                name="q"
                placeholder="Cari artikel ilmiah..."
                value="{{ request('q') }}"
                class="w-full pl-9 pr-3 py-2 text-xs bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </form>

        <!-- Navigation Links with Intuitive Icons -->
        <div class="space-y-1">
            <div class="px-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                Menu Utama
            </div>

            @if (!empty($navLinks))
                @foreach ($navLinks as $link)
                    @php
                        $labelLower = strtolower($link['label']);
                    @endphp
                    <a
                        href="{{ $link['url'] }}"
                        onclick="window.closeMobileNav()"
                        class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-all {{ $link['active'] ? 'bg-red-50/90 text-brand-maroon font-semibold shadow-2xs' : 'text-gray-700 hover:bg-gray-50 hover:text-brand-maroon' }}"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            @if(str_contains($labelLower, 'beranda'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            @elseif(str_contains($labelLower, 'cari'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            @elseif(str_contains($labelLower, 'terkini'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            @elseif(str_contains($labelLower, 'arsip'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            @elseif(str_contains($labelLower, 'tentang'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif(str_contains($labelLower, 'dewan') || str_contains($labelLower, 'redaksi'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            @elseif(str_contains($labelLower, 'panduan') || str_contains($labelLower, 'penulis'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            @elseif(str_contains($labelLower, 'pengumuman'))
                                <svg class="w-4 h-4 shrink-0 {{ $link['active'] ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            @else
                                <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            @endif

                            <span class="truncate">{{ $link['label'] }}</span>
                        </div>

                        <svg class="w-3.5 h-3.5 transition-colors {{ $link['active'] ? 'text-brand-maroon/70' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endforeach
            @else
                <a href="{{ route('journal.index') }}" onclick="window.closeMobileNav()" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-semibold bg-red-50/90 text-brand-maroon shadow-2xs">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Beranda</span>
                </a>
                <a href="{{ route('journal.issues.current') }}" onclick="window.closeMobileNav()" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-brand-maroon">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Edisi Terkini</span>
                </a>
                <a href="{{ route('journal.archives') }}" onclick="window.closeMobileNav()" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-brand-maroon">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <span>Arsip Edisi</span>
                </a>
            @endif
        </div>

        <!-- Fast CTA & Auth Section in Mobile Drawer -->
        <div class="pt-2 border-t border-gray-200 space-y-2">
            <div class="px-2 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                Aksi Cepat
            </div>

            <a
                href="{{ auth()->check() ? route('author.submissions.create') : route('login') }}"
                onclick="window.closeMobileNav()"
                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-2xs transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Kirim Naskah Baru</span>
            </a>

            @auth
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 space-y-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-gray-500 truncate">{{ auth()->user()->email }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1 border-t border-gray-200">
                        <a
                            href="{{ auth()->user()->isSysAdmin() ? route('sysadmin.journals.index') : route('dashboard') }}"
                            onclick="window.closeMobileNav()"
                            class="flex-1 text-center py-1.5 px-2 bg-white hover:bg-gray-100 text-gray-700 font-medium text-xs rounded border border-gray-200 transition-colors"
                        >
                            Dashboard
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button
                                type="submit"
                                class="py-1.5 px-2.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded border border-transparent hover:border-red-200 transition-colors cursor-pointer"
                                title="Keluar dari akun"
                            >
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a
                        href="{{ route('login') }}"
                        onclick="window.closeMobileNav()"
                        class="text-center py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-xs rounded-lg transition-colors border border-gray-200"
                    >
                        Masuk
                    </a>
                    <a
                        href="{{ route('register') }}"
                        onclick="window.closeMobileNav()"
                        class="text-center py-2 px-3 bg-red-50 hover:bg-red-100 text-brand-maroon font-semibold text-xs rounded-lg transition-colors border border-red-200"
                    >
                        Daftar
                    </a>
                </div>
            @endauth
        </div>
    </div>

    <!-- Drawer Footer -->
    <div class="p-3.5 bg-gray-50 border-t border-gray-200 text-[10px] text-gray-500 space-y-1 shrink-0">
        <div class="flex items-center justify-between">
            <span><strong>p-ISSN:</strong> {{ $journal?->issn ?: '2746-8194' }}</span>
            <span><strong>e-ISSN:</strong> {{ $journal?->eissn ?: '2746-8208' }}</span>
        </div>
        <div class="text-center text-gray-400 pt-0.5">
            &copy; BRIDA Kota Makassar • OJS Platform
        </div>
    </div>
</div>

<script>
    window.openMobileNav = function() {
        const drawer = document.getElementById('mobile-nav-drawer');
        const backdrop = document.getElementById('mobile-nav-backdrop');
        const toggleBtn = document.getElementById('mobile-nav-toggle-btn');
        const closeBtn = document.getElementById('mobile-nav-close-btn');
        const burgerIcon = document.getElementById('burger-icon');
        const closeIcon = document.getElementById('burger-close-icon');

        if (!drawer || !backdrop) return;

        backdrop.classList.remove('hidden');
        void backdrop.offsetWidth; // Force reflow
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');

        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');

        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');
        if (burgerIcon) burgerIcon.classList.add('hidden');
        if (closeIcon) closeIcon.classList.remove('hidden');

        document.body.style.overflow = 'hidden';

        setTimeout(function() {
            closeBtn?.focus();
        }, 100);
    };

    window.closeMobileNav = function() {
        const drawer = document.getElementById('mobile-nav-drawer');
        const backdrop = document.getElementById('mobile-nav-backdrop');
        const toggleBtn = document.getElementById('mobile-nav-toggle-btn');
        const burgerIcon = document.getElementById('burger-icon');
        const closeIcon = document.getElementById('burger-close-icon');

        if (!drawer || !backdrop) return;

        drawer.classList.add('translate-x-full');
        drawer.classList.remove('translate-x-0');

        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
        backdrop.classList.add('opacity-0', 'pointer-events-none');

        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');
        if (burgerIcon) burgerIcon.classList.remove('hidden');
        if (closeIcon) closeIcon.classList.add('hidden');

        document.body.style.overflow = '';

        setTimeout(function() {
            if (toggleBtn && toggleBtn.getAttribute('aria-expanded') === 'false') {
                backdrop.classList.add('hidden');
            }
        }, 300);
    };

    window.toggleMobileNav = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const drawer = document.getElementById('mobile-nav-drawer');
        const isOpen = drawer && !drawer.classList.contains('translate-x-full');
        if (isOpen) {
            window.closeMobileNav();
        } else {
            window.openMobileNav();
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const drawer = document.getElementById('mobile-nav-drawer');
            if (drawer && !drawer.classList.contains('translate-x-full')) {
                window.closeMobileNav();
                document.getElementById('mobile-nav-toggle-btn')?.focus();
            }
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            const drawer = document.getElementById('mobile-nav-drawer');
            if (drawer && !drawer.classList.contains('translate-x-full')) {
                window.closeMobileNav();
            }
        }
    });
</script>
