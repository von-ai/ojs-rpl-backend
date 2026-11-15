@props(['topbarData' => null, 'journal' => null])

<div class="bg-gray-50 border-b border-gray-200 text-xs py-1.5 px-4 sm:px-8">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2 text-gray-600">
        <!-- Left Metadata -->
        <div class="flex items-center gap-2">
            <span><strong class="font-medium text-gray-700">p-ISSN:</strong> {{ $topbarData['p_issn'] ?? '2985-789X' }}</span>
            <span class="text-gray-300">•</span>
            <span><strong class="font-medium text-gray-700">e-ISSN:</strong> {{ $topbarData['e_issn'] ?? '2985-8888' }}</span>
            @if($journal && $journal->status !== 'active' && auth()->user()?->isSysAdmin())
                <span class="ml-2 inline-flex items-center px-2 py-0.2 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                    Pratinjau SysAdmin: Status {{ ucfirst($journal->status) }}
                </span>
            @endif
        </div>

        <!-- Right Quick Actions & Language -->
        <div class="flex items-center gap-4 text-xs">
            <span class="font-normal text-gray-500" lang="id">Bahasa Indonesia</span>

            <span class="text-gray-300">|</span>

            <!-- Auth Links -->
            @auth
                @if(auth()->user()->isSysAdmin())
                    <a href="{{ route('sysadmin.journals.index') }}" class="font-medium text-brand-maroon hover:underline inline-flex items-center gap-1" title="Kembali ke Portal System Administrator">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Portal System Admin</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="font-medium text-brand-maroon hover:underline">
                        Dashboard Portal
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-brand-maroon transition-colors">
                    Masuk
                </a>
                <span class="text-gray-300">•</span>
                <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-brand-maroon transition-colors">
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</div>
