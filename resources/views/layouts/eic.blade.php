<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Pemimpin Redaksi — SIRIDA Kota Makassar')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-canvas h-dvh min-h-dvh flex flex-col lg:flex-row overflow-hidden pb-[env(safe-area-inset-bottom)]">

    <!-- Sidebar Navigasi Pemimpin Redaksi -->
    <x-eic.sidebar />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        
        <!-- Header Topbar Pemimpin Redaksi -->
        <x-eic.header />

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
            @yield('content')
        </main>
    </div>

    <!-- Reusable Action Confirmation Modal -->
    <x-action-modal />

    <!-- Reusable Toast Notification System -->
    <x-toast />

    <!-- Global Custom Floating Tooltip untuk Teks Terpotong & Header Detail -->
    <div 
        id="eic-custom-tooltip" 
        role="tooltip"
        class="fixed z-100 pointer-events-none invisible opacity-0 scale-95 transition-all duration-150 ease-out bg-gray-900 text-white text-xs rounded-lg px-3 py-2 shadow-lg border border-gray-800 max-w-xs break-words leading-snug"
    >
        <div id="eic-tooltip-title" class="font-semibold text-white"></div>
        <div id="eic-tooltip-desc" class="text-[11px] text-gray-300 mt-0.5 font-normal hidden"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltip = document.getElementById('eic-custom-tooltip');
            const titleEl = document.getElementById('eic-tooltip-title');
            const descEl = document.getElementById('eic-tooltip-desc');
            if (!tooltip || !titleEl || !descEl) return;

            let activeTarget = null;

            function isTextTruncated(el) {
                return (el.scrollWidth > el.clientWidth + 1) || (el.scrollHeight > el.clientHeight + 1);
            }

            function findTooltipTarget(node) {
                if (!node || node === document.body || !(node instanceof HTMLElement)) return null;

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
                if (top < 12) {
                    top = rect.bottom + margin;
                }

                let left = rect.left + (rect.width / 2) - (tipRect.width / 2);
                const maxLeft = window.innerWidth - tipRect.width - 12;
                if (left < 12) left = 12;
                if (left > maxLeft) left = Math.max(12, maxLeft);

                tooltip.style.top = `${Math.round(top)}px`;
                tooltip.style.left = `${Math.round(left)}px`;
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
                    if (el.hasAttribute('title')) {
                        el.setAttribute('data-original-title', el.getAttribute('title'));
                        el.removeAttribute('title');
                    }
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

                tooltip.classList.remove('invisible', 'opacity-0', 'scale-95');
                tooltip.classList.add('visible', 'opacity-100', 'scale-100');

                positionTooltip(el);
            }

            function hideTooltip() {
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
            });

            document.addEventListener('mouseout', function (e) {
                if (!activeTarget) return;
                const related = e.relatedTarget;
                if (!related || !activeTarget.contains(related)) {
                    hideTooltip();
                }
            });

            window.addEventListener('scroll', hideTooltip, true);
            document.addEventListener('mousedown', hideTooltip);
        });
    </script>

    @stack('scripts')
</body>
</html>
