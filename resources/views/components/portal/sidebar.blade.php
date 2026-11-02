@props([
    'id' => 'portal-sidebar',
    'roleLabel' => 'Portal Jurnal',
    'homeRoute' => url('/'),
    'guidelineUrl' => null,
    'guidelineLabel' => 'Pedoman Peran',
    'guidelineActive' => false,
])

<aside id="{{ $id }}" class="portal-sidebar fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 -translate-x-full lg:translate-x-0 lg:static lg:h-full lg:shrink-0 select-none">
    <!-- Header Brand & Tombol Kontrol -->
    <div class="h-16 px-4 flex items-center justify-between border-b border-gray-200 shrink-0 sidebar-header">
        <!-- Logo & Nama Platform -->
        <a href="{{ $homeRoute }}" class="flex items-center gap-2.5 min-w-0 group sidebar-brand-link" title="{{ $roleLabel }} — BRIDA Kota Makassar">
            <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Kota Makassar" class="w-8 h-8 object-contain shrink-0 sidebar-logo transition-transform duration-200 group-hover:scale-105">
            <div class="flex flex-col min-w-0 sidebar-brand-text text-left">
                <span class="text-xs font-bold tracking-wide text-brand-logo leading-tight truncate">BRIDA MAKASSAR</span>
                <span class="text-[11px] font-medium text-gray-500 leading-tight truncate">{{ $roleLabel }}</span>
            </div>
        </a>

        <!-- Desktop Minimize / Maximize Toggle Button -->
        <button id="sidebar-collapse-toggle-btn" type="button" class="hidden lg:flex w-7 h-7 items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 focus:outline-hidden transition shrink-0 cursor-pointer" title="Kecilkan / Perluas Navigasi" aria-label="Kecilkan atau perluas navigasi">
            <svg id="collapse-icon-left" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <svg id="collapse-icon-right" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Mobile Close Button -->
        <button id="close-sidebar-btn" type="button" class="lg:hidden ml-auto min-w-9 min-h-9 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-hidden transition shrink-0 cursor-pointer" aria-label="Tutup navigasi">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu Items (Sesuai Peran) -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1.5 sidebar-scroll" aria-label="Navigasi Utama {{ $roleLabel }}">
        <div class="pb-1 px-2.5 sidebar-heading">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Menu Utama</span>
        </div>

        {{ $slot }}
    </nav>

    <!-- Bottom Secondary Navigation (Panduan, Website Jurnal & Keluar) -->
    <div class="p-3 border-t border-gray-200 space-y-1 shrink-0 bg-gray-50/40 sidebar-footer">
        @if($guidelineUrl)
            <a
                href="{{ $guidelineUrl }}"
                title="{{ $guidelineLabel }}"
                class="group flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg transition duration-150 sidebar-nav-item {{ $guidelineActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}"
            >
                <svg class="w-4 h-4 shrink-0 {{ $guidelineActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="truncate sidebar-label">{{ $guidelineLabel }}</span>
            </a>
        @endif

        <a
            href="{{ url('/') }}"
            target="_blank"
            rel="noopener noreferrer"
            title="Lihat Website Jurnal"
            class="group flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150 sidebar-nav-item"
        >
            <div class="flex items-center gap-3 min-w-0">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="truncate sidebar-label">Lihat Website Jurnal</span>
            </div>
        </a>

        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button
                type="submit"
                title="Keluar dari akun"
                aria-label="Keluar dari akun"
                class="w-full group flex items-center gap-3 px-3 py-2 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50/80 rounded-lg transition duration-150 sidebar-nav-item cursor-pointer text-left"
            >
                <svg class="w-4 h-4 text-red-500 group-hover:text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="truncate sidebar-label">Keluar</span>
            </button>
        </form>
    </div>
</aside>

<!-- Mobile Overlay Backdrop -->
<div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-gray-900/40 backdrop-blur-xs hidden lg:hidden transition-opacity"></div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.__siridaSidebarInitialized) {
            return;
        }
        window.__siridaSidebarInitialized = true;

        const sidebar = document.querySelector('.portal-sidebar') || document.querySelector('aside[id$="-sidebar"]');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openBtn = document.getElementById('open-sidebar-btn');
        const closeBtn = document.getElementById('close-sidebar-btn');
        const collapseBtn = document.getElementById('sidebar-collapse-toggle-btn');
        const iconLeft = document.getElementById('collapse-icon-left');
        const iconRight = document.getElementById('collapse-icon-right');

        if (!sidebar) {
            return;
        }

        const STORAGE_KEY = 'sirida_portal_sidebar_collapsed';

        function applyCollapseState(collapsed) {
            if (collapsed) {
                sidebar.classList.add('sidebar-collapsed');
                iconLeft?.classList.add('hidden');
                iconRight?.classList.remove('hidden');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                iconLeft?.classList.remove('hidden');
                iconRight?.classList.add('hidden');
            }
        }

        if (window.innerWidth >= 1024 && localStorage.getItem(STORAGE_KEY) === '1') {
            applyCollapseState(true);
        }

        collapseBtn?.addEventListener('click', function () {
            const nextState = !sidebar.classList.contains('sidebar-collapsed');
            applyCollapseState(nextState);
            localStorage.setItem(STORAGE_KEY, nextState ? '1' : '0');
        });

        function toggleMobileDrawer(open) {
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop?.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop?.classList.add('hidden');
            }
        }

        openBtn?.addEventListener('click', function () {
            toggleMobileDrawer(true);
        });
        closeBtn?.addEventListener('click', function () {
            toggleMobileDrawer(false);
        });
        backdrop?.addEventListener('click', function () {
            toggleMobileDrawer(false);
        });
    });
</script>
@endonce
