@props(['type' => 'success', 'title' => null])

@php
    $styles = [
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'error'   => 'bg-red-50 border-red-200 text-brand-maroon',
    ][$type] ?? 'bg-gray-50 border-gray-200 text-gray-800';

    $iconStyles = [
        'success' => 'text-emerald-600',
        'error'   => 'text-brand-maroon',
    ][$type] ?? 'text-gray-600';
@endphp

<div {{ $attributes->merge(['class' => 'p-3.5 rounded-lg border text-xs sm:text-sm flex items-start gap-2.5 ' . $styles]) }}>
    @if ($type === 'success')
        <svg class="w-4 h-4 {{ $iconStyles }} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @else
        <svg class="w-4 h-4 {{ $iconStyles }} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @endif
    <div>
        @if ($title)
            <p class="font-bold">{{ $title }}</p>
        @endif
        <div class="mt-0.5">
            {{ $slot }}
        </div>
    </div>
</div>
