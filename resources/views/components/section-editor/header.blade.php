<x-portal.header
    role-code="section_editor"
    portal-title="Portal Editor Bagian"
    :home-route="route('section-editor.dashboard')"
    :default-name="auth()->user()?->name ?? 'Editor Bagian'"
    :default-sub="auth()->user()?->email ?? 'Editor Bagian'"
    default-initials="SE"
>
    <!-- Pintasan Cepat Pedoman Telaah (Icon-only pada layar mobile) -->
    <a
        href="{{ route('section-editor.guidelines') }}"
        class="inline-flex items-center justify-center gap-1.5 p-2 lg:px-3 lg:py-1.5 min-w-9 min-h-9 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition"
        data-tooltip="Pedoman Telaah"
        title="Pedoman Telaah"
        aria-label="Pedoman Telaah"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <span class="hidden lg:inline">Pedoman Telaah</span>
    </a>
</x-portal.header>
