@props(['viewModel' => null])

@php
    $profile = $viewModel ? $viewModel->userProfile() : [
        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
        'title' => 'Dr. Andi Muhammad Ilyas ...',
        'affiliation' => 'Universitas Hasanuddin',
        'unread_notifications' => 2,
    ];
@endphp

<header class="h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-20">
    <!-- Left: Mobile Menu & Breadcrumbs -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Toggle Button -->
        <button id="open-sidebar-btn" type="button" class="lg:hidden p-2 min-w-11 min-h-11 flex items-center justify-center rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-hidden" aria-label="Buka navigasi">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>


        <!-- Breadcrumbs Navigation -->
        <nav class="hidden sm:flex items-center gap-2 text-xs font-medium text-gray-500 truncate" aria-label="Breadcrumb">
            <a href="#dashboard" class="text-gray-400 hover:text-gray-600 transition duration-150">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-semibold">Portal Penulis</span>
        </nav>
    </div>


    <!-- Right: Notification, Role Switcher & Author Profile Menu -->
    <div class="flex items-center gap-3 shrink-0">
        <x-role-switcher current-role="author" />

        <!-- Notification Bell -->
        <a href="#notifikasi" class="relative p-2 min-w-11 min-h-11 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition duration-150" aria-label="Notifikasi baru">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if(($profile['unread_notifications'] ?? 0) > 0)
                <span class="absolute top-2 right-2 w-2 h-2 bg-red-600 rounded-full ring-2 ring-white"></span>
            @endif
        </a>

        <!-- User Profile Pill -->
        <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-gray-200">
            @php
                $user = auth()->user();
                $displayName = $user?->name ?? ($profile['title'] ?? ($profile['name'] ?? 'Penulis'));
                $displaySub = $user?->email ?? ($profile['affiliation'] ?? '');
                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $displayName) ?: 'AU', 0, 2));
            @endphp
            <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shrink-0" aria-hidden="true">
                {{ $initials }}
            </div>
            <div class="hidden sm:flex flex-col text-left leading-tight">
                <span class="text-xs font-bold text-gray-800 truncate max-w-45" title="{{ $displayName }}">{{ $displayName }}</span>
                <span class="text-xs text-gray-500 truncate max-w-45" title="{{ $displaySub }}">{{ $displaySub }}</span>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="inline ml-1">
                @csrf
                <button type="submit" class="p-2 min-w-11 min-h-11 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Keluar dari akun" aria-label="Keluar dari akun">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>
