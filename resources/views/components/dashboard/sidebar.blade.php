@props(['userProfile' => null])

<aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between shrink-0 min-h-screen">
    <div class="p-4 space-y-6">
        <!-- Logo & Title -->
        <a href="/" class="flex items-center gap-3 px-2 group">
            <img src="{{ asset('images/brida-logo.png') }}" alt="BRIDA Logo" class="w-8 h-8 object-contain shrink-0 transition-transform group-hover:scale-105">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-gray-900 tracking-tight leading-tight uppercase">BRIDA MAKASSAR</span>
                <span class="text-[10px] font-semibold text-brand-maroon uppercase tracking-wider leading-tight">Portal Penulis</span>
            </div>
        </a>

        <!-- Primary Action CTA Button -->
        <a href="#" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-xs transition-all text-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Kirim Naskah Baru</span>
        </a>

        <!-- Main Navigation Links -->
        <nav class="space-y-1 text-xs font-semibold text-gray-600">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-red-50 text-brand-maroon font-bold transition-colors">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span>Dashboard</span>
                </div>
            </a>

            <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Naskah Saya</span>
                </div>
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded bg-sky-100 text-sky-800">3</span>
            </a>

            <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4z"/>
                    </svg>
                    <span>Pesan & Komunikasi</span>
                </div>
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-800">1</span>
            </a>

            <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span>Notifikasi</span>
                </div>
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-800">2</span>
            </a>

            <a href="#" class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Profil Akademik & ORCID</span>
                </div>
            </a>
        </nav>
    </div>

    <!-- Bottom Secondary Links -->
    <div class="p-4 border-t border-gray-200 space-y-1 text-xs font-medium text-gray-600">
        <a href="#" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Bantuan & Panduan Penulis</span>
        </a>

        <a href="/" class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            <span>Lihat Website Jurnal</span>
        </a>

        <form action="{{ route('logout') }}" method="POST" class="pt-1">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors font-semibold">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
