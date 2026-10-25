<div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-2xs space-y-3">
    <div class="border-b border-gray-100 pb-3">
        <h3 class="text-sm font-bold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Linimasa Putaran Proofread</span>
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
            Jejak kronologis penugasan dan pertukaran draf galai.
        </p>
    </div>

    <div class="relative pl-5 border-l-2 border-gray-200 space-y-4 my-2">
        @forelse ($submission['activity_timeline'] as $item)
            <div class="relative">
                <!-- Dot Marker -->
                <div class="absolute -left-[27px] top-1 w-3 h-3 rounded-full border-2 border-white bg-brand-maroon"></div>

                <div>
                    <span class="text-xs font-bold text-gray-800 block">
                        {{ $item['title'] }}
                    </span>
                    <span class="text-[10px] text-gray-400 block mt-0.5">
                        {{ $item['actor'] }} • {{ $item['date'] }}
                    </span>
                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                        {{ $item['description'] }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-400 italic">Belum ada jejak aktivitas.</p>
        @endforelse
    </div>
</div>
