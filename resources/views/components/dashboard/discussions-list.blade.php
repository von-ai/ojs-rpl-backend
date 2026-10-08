@props(['discussions' => []])

<div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h2 class="text-base font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-4 4z"/>
            </svg>
            <span>Komunikasi & Diskusi Editorial Terkini</span>
        </h2>
        <a href="#" class="text-xs font-bold text-brand-maroon hover:underline flex items-center gap-1">
            <span>Buka Semua Diskusi</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($discussions as $disc)
            <div class="p-3.5 rounded-lg border border-gray-200/80 bg-gray-50/60 hover:bg-gray-50 transition-all flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 text-brand-maroon flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>

                <div class="space-y-1 flex-1 text-xs">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <strong class="font-bold text-gray-900">{{ $disc['sender'] }}</strong>
                            <span class="bg-gray-200 text-gray-700 text-[10px] px-1.5 py-0.2 rounded font-semibold">{{ $disc['sender_role'] }}</span>
                            <span class="text-gray-400 font-mono text-[10px]">{{ $disc['submission_id'] }}</span>
                        </div>
                        <span class="text-[10px] text-gray-400 font-medium shrink-0">{{ $disc['date'] }}</span>
                    </div>

                    <a href="{{ $disc['url'] ?? '#' }}" class="font-bold text-gray-800 hover:text-brand-maroon transition-colors block">
                        {{ $disc['subject'] }}
                    </a>

                    <p class="text-gray-500 text-[11px] line-clamp-1">
                        {{ $disc['snippet'] }}
                    </p>
                </div>

                @if (!$disc['is_read'])
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-brand-maroon text-white shrink-0">
                        Belum Dibaca
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-semibold bg-gray-200 text-gray-600 shrink-0">
                        Dibaca
                    </span>
                @endif
            </div>
        @empty
            <div class="p-4 text-center text-xs text-gray-400 italic">
                Belum ada pesan/diskusi editorial terkini.
            </div>
        @endforelse
    </div>
</div>
