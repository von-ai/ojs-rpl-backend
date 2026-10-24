{{-- resources/views/components/journalmanager/header.blade.php --}}
<header class="h-16 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0 select-none z-30">
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Open Sidebar Button -->
        <button 
            id="open-sidebar-btn" 
            type="button" 
            class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden transition shrink-0" 
            aria-label="Buka menu navigasi"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Breadcrumb / Lokasi Halaman -->
        <nav class="flex items-center gap-1.5 text-xs text-gray-500 truncate" aria-label="Breadcrumb">
            <span class="font-semibold text-brand-maroon uppercase tracking-wider text-[11px] shrink-0">BRIDA MAKASSAR</span>
            <span class="text-gray-300">/</span>
            <span class="font-medium text-gray-700 truncate">Portal Journal Manager</span>
        </nav>
    </div>

    <!-- Header Actions -->
    <div class="flex items-center gap-3 shrink-0">
        <!-- Badge Context Jurnal Aktif -->
        <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Jurnal Riset Daerah (Vol. 6 Aktif)</span>
        </div>

        <!-- Profil Pengguna Ringkas -->
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shadow-2xs">
                {{ strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', auth()->user()?->name ?? 'Andi Yusuf') ?: 'AY', 0, 2)) }}
            </div>
            <div class="hidden md:flex flex-col text-right">
                <span class="text-xs font-bold text-gray-800 leading-tight">
                    {{ auth()->user()?->name ?? 'Andi M. Yusuf, M.Si.' }}
                </span>
                <span class="text-[10px] text-gray-500 leading-tight">Journal Manager</span>
            </div>
        </div>
    </div>
</header>
