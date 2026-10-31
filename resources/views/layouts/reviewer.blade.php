<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Mitra Bestari (Peer Reviewer) — SIRIDA Kota Makassar')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Google Fonts: Inter & Lora -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-canvas min-h-screen flex flex-col justify-between">

    <!-- Topbar Navigasi Reviewer -->
    <header class="w-full bg-white border-b border-gray-200 shadow-2xs sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 py-3.5 flex items-center justify-between gap-4">
            <!-- Brand Logo & Title -->
            <div class="flex items-center gap-3">
                <a href="{{ route('reviewer.assignments.index') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-8 h-8 object-contain shrink-0">
                    <div>
                        <span class="font-extrabold text-brand-maroon text-sm tracking-tight block">SIRIDA MAKASSAR</span>
                        <span class="text-[10px] text-gray-500 font-bold tracking-wider uppercase">Portal Mitra Bestari (Peer Reviewer)</span>
                    </div>
                </a>
            </div>

            <!-- Profile & Menu -->
            <div class="flex items-center gap-4 text-xs">
                <x-role-switcher current-role="reviewer" />

                <nav class="hidden md:flex items-center gap-2">
                    <a href="{{ route('reviewer.assignments.index') }}" class="px-3 py-1.5 rounded-lg font-bold {{ request()->routeIs('reviewer.*') ? 'bg-brand-maroon/10 text-brand-maroon' : 'text-gray-600 hover:text-gray-900' }}">
                        Tugas Penelaahan Ilmiah
                    </a>
                </nav>

                <div class="h-4 w-px bg-gray-200 hidden md:block"></div>

                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-indigo-700 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'MB', 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <span class="block font-bold text-gray-900 text-xs">{{ auth()->user()?->name ?? 'Mitra Bestari' }}</span>
                        <span class="block text-[10px] text-gray-500 font-medium">Peer Reviewer</span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-2.5 py-1 text-xs text-gray-500 hover:text-red-600 font-semibold cursor-pointer">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="grow max-w-7xl w-full mx-auto px-4 sm:px-8 py-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-3 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center gap-3 shadow-2xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1 shadow-2xs">
                <div class="flex items-center gap-2 font-bold">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Terjadi kesalahan pada formulir telaah:</span>
                </div>
                <ul class="list-disc list-inside ml-6 text-xs text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 px-4 sm:px-8 text-center text-xs text-gray-500">
        <p>&copy; 2026 Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar — Sistem Informasi Manajemen Jurnal OJS 3.</p>
    </footer>

    <!-- Reusable Toast Notification System -->
    <x-toast />

    @stack('scripts')
</body>
</html>
