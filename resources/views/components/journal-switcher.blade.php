@props(['currentRole' => null])

@php
    $user = auth()->user();
    $activeRole = ($user && is_string($currentRole) && $user->hasRole($currentRole))
        ? $currentRole
        : ($user ? $user->activeRoleCode() : 'author');
    $activeJournal = $user ? $user->activeJournal($activeRole) : null;
    $journals = $user ? $user->availableJournalsMeta($activeRole) : [];
    $journalsCount = count($journals);
@endphp

@if($user && $journalsCount > 0 && $activeRole !== 'sysadmin')
<div class="relative inline-block text-left" data-journal-switcher>
    <!-- Tombol Pemicu Pengalih Jurnal (Icon-only pada mobile, teks penuh pada layar lebar) -->
    <button
        type="button"
        data-journal-switcher-trigger
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="Beralih konteks jurnal aktif (Saat ini: {{ $activeJournal?->name ?? 'Jurnal' }})"
        title="Jurnal Aktif: {{ $activeJournal?->name ?? 'Pilih Jurnal' }} — Klik untuk beralih jurnal"
        class="inline-flex items-center justify-center gap-2 p-2 md:px-3 md:py-1.5 min-w-9 min-h-9 rounded-lg text-xs font-semibold bg-surface-subtle hover:bg-gray-100 text-gray-800 border border-gray-200 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
        </svg>
        <span class="hidden lg:inline text-gray-500 font-medium">Jurnal:</span>
        <span class="hidden md:inline font-bold text-gray-900 truncate max-w-36 lg:max-w-44">
            {{ $activeJournal?->initials ?: ($activeJournal?->name ?? 'Jurnal') }}
        </span>
        @if($journalsCount > 1)
            <span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded bg-brand-maroon/10 text-brand-maroon text-[10px] font-bold leading-none shrink-0">
                {{ $journalsCount }}
            </span>
        @endif
        <svg data-journal-switcher-chevron class="hidden md:block w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Panel Dropdown Daftar Jurnal -->
    <div
        data-journal-switcher-menu
        role="menu"
        aria-orientation="vertical"
        class="hidden absolute right-0 mt-2 w-80 sm:w-96 max-w-[calc(100vw-2rem)] rounded-xl bg-white border border-gray-200 shadow-xl z-50 overflow-hidden"
    >
        <!-- Header Panel -->
        <div class="px-4 py-3 bg-surface-subtle border-b border-gray-200 flex items-center justify-between gap-2">
            <div class="min-w-0 text-left">
                <p class="text-xs font-bold text-gray-900 truncate">Beralih Konteks Jurnal</p>
                <p class="text-[11px] text-gray-500 truncate">Pilih jurnal aktif untuk ruang kerja portal</p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-gray-700 border border-gray-200 shrink-0">
                {{ $journalsCount }} Jurnal
            </span>
        </div>

        <!-- Daftar Jurnal yang Dapat Diakses Pengguna -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            @foreach($journals as $journal)
                @php
                    $isCurrent = $activeJournal && ((string) $journal['id'] === (string) $activeJournal->id);
                @endphp
                <form method="POST" action="{{ route('journal.switch') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="journal_id" value="{{ $journal['id'] }}">
                    <input type="hidden" name="current_role" value="{{ $activeRole }}">
                    <button
                        type="submit"
                        role="menuitem"
                        class="w-full px-4 py-3 text-left flex items-start justify-between gap-3 transition cursor-pointer group {{ $isCurrent ? 'bg-red-50/50 hover:bg-red-50/80' : 'hover:bg-gray-50' }}"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold {{ $isCurrent ? 'text-brand-maroon' : 'text-gray-900 group-hover:text-brand-maroon' }} transition-colors">
                                    {{ $journal['name'] }}
                                </span>
                                @if($isCurrent)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-maroon text-white leading-none">
                                        Aktif
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-1 text-[11px] text-gray-500 flex-wrap">
                                <span class="font-medium text-gray-700">{{ $journal['initials'] }}</span>
                                <span class="text-gray-300">•</span>
                                <span class="truncate">Peran: {{ $journal['roles_summary'] }}</span>
                            </div>
                        </div>

                        <div class="shrink-0 mt-0.5">
                            @if($isCurrent)
                                <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-brand-maroon group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            @endif
                        </div>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>

@once
<script>
    (function () {
        if (window.__siridaJournalSwitcherInitialized) {
            return;
        }
        window.__siridaJournalSwitcherInitialized = true;

        function closeAllJournalSwitchers(exceptContainer) {
            document.querySelectorAll('[data-journal-switcher]').forEach(function (container) {
                if (container === exceptContainer) {
                    return;
                }
                const menu = container.querySelector('[data-journal-switcher-menu]');
                const trigger = container.querySelector('[data-journal-switcher-trigger]');
                const chevron = container.querySelector('[data-journal-switcher-chevron]');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
                if (chevron) {
                    chevron.classList.remove('rotate-180');
                }
            });
        }

        // Tutup switcher peran jika switcher jurnal dibuka, dan sebaliknya
        function closeRoleSwitchers() {
            document.querySelectorAll('[data-role-switcher]').forEach(function (container) {
                const menu = container.querySelector('[data-role-switcher-menu]');
                const trigger = container.querySelector('[data-role-switcher-trigger]');
                const chevron = container.querySelector('[data-role-switcher-chevron]');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
                if (trigger) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
                if (chevron) {
                    chevron.classList.remove('rotate-180');
                }
            });
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-journal-switcher-trigger]');
            if (trigger) {
                event.preventDefault();
                event.stopPropagation();
                const container = trigger.closest('[data-journal-switcher]');
                const menu = container ? container.querySelector('[data-journal-switcher-menu]') : null;
                const chevron = container ? container.querySelector('[data-journal-switcher-chevron]') : null;
                if (!menu) {
                    return;
                }
                const isHidden = menu.classList.contains('hidden');
                closeAllJournalSwitchers(container);
                closeRoleSwitchers();
                if (isHidden) {
                    menu.classList.remove('hidden');
                    trigger.setAttribute('aria-expanded', 'true');
                    if (chevron) {
                        chevron.classList.add('rotate-180');
                    }
                } else {
                    menu.classList.add('hidden');
                    trigger.setAttribute('aria-expanded', 'false');
                    if (chevron) {
                        chevron.classList.remove('rotate-180');
                    }
                }
                return;
            }

            if (!event.target.closest('[data-journal-switcher-menu]')) {
                closeAllJournalSwitchers(null);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllJournalSwitchers(null);
            }
        });
    })();
</script>
@endonce
@endif
