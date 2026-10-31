@props([
    'activeCount' => null,
])

@php
    $isDashboardActive = request()->routeIs('section-editor.dashboard');
    $isQueueActive = request()->routeIs('section-editor.submissions.*');
    $isGuidelinesActive = request()->routeIs('section-editor.guidelines');

    $badgeCount = $activeCount ?? \App\ViewModels\SectionEditorSubmissionsViewModel::getSidebarBadgeCount();
@endphp

<x-portal.sidebar
    id="section-editor-sidebar"
    role-label="Editor Bagian"
    :home-route="route('section-editor.dashboard')"
    :guideline-url="route('section-editor.guidelines')"
    guideline-label="Pedoman Telaah"
    :guideline-active="$isGuidelinesActive"
>
    <!-- 1. Dashboard -->
    <a
        href="{{ route('section-editor.dashboard') }}"
        title="Dashboard"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isDashboardActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isDashboardActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        <span class="truncate sidebar-label">Dashboard</span>
    </a>

    <!-- 2. Antrean Telaah Naskah -->
    <a
        href="{{ route('section-editor.submissions.index') }}"
        title="Antrean Telaah Naskah"
        class="group relative flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isQueueActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-5 h-5 shrink-0 {{ $isQueueActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="truncate sidebar-label">Antrean Telaah</span>
        </div>
        <span class="sidebar-badge-full inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full tabular-nums shrink-0 {{ $isQueueActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">
            {{ $badgeCount }}
        </span>
    </a>
</x-portal.sidebar>
