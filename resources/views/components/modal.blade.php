@props([
    'name' => 'generic-modal',
    'title' => null,
    'maxWidth' => 'md',
    'show' => false
])

@php
    $maxWidthClass = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ][$maxWidth] ?? 'max-w-md';
@endphp

<div 
    id="{{ $name }}" 
    class="fixed inset-0 z-50 {{ $show ? 'flex' : 'hidden' }} items-center justify-center p-4 bg-gray-950/45 backdrop-blur-xs transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    @if($title) aria-labelledby="{{ $name }}-title" @endif
    tabindex="-1"
    data-modal="{{ $name }}"
>
    <!-- Modal Card Container -->
    <div 
        class="relative w-full {{ $maxWidthClass }} bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden transition-all duration-200 transform scale-100 opacity-100"
    >
        @if($title)
            <!-- Header Modal -->
            <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 id="{{ $name }}-title" class="text-base font-bold text-gray-900 leading-snug">
                    {{ $title }}
                </h3>
                <button 
                    type="button" 
                    class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer active:scale-95"
                    aria-label="Tutup dialog"
                    onclick="document.getElementById('{{ $name }}').classList.add('hidden'); document.getElementById('{{ $name }}').classList.remove('flex');"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <!-- Body Modal -->
        <div class="p-5 sm:p-6 text-gray-700 text-xs sm:text-sm">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <!-- Footer Aksi -->
            <div class="bg-gray-50/80 px-5 py-3.5 border-t border-gray-100 flex items-center justify-end gap-2.5">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
