<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Katalog Dialog & Alur Transaksional Copy Editor — BRIDA Kota Makassar')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Google Fonts: Inter & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar Collapse toggle
            const toggleBtn = document.getElementById('sidebar-collapse-toggle-btn');
            const sidebar = document.getElementById('copyeditor-sidebar');
            const collapseLeft = document.getElementById('collapse-icon-left');
            const collapseRight = document.getElementById('collapse-icon-right');
            const openSidebarBtn = document.getElementById('open-sidebar-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('w-60');
                    sidebar.classList.toggle('w-18');
                    document.body.classList.toggle('sidebar-collapsed');

                    const isCollapsed = sidebar.classList.contains('w-18');
                    if (collapseLeft) collapseLeft.classList.toggle('hidden', isCollapsed);
                    if (collapseRight) collapseRight.classList.toggle('hidden', !isCollapsed);
                });
            }

            // Mobile drawer toggle
            if (openSidebarBtn && sidebar) {
                openSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.remove('-translate-x-full');
                });
            }
            if (closeSidebarBtn && sidebar) {
                closeSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.add('-translate-x-full');
                });
            }
        });
    </script>
</body>
</html>
