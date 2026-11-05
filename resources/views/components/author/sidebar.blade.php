@php
    $isSubmissionsActive = request()->routeIs('author.submissions.index') || request()->routeIs('author.submissions.show') || request()->routeIs('author.submissions.export');
    $isCreateActive = request()->routeIs('author.submissions.create');
@endphp

<x-portal.sidebar
    id="author-sidebar"
    role-label="Portal Penulis"
    :home-route="route('author.submissions.index')"
    guideline-url="#bantuan"
    guideline-label="Panduan Penulis"
>
    <!-- 1. Naskah Saya -->
    <a
        href="{{ route('author.submissions.index') }}"
        title="Naskah Saya"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isSubmissionsActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isSubmissionsActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="truncate sidebar-label">Naskah Saya</span>
    </a>

    <!-- 2. Ajukan Naskah Baru -->
    <a
        href="{{ route('author.submissions.create') }}"
        title="Ajukan Naskah Baru"
        class="group relative flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition duration-150 sidebar-nav-item {{ $isCreateActive ? 'bg-brand-maroon text-white font-semibold shadow-2xs' : 'font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100/80' }}"
    >
        <svg class="w-5 h-5 shrink-0 {{ $isCreateActive ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
        </svg>
        <span class="truncate sidebar-label">Ajukan Naskah</span>
    </a>
</x-portal.sidebar>
