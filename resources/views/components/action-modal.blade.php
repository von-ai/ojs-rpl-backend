<!-- Reusable Action Confirmation & Validation Modal (BRIDA Modern Design System) -->
<div 
    id="action-confirm-modal" 
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-950/45 backdrop-blur-xs transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="action-modal-title"
    tabindex="-1"
>
    <!-- Modal Card Container -->
    <div 
        id="action-modal-card" 
        class="relative w-full max-w-md bg-white rounded-2xl border border-gray-200/90 shadow-2xl overflow-hidden transition-all duration-200 transform scale-95 opacity-0"
    >
        <!-- Tombol Tutup (X) -->
        <button 
            type="button" 
            id="action-modal-btn-close" 
            class="absolute top-4 right-4 w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer active:scale-[0.96]"
            aria-label="Tutup dialog"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg>
        </button>

        <!-- Body Modal -->
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <!-- Icon Badge Dinamis -->
                <div id="action-modal-icon-container" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border shadow-2xs">
                    <!-- Icon Logout (Red) -->
                    <svg id="action-modal-icon-logout" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <!-- Icon Danger (Red) -->
                    <svg id="action-modal-icon-danger" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                        <line x1="10" y1="11" x2="10" y2="17" />
                        <line x1="14" y1="11" x2="14" y2="17" />
                    </svg>
                    <!-- Icon Warning (Amber) -->
                    <svg id="action-modal-icon-warning" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <!-- Icon Primary / Info (Maroon / Blue) -->
                    <svg id="action-modal-icon-primary" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </div>

                <!-- Teks Konten -->
                <div class="flex-1 min-w-0 pr-4">
                    <h3 id="action-modal-title" class="text-base font-bold text-gray-900 leading-snug">
                        Konfirmasi Tindakan
                    </h3>
                    <p id="action-modal-message" class="mt-1.5 text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Apakah Anda yakin ingin melanjutkan tindakan ini?
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Aksi -->
        <div class="bg-gray-50/80 px-5 py-3.5 border-t border-gray-100 flex items-center justify-end gap-2.5">
            <button 
                type="button" 
                id="action-modal-btn-cancel" 
                class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-xl transition cursor-pointer active:scale-[0.96] shadow-2xs"
            >
                Batal
            </button>
            <button 
                type="button" 
                id="action-modal-btn-confirm" 
                class="px-4 py-2 text-xs sm:text-sm font-bold text-white rounded-xl transition cursor-pointer active:scale-[0.96] shadow-xs"
            >
                Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let activeResolve = null;
    let previousActiveElement = null;

    const modal = document.getElementById('action-confirm-modal');
    const card = document.getElementById('action-modal-card');
    const titleEl = document.getElementById('action-modal-title');
    const msgEl = document.getElementById('action-modal-message');
    const iconContainer = document.getElementById('action-modal-icon-container');
    const iconLogout = document.getElementById('action-modal-icon-logout');
    const iconDanger = document.getElementById('action-modal-icon-danger');
    const iconWarning = document.getElementById('action-modal-icon-warning');
    const iconPrimary = document.getElementById('action-modal-icon-primary');
    const btnCancel = document.getElementById('action-modal-btn-cancel');
    const btnConfirm = document.getElementById('action-modal-btn-confirm');
    const btnClose = document.getElementById('action-modal-btn-close');

    function hideAllIcons() {
        if (iconLogout) iconLogout.classList.add('hidden');
        if (iconDanger) iconDanger.classList.add('hidden');
        if (iconWarning) iconWarning.classList.add('hidden');
        if (iconPrimary) iconPrimary.classList.add('hidden');
    }

    function closeModal(result) {
        if (!modal) return;
        
        // Animasi keluar
        if (card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            
            if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
                previousActiveElement.focus();
            }

            if (typeof activeResolve === 'function') {
                const cb = activeResolve;
                activeResolve = null;
                cb(Boolean(result));
            }
        }, 150);
    }

    if (btnCancel) btnCancel.addEventListener('click', () => closeModal(false));
    if (btnClose) btnClose.addEventListener('click', () => closeModal(false));
    if (btnConfirm) btnConfirm.addEventListener('click', () => closeModal(true));

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(false);
            }
        });

        window.addEventListener('keydown', (e) => {
            if (!modal.classList.contains('hidden') && e.key === 'Escape') {
                e.preventDefault();
                closeModal(false);
            }
        });
    }

    /**
     * Reusable Confirmation Modal
     * Returns Promise<boolean>
     */
    window.showConfirmModal = function({
        title = 'Konfirmasi Tindakan',
        message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmText = 'Lanjutkan',
        cancelText = 'Batal',
        type = 'danger', // 'danger' | 'warning' | 'primary' | 'info'
        showCancel = true
    } = {}) {
        return new Promise((resolve) => {
            if (!modal) {
                // Fallback graceful jika elemen modal tidak ada
                const fallback = window.confirm(message);
                resolve(fallback);
                return;
            }

            activeResolve = resolve;
            previousActiveElement = document.activeElement;

            // Set konten teks
            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.textContent = message;
            if (btnConfirm) btnConfirm.textContent = confirmText;
            if (btnCancel) {
                btnCancel.textContent = cancelText;
                if (showCancel) {
                    btnCancel.classList.remove('hidden');
                } else {
                    btnCancel.classList.add('hidden');
                }
            }

            // Atur styling tipe & ikon
            hideAllIcons();
            if (iconContainer && btnConfirm) {
                // Reset classes
                iconContainer.className = 'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border shadow-2xs';
                btnConfirm.className = 'px-4 py-2 text-xs sm:text-sm font-bold text-white rounded-xl transition cursor-pointer active:scale-[0.96] shadow-xs';

                if (type === 'logout') {
                    iconContainer.classList.add('bg-red-50', 'text-red-600', 'border-red-100');
                    btnConfirm.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-2', 'focus:ring-red-500/30');
                    if (iconLogout) iconLogout.classList.remove('hidden');
                } else if (type === 'danger') {
                    iconContainer.classList.add('bg-red-50', 'text-red-600', 'border-red-100');
                    btnConfirm.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-2', 'focus:ring-red-500/30');
                    if (iconDanger) iconDanger.classList.remove('hidden');
                } else if (type === 'warning') {
                    iconContainer.classList.add('bg-amber-50', 'text-amber-600', 'border-amber-100');
                    btnConfirm.classList.add('bg-amber-600', 'hover:bg-amber-700', 'focus:ring-2', 'focus:ring-amber-500/30');
                    if (iconWarning) iconWarning.classList.remove('hidden');
                } else { // primary atau info
                    iconContainer.classList.add('bg-red-50', 'text-brand-maroon', 'border-red-100');
                    btnConfirm.classList.add('bg-brand-maroon', 'hover:bg-brand-maroon-hover', 'focus:ring-2', 'focus:ring-brand-maroon/30');
                    if (iconPrimary) iconPrimary.classList.remove('hidden');
                }
            }

            // Tampilkan modal dengan transisi
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                if (card) {
                    card.classList.remove('scale-95', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                }
                if (showCancel && btnCancel) {
                    btnCancel.focus();
                } else if (btnConfirm) {
                    btnConfirm.focus();
                }
            });
        });
    };

    /**
     * Reusable Alert Notification Modal (Single action "Mengerti")
     * Returns Promise<boolean>
     */
    window.showAlertModal = function({
        title = 'Perhatian',
        message = '',
        buttonText = 'Mengerti',
        type = 'warning'
    } = {}) {
        return window.showConfirmModal({
            title,
            message,
            confirmText: buttonText,
            showCancel: false,
            type
        });
    };

    // Global interceptor untuk konfirmasi keluar (Logout)
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;

        const action = form.getAttribute('action') || '';
        if (action.endsWith('/logout') && !form.dataset.confirmed) {
            e.preventDefault();
            const confirmed = await window.showConfirmModal({
                title: 'Konfirmasi Keluar Akun',
                message: 'Apakah Anda yakin ingin keluar dari Portal Penulis BRIDA Kota Makassar?',
                confirmText: 'Ya, Keluar',
                cancelText: 'Batal',
                type: 'logout'
            });

            if (confirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        }
    });
})();
</script>
