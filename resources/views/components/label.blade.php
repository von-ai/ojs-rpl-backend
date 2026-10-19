@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1.5 sm:mb-2']) }}>
    {{ $value ?? $slot }}
</label>
