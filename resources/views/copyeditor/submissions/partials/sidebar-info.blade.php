<div class="space-y-6">

    <!-- Card: Informasi Penugasan -->
    <div class="bg-white rounded-2xl border border-gray-200/90 p-5 shadow-2xs space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">
            Informasi Penugasan
        </h3>

        <div class="space-y-3 text-xs">
            <div>
                <span class="text-gray-400 block">Section Editor:</span>
                <strong class="text-gray-800 font-semibold">{{ $submission['section_editor'] }}</strong>
            </div>

            <div>
                <span class="text-gray-400 block">Layout Editor:</span>
                <strong class="text-gray-800 font-semibold">{{ $submission['layout_editor'] }}</strong>
            </div>

            <div>
                <span class="text-gray-400 block">Ditugaskan Pada:</span>
                <span class="text-gray-700 font-medium">{{ $submission['assigned_at'] }}</span>
            </div>

            <div>
                <span class="text-gray-400 block">Batas Waktu:</span>
                <span class="text-amber-700 font-bold">{{ $submission['due_at'] }}</span>
            </div>
        </div>
    </div>

    <!-- Card: Pintasan Panduan Gaya Selingkung -->
    <div class="bg-gradient-to-br from-brand-maroon/5 to-transparent rounded-2xl border border-brand-maroon/15 p-5 shadow-2xs space-y-3">
        <div class="flex items-center gap-2 text-brand-maroon">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <h3 class="text-xs font-bold uppercase tracking-wider">
                Pedoman Selingkung
            </h3>
        </div>

        <p class="text-xs text-gray-600 leading-relaxed">
            Format sitasi menggunakan <strong>APA 7th Edition</strong> dan istilah asing wajib dicetak miring (<em>italic</em>).
        </p>

        <button 
            type="button" 
            onclick="openStyleGuideModal()"
            class="w-full py-2 px-3 text-xs font-semibold text-brand-maroon bg-white hover:bg-brand-maroon hover:text-white border border-brand-maroon/30 rounded-xl transition duration-150 shadow-2xs cursor-pointer"
        >
            Buka Panduan Lengkap
        </button>
    </div>

    <!-- Card: Riwayat Aktivitas Naskah -->
    @if (!empty($submission['activity_timeline']))
        <div class="bg-white rounded-2xl border border-gray-200/90 p-5 shadow-2xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">
                Riwayat Aktivitas
            </h3>

            <div class="space-y-4 relative before:absolute before:inset-0 before:left-2.5 before:w-0.5 before:bg-gray-100">
                @foreach ($submission['activity_timeline'] as $activity)
                    <div class="relative pl-6 space-y-1">
                        <div class="absolute left-1.5 top-1.5 w-2.5 h-2.5 rounded-full bg-brand-maroon ring-4 ring-white"></div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-gray-800 leading-tight">
                                {{ $activity['title'] }}
                            </span>
                        </div>
                        <span class="text-[10px] text-gray-400 block font-medium">
                            {{ $activity['date'] }} • {{ $activity['actor'] }}
                        </span>
                        <p class="text-[11px] text-gray-600 leading-snug">
                            {{ $activity['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
