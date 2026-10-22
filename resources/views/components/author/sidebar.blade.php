<aside id="author-sidebar" class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 -translate-x-full lg:translate-x-0 lg:static lg:h-full lg:shrink-0 select-none">
    <!-- Header Brand & Tombol Kontrol -->
    <div class="h-16 px-3.5 flex items-center justify-between border-b border-gray-200 shrink-0 sidebar-header">
        <!-- Logo & Nama Platform -->
        <a href="{{ route('author.submissions.index') }}" class="flex items-center gap-2.5 min-w-0 group sidebar-brand-link" title="Portal Penulis BRIDA Kota Makassar">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Kota Makassar" class="w-8 h-8 object-contain shrink-0 sidebar-logo transition-transform duration-200 group-hover:scale-105">
            <div class="flex flex-col min-w-0 sidebar-brand-text">
                <span class="text-xs font-bold tracking-wide text-brand-maroon leading-tight truncate">BRIDA MAKASSAR</span>
                <span class="text-[11px] font-medium text-gray-500 leading-tight">Portal Penulis</span>
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

    <!-- Navigation Menu Items -->
    <nav class="flex-1 px-3 py-3 overflow-y-auto space-y-3 sidebar-scroll" aria-label="Navigasi Utama">
        <!-- Grup 1: Menu Utama -->
        <div class="space-y-1">
            <span class="px-2.5 text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1 sidebar-heading">
                Menu Utama
            </span>

            <!-- Dashboard -->
            <a href="#dashboard" title="Dashboard" class="group relative flex items-center gap-2.5 px-2.5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 rounded-lg transition duration-150 sidebar-nav-item">
                <svg class="w-4.5 h-4.5 text-gray-400 group-hover:text-gray-600 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="truncate sidebar-label">Dashboard</span>
            </a>

            <!-- Naskah Saya (Active Menu Item) -->
            <a href="{{ route('author.submissions.index') }}" aria-current="page" title="Naskah Saya" class="relative flex items-center justify-between px-2.5 py-2 text-sm font-semibold bg-brand-maroon text-white rounded-lg shadow-2xs transition duration-150 sidebar-nav-item">
                <div class="flex items-center gap-2.5 min-w-0">
                    <svg class="w-4.5 h-4.5 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="truncate sidebar-label">Naskah Saya</span>
                </div>
                <span class="sidebar-badge-full inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold bg-white/20 text-white rounded-full tabular-nums shrink-0">5</span>
            </a>
        </div>

        <div class="border-t border-gray-100 my-2 sidebar-divider"></div>

        <!-- Grup 2: Komunikasi & Aktivitas -->
        <div class="space-y-1">
            <span class="px-2.5 text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1 sidebar-heading">
                Aktivitas
            </span>

            <!-- Pesan Redaksi -->
            <a href="#pesan" title="Pesan Redaksi (1)" class="group relative flex items-center justify-between px-2.5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 rounded-lg transition duration-150 sidebar-nav-item">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative shrink-0">
                        <svg class="w-4.5 h-4.5 text-gray-400 group-hover:text-gray-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="sidebar-badge-dot absolute -top-0.5 -right-0.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </div>
                    <span class="truncate sidebar-label">Pesan Redaksi</span>
                </div>
                <span class="sidebar-badge-full inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold bg-red-50 text-red-700 border border-red-200/60 rounded-full tabular-nums shrink-0">1</span>
            </a>

            <!-- Notifikasi -->
            <a href="#notifikasi" title="Notifikasi (2)" class="group relative flex items-center justify-between px-2.5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 rounded-lg transition duration-150 sidebar-nav-item">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative shrink-0">
                        <svg class="w-4.5 h-4.5 text-gray-400 group-hover:text-gray-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="sidebar-badge-dot absolute -top-0.5 -right-0.5 w-2 h-2 bg-amber-500 rounded-full ring-2 ring-white"></span>
                    </div>
                    <span class="truncate sidebar-label">Notifikasi</span>
                </div>
                <span class="sidebar-badge-full inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full tabular-nums shrink-0">2</span>
            </a>
        </div>

        <div class="border-t border-gray-100 my-2 sidebar-divider"></div>

        <!-- Grup 3: Akun & Preferensi -->
        <div class="space-y-1">
            <span class="px-2.5 text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1 sidebar-heading">
                Akun
            </span>

            <!-- Profil Akademik & ORCID -->
            <a href="#profil" title="Profil & ORCID" class="group relative flex items-center gap-2.5 px-2.5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 rounded-lg transition duration-150 sidebar-nav-item">
                <svg class="w-4.5 h-4.5 text-gray-400 group-hover:text-gray-600 shrink-0 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="truncate sidebar-label">Profil & ORCID</span>
            </a>
        </div>
    </nav>

    <!-- Bottom Secondary Links -->
    <div class="p-2.5 border-t border-gray-200/70 space-y-0.5 shrink-0 mt-auto bg-gray-50/40">
        <a href="#bantuan" title="Panduan Penulis" class="flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-2xs rounded-md transition duration-150 sidebar-nav-item">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="truncate sidebar-label">Panduan Penulis</span>
        </a>

        <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" title="Lihat Website Jurnal" class="flex items-center justify-between px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-2xs rounded-md transition duration-150 sidebar-nav-item">
            <div class="flex items-center gap-2.5 min-w-0">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="truncate sidebar-label">Lihat Website Jurnal</span>
            </div>
            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 sidebar-label" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" title="Keluar dari akun" aria-label="Keluar dari akun" class="w-full flex items-center gap-2.5 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50/80 rounded-md transition duration-150 sidebar-nav-item cursor-pointer text-left">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="truncate sidebar-label">Keluar</span>
            </button>
        </form>
    </div>
</aside>

<!-- Mobile Overlay Backdrop -->
<div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-gray-900/40 backdrop-blur-xs transition-opacity duration-300 opacity-0 pointer-events-none lg:hidden"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('author-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const closeBtn = document.getElementById('close-sidebar-btn');
        const openBtn = document.getElementById('open-sidebar-btn');
        const collapseBtn = document.getElementById('sidebar-collapse-toggle-btn');
        const collapseIconLeft = document.getElementById('collapse-icon-left');
        const collapseIconRight = document.getElementById('collapse-icon-right');

        const STORAGE_KEY = 'author_sidebar_minimized';

        function setSidebarCollapsed(collapsed) {
            if (!sidebar) return;

            if (collapsed) {
                sidebar.classList.add('sidebar-collapsed');
                collapseIconLeft?.classList.add('hidden');
                collapseIconRight?.classList.remove('hidden');
                localStorage.setItem(STORAGE_KEY, 'true');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                collapseIconLeft?.classList.remove('hidden');
                collapseIconRight?.classList.add('hidden');
                localStorage.setItem(STORAGE_KEY, 'false');
            }
        }

        // Restore user preference on desktop screens
        if (window.innerWidth >= 1024 && localStorage.getItem(STORAGE_KEY) === 'true') {
            setSidebarCollapsed(true);
        }

        // Event listener toggle collapse/expand
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                setSidebarCollapsed(!sidebar.classList.contains('sidebar-collapsed'));
            });
        }

        // Mobile Drawer logic
        function toggleMobileSidebar(open) {
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100');
            }
        }

        if (openBtn) openBtn.addEventListener('click', () => toggleMobileSidebar(true));
        if (closeBtn) closeBtn.addEventListener('click', () => toggleMobileSidebar(false));
        if (backdrop) backdrop.addEventListener('click', () => toggleMobileSidebar(false));
    });
</script>
