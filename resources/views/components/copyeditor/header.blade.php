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
        title="Lihat Panduan Gaya Selingkung Jurnal"
        aria-label="Panduan Selingkung"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="hidden lg:inline">Panduan Selingkung</span>
    </button>
</x-portal.header>
