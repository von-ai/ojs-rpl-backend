@props([
    'name',
    'id' => null,
    'value' => null,
    'selected' => null,
    'options' => [],
    'placeholder' => 'Pilih opsi...',
    'required' => false,
    'disabled' => false,
    'buttonClass' => '',
    'panelClass' => '',
    'containerClass' => '',
    'autoSubmit' => false,
])

@php
    $id = $id ?? $name;
    $currentValue = (string) ($selected ?? $value ?? old($name, request($name, '')));

    // Normalisasi opsi menjadi array seragam: [['value' => ..., 'label' => ..., 'desc' => ...]]
    $normalizedOptions = [];
    foreach ($options as $optKey => $optVal) {
        if (is_array($optVal)) {
            $normalizedOptions[] = [
                'value' => (string) ($optVal['value'] ?? $optKey),
                'label' => (string) ($optVal['label'] ?? $optVal['name'] ?? $optVal['title'] ?? $optKey),
                'desc' => (string) ($optVal['desc'] ?? $optVal['description'] ?? ''),
            ];
        } elseif (is_object($optVal)) {
            $normalizedOptions[] = [
                'value' => (string) ($optVal->value ?? $optVal->id ?? $optKey),
                'label' => (string) ($optVal->label ?? $optVal->name ?? $optVal->title ?? $optKey),
                'desc' => (string) ($optVal->desc ?? $optVal->description ?? ''),
            ];
        } elseif (array_is_list((array) $options) && is_string($optVal)) {
            $normalizedOptions[] = [
                'value' => (string) $optVal,
                'label' => (string) $optVal,
                'desc' => '',
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string) $optKey,
                'label' => (string) $optVal,
                'desc' => '',
            ];
        }
    }

    // Cari label untuk nilai yang saat ini aktif
    $activeLabel = null;
    foreach ($normalizedOptions as $opt) {
        if ($opt['value'] === $currentValue) {
            $activeLabel = $opt['label'];
            break;
        }
    }
    if ($activeLabel === null) {
        $activeLabel = $placeholder;
        $currentValue = '';
    }

    $isFullWidth = str_contains($panelClass, 'w-full') || str_contains($containerClass, 'w-full');
    $defaultPanelWidth = $isFullWidth ? 'w-full left-0 right-0 max-w-full' : 'min-w-full sm:min-w-44 w-max max-w-xs sm:max-w-sm';
@endphp

<div 
    class="relative custom-dropdown-container {{ $isFullWidth ? 'w-full block' : 'inline-block' }} text-left {{ $containerClass }}"
    data-dropdown="{{ $name }}"
    data-native-dropdown
    data-placeholder="{{ $placeholder }}"
    @if($autoSubmit) data-auto-submit="true" @endif
>
    <!-- Hidden native select for form serialization, accessibility, and tests -->
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        class="custom-dropdown-native w-full px-3 py-2.5 rounded-md border border-gray-300 bg-white text-sm"
        tabindex="-1"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->whereStartsWith('on') }}
    >
        @if(!collect($normalizedOptions)->contains(fn ($opt) => $opt['value'] === ''))
            <option value="" @selected($currentValue === '') @disabled($required)>{{ $placeholder }}</option>
        @endif
        @foreach($normalizedOptions as $opt)
            <option value="{{ $opt['value'] }}" {{ $opt['value'] === $currentValue ? 'selected' : '' }}>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    <!-- Custom Trigger Button -->
    <button
        type="button"
        class="custom-dropdown-btn w-full text-xs sm:text-sm bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-md px-3 py-2.5 text-gray-700 font-medium flex items-center justify-between gap-2 shadow-2xs transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon {{ $buttonClass }}"
        id="{{ $id }}-trigger"
        aria-controls="{{ $id }}-listbox"
        aria-haspopup="listbox"
        aria-expanded="false"
        {{ $disabled ? 'disabled' : '' }}
    >
        <span class="custom-dropdown-label truncate">{{ $activeLabel }}</span>
        <svg class="custom-dropdown-chevron w-3.5 h-3.5 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Custom Options Floating Panel -->
    <div id="{{ $id }}-listbox" role="listbox" aria-labelledby="{{ $id }}-trigger" class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top-left absolute left-0 top-full mt-1.5 {{ $defaultPanelWidth }} max-h-60 overflow-y-auto bg-white rounded-lg border border-gray-200 shadow-[0px_4px_16px_rgba(0,0,0,0.08)] p-1.5 z-50 space-y-0.5 {{ $panelClass }}">
        @foreach($normalizedOptions as $opt)
            @php $isSelected = ($opt['value'] === $currentValue); @endphp
            <div 
                class="custom-dropdown-item flex items-start justify-between gap-2.5 px-3 py-2 text-xs sm:text-sm rounded-lg cursor-pointer transition {{ $isSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                data-value="{{ $opt['value'] }}"
                data-label="{{ $opt['label'] }}"
                role="option"
                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
            >
                <div class="min-w-0 flex-1">
                    <div class="{{ $isSelected ? 'text-brand-maroon font-semibold' : 'text-gray-900 font-medium' }} leading-snug {{ empty($opt['desc']) ? 'truncate' : '' }}">
                        {{ $opt['label'] }}
                    </div>
                    @if(!empty($opt['desc']))
                        <div class="text-[11px] {{ $isSelected ? 'text-brand-maroon/80' : 'text-gray-500' }} font-normal mt-0.5 leading-snug">
                            {{ $opt['desc'] }}
                        </div>
                    @endif
                </div>
                <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        @endforeach
    </div>
</div>
