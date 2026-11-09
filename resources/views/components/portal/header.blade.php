@props([
    'roleCode' => 'author',
    'portalTitle' => 'Portal Jurnal',
    'homeRoute' => url('/'),
    'pageTitle' => null,
    'defaultName' => 'Pengguna SIRIDA',
    'defaultSub' => null,
    'defaultInitials' => null,
])

@php
    $authUser = auth()->user();
    $roleMeta = \App\Models\UserRole::roleMeta($roleCode);
    $displayName = $authUser?->name ?: $defaultName;
    $displaySub = $defaultSub ?: ($authUser?->email ?: $roleMeta['label']);
    $computedInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $displayName) ?: 'SR', 0, 2));
    $initials = $authUser ? $computedInitials : ($defaultInitials ?: $computedInitials);
@endphp

<header class="h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 shrink-0 sticky top-0 z-20 select-none">
    <!-- Left Section: Mobile Sidebar Trigger & Breadcrumb -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Sidebar Trigger -->
        <button
            id="open-sidebar-btn"
            type="button"
            class="lg:hidden min-w-11 min-h-11 -ml-1.5 p-2 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden transition shrink-0 cursor-pointer"
            aria-label="Buka menu navigasi"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Breadcrumb Context -->
        <nav class="hidden sm:flex items-center gap-2 text-xs text-gray-500 truncate" aria-label="Breadcrumb">
            <a href="{{ $homeRoute }}" class="text-gray-400 hover:text-brand-maroon transition shrink-0" title="{{ $portalTitle }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <span class="text-gray-300">/</span>
            <a href="{{ $homeRoute }}" class="font-semibold {{ $pageTitle ? 'text-gray-600 hover:text-gray-900' : 'text-gray-800' }} transition truncate">
                {{ $portalTitle }}
            </a>
            @if($pageTitle)
                <span class="text-gray-300">/</span>
                <span class="font-semibold text-gray-900 truncate">{{ $pageTitle }}</span>
            @endif
        </nav>
    </div>

    <!-- Right Section: Role Switcher, Quick Actions & User Profile -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
        <!-- Pengalih Peran Multi-Role -->
        <x-role-switcher :current-role="$roleCode" />

        <!-- Slot Aksi Cepat Spesifik Peran (Pedoman, Lencana Status, Notifikasi) -->
        {{ $slot }}

        <div class="h-6 w-px bg-gray-200 hidden sm:block" aria-hidden="true"></div>

        <!-- User Profile Pill & Tombol Keluar -->
        <div class="flex items-center gap-2.5 pl-0.5">
            <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shrink-0 shadow-2xs" aria-hidden="true">
                {{ $initials }}
            </div>
            <div class="hidden md:flex flex-col min-w-0 max-w-44 text-left leading-tight">
                <span class="text-xs font-bold text-gray-900 truncate" title="{{ $displayName }}">{{ $displayName }}</span>
                <span class="text-[11px] text-gray-500 truncate" title="{{ $displaySub }}">{{ $displaySub }}</span>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="m-0 ml-0.5">
                @csrf
                <button
                    type="submit"
                    title="Keluar dari akun"
                    aria-label="Keluar dari akun"
                    class="min-w-11 min-h-11 p-2 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>
