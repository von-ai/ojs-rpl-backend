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

    @if(!$submission['is_completed'])
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
