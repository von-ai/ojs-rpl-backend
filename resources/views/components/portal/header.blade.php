@props([
    'roleCode' => 'author',
    'portalTitle' => 'Portal Jurnal',
    'homeRoute' => url('/'),
    'pageTitle' => null,
    'defaultName' => 'Pengguna SIRIDA',
    'defaultSub' => null,
    'defaultInitials' => null,
])

@php
    $authUser = auth()->user();
    $roleMeta = \App\Models\UserRole::roleMeta($roleCode);
    $displayName = $authUser?->name ?: $defaultName;
    $displaySub = $defaultSub ?: ($authUser?->email ?: $roleMeta['label']);
    $computedInitials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $displayName) ?: 'SR', 0, 2));
    $initials = $authUser ? $computedInitials : ($defaultInitials ?: $computedInitials);
    $activeJournal = ($authUser && $roleCode !== 'sysadmin') ? $authUser->activeJournal($roleCode) : null;
@endphp

<header class="h-14 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 shrink-0 sticky top-0 z-20 select-none">
    <!-- Left Section: Mobile Sidebar Trigger & Breadcrumb -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Sidebar Drawer Trigger -->
        <button
            id="open-sidebar-btn"
            type="button"
            class="lg:hidden min-w-11 min-h-11 -ml-1.5 p-2 flex items-center justify-center rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden transition shrink-0 cursor-pointer"
            aria-label="Buka menu navigasi"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Breadcrumb Context -->
        <nav class="hidden sm:flex items-center gap-2 text-xs sm:text-[13px] text-gray-600 truncate" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="text-gray-600 hover:text-brand-maroon transition shrink-0" title="Beranda Situs" aria-label="Beranda Situs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </a>
            <span class="text-gray-400" aria-hidden="true">/</span>
            <a href="{{ $homeRoute }}" class="font-semibold {{ $pageTitle ? 'text-gray-700 hover:text-brand-maroon' : 'text-gray-900' }} transition truncate" title="Halaman Utama {{ $portalTitle }}">
                {{ $portalTitle }}
            </a>
            @if($pageTitle)
                <span class="text-gray-400" aria-hidden="true">/</span>
                <span class="font-semibold text-gray-900 truncate">{{ $pageTitle }}</span>
            @endif
        </nav>

        @if($activeJournal)
            <span
                class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white border border-gray-300 text-xs font-semibold text-gray-900 max-w-sm truncate shrink shadow-2xs"
                title="Sedang berada di jurnal: {{ $activeJournal->name }}"
            >
                <span class="w-2 h-2 rounded-full bg-brand-maroon shrink-0" aria-hidden="true"></span>
                <span class="text-gray-600 font-normal shrink-0">Jurnal:</span>
                <span class="truncate">{{ $activeJournal->name }}</span>
            </span>
        @endif
    </div>

    <!-- Right Section: Role Switcher, Quick Actions & User Profile -->
    <div class="flex items-center gap-2 shrink-0">
        <!-- Kontrol Ukuran Teks Compact -->
        <button
            type="button"
            data-text-scale-btn="cycle"
            aria-pressed="false"
            title="Perbesar ukuran teks"
            aria-label="Perbesar ukuran teks"
            class="inline-flex items-center justify-center min-h-9 px-2.5 rounded-lg text-xs font-bold bg-surface-subtle hover:bg-gray-100 text-gray-800 border border-gray-200 transition cursor-pointer shrink-0"
        >
            <span data-text-scale-label>A+</span>
        </button>

        <!-- Pengalih Jurnal Multi-Journal (Standar OJS) -->
        @if($roleCode !== 'sysadmin')
            <x-journal-switcher :current-role="$roleCode" />
        @endif

        <!-- Pengalih Peran Multi-Role -->
        <x-role-switcher :current-role="$roleCode" />

        <!-- Slot Aksi Cepat Spesifik Peran (Pedoman, Lencana Status, Notifikasi) -->
        {{ $slot }}

        <div class="h-6 w-px bg-gray-200 hidden sm:block" aria-hidden="true"></div>

        <!-- User Profile Avatar, Hover Card Info & Tombol Keluar -->
        <div class="flex items-center gap-2 pl-0.5">
            <div class="relative group">
                <button
                    type="button"
                    class="w-9 h-9 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-xs uppercase tracking-wider shrink-0 shadow-2xs select-none cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/30 transition hover:ring-2 hover:ring-brand-maroon/20"
                    aria-label="{{ $displayName }}"
                    aria-haspopup="true"
                >
                    {{ $initials }}
                </button>

                <!-- Floating Hover Tooltip / Profile Card -->
                <div class="absolute right-0 top-full mt-2 w-64 p-3 bg-white border border-gray-200/90 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus-within:opacity-100 group-focus-within:visible transition-all duration-200 transform origin-top-right z-50 pointer-events-none group-hover:pointer-events-auto">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-sm uppercase tracking-wider shrink-0 shadow-xs">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0 flex-1 text-left">
                            <p class="text-xs font-bold text-gray-900 truncate" title="{{ $displayName }}">
                                {{ $displayName }}
                            </p>
                            <p class="text-[11px] text-gray-500 truncate" title="{{ $authUser?->email ?? $displaySub }}">
                                {{ $authUser?->email ?? $displaySub }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                        <span class="text-gray-500 font-medium">Peran Aktif:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md font-semibold bg-brand-maroon text-white text-[10px]">
                            {{ $roleMeta['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button
                    type="submit"
                    title="Keluar dari akun"
                    aria-label="Keluar dari akun"
                    class="min-h-9 px-2.5 py-1.5 inline-flex items-center justify-center gap-1.5 rounded-lg text-xs font-semibold text-gray-700 hover:text-red-700 bg-white hover:bg-red-50 border border-gray-200 hover:border-red-200 transition cursor-pointer"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="hidden sm:inline">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</header>

@once
<script>
(function () {
    if (window.__siridaTextScaleInitialized) return;
    window.__siridaTextScaleInitialized = true;
    var KEY = 'sirida_text_scale';
    var SCALES = ['normal', 'lg', 'xl'];
    function getSaved() { try { return localStorage.getItem(KEY) || 'normal'; } catch (e) { return 'normal'; } }
    function apply(scale) {
        var v = SCALES.indexOf(scale) !== -1 ? scale : 'normal';
        var r = document.documentElement;
        r.classList.remove('text-scale-lg', 'text-scale-xl');
        r.style.fontSize = v === 'lg' ? '112.5%' : (v === 'xl' ? '125%' : '');
        if (v === 'lg') r.classList.add('text-scale-lg');
        if (v === 'xl') r.classList.add('text-scale-xl');
        try { localStorage.setItem(KEY, v); } catch (e) {}
        document.querySelectorAll('[data-text-scale-btn]').forEach(function (btn) {
            var t = btn.getAttribute('data-text-scale-btn');
            if (t === 'cycle') {
                var lbl = v === 'normal' ? 'A+' : (v === 'lg' ? 'A++' : 'A');
                var span = btn.querySelector('[data-text-scale-label]');
                if (span) span.textContent = lbl;
                btn.setAttribute('aria-pressed', v !== 'normal' ? 'true' : 'false');
                btn.classList.toggle('bg-brand-maroon', v !== 'normal');
                btn.classList.toggle('text-white', v !== 'normal');
                btn.classList.toggle('border-brand-maroon', v !== 'normal');
                btn.classList.toggle('bg-surface-subtle', v === 'normal');
                btn.classList.toggle('text-gray-800', v === 'normal');
            } else {
                var active = t === v;
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                btn.classList.toggle('bg-brand-maroon', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('text-gray-700', !active);
            }
        });
    }
    window.siridaApplyTextScale = apply;
    apply(getSaved());
    document.addEventListener('DOMContentLoaded', function () { apply(getSaved()); });
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-text-scale-btn]');
        if (!btn) return;
        var t = btn.getAttribute('data-text-scale-btn');
        if (t === 'cycle') {
            var cur = getSaved();
            apply(SCALES[(SCALES.indexOf(cur) + 1) % SCALES.length]);
        } else {
            apply(t);
        }
    });
})();
</script>
@endonce
