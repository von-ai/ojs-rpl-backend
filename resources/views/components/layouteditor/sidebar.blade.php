<aside id="layouteditor-sidebar" class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 -translate-x-full lg:translate-x-0 lg:static lg:h-full lg:shrink-0 select-none">
    <!-- Header Brand & Tombol Kontrol -->
    <div class="h-16 px-4 flex items-center justify-between border-b border-gray-200 shrink-0 sidebar-header">
        <!-- Logo & Nama Platform -->
        <a href="{{ route('layouteditor.submissions.index') }}" class="flex items-center gap-2.5 min-w-0 group sidebar-brand-link" title="Portal Layout Editor BRIDA Kota Makassar">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Kota Makassar" class="w-8 h-8 object-contain shrink-0 sidebar-logo transition-transform duration-200 group-hover:scale-105">
            <div class="flex flex-col min-w-0 sidebar-brand-text">
                <span class="text-xs font-bold tracking-wide text-brand-maroon leading-tight truncate">BRIDA MAKASSAR</span>
                <span class="text-[11px] font-medium text-gray-500 leading-tight">Layout Editor</span>
            </div>
        </a>

        <!-- Desktop Minimize / Maximize Toggle Button -->
        <button id="sidebar-collapse-toggle-btn" type="button" class="hidden lg:flex w-7 h-7 items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 focus:outline-hidden transition shrink-0" title="Kecilkan / Perluas Navigasi">
            <svg id="collapse-icon-left" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <svg id="collapse-icon-right" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" type="button" class="lg:hidden ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-hidden transition shrink-0" aria-label="Tutup navigasi">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu Items (Sederhana & Menunjang UX Bersih) -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1.5 sidebar-scroll" aria-label="Navigasi Utama Layout Editor">
        @php
            $isSubmissionsActive = request()->routeIs('layouteditor.submissions.*') || request()->routeIs('layouteditor.dashboard');
            $isGuidelinesActive = request()->routeIs('layouteditor.guidelines');
            $badgeCount = $assignedCount ?? \App\ViewModels\LayoutEditorSubmissionsViewModel::getSidebarBadgeCount();
        @endphp

        <!-- 1. Penugasan Galai & Tata Letak -->
        <a 
            href="{{ route('layouteditor.submissions.index') }}" 
            title="Penugasan Galai" 
            class="group relative flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-lg transition duration-150 sidebar-nav-item {{ $isSubmissionsActive ? 'bg-brand-maroon text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
        >
            <div class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 shrink-0 {{ $isSubmissionsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                <span class="truncate sidebar-label">Penugasan Galai</span>
            </div>
            <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isSubmissionsActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                {{ $badgeCount }}
            </span>
        </a>

        <!-- 2. Panduan Layout & Format PDF -->
        <a 
            href="{{ route('layouteditor.guidelines') }}" 
            title="Panduan Tata Letak Galai" 
            class="group relative flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition duration-150 sidebar-nav-item {{ $isGuidelinesActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
        >
            <svg class="w-5 h-5 shrink-0 {{ $isGuidelinesActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="truncate sidebar-label">Panduan Galai</span>
        </a>
    </nav>

    <!-- Bottom User Profile & Logout -->
    <div class="p-3 border-t border-gray-200 shrink-0 sidebar-footer bg-gray-50/50">
        <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white border border-gray-200/80 shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shrink-0" aria-hidden="true">
                    {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', auth()->user()?->name ?? 'Rahmat Hidayat') ?: 'RH', 0, 2)) }}
                </div>
                <div class="flex flex-col min-w-0 sidebar-user-info">
                    <span class="text-xs font-bold text-gray-800 truncate" title="{{ auth()->user()?->name ?? 'Rahmat Hidayat, S.Kom.' }}">
                        {{ auth()->user()?->name ?? 'Rahmat Hidayat, S.Kom.' }}
                    </span>
                    <span class="text-[10px] text-gray-500 truncate">
                        Layout Editor
                    </span>
                </div>
            </div>

            <!-- Tombol Keluar (Logout) -->
            <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                @csrf
                <button 
                    type="submit" 
                    title="Keluar dari akun" 
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                    aria-label="Logout"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
