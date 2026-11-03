<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="application-name" content="{{ $platformSettings['site_name'] ?? config('app.name') }}">
    <meta name="description" content="{{ $platformSettings['site_description'] ?? 'Portal jurnal ilmiah BRIDA Makassar' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Katalog Dialog & Alur Transaksional Copy Editor — BRIDA Kota Makassar')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Inter is bundled by Vite. -->

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-canvas h-dvh min-h-dvh flex flex-col lg:flex-row overflow-hidden pb-[env(safe-area-inset-bottom)]">

    <!-- Sidebar Navigation Khusus Copy Editor -->
    <x-copyeditor.sidebar />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- Header Topbar Copy Editor -->
        <x-copyeditor.header />

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
            @yield('content')
        </main>
    </div>

    <!-- Reusable Action Confirmation Modal -->
    <x-action-modal />

    <!-- Panduan Gaya Selingkung Modal -->
    <x-copyeditor.style-guide-modal />

    <!-- Reusable Toast Notification System -->
    <x-toast />

    @stack('scripts')
</body>
</html>
