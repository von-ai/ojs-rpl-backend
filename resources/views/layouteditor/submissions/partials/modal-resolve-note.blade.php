{{-- resources/views/layouteditor/submissions/partials/modal-resolve-note.blade.php --}}
<div id="modal-resolve-note" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-resolve-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeResolveNoteModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-resolve-title">
                            Selesaikan Catatan Proofreading
                        </h3>
                        <p class="text-[11px] text-gray-500">Konfirmasi tindakan koreksi tata letak</p>
                    </div>
                </div>

                <button type="button" onclick="closeResolveNoteModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('layouteditor.resolve-note') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
                <input type="hidden" name="note_id" id="resolve_note_id" value="">

                <!-- Tinjauan Catatan yang Diselesaikan -->
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs">
                    <span class="font-bold text-gray-500 text-[10px] uppercase block mb-1">Catatan Proofreader:</span>
                    <p id="resolve_note_description" class="text-gray-800 italic leading-relaxed"></p>
                </div>

                <!-- Penjelasan Perbaikan / Tindakan -->
                <div>
                    <label for="resolution" class="block text-xs font-semibold text-gray-700 mb-1">
                        Tindakan Perbaikan yang Dilakukan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="resolution" 
                        id="resolution" 
                        rows="3" 
                        required
                        placeholder="Contoh: Posisi Tabel 2 telah dipindahkan ke awal halaman 5 dengan lebar 2 kolom penuh pada draf galai versi 2."
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                    ></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Penjelasan ini akan dicatat dalam riwayat audit naskah.</p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        onclick="closeResolveNoteModal()"
                        class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition"
                    >
                        Tandai Selesai & Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openResolveNoteModal(noteId, description) {
        const modal = document.getElementById('modal-resolve-note');
        const idInput = document.getElementById('resolve_note_id');
        const descPreview = document.getElementById('resolve_note_description');
        if (idInput) idInput.value = noteId;
        if (descPreview) descPreview.innerText = description;
        if (modal) modal.classList.remove('hidden');
    }
    function closeResolveNoteModal() {
        const modal = document.getElementById('modal-resolve-note');
        if (modal) modal.classList.add('hidden');
    }
</script>
