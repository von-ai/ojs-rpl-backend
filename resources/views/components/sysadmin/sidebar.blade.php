@php
    $isJournalsActive = request()->routeIs('sysadmin.journals.*') || request()->routeIs('sysadmin.dashboard');
    $isUsersActive = request()->routeIs('sysadmin.users.*');
    $isSettingsActive = request()->routeIs('sysadmin.settings.*');
    $isAuditActive = request()->routeIs('sysadmin.audit-logs.*');
@endphp

<x-portal.sidebar
    id="sysadmin-sidebar"
    role-label="System Admin"
    :home-route="route('sysadmin.journals.index')"
>
    <!-- 1. Jurnal Ilmiah -->
    <a
        href="{{ route('sysadmin.journals.index') }}"
        title="Kelola Instansi Jurnal Ilmiah"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isJournalsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isJournalsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <span class="truncate sidebar-label">Jurnal Ilmiah</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isJournalsActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            4
        </span>
    </a>

    <!-- 2. Pengguna & Hak Akses Global -->
    <a
        href="{{ route('sysadmin.users.index') }}"
        title="Direktori Pengguna & Peran Sistem"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isUsersActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isUsersActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="truncate sidebar-label">Pengguna &amp; Akses</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isUsersActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            142
        </span>
    </a>

    <!-- 3. Pengaturan Situs & Platform -->
    <a
        href="{{ route('sysadmin.settings.index') }}"
        title="Pengaturan Situs & Kesehatan Server"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isSettingsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isSettingsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="truncate sidebar-label">Pengaturan Situs</span>
    </a>

    <!-- 4. Jejak Audit & Pemantauan Sistem -->
    <a
        href="{{ route('sysadmin.audit-logs.index') }}"
        title="Log Jejak Audit Append-Only"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isAuditActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isAuditActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="truncate sidebar-label">Jejak Audit</span>
    </a>
</x-portal.sidebar>
