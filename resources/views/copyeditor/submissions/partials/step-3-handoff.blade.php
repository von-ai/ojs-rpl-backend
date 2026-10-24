<!-- LANGKAH 3: Serah Terima ke Layout Editor (Galley Handoff) -->
<div class="bg-white rounded-2xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
    <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg {{ $submission['handoff']['is_completed'] ? 'bg-gray-400' : ($submission['approval']['is_approved'] ? 'bg-emerald-600' : 'bg-gray-300') }} text-white font-bold text-xs flex items-center justify-center shrink-0">
                3
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 leading-tight">
                    Serah Terima ke Layout Editor
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    @if ($submission['handoff']['is_completed'])
                        Naskah final telah selesai diteruskan ke tahap perancangan tata letak publikasi (Galley).
                    @else
                        Teruskan naskah final yang telah disetujui ke tahap tata letak (layout & galley setting).
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if ($submission['handoff']['is_completed'])
        <!-- Status: Naskah Telah Selesai Diserahterimakan (Mode Riwayat) -->
        <div class="p-4.5 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="text-xs text-gray-800 flex-1">
                    <div class="flex items-center gap-2">
                        <strong class="font-bold text-sm text-gray-900">Serah Terima Selesai</strong>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Tahap Copyediting Rampung
                        </span>
                    </div>
                    <p class="mt-1 text-gray-600">
                        Diserahkan ke Layout Editor: <strong>{{ $submission['layout_editor'] }}</strong> pada {{ $submission['handoff']['completed_at'] }}.
                    </p>

                    @if($submission['handoff']['submitted_notes'])
                        <div class="mt-2.5 p-3 bg-white rounded-lg border border-gray-200 text-gray-700">
                            <span class="font-bold text-[10px] text-gray-400 uppercase tracking-wider block mb-1">
                                Catatan Serah Terima:
                            </span>
                            {{ $submission['handoff']['submitted_notes'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- Formulir Serah Terima Aktif -->
        <form action="{{ route('copyeditor.complete-handoff') }}" method="POST" id="handoff-form" class="space-y-4">
            @csrf
            <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

            <div>
                <label for="handoff_notes" class="block text-xs font-semibold text-gray-700 mb-1.5">
                    Catatan untuk Layout Editor (Opsional):
                </label>
                <textarea 
                    id="handoff_notes" 
                    name="notes" 
                    rows="3" 
                    placeholder="{{ $submission['handoff']['notes_placeholder'] }}"
                    class="w-full p-3 text-xs text-gray-900 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                ></textarea>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <div class="text-[11px] text-gray-500">
                    Layout Editor Tujuan: <strong class="text-gray-800">{{ $submission['layout_editor'] }}</strong>
                </div>

                @if($submission['approval']['is_approved'] && !$submission['escalation']['has_active'])
                    <button 
                        type="button" 
                        onclick="openHandoffModal()"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold shadow-2xs transition bg-emerald-700 hover:bg-emerald-800 text-white cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Serahkan ke Layout Editor</span>
                    </button>
                @else
                    <button 
                        type="button" 
                        disabled
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold shadow-2xs transition bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Serahkan ke Layout Editor</span>
                    </button>
                @endif
            </div>

            @if(!$submission['approval']['is_approved'])
                <p class="text-[11px] text-amber-700 bg-amber-50/60 p-2.5 rounded-lg border border-amber-200/60">
                    * Tombol serah terima akan aktif setelah penulis menyetujui seluruh naskah hasil suntingan dan tidak ada tiket eskalasi yang terbuka.
                </p>
            @endif
        </form>
    @endif
</div>
