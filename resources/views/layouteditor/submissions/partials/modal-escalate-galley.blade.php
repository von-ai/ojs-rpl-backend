{{-- resources/views/layouteditor/submissions/partials/modal-escalate-galley.blade.php --}}
<div id="modal-escalate-galley" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-escalate-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeEscalateGalleyModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-escalate-title">
                            Eskalasi Masalah Tata Letak
                        </h3>
                        <p class="text-[11px] text-gray-500">Minta keputusan teknis redaksi ke Section Editor</p>
                    </div>
                </div>

                <button type="button" onclick="closeEscalateGalleyModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('layouteditor.escalate') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

                <!-- Penerima Eskalasi -->
                <div class="p-3 bg-purple-50/70 rounded-lg border border-purple-100 text-xs text-purple-900">
                    Tiket ini akan diteruskan langsung ke <strong>{{ $submission['section_editor'] }}</strong> (Section Editor) untuk ditindaklanjuti.
                </div>

                <!-- Alasan Eskalasi -->
                <div>
                    <label for="escalation_reason" class="block text-xs font-semibold text-gray-700 mb-1">
                        Rincian Kendala Tata Letak / Galai <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="reason" 
                        id="escalation_reason" 
                        rows="4" 
                        required
                        placeholder="Jelaskan secara spesifik kendala format, tabel atau gambar yang memerlukan keputusan redaksi (misal: permintaan penambahan halaman landscape, resolusi grafik yang pecah dari naskah sumber, dll)..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    ></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        onclick="closeEscalateGalleyModal()"
                        class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg shadow-2xs transition"
                    >
                        Terbitkan Tiket Eskalasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEscalateGalleyModal() {
        const modal = document.getElementById('modal-escalate-galley');
        if (modal) modal.classList.remove('hidden');
    }
    function closeEscalateGalleyModal() {
        const modal = document.getElementById('modal-escalate-galley');
        if (modal) modal.classList.add('hidden');
    }
</script>
