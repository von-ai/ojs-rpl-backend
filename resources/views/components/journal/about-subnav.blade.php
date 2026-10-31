@php
    $subnavItems = [
        ['label' => 'Tentang Jurnal', 'route' => 'journal.about', 'active' => request()->routeIs('journal.about')],
        ['label' => 'Dewan Redaksi', 'route' => 'journal.about.editorial-team', 'active' => request()->routeIs('journal.about.editorial-team')],
        ['label' => 'Kebijakan & Etika', 'route' => 'journal.about.policies', 'active' => request()->routeIs('journal.about.policies')],
        ['label' => 'Panduan Penulis', 'route' => 'journal.about.submissions', 'active' => request()->routeIs('journal.about.submissions')],
        ['label' => 'Kontak Redaksi', 'route' => 'journal.about.contact', 'active' => request()->routeIs('journal.about.contact')],
    ];
@endphp

<div class="border-b border-gray-200 mb-6 overflow-x-auto no-scrollbar">
    <nav class="flex items-center gap-2 text-xs font-semibold">
        @foreach($subnavItems as $item)
            <a 
                href="{{ route($item['route']) }}"
                class="px-4 py-2.5 border-b-2 text-nowrap transition-colors {{ $item['active'] ? 'border-brand-maroon text-brand-maroon font-bold' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
