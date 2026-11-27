<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="application-name" content="{{ $platformSettings['site_name'] ?? config('app.name') }}">
    <meta name="description" content="{{ $platformSettings['site_description'] ?? 'Portal jurnal ilmiah BRIDA Makassar' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jurnal Riset & Inovasi BRIDA Makassar (JRID-MKS)')</title>

    <!-- Favicon / Website Title Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <x-fonts />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('meta')
</head>
@php
    $supportsTransparentHeader = request()->routeIs(
        'journal.index',
        'journal.scoped.index',
        'journal.journals.*',
        'journal.scoped.journals.*',
        'journal.articles.search',
        'journal.scoped.articles.search',
        'journal.announcements.*',
        'journal.scoped.announcements.*',
        'journal.about*',
        'journal.scoped.about*'
    );
@endphp
<body
    @if($supportsTransparentHeader) data-transparent-header="true" @endif
    class="font-sans antialiased text-gray-900 bg-canvas selection:bg-brand-maroon selection:text-white min-h-screen flex flex-col justify-between overflow-x-hidden w-full max-w-full"
>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:px-4 focus:py-2.5 focus:rounded-lg focus:bg-brand-maroon focus:text-white focus:text-sm focus:font-semibold focus:shadow-lg">
        Lewati ke konten utama
    </a>

    <!-- Topbar & Main Navigation Header -->
    <header
        id="public-site-header"
        @if($supportsTransparentHeader) data-header-mode="transparent" @endif
        class="w-full max-w-full bg-white/95 backdrop-blur-md border-b border-gray-200 {{ $supportsTransparentHeader ? 'fixed top-0 inset-x-0' : 'sticky top-0' }} z-40 transition-all duration-200"
    >
        <x-journal.topbar :topbarData="$topbarData ?? null" :journal="$journal ?? null" />
        <x-journal.navbar :navLinks="$navLinks ?? null" :journal="$journal ?? null" />
    </header>

    @if($supportsTransparentHeader)
        <script>
            (function () {
                const header = document.getElementById('public-site-header');
                if (!header) return;
                function syncHeader() {
                    header.setAttribute('data-header-mode', window.scrollY > 24 ? 'solid' : 'transparent');
                }
                syncHeader();
                window.addEventListener('scroll', syncHeader, { passive: true });
            })();
        </script>
    @endif

    <!-- Main Page Content -->
    <main id="main-content" tabindex="-1" class="grow w-full max-w-full overflow-x-clip focus:outline-none">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <x-journal.footer :footerData="$footerData ?? null" />

    <!-- Mobile Navigation Drawer -->
    <x-journal.mobile-drawer :navLinks="$navLinks ?? null" :journal="$journal ?? null" />

    @stack('scripts')
    <script>
        (function () {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            if (!('IntersectionObserver' in window)) return;

            const targets = document.querySelectorAll('#main-content section:not([data-hero-banner]) article, #main-content section:not([data-hero-banner]) dl > a, #main-content section:not([data-hero-banner]) .divide-y > a');
            if (!targets.length) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });

            targets.forEach((el, idx) => {
                el.classList.add('sirida-reveal');
                el.style.transitionDelay = Math.min((idx % 4) * 55, 165) + 'ms';
                observer.observe(el);
            });
        })();
    </script>
</body>
</html>
