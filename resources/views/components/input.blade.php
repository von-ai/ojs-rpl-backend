@props(['disabled' => false, 'error' => false])

<input 
    {{ $disabled ? 'disabled' : '' }} 
    {!! $attributes->merge([
        'class' => 'w-full px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg border text-xs sm:text-sm text-gray-900 placeholder-gray-400 bg-white focus:outline-none focus:ring-1 transition-all ' . 
        ($error 
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500' 
            : 'border-gray-300 focus:border-brand-maroon focus:ring-brand-maroon')
    ]) !!}
>
