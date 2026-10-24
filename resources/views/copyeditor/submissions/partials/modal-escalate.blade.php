<div 
    id="escalate-modal" 
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="escalate-modal-title"
>
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Header Modal -->
        <div class="px-6 py-4.5 border-b border-gray-200 flex items-center justify-between bg-surface-subtle/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h2 id="escalate-modal-title" class="text-sm font-bold text-gray-900">
                        Permohonan Mediasi Redaksi
                    </h2>
                    <p class="text-[11px] text-gray-500">
                        Eskalasi kendala penyuntingan ke Section Editor ({{ $submission['section_editor'] }})
                    </p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="closeEscalateModal()"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition"
                aria-label="Tutup modal"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Mediasi -->
        <form action="{{ route('copyeditor.escalate') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

            <div>
                <label for="escalate_category" class="block text-xs font-semibold text-gray-700 mb-1">
                    Kategori Masalah / Sengketa:
                </label>
                <select 
                    id="escalate_category" 
                    name="category" 
                    required
                    class="w-full p-2.5 text-xs text-gray-900 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                >
                    <option value="Ketidaksesuaian Terminologi Teknis">Ketidaksesuaian Terminologi Teknis</option>
                    <option value="Perbedaan Pendapat Substansi">Perbedaan Pendapat Substansi</option>
                    <option value="Format Sitasi & Bibliografi">Format Sitasi & Bibliografi</option>
                    <option value="Kendala Komunikasi Penulis">Kendala Komunikasi Penulis</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div>
                <label for="escalate_reason" class="block text-xs font-semibold text-gray-700 mb-1">
                    Uraian Catatan untuk Section Editor:
                </label>
                <textarea 
                    id="escalate_reason" 
                    name="reason" 
                    rows="4" 
                    required
                    placeholder="Jelaskan secara ringkas poin keberatan penulis atau kebuntuan yang memerlukan keputusan editor..."
                    class="w-full p-3 text-xs text-gray-900 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                ></textarea>
            </div>

            <p class="text-[11px] text-gray-400 leading-normal">
                Menerbitkan tiket mediasi akan memberitahukan Section Editor melalui notifikasi dan menangguhkan serah terima hingga tercapai kesepakatan.
            </p>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button 
                    type="button" 
                    onclick="closeEscalateModal()"
                    class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-2xs transition"
                >
                    Kirim Tiket Mediasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEscalateModal() {
        const modal = document.getElementById('escalate-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeEscalateModal() {
        const modal = document.getElementById('escalate-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
</script>
