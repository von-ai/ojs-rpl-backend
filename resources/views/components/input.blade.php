@props(['disabled' => false, 'error' => false])

<input 
    {{ $disabled ? 'disabled' : '' }} 
    {!! $attributes->merge([
        'class' => 'w-full min-h-11 sm:min-h-10 px-3 py-2 rounded-lg border text-base sm:text-sm text-gray-900 placeholder-gray-500 bg-white focus:outline-none focus:ring-1 transition-all ' .
        ($error 
            ? 'border-red-500 focus:border-red-500 focus:ring-red-500' 
            : 'border-outline focus:border-brand-maroon focus:ring-brand-maroon')
    ]) !!}
>
