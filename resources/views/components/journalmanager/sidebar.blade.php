{{-- resources/views/components/journalmanager/sidebar.blade.php --}}
@php
    $isIssuesActive = request()->routeIs('journalmanager.issues.*');
    $isUsersActive = request()->routeIs('journalmanager.users.*');
    $isSectionsActive = request()->routeIs('journalmanager.sections.*');
    $isSettingsActive = request()->routeIs('journalmanager.settings.*');
@endphp

<aside id="journalmanager-sidebar" class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 -translate-x-full lg:translate-x-0 lg:static lg:h-full lg:shrink-0 select-none">
    <!-- Header Brand & Tombol Kontrol -->
    <div class="h-16 px-4 flex items-center justify-between border-b border-gray-200 shrink-0 sidebar-header">
        <!-- Logo & Nama Platform -->
        <a href="{{ route('journalmanager.issues.index') }}" class="flex items-center gap-2.5 min-w-0 group sidebar-brand-link" title="Portal Journal Manager BRIDA Kota Makassar">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Kota Makassar" class="w-8 h-8 object-contain shrink-0 sidebar-logo transition-transform duration-200 group-hover:scale-105">
            <div class="flex flex-col min-w-0 sidebar-brand-text">
                <span class="text-xs font-bold tracking-wide text-brand-maroon leading-tight truncate">BRIDA MAKASSAR</span>
                <span class="text-[11px] font-medium text-gray-500 leading-tight">Journal Manager</span>
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

    <!-- Navigation Menu Items (4 Menu Ergonomis & Fokus) -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1.5 sidebar-scroll" aria-label="Navigasi Utama Journal Manager">
        
        <!-- 1. Edisi & Terbitan (Issues Management) -->
        <a 
            href="{{ route('journalmanager.issues.index') }}" 
            title="Edisi & Terbitan" 
            class="group relative flex items-center justify-between px-3 py-2.5 text-sm font-semibold rounded-lg transition duration-150 sidebar-nav-item {{ $isIssuesActive ? 'bg-brand-maroon text-white shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
        >
            <div class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 shrink-0 {{ $isIssuesActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <span class="truncate sidebar-label">Edisi & Terbitan</span>
            </div>
            <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isIssuesActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                14
            </span>
        </a>

        <!-- 2. Pengguna & Tim Redaksi (Users & Roles) -->
        <a 
            href="{{ route('journalmanager.users.index') }}" 
            title="Pengguna & Tim Redaksi" 
            class="group relative flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-lg transition duration-150 sidebar-nav-item {{ $isUsersActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
        >
            <div class="flex items-center gap-3 min-w-0">
                <svg class="w-5 h-5 shrink-0 {{ $isUsersActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="truncate sidebar-label">Pengguna & Redaksi</span>
            </div>
            <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isUsersActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
                28
            </span>
        </a>

        <!-- 3. Bidang Naskah (Sections) -->
        <a 
            href="{{ route('journalmanager.sections.index') }}" 
            title="Bidang Naskah / Seksi" 
            class="group relative flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition duration-150 sidebar-nav-item {{ $isSectionsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
        >
            <svg class="w-5 h-5 shrink-0 {{ $isSectionsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            <span class="truncate sidebar-label">Bidang Naskah</span>
        </a>

        <!-- 4. Pengaturan Jurnal (Settings) -->
        <a 
            href="{{ route('journalmanager.settings.index') }}" 
            title="Pengaturan Jurnal & Kebijakan" 
            class="group relative flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition duration-150 sidebar-nav-item {{ $isSettingsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
        >
            <svg class="w-5 h-5 shrink-0 {{ $isSettingsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="truncate sidebar-label">Pengaturan Jurnal</span>
        </a>
    </nav>

    <!-- Bottom User Profile & Logout -->
    <div class="p-3 border-t border-gray-200 shrink-0 sidebar-footer bg-gray-50/50">
        <div class="flex items-center justify-between gap-2 p-2 rounded-xl bg-white border border-gray-200/80 shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shrink-0" aria-hidden="true">
                    {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', auth()->user()?->name ?? 'Andi Yusuf') ?: 'AY', 0, 2)) }}
                </div>
                <div class="flex flex-col min-w-0 sidebar-user-info">
                    <span class="text-xs font-bold text-gray-800 truncate" title="{{ auth()->user()?->name ?? 'Andi M. Yusuf, M.Si.' }}">
                        {{ auth()->user()?->name ?? 'Andi M. Yusuf, M.Si.' }}
                    </span>
                    <span class="text-[10px] text-gray-500 truncate">
                        Journal Manager
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
