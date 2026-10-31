@props([
    'title' => 'Penugasan Galai & Tata Letak',
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Rahmat Hidayat, S.Kom.',
        'affiliation' => 'Bagian Publikasi Ilmiah BRIDA Makassar',
        'initials' => 'RH',
    ],
])

<header class="h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 sticky top-0 z-20">
    <!-- Left: Mobile Menu & Breadcrumbs -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Toggle Button -->
        <button id="open-sidebar-btn" type="button" class="lg:hidden p-2 min-w-11 min-h-11 flex items-center justify-center rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-hidden" aria-label="Buka navigasi">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Clean Breadcrumb -->
        <nav class="hidden sm:flex items-center gap-2 text-xs font-medium text-gray-500 truncate" aria-label="Breadcrumb">
            <a href="{{ route('layouteditor.submissions.index') }}" class="text-gray-400 hover:text-gray-600 transition duration-150" title="Penugasan Galai">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('layouteditor.submissions.index') }}" class="text-gray-600 hover:text-gray-900 transition">Layout Editor</a>
            @if($title && $title !== 'Penugasan Galai & Tata Letak')
                <span class="text-gray-300">/</span>
                <span class="text-gray-900 font-semibold truncate">{{ $title }}</span>
            @endif
        </nav>
    </div>

    <!-- Right: Quick Actions & User Profile -->
    <div class="flex items-center gap-3 shrink-0">
        <x-role-switcher current-role="layout_editor" />

        <!-- Pintasan Cepat ke Panduan Galai (Icon-only pada mobile/layar sempit) -->
        <a 
            href="{{ route('layouteditor.guidelines') }}"
            class="inline-flex items-center justify-center gap-1.5 p-2 lg:px-3 lg:py-1.5 min-w-9 min-h-9 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition"
            title="Lihat Format Standar Galai PDF"
            aria-label="Panduan Galai"
        >
            <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="hidden lg:inline">Panduan Galai</span>
        </a>

        <!-- User Profile Pill -->
        <div class="flex items-center gap-2.5 pl-2 sm:pl-3 border-l border-gray-200">
            @php
                $displayName = auth()->user()?->name ?? $user['name'];
                $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $displayName) ?: 'RH', 0, 2));
            @endphp
            <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shrink-0 shadow-2xs" aria-hidden="true">
                {{ $initials }}
            </div>
            <div class="hidden sm:flex flex-col text-left leading-tight">
                <span class="text-xs font-bold text-gray-800 truncate max-w-44" title="{{ $displayName }}">{{ $displayName }}</span>
                <span class="text-[11px] text-gray-500 truncate">Layout Editor</span>
            </div>

            <!-- Quick Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="inline ml-1">
                @csrf
                <button type="submit" class="p-1.5 flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Keluar dari akun" aria-label="Keluar dari akun">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>
