@props(['currentRole' => null])

@php
    $user = auth()->user();
    $roles = $user ? $user->availableRolesMeta() : [];
    $activeCode = ($user && is_string($currentRole) && $user->hasRole($currentRole))
        ? $currentRole
        : ($user ? $user->activeRoleCode() : 'author');
    $activeMeta = \App\Models\UserRole::roleMeta($activeCode);
    $rolesCount = count($roles);
@endphp

@if($user && $rolesCount > 0)
<div class="relative inline-block text-left" data-role-switcher>
    <!-- Tombol Pemicu Pengalih Peran (Icon-only pada mobile/layar sempit, penuh pada layar lebar) -->
    <button
        type="button"
        data-role-switcher-trigger
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="Beralih peran ruang kerja portal (Saat ini: {{ $activeMeta['label'] }})"
        title="Peran Aktif: {{ $activeMeta['label'] }} — Klik untuk beralih peran"
        class="inline-flex items-center justify-center gap-2 p-2 md:px-3 md:py-1.5 min-w-9 min-h-9 rounded-lg text-xs font-semibold bg-surface-subtle hover:bg-gray-100 text-gray-800 border border-gray-200 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
    >
        <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        <span class="hidden lg:inline text-gray-500 font-medium">Peran:</span>
        <span class="hidden md:inline font-bold text-gray-900 truncate max-w-36">{{ $activeMeta['label'] }}</span>
        @if($rolesCount > 1)
            <span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded bg-brand-maroon/10 text-brand-maroon text-[10px] font-bold leading-none shrink-0">
                {{ $rolesCount }}
            </span>
        @endif
        <svg data-role-switcher-chevron class="hidden md:block w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Panel Dropdown Daftar Peran -->
    <div
        data-role-switcher-menu
        role="menu"
        aria-orientation="vertical"
        class="hidden absolute right-0 mt-2 w-72 sm:w-80 max-w-[calc(100vw-2rem)] rounded-xl bg-white border border-gray-200 shadow-xl z-50 overflow-hidden"
    >
        <!-- Header Panel -->
        <div class="px-4 py-3 bg-surface-subtle border-b border-gray-200 flex items-center justify-between gap-2">
            <div class="min-w-0 text-left">
                <p class="text-xs font-bold text-gray-900 truncate">Beralih Ruang Kerja Peran</p>
                <p class="text-[11px] text-gray-500 truncate">Pilih peran aktif untuk berpindah portal</p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-gray-700 border border-gray-200 shrink-0">
                {{ $rolesCount }} Peran
            </span>
        </div>

        <!-- Daftar Peran yang Dimiliki Pengguna -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            @foreach($roles as $role)
                @php
                    $isCurrent = $role['code'] === $activeCode;
                @endphp
                <form method="POST" action="{{ route('role.switch') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="role_code" value="{{ $role['code'] }}">
                    <button
                        type="submit"
                        role="menuitem"
                        class="w-full px-4 py-3 text-left flex items-start justify-between gap-3 transition cursor-pointer group {{ $isCurrent ? 'bg-red-50/50 hover:bg-red-50/80' : 'hover:bg-gray-50' }}"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold {{ $isCurrent ? 'text-brand-maroon' : 'text-gray-900 group-hover:text-brand-maroon' }} transition-colors">
                                    {{ $role['label'] }}
                                </span>
                                @if($isCurrent)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-brand-maroon text-white leading-none">
                                        Aktif
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">
                                {{ $role['description'] }}
                            </p>
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
        if (window.__siridaRoleSwitcherInitialized) {
            return;
        }
        window.__siridaRoleSwitcherInitialized = true;

        function closeAllRoleSwitchers(exceptContainer) {
            document.querySelectorAll('[data-role-switcher]').forEach(function (container) {
                if (container === exceptContainer) {
                    return;
                }
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
            const trigger = event.target.closest('[data-role-switcher-trigger]');
            if (trigger) {
                event.preventDefault();
                event.stopPropagation();
                const container = trigger.closest('[data-role-switcher]');
                const menu = container ? container.querySelector('[data-role-switcher-menu]') : null;
                const chevron = container ? container.querySelector('[data-role-switcher-chevron]') : null;
                if (!menu) {
                    return;
                }
                const isHidden = menu.classList.contains('hidden');
                closeAllRoleSwitchers(container);
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

            if (!event.target.closest('[data-role-switcher-menu]')) {
                closeAllRoleSwitchers(null);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllRoleSwitchers(null);
            }
        });
    })();
</script>
@endonce
@endif
