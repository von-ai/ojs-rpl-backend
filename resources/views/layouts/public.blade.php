<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jurnal Riset & Inovasi BRIDA Makassar (JRID-MKS)')</title>

    <!-- Favicon / Website Title Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Google Fonts: Inter (Akademis, Rapi & Seragam) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('meta')
</head>
<body class="font-sans antialiased text-gray-900 bg-canvas selection:bg-brand-maroon selection:text-white min-h-screen flex flex-col justify-between">

    <!-- Topbar & Main Navigation Header -->
    <header class="w-full bg-white border-b border-gray-200 shadow-2xs sticky top-0 z-40">
        <x-journal.topbar :topbarData="$topbarData ?? null" />
        <x-journal.navbar :navLinks="$navLinks ?? null" />
    </header>

    <!-- Main Page Content -->
    <main class="grow">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <x-journal.footer :footerData="$footerData ?? null" />

    @stack('scripts')
</body>
</html>
