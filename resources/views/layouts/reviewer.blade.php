<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Mitra Bestari — SIRIDA Kota Makassar')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/brida-logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-canvas h-dvh min-h-dvh flex flex-col lg:flex-row overflow-hidden pb-[env(safe-area-inset-bottom)]">

    <!-- Sidebar Navigasi Mitra Bestari -->
    <x-reviewer.sidebar />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- Header Topbar Mitra Bestari -->
        <x-reviewer.header />

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
            <div class="max-w-7xl w-full mx-auto space-y-6">
                @if($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm space-y-1 shadow-2xs">
                        <div class="flex items-center gap-2 font-bold">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Terjadi kesalahan pada formulir telaah:</span>
                        </div>
                        <ul class="list-disc list-inside ml-6 text-xs text-rose-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Reusable Action Modal System -->
    <x-action-modal />

    <!-- Reusable Toast Notification System -->
    <x-toast />

    <!-- Global Custom Floating Tooltip -->
    <div 
        id="reviewer-custom-tooltip" 
        role="tooltip"
        class="fixed z-100 pointer-events-none invisible opacity-0 scale-95 transition-all duration-150 ease-out bg-gray-900 text-white text-xs rounded-lg px-3 py-2 shadow-lg border border-gray-800 max-w-xs break-words leading-snug"
    >
        <div id="reviewer-tooltip-title" class="font-semibold text-white"></div>
        <div id="reviewer-tooltip-desc" class="text-[11px] text-gray-300 mt-0.5 font-normal hidden"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltip = document.getElementById('reviewer-custom-tooltip');
            const titleEl = document.getElementById('reviewer-tooltip-title');
            const descEl = document.getElementById('reviewer-tooltip-desc');
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

                let top = rect.top - tipRect.height - 8;
                let left = rect.left + (rect.width / 2) - (tipRect.width / 2);

                if (top < 8) {
                    top = rect.bottom + 8;
                }

                const pad = 12;
                if (left < pad) left = pad;
                if (left + tipRect.width > window.innerWidth - pad) {
                    left = window.innerWidth - tipRect.width - pad;
                }

                tooltip.style.top = `${top}px`;
                tooltip.style.left = `${left}px`;
            }

            function showTooltip(target) {
                activeTarget = target.el;

                let title = '';
                let desc = '';

                if (target.type === 'explicit') {
                    title = target.el.getAttribute('data-tooltip') || '';
                    desc = target.el.getAttribute('data-tooltip-desc') || '';
                } else if (target.type === 'truncated') {
                    title = target.el.getAttribute('title') || target.el.textContent.trim();
                }

                if (!title) return;

                titleEl.textContent = title;
                if (desc) {
                    descEl.textContent = desc;
                    descEl.classList.remove('hidden');
                } else {
                    descEl.classList.add('hidden');
                }

                tooltip.classList.remove('invisible');
                tooltip.classList.remove('opacity-0', 'scale-95');
                tooltip.classList.add('opacity-100', 'scale-100');

                positionTooltip(target.el);
            }

            function hideTooltip() {
                activeTarget = null;
                tooltip.classList.remove('opacity-100', 'scale-100');
                tooltip.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    if (!activeTarget) tooltip.classList.add('invisible');
                }, 150);
            }

            document.addEventListener('mouseover', function (e) {
                const target = findTooltipTarget(e.target);
                if (target) {
                    if (target.el.hasAttribute('title')) {
                        target.el.setAttribute('data-original-title', target.el.getAttribute('title'));
                        target.el.removeAttribute('title');
                    }
                    showTooltip(target);
                } else if (activeTarget) {
                    hideTooltip();
                }
            });

            document.addEventListener('mouseout', function (e) {
                if (!activeTarget) return;
                const related = e.relatedTarget;
                if (!related || !activeTarget.contains(related)) {
                    if (activeTarget.hasAttribute('data-original-title')) {
                        activeTarget.setAttribute('title', activeTarget.getAttribute('data-original-title'));
                        activeTarget.removeAttribute('data-original-title');
                    }
                    hideTooltip();
                }
            });

            window.addEventListener('scroll', function () {
                if (activeTarget) hideTooltip();
            }, true);
        });
    </script>

    @stack('scripts')
</body>
</html>
