<div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-2xs space-y-3">
    <div class="border-b border-gray-100 pb-3">
        <h3 class="text-sm font-bold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Eskalasi Ketidaksesuaian Copyedit</span>
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
            Ajukan eskalasi jika draf galai memuat perubahan substansial atau teks terpotong dari versi copyedit rujukan.
        </p>
    </div>

    @if(!empty($submission['escalation']['has_active']))
        <!-- Panel Status Tiket Eskalasi Aktif -->
        <div class="bg-purple-50/80 border border-purple-200 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-600 animate-pulse"></span>
                    <span class="text-xs font-bold text-purple-900">Eskalasi Masalah Aktif</span>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-200 text-purple-900 border border-purple-300">
                    {{ $submission['escalation']['ticket_id'] }}
                </span>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-purple-800 font-semibold">Status: {{ $submission['escalation']['status_label'] ?? 'Menunggu Mediasi Editor' }}</span>
                    @if(!empty($submission['escalation']['created_at']))
                        <span class="text-gray-500 text-[10px]">{{ $submission['escalation']['created_at'] }}</span>
                    @endif
                </div>

                <div class="bg-white/90 p-2.5 rounded-lg border border-purple-100 text-gray-800 text-[11px] leading-relaxed space-y-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Alasan Pengajuan:</span>
                    <p class="italic text-gray-700">"{{ $submission['escalation']['reason'] }}"</p>
                </div>

                <div class="p-2 bg-purple-100/60 rounded border border-purple-200/60 text-[11px] text-purple-800 leading-tight">
                    Section Editor sedang meninjau naskah ini. Persetujuan akhir (sign-off) ditangguhkan hingga mediasi selesai.
                </div>
            </div>
        </div>
    @elseif(!$submission['is_completed'])
        <form action="{{ route('proofreader.escalate') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

            <div>
                <label for="escalation_reason" class="block text-xs font-semibold text-gray-700 mb-1">
                    Alasan Eskalasi Redaksi
                </label>
                <textarea 
                    id="escalation_reason" 
                    name="reason" 
                    rows="2" 
                    required
                    placeholder="Contoh: Paragraf analisis pada subbab 4.2 hilang pada galai revisi v2..."
                    class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                ></textarea>
            </div>

            <button 
                type="submit" 
                class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 min-h-11 text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100/80 border border-amber-300 rounded-lg transition cursor-pointer"
                title="Kirim Tiket Eskalasi ke Section Editor"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                <span>Ajukan Eskalasi ke Editor</span>
            </button>
        </form>
    @else
        <p class="text-xs text-gray-500 italic">
            Naskah telah disetujui (sign-off). Eskalasi tidak diperlukan lagi.
        </p>
    @endif
</div>
