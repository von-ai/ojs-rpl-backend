@props([
    'assignedCount' => null,
])

@php
    $isSubmissionsActive = request()->routeIs('layouteditor.submissions.*') || request()->routeIs('layouteditor.dashboard');
    $isGuidelinesActive = request()->routeIs('layouteditor.guidelines');
    $badgeCount = $assignedCount ?? \App\ViewModels\LayoutEditorSubmissionsViewModel::getSidebarBadgeCount();
@endphp

<x-portal.sidebar
    id="layouteditor-sidebar"
    role-label="Layout Editor"
    :home-route="route('layouteditor.submissions.index')"
    :guideline-url="route('layouteditor.guidelines')"
    guideline-label="Panduan Galai"
    :guideline-active="$isGuidelinesActive"
>
    <!-- 1. Penugasan Galai & Tata Letak -->
    <a
        href="{{ route('layouteditor.submissions.index') }}"
        title="Penugasan Galai"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isSubmissionsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isSubmissionsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <span class="truncate sidebar-label">Penugasan Galai</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isSubmissionsActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            {{ $badgeCount }}
        </span>
    </a>
</x-portal.sidebar>
