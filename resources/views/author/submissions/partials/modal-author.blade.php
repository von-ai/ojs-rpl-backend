<!-- Modal Dialog: Tambah Penulis Bersama -->
<div id="modal-author" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-author-title">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div id="modal-author-backdrop" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>
        
        <div class="relative bg-white rounded-xl max-w-lg w-full p-6 text-left shadow-xl transition-all border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 id="modal-author-title" class="text-base font-bold text-gray-900">
                    Tambah Penulis Bersama
                </h3>
                <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-600 transition p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label for="input-author-given" class="block text-xs font-bold text-gray-700">Nama Depan</label>
                        <input type="text" id="input-author-given" placeholder="Contoh: Muhammad" class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon">
                    </div>
                    <div class="space-y-1">
                        <label for="input-author-family" class="block text-xs font-bold text-gray-700">Nama Belakang / Lengkap <span class="text-red-600">*</span></label>
                        <input type="text" id="input-author-family" placeholder="Contoh: Fadly, M.Eng." class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon">
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="input-author-email" class="block text-xs font-bold text-gray-700">Alamat Email Aktif <span class="text-red-600">*</span></label>
                    <input type="email" id="input-author-email" placeholder="contoh: fadly@institusi.ac.id" class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon">
                </div>

                <div class="space-y-1">
                    <label for="input-author-affiliation" class="block text-xs font-bold text-gray-700">Afiliasi / Universitas <span class="text-red-600">*</span></label>
                    <input type="text" id="input-author-affiliation" placeholder="Contoh: Universitas Hasanuddin" class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon">
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="input-author-orcid" class="block text-xs font-bold text-gray-700">ORCID iD <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <span class="text-[10px] text-gray-400">Format: 0000-0000-0000-000X</span>
                    </div>
                    <input type="text" id="input-author-orcid" placeholder="0000-0002-1825-0097" class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon font-mono">
                </div>

                <div class="space-y-2 pt-2 border-t border-gray-100">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="input-author-first" class="w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon cursor-pointer">
                        <span class="text-xs font-semibold text-gray-700">Jadikan sebagai Penulis Pertama (Urutan ke-1)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="input-author-corresponding" class="w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon cursor-pointer">
                        <span class="text-xs font-semibold text-gray-700">Tandai sebagai Penulis Korespondensi</span>
                    </label>
                </div>

                <div id="modal-author-error" class="hidden text-xs text-red-600 font-semibold p-2 bg-red-50 rounded border border-red-200"></div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" id="btn-cancel-modal" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition">
                    Batal
                </button>
                <button type="button" id="btn-save-author" class="px-4 py-2 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg transition">
                    Tambahkan Penulis
                </button>
            </div>
        </div>
    </div>
</div>
