@props(['userProfile' => null])

<header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 sticky top-0 z-30 flex items-center justify-between gap-4">
    <!-- Breadcrumb & Mobile Menu Toggle -->
    <div class="flex items-center gap-2 text-xs text-gray-500 font-medium">
        <a href="/" class="hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>
        <span class="text-gray-300">/</span>
        <span class="font-bold text-gray-800">Portal Penulis</span>
    </div>

    <!-- Search Input & User Quick Actions -->
    <div class="flex items-center gap-4 flex-1 max-w-xl justify-end">
        <!-- Search Input -->
        <div class="relative w-full max-w-xs">
            <input
                type="text"
                placeholder="Cari naskah, ID submisi, atau catatan editorial..."
                class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-maroon focus:border-brand-maroon transition-all"
            >
            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Notification Bell Icon -->
        <button type="button" class="p-1.5 text-gray-500 hover:text-brand-maroon hover:bg-red-50 rounded-full transition-colors relative shrink-0" title="Notifikasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="w-2 h-2 rounded-full bg-red-600 absolute top-1 right-1"></span>
        </button>

        <!-- User Profile Pill -->
        <div class="flex items-center gap-2.5 pl-2 border-l border-gray-200 shrink-0">
            <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                AI
            </div>
            <div class="hidden sm:flex flex-col text-xs">
                <span class="font-bold text-gray-900 leading-tight">
                    {{ $userProfile['name'] ?? 'Dr. Andi Muhammad Ilyas, M.Si.' }}
                </span>
                <span class="text-[10px] text-gray-500 leading-tight">
                    Afiliasi: {{ $userProfile['affiliation'] ?? 'Universitas Hasanuddin' }}
                </span>
            </div>
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
</header>
