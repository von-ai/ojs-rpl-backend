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
    <!-- Pintasan Cepat ke Panduan Bukti Baca (Icon-only pada mobile/layar sempit) -->
    <a
        href="{{ route('proofreader.guidelines') }}"
        class="inline-flex items-center justify-center gap-1.5 p-2 lg:px-3 lg:py-1.5 min-w-9 min-h-9 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition"
        title="Lihat Standar Koreksi & Pedoman EYD"
        aria-label="Panduan Bukti Baca"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="hidden lg:inline">Panduan Bukti Baca</span>
    </a>
</x-portal.header>
