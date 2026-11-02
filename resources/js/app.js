/**
 * SIRIDA - Global Client Scripts
 * Menangani interaktivitas komponen UI seperti Custom Dropdown, Modal, Toast, dll.
 */

// Inisialisasi Custom Dropdown
function initCustomDropdowns() {
    const containers = document.querySelectorAll('.custom-dropdown-container:not([data-dropdown-initialized])');
    
    containers.forEach(container => {
        container.setAttribute('data-dropdown-initialized', 'true');

        const btn = container.querySelector('.custom-dropdown-btn');
        const panel = container.querySelector('.custom-dropdown-panel');
        const chevron = container.querySelector('.custom-dropdown-chevron');
        const labelEl = container.querySelector('.custom-dropdown-label');
        const selectEl = container.querySelector('select');
        const items = container.querySelectorAll('.custom-dropdown-item');

        if (!btn || !panel) return;

        function openDropdown() {
            closeAllCustomDropdowns();
            panel.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
            panel.classList.add('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
            btn.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.classList.add('rotate-180');
        }

        function closeDropdown() {
            panel.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
            panel.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
            btn.setAttribute('aria-expanded', 'false');
            if (chevron) chevron.classList.remove('rotate-180');
        }

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = panel.classList.contains('visible');
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        items.forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = item.getAttribute('data-value');
                const label = item.getAttribute('data-label') || item.textContent.trim();

                if (selectEl) {
                    selectEl.value = val;
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (labelEl) {
                    labelEl.textContent = label;
                }

                items.forEach(it => {
                    const check = it.querySelector('.custom-dropdown-check');
                    if (it === item) {
                        it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                        it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                        it.setAttribute('aria-selected', 'true');
                        if (check) check.classList.remove('hidden');
                    } else {
                        it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                        it.classList.add('text-gray-700', 'hover:bg-gray-50');
                        it.setAttribute('aria-selected', 'false');
                        if (check) check.classList.add('hidden');
                    }
                });

                closeDropdown();

                if (container.dataset.autoSubmit === 'true') {
                    const form = container.closest('form');
                    if (form) form.submit();
                }
            });
        });
    });
}

function closeAllCustomDropdowns() {
    document.querySelectorAll('.custom-dropdown-panel.visible').forEach(panel => {
        const container = panel.closest('.custom-dropdown-container');
        const btn = container ? container.querySelector('.custom-dropdown-btn') : null;
        const chevron = container ? container.querySelector('.custom-dropdown-chevron') : null;

        panel.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
        panel.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (chevron) chevron.classList.remove('rotate-180');
    });
}

// Tutup jika klik di luar dropdown
document.addEventListener('click', (e) => {
    if (!e.target.closest('.custom-dropdown-container')) {
        closeAllCustomDropdowns();
    }
});

// Tutup dengan tombol Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAllCustomDropdowns();
    }
});

// Auto-run on DOM Ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCustomDropdowns);
} else {
    initCustomDropdowns();
}

window.initCustomDropdowns = initCustomDropdowns;
window.closeAllCustomDropdowns = closeAllCustomDropdowns;
