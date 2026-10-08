<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk | Portal Jurnal BRIDA Makassar')</title>

    <!-- Favicon / Website Title Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Google Fonts: Inter (Akademis, Rapi & Seragam) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900 bg-canvas selection:bg-brand-maroon selection:text-white">
    <div class="min-h-full flex flex-col justify-between">
        
        <!-- Header Bar -->
        <header class="bg-white border-b border-gray-200/80 px-4 sm:px-8 py-3 sticky top-0 z-30 shadow-2xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                <!-- Brand Logo & 2-Line Title -->
                <a href="/" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-9 h-9 object-contain shrink-0 transition-transform group-hover:scale-105">
                    <div class="flex flex-col justify-center">
                        <span class="text-xs sm:text-sm font-bold text-brand-maroon leading-tight tracking-tight">
                            Jurnal Riset & Inovasi
                        </span>
                        <span class="text-[10px] sm:text-[11px] font-semibold text-gray-500 uppercase tracking-wider leading-tight">
                            BRIDA Kota Makassar
                        </span>
                    </div>
                </a>

                <!-- Navigation Back Button (Icon Only on Mobile, Icon + Text on Desktop) -->
                <a href="/" title="Kembali ke beranda" aria-label="Kembali ke beranda" class="inline-flex items-center justify-center gap-1.5 text-xs sm:text-sm font-semibold text-brand-maroon hover:text-brand-maroon-hover transition-colors p-2 sm:py-1.5 sm:px-3 rounded-lg hover:bg-red-50/50 shrink-0">
                    <svg class="w-5 h-5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="hidden sm:inline">Kembali ke beranda</span>
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="grow flex flex-col items-center justify-center p-3.5 sm:p-6 lg:p-8">
            @yield('content')
        </main>

        <!-- Global Page Footer -->
        <footer class="bg-white border-t border-gray-200/80 py-4 px-4 sm:px-8">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 gap-2.5 text-center sm:text-left">
                <p>&copy; {{ date('Y') }} Badan Riset dan Inovasi Daerah Kota Makassar. Seluruh hak cipta dilindungi.</p>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <a href="#" class="hover:text-gray-800 transition-colors">Kebijakan Privasi</a>
                    <span class="text-gray-300">•</span>
                    <a href="#" class="hover:text-gray-800 transition-colors">Etika Publikasi</a>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
