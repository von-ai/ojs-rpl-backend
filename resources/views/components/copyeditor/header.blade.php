@props([
    'title' => 'Penugasan Copyediting',
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Andi Kurniawan, S.Hum., M.Pd.',
        'affiliation' => 'Balai Penelitian & Pengembangan BRIDA Makassar',
        'initials' => 'AK',
        'notifications_count' => 0,
    ],
])

<x-portal.header
    role-code="copyeditor"
    portal-title="Copy Editor"
    :home-route="route('copyeditor.submissions.index')"
    :page-title="($title && $title !== 'Penugasan Copyediting') ? $title : null"
    :default-name="$user['name'] ?? 'Andi Kurniawan, S.Hum., M.Pd.'"
    default-sub="Copy Editor"
    :default-initials="$user['initials'] ?? 'AK'"
>
    <!-- Buka Panduan Selingkung Cepat (Icon-only pada mobile/layar sempit) -->
    <button
        type="button"
        onclick="openStyleGuideModal()"
        class="inline-flex items-center justify-center gap-1.5 p-2 lg:px-3 lg:py-1.5 min-w-9 min-h-9 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition cursor-pointer"
        data-tooltip="Panduan Selingkung"
        title="Panduan Selingkung"
        aria-label="Panduan Selingkung"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <span class="hidden lg:inline">Panduan Selingkung</span>
    </button>
</x-portal.header>
