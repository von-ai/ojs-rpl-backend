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
    class="fixed inset-0 z-50 {{ $show ? 'flex' : 'hidden' }} overflow-y-auto items-center justify-center p-3 sm:p-4 md:p-6 bg-gray-950/60 backdrop-blur-xs transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    @if($title) aria-labelledby="{{ $name }}-title" @endif
    tabindex="-1"
    data-modal="{{ $name }}"
>
    <div class="min-h-full flex items-center justify-center py-2 sm:py-4 w-full mx-auto">
        <!-- Backdrop Klik untuk Menutup -->
        <div 
            class="fixed inset-0 -z-10 cursor-pointer" 
            onclick="document.getElementById('{{ $name }}').classList.add('hidden'); document.getElementById('{{ $name }}').classList.remove('flex');"
            aria-hidden="true"
        ></div>

        <!-- Modal Card Container (Terkunci Maksimal Tinggi Layar & Scroll di Dalam) -->
        <div 
            class="relative w-full {{ $maxWidthClass }} mx-auto my-auto max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3.5rem)] flex flex-col bg-white rounded-2xl border border-outline-variant shadow-2xl overflow-hidden transition-all duration-200 transform scale-100 opacity-100 text-xs sm:text-sm"
        >
            @if($title)
                <!-- Header Modal (Pinned di Atas) -->
                <div class="px-5 sm:px-6 py-4 border-b border-divider flex items-center justify-between shrink-0 bg-gray-50/70">
                    <h3 id="{{ $name }}-title" class="text-base font-bold text-gray-900 leading-snug">
                        {{ $title }}
                    </h3>
                    <button 
                        type="button" 
                        class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer active:scale-95 shrink-0"
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

            <!-- Body Modal (Scrollable di Tengah) -->
            <div class="p-5 sm:p-6 text-gray-700 overflow-y-auto flex-1 min-h-0 [scrollbar-width:thin]">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <!-- Footer Aksi (Pinned di Bawah) -->
                <div class="bg-gray-50/80 px-5 py-3.5 border-t border-divider flex items-center justify-end gap-2.5 shrink-0">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
