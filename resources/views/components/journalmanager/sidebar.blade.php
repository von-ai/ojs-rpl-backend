@props([
    'issuesCount' => null,
    'usersCount' => null,
])

@php
    $isIssuesActive = request()->routeIs('journalmanager.issues.*');
    $isUsersActive = request()->routeIs('journalmanager.users.*');
    $isSectionsActive = request()->routeIs('journalmanager.sections.*');
    $isSettingsActive = request()->routeIs('journalmanager.settings.*');

    $activeIssuesCount = $issuesCount ?? \App\ViewModels\JournalManagerIssuesViewModel::getSidebarBadgeCount();
    $activeUsersCount = $usersCount ?? \App\ViewModels\JournalManagerUsersViewModel::getSidebarBadgeCount();
@endphp

<x-portal.sidebar
    id="journalmanager-sidebar"
    role-label="Journal Manager"
    :home-route="route('journalmanager.issues.index')"
>
    <!-- 1. Edisi & Terbitan -->
    <a
        href="{{ route('journalmanager.issues.index') }}"
        title="Edisi & Terbitan"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isIssuesActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isIssuesActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <span class="truncate sidebar-label">Edisi & Terbitan</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isIssuesActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            {{ $activeIssuesCount }}
        </span>
    </a>

    <!-- 2. Pengguna & Tim Redaksi -->
    <a
        href="{{ route('journalmanager.users.index') }}"
        title="Pengguna & Tim Redaksi"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isUsersActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isUsersActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="truncate sidebar-label">Pengguna & Redaksi</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isUsersActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            {{ $activeUsersCount }}
        </span>
    </a>

    <!-- 3. Bidang Naskah -->
    <a
        href="{{ route('journalmanager.sections.index') }}"
        title="Bidang Naskah / Seksi"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isSectionsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isSectionsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        <span class="truncate sidebar-label">Bidang Naskah</span>
    </a>

    <!-- 4. Pengaturan Jurnal -->
    <a
        href="{{ route('journalmanager.settings.index') }}"
        title="Pengaturan Jurnal & Kebijakan"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isSettingsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isSettingsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="truncate sidebar-label">Pengaturan Jurnal</span>
    </a>
</x-portal.sidebar>
