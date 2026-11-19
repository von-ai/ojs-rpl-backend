@props([
    'title' => 'Penugasan Galai & Tata Letak',
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Rahmat Hidayat, S.Kom.',
        'affiliation' => 'Bagian Publikasi Ilmiah BRIDA Makassar',
        'initials' => 'RH',
    ],
])

<x-portal.header
    role-code="layout_editor"
    portal-title="Layout Editor"
    :home-route="route('layouteditor.submissions.index')"
    :page-title="($title && $title !== 'Penugasan Galai & Tata Letak') ? $title : null"
    :default-name="$user['name'] ?? 'Rahmat Hidayat, S.Kom.'"
    default-sub="Layout Editor"
    :default-initials="$user['initials'] ?? 'RH'"
>
    <!-- Pintasan Cepat ke Panduan Galai (Icon-only) -->
    <a
        href="{{ route('layouteditor.guidelines') }}"
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-xs font-medium text-gray-700 bg-surface-subtle hover:bg-gray-100 border border-gray-200 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
        title="Panduan Galai"
        aria-label="Panduan Galai"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
    </a>
</x-portal.header>
