function enhanceNativeSelects() {
    document.querySelectorAll('select:not([multiple])').forEach(select => {
        if (select.closest('.custom-dropdown-container') || select.hidden || select.classList.contains('sr-only') || select.classList.contains('hidden')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'relative custom-dropdown-container w-full';
        wrapper.dataset.nativeDropdown = '';
        wrapper.dataset.dropdown = select.name || select.id || 'Pilih opsi';
        select.before(wrapper);
        wrapper.append(select);
        select.classList.add('custom-dropdown-native');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'custom-dropdown-btn w-full text-xs sm:text-sm bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-md px-3 py-2.5 text-gray-700 font-medium flex items-center justify-between gap-2 shadow-2xs transition cursor-pointer focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon';
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        const label = document.createElement('span');
        label.className = 'custom-dropdown-label truncate';
        button.append(label);
        const arrow = document.createElement('span');
        arrow.className = 'custom-dropdown-chevron text-gray-400 transition-transform';
        arrow.textContent = '⌄';
        arrow.setAttribute('aria-hidden', 'true');
        button.append(arrow);
        wrapper.append(button);
        const panel = document.createElement('div');
        panel.className = 'custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all absolute left-0 top-full mt-1.5 min-w-full max-h-60 overflow-y-auto bg-white rounded-md border border-gray-200 shadow-lg p-1 z-50';
        for (const option of select.options) {
            const item = document.createElement('div');
            item.className = 'custom-dropdown-item px-2.5 py-1.5 text-xs sm:text-sm rounded cursor-pointer text-gray-700 hover:bg-gray-50';
            item.dataset.value = option.value;
            item.dataset.label = option.label;
            item.textContent = option.label;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-disabled', String(option.disabled || option.parentElement.disabled === true));
            panel.append(item);
        }
        wrapper.append(panel);
    });
}

/** Shared dropdown behavior for every portal. Native selects remain usable without JavaScript. */
function closeAllCustomDropdowns({ restoreFocus = false } = {}) {
    document.querySelectorAll('.custom-dropdown-container').forEach(container => {
        const button = container.querySelector('.custom-dropdown-btn');
        const panel = container.querySelector('.custom-dropdown-panel');
        if (!panel || !button) return;
        const wasOpen = button.getAttribute('aria-expanded') === 'true';
        panel.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
        panel.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
        button.setAttribute('aria-expanded', 'false');
        container.querySelector('.custom-dropdown-chevron')?.classList.remove('rotate-180');
        if (wasOpen && restoreFocus) button.focus();
    });
}

function initCustomDropdowns() {
    enhanceNativeSelects();
    document.querySelectorAll('.custom-dropdown-container:not([data-dropdown-initialized])').forEach(container => {
        const button = container.querySelector('.custom-dropdown-btn');
        const panel = container.querySelector('.custom-dropdown-panel');
        const label = container.querySelector('.custom-dropdown-label');
        const select = container.querySelector('select');
        if (!button || !panel) return;
        container.dataset.dropdownInitialized = 'true';
        const listId = panel.id || `sirida-listbox-${document.querySelectorAll('[data-dropdown-initialized]').length}`;
        panel.id = listId;
        panel.setAttribute('role', 'listbox');
        button.setAttribute('aria-controls', listId);
        const items = [...panel.querySelectorAll('.custom-dropdown-item')];
        const enabledItems = () => items.filter(item => item.getAttribute('aria-disabled') !== 'true' && !item.hidden);
        const isOpen = () => button.getAttribute('aria-expanded') === 'true';
        const fieldLabel = select?.id && [...document.querySelectorAll('label[for]')].find(el => el.htmlFor === select.id);
        if (fieldLabel && !button.hasAttribute('aria-label')) button.setAttribute('aria-label', fieldLabel.textContent.trim());
        if (!button.hasAttribute('aria-label')) button.setAttribute('aria-label', container.dataset.dropdown || 'Pilih opsi');
        if (select) { select.tabIndex = -1; select.setAttribute('aria-hidden', 'true'); }
        const sync = () => {
            if (!select) return;
            const option = select.selectedOptions[0];
            if (label) label.textContent = option?.textContent.trim() || container.dataset.placeholder || 'Pilih opsi...';
            button.disabled = select.disabled;
            button.setAttribute('aria-required', String(select.required));
            items.forEach(item => {
                const selected = item.dataset.value === select.value;
                item.setAttribute('aria-selected', String(selected));
                item.classList.toggle('bg-brand-maroon-light', selected);
                item.classList.toggle('text-brand-maroon', selected);
                item.classList.toggle('font-semibold', selected);
                item.classList.toggle('text-gray-700', !selected);
                item.querySelector('.custom-dropdown-check')?.classList.toggle('hidden', !selected);
            });
        };
        const open = (last = false) => {
            if (button.disabled) return;
            closeAllCustomDropdowns();
            panel.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
            panel.classList.add('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
            button.setAttribute('aria-expanded', 'true');
            container.querySelector('.custom-dropdown-chevron')?.classList.add('rotate-180');
            const candidates = enabledItems();
            (last ? candidates.at(-1) : candidates.find(item => item.getAttribute('aria-selected') === 'true') || candidates[0])?.focus();
        };
        const choose = item => {
            if (item.getAttribute('aria-disabled') === 'true') return;
            if (select) {
                select.value = item.dataset.value;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
                sync();
            } else if (label) {
                label.textContent = item.dataset.label || item.textContent.trim();
            }
            container.dataset.value = item.dataset.value;
            items.forEach(option => {
                const chosen = option === item;
                option.setAttribute('aria-selected', String(chosen));
                option.classList.toggle('bg-brand-maroon-light', chosen);
                option.classList.toggle('text-brand-maroon', chosen);
                option.classList.toggle('font-semibold', chosen);
                option.querySelector('.custom-dropdown-check')?.classList.toggle('hidden', !chosen);
            });
            container.dispatchEvent(new CustomEvent('dropdown:change', { bubbles: true, detail: { value: item.dataset.value } }));
            closeAllCustomDropdowns();
            button.focus();
            if (container.dataset.autoSubmit === 'true' && !select?.hasAttribute('onchange')) container.closest('form')?.requestSubmit();
        };
        button.addEventListener('click', event => {
            event.stopPropagation();
            if (isOpen()) closeAllCustomDropdowns(); else open();
        });
        button.addEventListener('keydown', event => {
            if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                event.preventDefault();
                open(['ArrowUp', 'End'].includes(event.key));
            }
        });
        items.forEach(item => {
            item.tabIndex = -1;
            item.addEventListener('click', event => { event.stopPropagation(); choose(item); });
            item.addEventListener('keydown', event => {
                const candidates = enabledItems();
                let index = candidates.indexOf(item);
                if (event.key === 'ArrowDown') index = (index + 1) % candidates.length;
                else if (event.key === 'ArrowUp') index = (index - 1 + candidates.length) % candidates.length;
                else if (event.key === 'Home') index = 0;
                else if (event.key === 'End') index = candidates.length - 1;
                else if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); choose(item); return; }
                else if (event.key === 'Escape') { event.preventDefault(); closeAllCustomDropdowns({ restoreFocus: true }); return; }
                else if (event.key === 'Tab') { closeAllCustomDropdowns(); return; }
                else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey) {
                    const ordered = [...candidates.slice(index + 1), ...candidates.slice(0, index + 1)];
                    const found = ordered.find(el => (el.dataset.label || el.textContent).trim().toLocaleLowerCase().startsWith(event.key.toLocaleLowerCase()));
                    if (found) { event.preventDefault(); found.focus(); }
                    return;
                } else return;
                event.preventDefault();
                candidates[index]?.focus();
            });
        });
        if (select) new MutationObserver(sync).observe(select, { attributes: true, attributeFilter: ['disabled', 'required'] });
        select?.addEventListener('change', sync);
        select?.addEventListener('focus', () => button.focus());
        select?.addEventListener('invalid', event => {
            event.preventDefault();
            button.setAttribute('aria-invalid', 'true');
            button.focus();
        });
        select?.addEventListener('change', () => button.removeAttribute('aria-invalid'));
        container.closest('form')?.addEventListener('reset', () => setTimeout(sync, 0));
        sync();
    });
}

document.addEventListener('click', event => {
    if (!event.target.closest('.custom-dropdown-container')) closeAllCustomDropdowns();
});
document.addEventListener('focusin', event => {
    if (!event.target.closest('.custom-dropdown-container')) closeAllCustomDropdowns();
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeAllCustomDropdowns({ restoreFocus: true });
});
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initCustomDropdowns);
else initCustomDropdowns();
window.initCustomDropdowns = initCustomDropdowns;
window.closeAllCustomDropdowns = closeAllCustomDropdowns;
