@props([
    'assignedCount' => null,
])

@php
    $isSubmissionsActive = request()->routeIs('copyeditor.submissions.*') || request()->routeIs('copyeditor.catalog') || request()->routeIs('copyeditor.dashboard');
    $isGuidelinesActive = request()->routeIs('copyeditor.guidelines');
    $badgeCount = $assignedCount ?? \App\ViewModels\CopyeditorSubmissionsViewModel::getSidebarBadgeCount();
@endphp

<x-portal.sidebar
    id="copyeditor-sidebar"
    role-label="Copy Editor"
    :home-route="route('copyeditor.submissions.index')"
    :guideline-url="route('copyeditor.guidelines')"
    guideline-label="Panduan Selingkung"
    :guideline-active="$isGuidelinesActive"
>
    <!-- 1. Penugasan Naskah -->
    <a
        href="{{ route('copyeditor.submissions.index') }}"
        title="Penugasan Naskah"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isSubmissionsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isSubmissionsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="truncate sidebar-label">Penugasan Naskah</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isSubmissionsActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            {{ $badgeCount }}
        </span>
    </a>
</x-portal.sidebar>
