@props([
    'title' => 'Penugasan Bukti Baca (Proofreading)',
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Dra. Sitti Aminah, M.Hum.',
        'affiliation' => 'Tim Redaksi & Bukti Baca BRIDA Makassar',
        'initials' => 'SA',
    ],
])

<header class="h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-20">
    <!-- Left: Mobile Menu & Breadcrumbs -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Toggle Button (Ergonomic Touch Target >= 44x44px) -->
        <button id="open-sidebar-btn" type="button" class="lg:hidden p-2 min-w-11 min-h-11 flex items-center justify-center rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-hidden" aria-label="Buka navigasi">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Clean Breadcrumb -->
        <nav class="hidden sm:flex items-center gap-2 text-xs font-medium text-gray-500 truncate" aria-label="Breadcrumb">
            <a href="{{ route('proofreader.submissions.index') }}" class="text-gray-400 hover:text-gray-600 transition duration-150" title="Penugasan Proofreading">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('proofreader.submissions.index') }}" class="text-gray-600 hover:text-gray-900 transition">Proofreader</a>
            @if($title && $title !== 'Penugasan Bukti Baca (Proofreading)')
                <span class="text-gray-300">/</span>
                <span class="text-gray-900 font-semibold truncate">{{ $title }}</span>
            @endif
        </nav>
    </div>

    <!-- Right: Quick Actions & User Profile -->
    <div class="flex items-center gap-3 shrink-0">
        <!-- Pintasan Cepat ke Panduan Bukti Baca -->
        <a 
            href="{{ route('proofreader.guidelines') }}"
            class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition"
            title="Lihat Standar Koreksi & Pedoman EYD"
        >
            <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>Panduan Bukti Baca</span>
        </a>

        <!-- Status Tag Aktif -->
        <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full text-[11px] font-semibold text-emerald-800">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Galai Siap Diperiksa</span>
        </div>

        <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>

        <!-- Profil Ringkas Header -->
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-brand-maroon/10 border border-brand-maroon/20 text-brand-maroon flex items-center justify-center font-bold text-xs uppercase" aria-hidden="true">
                {{ $user['initials'] ?? 'SA' }}
            </div>
            <div class="hidden xl:flex flex-col text-left">
                <span class="text-xs font-semibold text-gray-800 leading-tight">
                    {{ $user['name'] ?? 'Dra. Sitti Aminah, M.Hum.' }}
                </span>
                <span class="text-[10px] text-gray-500 leading-tight">
                    {{ $user['affiliation'] ?? 'Tim Redaksi & Bukti Baca' }}
                </span>
            </div>
        </div>
    </div>
</header>
