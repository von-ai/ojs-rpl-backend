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
    <!-- Pintasan Cepat ke Panduan Galai (Icon-only pada mobile/layar sempit) -->
    <a
        href="{{ route('layouteditor.guidelines') }}"
        class="inline-flex items-center justify-center gap-1.5 p-2 lg:px-3 lg:py-1.5 min-w-9 min-h-9 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition"
        data-tooltip="Panduan Galai"
        title="Panduan Galai"
        aria-label="Panduan Galai"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <span class="hidden lg:inline">Panduan Galai</span>
    </a>
</x-portal.header>
