@php
    $subnavItems = [
        [
            'label' => 'Tentang Jurnal',
            'desc' => 'Profil, fokus, dan cakupan ilmiah',
            'route' => 'journal.about',
            'active' => request()->routeIs('journal.about'),
            'icon' => 'book',
        ],
        [
            'label' => 'Dewan Redaksi',
            'desc' => 'Editorial team & mitra bestari',
            'route' => 'journal.about.editorial-team',
            'active' => request()->routeIs('journal.about.editorial-team'),
            'icon' => 'users',
        ],
        [
            'label' => 'Kebijakan & Etika',
            'desc' => 'Etika publikasi & peer review',
            'route' => 'journal.about.policies',
            'active' => request()->routeIs('journal.about.policies'),
            'icon' => 'shield',
        ],
        [
            'label' => 'Panduan Penulis',
            'desc' => 'Petunjuk & format manuskrip',
            'route' => 'journal.about.submissions',
            'active' => request()->routeIs('journal.about.submissions'),
            'icon' => 'document',
        ],
        [
            'label' => 'Kontak Redaksi',
            'desc' => 'Sekretariat & korespondensi',
            'route' => 'journal.about.contact',
            'active' => request()->routeIs('journal.about.contact'),
            'icon' => 'mail',
        ],
    ];

    $activeItem = collect($subnavItems)->firstWhere('active', true) ?? $subnavItems[0];
@endphp

<!-- 1. Tampilan Desktop & Tablet (Tab Horisontal Rapi & Elegan) -->
<div class="hidden sm:block border-b border-gray-200 mb-6 overflow-x-auto no-scrollbar">
    <nav class="flex items-center gap-1.5 text-xs font-semibold">
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

<!-- 2. Tampilan Mobile (Sticky / Fixed Bar + Daftar Menu Lengkap) -->
<div id="about-subnav-mobile-container" class="sm:hidden sticky top-[73px] z-30 -mx-4 sm:mx-0 -mt-4 sm:mt-0 mb-5 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-xs px-3.5 py-2.5 rounded-t-xl transition-all">
    <!-- Bar Menu Utama Mobile (Ringkas & Selalu Menempel saat Scroll) -->
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 min-w-0">
            <span class="w-7 h-7 rounded-lg bg-red-50 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </span>
            <div class="flex flex-col min-w-0">
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Halaman Informasi</span>
                <span class="text-xs font-bold text-gray-900 truncate mt-0.5">{{ $activeItem['label'] }}</span>
            </div>
        </div>

        <button
            type="button"
            onclick="window.toggleAboutSubnavMenu()"
            id="about-subnav-toggle-btn"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold text-brand-maroon bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition-all cursor-pointer active:scale-95 shrink-0"
            aria-expanded="false"
            aria-controls="about-subnav-menu-panel"
        >
            <span>Daftar Menu</span>
            <svg id="about-subnav-chevron" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    <!-- Panel Dropdown: Daftar Menu Vertikal Lengkap -->
    <div
        id="about-subnav-menu-panel"
        class="hidden mt-2.5 pt-2 border-t border-gray-100 space-y-1.5 transition-all max-h-[65vh] overflow-y-auto"
    >
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block px-1 mb-1">
            Pilih Menu Halaman:
        </span>
        @foreach($subnavItems as $item)
            <a
                href="{{ route($item['route']) }}"
                class="flex items-center justify-between p-2.5 rounded-lg text-xs transition-colors {{ $item['active'] ? 'bg-brand-maroon text-white font-bold' : 'text-gray-700 hover:bg-gray-100 bg-gray-50/80 border border-gray-200/60 font-medium' }}"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-md {{ $item['active'] ? 'bg-white/20 text-white' : 'bg-white text-gray-500 border border-gray-200' }} flex items-center justify-center shrink-0">
                        @if($item['icon'] === 'book')
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        @elseif($item['icon'] === 'users')
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        @elseif($item['icon'] === 'shield')
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        @elseif($item['icon'] === 'document')
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex flex-col min-w-0 text-left">
                        <span class="truncate leading-tight">{{ $item['label'] }}</span>
                        <span class="text-[10px] font-normal {{ $item['active'] ? 'text-white/80' : 'text-gray-500' }} truncate mt-0.5">{{ $item['desc'] }}</span>
                    </div>
                </div>
                @if($item['active'])
                    <span class="text-[9.5px] font-bold uppercase bg-white/20 text-white px-1.5 py-0.5 rounded shrink-0">Aktif</span>
                @else
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>

<script>
    window.toggleAboutSubnavMenu = function() {
        const panel = document.getElementById('about-subnav-menu-panel');
        const chevron = document.getElementById('about-subnav-chevron');
        const btn = document.getElementById('about-subnav-toggle-btn');
        if (!panel) return;
        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
            if (btn) btn.setAttribute('aria-expanded', 'true');
        } else {
            panel.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    };

    document.addEventListener('click', function(e) {
        const container = document.getElementById('about-subnav-mobile-container');
        const panel = document.getElementById('about-subnav-menu-panel');
        const chevron = document.getElementById('about-subnav-chevron');
        if (container && panel && !container.contains(e.target) && !panel.classList.contains('hidden')) {
            panel.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });
</script>
