<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="application-name" content="{{ $platformSettings['site_name'] ?? config('app.name') }}">
    <meta name="description" content="{{ $platformSettings['site_description'] ?? 'Portal jurnal ilmiah BRIDA Makassar' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal System Administrator — BRIDA Kota Makassar')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <x-fonts />

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-canvas h-dvh min-h-dvh flex flex-col lg:flex-row overflow-hidden pb-[env(safe-area-inset-bottom)]">

    <!-- Sidebar Navigation Khusus System Administrator -->
    <x-sysadmin.sidebar :journals-count="$journalsCount ?? null" :users-count="$usersCount ?? null" />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- Header Topbar SysAdmin -->
        <x-sysadmin.header />

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
            @yield('content')
        </main>
    </div>

    <!-- Reusable Action Confirmation Modal -->
    <x-action-modal />

    <!-- Reusable Toast Notification System -->
    <x-toast />

    <!-- Global Custom Floating Tooltip untuk SysAdmin Portal -->
    <div
        id="sysadmin-custom-tooltip"
        role="tooltip"
        class="fixed z-[9999] pointer-events-none invisible opacity-0 scale-95 transition-all duration-150 ease-out bg-gray-900 text-white text-xs rounded-lg px-2.5 py-1.5 shadow-xl border border-gray-800 max-w-xs break-words leading-snug"
        style="top: -9999px; left: -9999px;"
    >
        <div id="sysadmin-tooltip-title" class="font-medium text-white text-[11px] whitespace-nowrap"></div>
        <div id="sysadmin-tooltip-desc" class="text-[10px] text-gray-300 mt-0.5 font-normal hidden"></div>
        <div id="sysadmin-tooltip-arrow" class="absolute w-2 h-2 bg-gray-900 rotate-45 border-r border-b border-gray-800 -bottom-1 left-1/2 -translate-x-1/2"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltip = document.getElementById('sysadmin-custom-tooltip');
            const titleEl = document.getElementById('sysadmin-tooltip-title');
            const descEl = document.getElementById('sysadmin-tooltip-desc');
            const arrowEl = document.getElementById('sysadmin-tooltip-arrow');
            if (!tooltip || !titleEl || !descEl) return;

            let activeTarget = null;

            function isTextTruncated(el) {
                return (el.scrollWidth > el.clientWidth + 1) || (el.scrollHeight > el.clientHeight + 1);
            }

            function findTooltipTarget(node) {
                if (!node || node === document.body || !(node instanceof Element)) return null;

                const explicit = node.closest('[data-tooltip]');
                if (explicit) return { el: explicit, type: 'explicit' };

                const truncated = node.closest('.truncate, .line-clamp-1, .line-clamp-2');
                if (truncated && isTextTruncated(truncated)) {
                    return { el: truncated, type: 'truncated' };
                }

                return null;
            }

            function positionTooltip(targetEl) {
                const rect = targetEl.getBoundingClientRect();
                const tipRect = tooltip.getBoundingClientRect();
                const margin = 8;

                let top = rect.top - tipRect.height - margin;
                let isBelow = false;
                if (top < 12) {
                    top = rect.bottom + margin;
                    isBelow = true;
                }

                let left = rect.left + (rect.width / 2) - (tipRect.width / 2);
                const maxLeft = window.innerWidth - tipRect.width - 12;
                if (left < 12) left = 12;
                if (left > maxLeft) left = Math.max(12, maxLeft);

                tooltip.style.top = `${Math.round(top)}px`;
                tooltip.style.left = `${Math.round(left)}px`;

                if (arrowEl) {
                    if (isBelow) {
                        arrowEl.className = 'absolute w-2 h-2 bg-gray-900 rotate-45 border-l border-t border-gray-800 -top-1 left-1/2 -translate-x-1/2';
                    } else {
                        arrowEl.className = 'absolute w-2 h-2 bg-gray-900 rotate-45 border-r border-b border-gray-800 -bottom-1 left-1/2 -translate-x-1/2';
                    }
                }
            }

            function showTooltip(targetObj) {
                const el = targetObj.el;
                let titleText = '';
                let descText = '';

                if (targetObj.type === 'explicit') {
                    titleText = (el.getAttribute('data-tooltip') || '').trim();
                    descText = (el.getAttribute('data-tooltip-desc') || '').trim();
                } else {
                    titleText = (el.getAttribute('title') || el.textContent || '').trim();
                }

                if (el.hasAttribute('title')) {
                    el.setAttribute('data-original-title', el.getAttribute('title'));
                    el.removeAttribute('title');
                }

                if (!titleText) return;

                titleEl.textContent = titleText;
                if (descText) {
                    descEl.textContent = descText;
                    descEl.classList.remove('hidden');
                } else {
                    descEl.textContent = '';
                    descEl.classList.add('hidden');
                }

                positionTooltip(el);

                tooltip.classList.remove('invisible', 'opacity-0', 'scale-95');
                tooltip.classList.add('visible', 'opacity-100', 'scale-100');

                requestAnimationFrame(() => {
                    positionTooltip(el);
                });
            }

            function hideTooltip() {
                if (activeTarget && activeTarget.hasAttribute('data-original-title')) {
                    activeTarget.setAttribute('title', activeTarget.getAttribute('data-original-title'));
                    activeTarget.removeAttribute('data-original-title');
                }
                activeTarget = null;
                tooltip.classList.remove('visible', 'opacity-100', 'scale-100');
                tooltip.classList.add('invisible', 'opacity-0', 'scale-95');
            }

            document.addEventListener('mouseover', function (e) {
                const found = findTooltipTarget(e.target);
                if (!found) {
                    if (activeTarget) hideTooltip();
                    return;
                }
                if (activeTarget !== found.el) {
                    activeTarget = found.el;
                    showTooltip(found);
                }
            }, true);

            document.addEventListener('mouseout', function (e) {
                if (!activeTarget) return;
                const related = e.relatedTarget;
                if (related instanceof Node && activeTarget.contains(related)) {
                    return;
                }
                hideTooltip();
            }, true);

            window.addEventListener('scroll', hideTooltip, true);
            document.addEventListener('mousedown', hideTooltip);
        });
    </script>

    @stack('scripts')
</body>
</html>
