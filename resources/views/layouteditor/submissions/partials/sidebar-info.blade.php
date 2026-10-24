{{-- resources/views/layouteditor/submissions/partials/sidebar-info.blade.php --}}
<div class="space-y-5">
    <!-- 1. Kotak Informasi Penugasan & Tenggat Waktu -->
    <div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">
            Informasi Penugasan
        </h3>

        <div class="space-y-3 text-xs">
            <div>
                <span class="text-gray-500 block text-[11px]">Batas Waktu Tata Letak:</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <svg class="w-4 h-4 {{ $submission['is_urgent'] ? 'text-amber-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold {{ $submission['is_urgent'] ? 'text-amber-700' : 'text-gray-900' }}">
                        {{ $submission['due_at'] }}
                    </span>
                </div>
                @if($submission['is_urgent'])
                    <span class="text-[10px] font-semibold text-amber-600 block mt-0.5">Prioritas tinggi: batas waktu segera berakhir</span>
                @endif
            </div>

            <div class="pt-2.5 border-t border-gray-100">
                <span class="text-gray-500 block text-[11px]">Section Editor:</span>
                <span class="font-semibold text-gray-900">{{ $submission['section_editor'] }}</span>
            </div>

            <div class="pt-2.5 border-t border-gray-100">
                <span class="text-gray-500 block text-[11px]">Proofreader Ditugaskan:</span>
                <span class="font-semibold text-gray-900">{{ $submission['proofreader'] }}</span>
            </div>

            <div class="pt-2.5 border-t border-gray-100">
                <span class="text-gray-500 block text-[11px]">Email Penulis:</span>
                <span class="font-mono text-gray-800 break-all">{{ $submission['corresponding_author']['email'] }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Kotak Status Eskalasi Kendala Tata Letak -->
    @if($submission['escalation']['has_active'])
        <div class="bg-purple-50/70 rounded-xl border border-purple-200 p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-900">
                    Eskalasi Masalah Aktif
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-200 text-purple-900 font-mono">
                    {{ $submission['escalation']['ticket_id'] }}
                </span>
            </div>

            <div class="space-y-1.5 text-xs">
                <span class="text-purple-800 font-semibold block">Status: {{ $submission['escalation']['status_label'] }}</span>
                <p class="text-gray-700 bg-white/70 p-2.5 rounded border border-purple-100 leading-relaxed text-[11px]">
                    {{ $submission['escalation']['reason'] }}
                </p>
                <span class="text-[10px] text-gray-500 block">Diterbitkan pada: {{ $submission['escalation']['created_at'] }}</span>
            </div>
        </div>
    @else
        <!-- Tombol Terbitkan Eskalasi Jika Ada Kendala -->
        @if(!$submission['is_completed'])
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs space-y-2">
                <h4 class="text-xs font-bold text-gray-800">Menghadapi Kendala Tata Letak?</h4>
                <p class="text-[11px] text-gray-500 leading-relaxed">
                    Jika terdapat tabel lebar, format gambar non-standar, atau sengketa layout yang memerlukan persetujuan Section Editor, ajukan eskalasi resmi.
                </p>
                <button 
                    type="button" 
                    onclick="openEscalateGalleyModal()"
                    class="w-full mt-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Eskalasi ke Section Editor</span>
                </button>
            </div>
        @endif
    @endif

    <!-- 3. Panduan Tata Letak Cepat -->
    <div class="bg-gray-50/70 rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-gray-800">Standar Format Galai</h4>
            <span class="text-[10px] font-semibold text-gray-400">BRIDA OJS</span>
        </div>

        <ul class="space-y-1.5 text-[11px] text-gray-600">
            <li class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-maroon"></span>
                <span>Kertas A4, Format 2-Kolom Seimbang</span>
            </li>
            <li class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-maroon"></span>
                <span>Margin: Atas/Bawah 2.5 cm, Kiri/Kanan 2.0 cm</span>
            </li>
            <li class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-maroon"></span>
                <span>Running Header & DOI di Halaman Ganjil</span>
            </li>
        </ul>

        <div class="pt-2 border-t border-gray-200/60">
            <a 
                href="{{ route('layouteditor.guidelines') }}" 
                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-maroon hover:underline"
            >
                <span>Lihat Panduan Galai Lengkap & Unduh Template</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    <!-- 4. Jejak Aktivitas Produksi (Timeline) -->
    <div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 shadow-2xs space-y-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
            Jejak Aktivitas Produksi
        </h4>

        <div class="relative pl-4 space-y-3 border-l-2 border-gray-100 ml-1">
            @foreach($submission['activity_timeline'] as $activity)
                <div class="relative">
                    <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $activity['badge_class'] }}"></div>
                    <span class="text-[11px] font-bold text-gray-900 block leading-tight">{{ $activity['title'] }}</span>
                    <span class="text-[10px] text-gray-500 block mt-0.5">{{ $activity['actor'] }} • {{ $activity['date'] }}</span>
                    <p class="text-[11px] text-gray-600 mt-1 leading-snug">{{ $activity['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
