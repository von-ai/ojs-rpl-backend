@props([
    'title' => 'Penugasan Bukti Baca (Proofreading)',
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Dra. Sitti Aminah, M.Hum.',
        'affiliation' => 'Tim Redaksi & Bukti Baca BRIDA Makassar',
        'initials' => 'SA',
    ],
])

<x-portal.header
    role-code="proofreader"
    portal-title="Proofreader"
    :home-route="route('proofreader.submissions.index')"
    :page-title="($title && $title !== 'Penugasan Bukti Baca (Proofreading)') ? $title : null"
    :default-name="$user['name'] ?? 'Dra. Sitti Aminah, M.Hum.'"
    :default-sub="$user['affiliation'] ?? 'Proofreader'"
    :default-initials="$user['initials'] ?? 'SA'"
>
    <!-- Pintasan Cepat ke Panduan Bukti Baca (Icon-only) -->
    <a
        href="{{ route('proofreader.guidelines') }}"
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-xs font-medium text-gray-700 bg-surface-subtle hover:bg-gray-100 border border-gray-200 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
        title="Panduan Bukti Baca"
        aria-label="Panduan Bukti Baca"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
    </a>
</x-portal.header>
