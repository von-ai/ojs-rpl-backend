<div 
    id="toast-notification-container" 
    class="fixed top-4 right-4 sm:top-5 sm:right-5 z-100 flex flex-col gap-2.5 pointer-events-none max-w-sm sm:max-w-md w-full"
    aria-live="polite" 
    role="region" 
    aria-label="Pemberitahuan Sistem"
></div>

<script>
(function() {
    'use strict';

    const container = document.getElementById('toast-notification-container');

    const TOAST_ICONS = {
        warning: `
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
        `,
        error: `
            <svg class="w-5 h-5 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="15" y1="9" x2="9" y2="15" />
                <line x1="9" y1="9" x2="15" y2="15" />
            </svg>
        `,
        success: `
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        `,
        info: `
            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
            </svg>
        `
    };

    const TOAST_STYLES = {
        warning: {
            card: 'bg-white border-gray-200 border-l-4 border-l-amber-600 text-gray-900 shadow-lg',
            badge: 'bg-transparent',
            title: 'text-gray-900 font-semibold',
            message: 'text-gray-600',
            closeBtn: 'text-gray-400 hover:text-gray-800 hover:bg-gray-100',
            defaultTitle: 'Peringatan'
        },
        error: {
            card: 'bg-white border-gray-200 border-l-4 border-l-brand-maroon text-gray-900 shadow-lg',
            badge: 'bg-transparent',
            title: 'text-gray-900 font-semibold',
            message: 'text-gray-600',
            closeBtn: 'text-gray-400 hover:text-gray-800 hover:bg-gray-100',
            defaultTitle: 'Terjadi Kesalahan'
        },
        success: {
            card: 'bg-white border-gray-200 border-l-4 border-l-emerald-600 text-gray-900 shadow-lg',
            badge: 'bg-transparent',
            title: 'text-gray-900 font-semibold',
            message: 'text-gray-600',
            closeBtn: 'text-gray-400 hover:text-gray-800 hover:bg-gray-100',
            defaultTitle: 'Berhasil'
        },
        info: {
            card: 'bg-white border-gray-200 border-l-4 border-l-blue-600 text-gray-900 shadow-lg',
            badge: 'bg-transparent',
            title: 'text-gray-900 font-semibold',
            message: 'text-gray-600',
            closeBtn: 'text-gray-400 hover:text-gray-800 hover:bg-gray-100',
            defaultTitle: 'Informasi'
        }
    };

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Menampilkan toast notification
     * 
     * @param {Object|string} options
     * @param {string} options.message - Pesan keterangan toast
     * @param {string} [options.title] - Judul singkat toast
     * @param {'warning'|'error'|'success'|'info'} [options.type='warning'] - Jenis toast
     * @param {number} [options.duration=4000] - Durasi auto-dismiss dalam milidetik (0 untuk non-auto dismiss)
     * @returns {HTMLElement} Elemen toast yang dibuat
     */
    function showToast(options) {
        if (typeof options === 'string') {
            options = { message: options };
        }

        const config = Object.assign({
            message: '',
            title: '',
            type: 'warning',
            duration: 4000
        }, options);

        if (config.type === 'danger') config.type = 'error';
        if (config.type === 'yellow') config.type = 'warning';
        const style = TOAST_STYLES[config.type] || TOAST_STYLES.warning;
        const iconSvg = TOAST_ICONS[config.type] || TOAST_ICONS.warning;
        const titleText = config.title || style.defaultTitle;

        if (!container) return null;

        // Batasi maksimal 4 toast aktif
        while (container.children.length >= 4) {
            const oldest = container.firstElementChild;
            if (oldest && oldest._dismiss) {
                oldest._dismiss();
            } else if (oldest) {
                oldest.remove();
            }
        }

        // Buat DOM element toast
        const toast = document.createElement('div');
        toast.className = `toast-item pointer-events-auto relative w-full rounded-xl border ${style.card} transition-all duration-200 ease-out transform -translate-y-2 opacity-0 scale-[0.98]`;
        toast.setAttribute('role', 'alert');

        const hasAutoDismiss = config.duration > 0;

        toast.innerHTML = `
            <div class="p-3.5 sm:p-4 flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5 shadow-2xs ${style.badge}">
                    ${iconSvg}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs sm:text-sm font-semibold leading-snug ${style.title}">
                        ${escapeHtml(titleText)}
                    </h4>
                    <p class="text-xs mt-0.5 leading-relaxed ${style.message}">
                        ${escapeHtml(config.message)}
                    </p>
                </div>
                <button 
                    type="button" 
                    class="toast-close-btn -mr-1 -mt-0.5 rounded-lg p-1 transition cursor-pointer active:scale-95 ${style.closeBtn}" 
                    aria-label="Tutup notifikasi"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(toast);

        // Memicu animasi enter pada frame berikutnya
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.remove('-translate-y-2', 'opacity-0', 'scale-[0.98]');
                toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');
            });
        });

        let isDismissed = false;
        let timerId = null;
        let remainingTime = config.duration;
        let startTime = Date.now();

        function dismiss() {
            if (isDismissed) return;
            isDismissed = true;
            if (timerId) clearTimeout(timerId);

            toast.classList.remove('translate-y-0', 'opacity-100', 'scale-100');
            toast.classList.add('-translate-y-2', 'opacity-0', 'scale-[0.98]');

            toast.addEventListener('transitionend', () => {
                toast.remove();
            }, { once: true });

            // Fallback jika transitionend tidak terpanggil
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 300);
        }

        toast._dismiss = dismiss;

        const closeBtn = toast.querySelector('.toast-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dismiss();
            });
        }

        if (hasAutoDismiss) {
            timerId = setTimeout(dismiss, remainingTime);

            toast.addEventListener('mouseenter', () => {
                if (timerId) clearTimeout(timerId);
                remainingTime -= (Date.now() - startTime);
            });

            toast.addEventListener('mouseleave', () => {
                startTime = Date.now();
                timerId = setTimeout(dismiss, Math.max(remainingTime, 500));
            });
        }

        return toast;
    }

    // Ekspor API Global Window
    window.showToast = showToast;
    window.toast = function(message, options) {
        if (typeof message === 'object') {
            return showToast(message);
        }
        return showToast(Object.assign({ message: message }, options || {}));
    };

    window.toast.warning = function(message, title, options) {
        const opts = typeof title === 'object' ? title : Object.assign({ title: title }, options || {});
        return showToast(Object.assign({ message: message, type: 'warning' }, opts));
    };

    window.toast.error = function(message, title, options) {
        const opts = typeof title === 'object' ? title : Object.assign({ title: title }, options || {});
        return showToast(Object.assign({ message: message, type: 'error' }, opts));
    };

    window.toast.success = function(message, title, options) {
        const opts = typeof title === 'object' ? title : Object.assign({ title: title }, options || {});
        return showToast(Object.assign({ message: message, type: 'success' }, opts));
    };

    window.toast.info = function(message, title, options) {
        const opts = typeof title === 'object' ? title : Object.assign({ title: title }, options || {});
        return showToast(Object.assign({ message: message, type: 'info' }, opts));
    };

    // Event listener untuk integrasi Custom Event
    window.addEventListener('app:toast', function(event) {
        if (event.detail) {
            showToast(event.detail);
        }
    });

    // Tampilkan toast otomatis dari flash session (mis. saat berhasil aksi atau beralih peran)
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            showToast({
                type: 'error',
                title: 'Periksa Isian Formulir',
                message: @json($errors->count() > 1 ? $errors->first() . ' (dan ' . ($errors->count() - 1) . ' kendala lainnya)' : $errors->first()),
                duration: 5000
            });
        @elseif(session('journal_switched_toast'))
            showToast({
                type: 'success',
                title: 'Konteks Jurnal Dialihkan',
                message: @json(session('journal_switched_toast')),
                duration: 3500
            });
        @elseif(session('role_switched_toast'))
            showToast({
                type: 'success',
                title: 'Peran Aktif Dialihkan',
                message: @json(session('role_switched_toast')),
                duration: 3500
            });
        @elseif(session('toast_error'))
            showToast({
                type: 'error',
                title: 'Akses Ditolak',
                message: @json(session('toast_error')),
                duration: 4500
            });
        @elseif(session('error'))
            showToast({
                type: 'error',
                title: 'Pemberitahuan',
                message: @json(session('error')),
                duration: 5000
            });
        @elseif(session('toast_success'))
            showToast({
                type: 'success',
                title: 'Berhasil',
                message: @json(session('toast_success')),
                duration: 4000
            });
        @elseif(session('success'))
            showToast({
                type: 'success',
                title: 'Berhasil',
                message: @json(session('success')),
                duration: 4500
            });
        @elseif(session('status'))
            showToast({
                type: 'success',
                title: 'Berhasil',
                message: @json(session('status')),
                duration: 4500
            });
        @endif
    });
})();
</script>
