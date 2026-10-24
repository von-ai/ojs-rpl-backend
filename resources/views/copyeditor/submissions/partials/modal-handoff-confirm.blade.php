<div 
    id="handoff-confirm-modal" 
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="handoff-modal-title"
>
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Header Modal -->
        <div class="p-6 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 mx-auto flex items-center justify-center shadow-2xs">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div>
                <h2 id="handoff-modal-title" class="text-base font-bold text-gray-900">
                    Konfirmasi Serah Terima ke Layout
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Pastikan seluruh syarat penerbitan terpenuhi sebelum meneruskan naskah ke tahap tata letak.
                </p>
            </div>

            <!-- Checklist Kelayakan Transparan -->
            <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200 text-left space-y-2 text-xs">
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Persetujuan penulis berstatus <strong>aktif</strong></span>
                </div>
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Integritas berkas (Checksum SHA-256) terverifikasi</span>
                </div>
                <div class="flex items-center gap-2 text-emerald-800">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tidak ada tiket eskalasi / sengketa terbuka</span>
                </div>
                <div class="pt-2 border-t border-gray-200 text-gray-600">
                    Penerima: <strong class="text-gray-900">{{ $submission['layout_editor'] }}</strong>
                </div>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end gap-2.5">
            <button 
                type="button" 
                onclick="closeHandoffModal()"
                class="px-4 py-2 text-xs font-medium text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-xl transition"
            >
                Batal
            </button>
            <button 
                type="button" 
                onclick="confirmAndSubmitHandoff()"
                class="px-4.5 py-2 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl shadow-2xs transition cursor-pointer"
            >
                Ya, Serahkan Naskah
            </button>
        </div>
    </div>
</div>

<script>
    function openHandoffModal() {
        const modal = document.getElementById('handoff-confirm-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeHandoffModal() {
        const modal = document.getElementById('handoff-confirm-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function confirmAndSubmitHandoff() {
        const form = document.getElementById('handoff-form');
        if (form) {
            form.submit();
        }
    }
</script>
