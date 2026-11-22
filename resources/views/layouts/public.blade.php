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
<body class="font-sans antialiased text-gray-900 bg-canvas selection:bg-brand-maroon selection:text-white min-h-screen flex flex-col justify-between overflow-x-hidden w-full max-w-full">

    <!-- Topbar & Main Navigation Header -->
    <header class="w-full max-w-full bg-white/95 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40">
        <x-journal.topbar :topbarData="$topbarData ?? null" :journal="$journal ?? null" />
        <x-journal.navbar :navLinks="$navLinks ?? null" :journal="$journal ?? null" />
    </header>

    <!-- Main Page Content -->
    <main class="grow w-full max-w-full overflow-x-clip">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <x-journal.footer :footerData="$footerData ?? null" />

    <!-- Mobile Navigation Drawer -->
    <x-journal.mobile-drawer :navLinks="$navLinks ?? null" :journal="$journal ?? null" />

    @stack('scripts')
</body>
</html>
