@props(['topbarData' => null])

<div class="bg-gray-50 border-b border-gray-200 text-xs py-1.5 px-4 sm:px-8">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2 text-gray-600">
        <!-- Left Metadata -->
        <div class="flex items-center gap-2">
            <span><strong class="font-semibold text-gray-700">p-ISSN:</strong> {{ $topbarData['p_issn'] ?? '2985-789X' }}</span>
            <span class="text-gray-300">•</span>
            <span><strong class="font-semibold text-gray-700">e-ISSN:</strong> {{ $topbarData['e_issn'] ?? '2985-8888' }}</span>
        </div>

        <!-- Right Quick Actions & Language -->
        <div class="flex items-center gap-4 text-xs">
            <!-- Language Switcher -->
            @if (!empty($topbarData['languages']))
                <div class="flex items-center gap-1 font-semibold text-gray-500">
                    @foreach ($topbarData['languages'] as $index => $lang)
                        @if ($index > 0)
                            <span class="text-gray-300">/</span>
                        @endif
                        <a href="#" class="{{ $lang['active'] ? 'text-brand-maroon font-bold' : 'hover:text-gray-900 transition-colors' }}">
                            {{ $lang['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            <span class="text-gray-300">|</span>

            <!-- Auth Links -->
            @auth
                <a href="{{ route('dashboard') }}" class="font-bold text-brand-maroon hover:underline">
                    Dashboard Portal
                </a>
            @else
                <a href="{{ route('login') }}" class="font-semibold text-gray-700 hover:text-brand-maroon transition-colors">
                    Masuk
                </a>
                <span class="text-gray-300">•</span>
                <a href="{{ route('login') }}" class="font-semibold text-gray-700 hover:text-brand-maroon transition-colors">
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</div>
