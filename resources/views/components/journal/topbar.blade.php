@props(['topbarData' => null, 'journal' => null])

<div class="bg-gray-50 border-b border-gray-200 text-xs py-1 sm:py-1.5 px-3.5 sm:px-6 lg:px-8 w-full max-w-full overflow-hidden">
    <div class="max-w-7xl mx-auto flex flex-row items-center justify-between gap-2 text-gray-600 text-[10px] sm:text-xs">
        <!-- Left Metadata -->
        <div class="flex items-center gap-1.5 sm:gap-2 truncate">
            <span><strong class="font-medium text-gray-700">p-ISSN:</strong> {{ $topbarData['p_issn'] ?? '2985-789X' }}</span>
            <span class="text-gray-300 hidden sm:inline">•</span>
            <span class="hidden sm:inline"><strong class="font-medium text-gray-700">e-ISSN:</strong> {{ $topbarData['e_issn'] ?? '2985-8888' }}</span>
            @if($journal && $journal->status !== 'active' && auth()->user()?->isSysAdmin())
                <span class="ml-1 hidden md:inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                    Pratinjau SysAdmin: Status {{ ucfirst($journal->status) }}
                </span>
            @endif
        </div>

        <!-- Right Quick Actions & Language -->
        <div class="flex items-center justify-end gap-2 sm:gap-4 shrink-0 text-[10px] sm:text-xs">
            <span class="hidden sm:inline font-normal text-gray-500" lang="id">Bahasa Indonesia</span>

            <span class="hidden sm:inline text-gray-300">|</span>

            <!-- Auth Links -->
            @auth
                @if(auth()->user()->isSysAdmin())
                    <a href="{{ route('sysadmin.journals.index', absolute: false) }}" class="font-medium text-brand-maroon hover:underline inline-flex items-center gap-1" title="Kembali ke Portal System Administrator">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Portal System Admin</span>
                        <span class="sm:hidden">Portal Admin</span>
                    </a>
                @else
                    <a href="{{ route('dashboard', absolute: false) }}" class="font-medium text-brand-maroon hover:underline">
                        Dashboard Portal
                    </a>
                @endif
            @else
                <a href="{{ route('login', absolute: false) }}" class="font-medium text-gray-600 hover:text-brand-maroon transition-colors">
                    Masuk
                </a>
                <span class="text-gray-300">•</span>
                <a href="{{ route('register') }}" class="font-medium text-gray-600 hover:text-brand-maroon transition-colors">
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</div>
