@props([
    'activeCount' => null,
])

@php
    $isDashboardActive = request()->routeIs('reviewer.dashboard');
    $isAssignmentsActive = request()->routeIs('reviewer.assignments.*');
    $isGuidelinesActive = request()->routeIs('reviewer.guidelines');
@endphp

<x-portal.sidebar
    id="reviewer-sidebar"
    role-label="Mitra Bestari"
    :home-route="route('reviewer.dashboard')"
    :guideline-url="route('reviewer.guidelines')"
    guideline-label="Pedoman Telaah"
    :guideline-active="$isGuidelinesActive"
>
    <!-- 1. Dashboard -->
    <a
        href="{{ route('reviewer.dashboard') }}"
        title="Dashboard"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isDashboardActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isDashboardActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
        <span class="truncate sidebar-label">Dashboard</span>
    </a>

    <!-- 2. Tugas Penelaahan Ilmiah -->
    <a
        href="{{ route('reviewer.assignments.index') }}"
        title="Tugas Penelaahan Ilmiah"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isAssignmentsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isAssignmentsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <span class="truncate sidebar-label">Tugas Penelaahan</span>
    </a>
</x-portal.sidebar>
