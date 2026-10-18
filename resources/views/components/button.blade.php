@props(['type' => 'submit'])

<button 
    type="{{ $type }}" 
    {{ $attributes->merge([
        'class' => 'w-full py-3 sm:py-3.5 px-4 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-xs transition-colors duration-200 flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-maroon disabled:opacity-80 disabled:cursor-not-allowed'
    ]) }}
>
    {{ $slot }}
</button>
