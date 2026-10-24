{{-- resources/views/layouteditor/submissions/partials/modal-upload-galley.blade.php --}}
<div id="modal-upload-galley" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-upload-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeUploadGalleyModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900" id="modal-upload-title">
                            Unggah Berkas Galai Publikasi
                        </h3>
                        <p class="text-[11px] text-gray-500">Berkas format PDF resmi terbitan berkala</p>
                    </div>
                </div>

                <button type="button" onclick="closeUploadGalleyModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('layouteditor.upload-galley') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

                <!-- Label Galai -->
                <div>
                    <label for="galley_label" class="block text-xs font-semibold text-gray-700 mb-1">
                        Label Berkas Galai
                    </label>
                    <input 
                        type="text" 
                        name="galley_label" 
                        id="galley_label" 
                        value="{{ count($submission['galleys']) > 0 ? 'PDF Full Text (v' . (count($submission['galleys']) + 1) . ' Revisi)' : 'PDF Full Text (Draf v1)' }}"
                        required
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                    >
                </div>

                <!-- Format Galai -->
                <div>
                    <label for="format" class="block text-xs font-semibold text-gray-700 mb-1">
                        Format Publikasi
                    </label>
                    <select 
                        name="format" 
                        id="format" 
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition bg-white"
                    >
                        <option value="pdf" selected>Portable Document Format (PDF Full Text)</option>
                        <option value="html">HTML Standalone / Interaktif</option>
                    </select>
                </div>

                <!-- Input Berkas PDF -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Pilih Berkas Galai (.pdf)
                    </label>
                    <div class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-brand-maroon transition">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-xs text-gray-600 justify-center">
                                <label for="file_galley" class="relative cursor-pointer font-bold text-brand-maroon hover:underline">
                                    <span>Pilih berkas dari perangkat</span>
                                    <input id="file_galley" name="file" type="file" accept=".pdf,.html" class="sr-only">
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400">PDF maksimal 25 MB</p>
                        </div>
                    </div>
                </div>

                <!-- Catatan Revisi / Keterangan -->
                <div>
                    <label for="change_summary" class="block text-xs font-semibold text-gray-700 mb-1">
                        Keterangan Perubahan (Opsional)
                    </label>
                    <textarea 
                        name="change_summary" 
                        id="change_summary" 
                        rows="2" 
                        placeholder="Contoh: Perbaikan pemotongan tabel halaman 4 dan penyesuaian running DOI."
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                    ></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        onclick="closeUploadGalleyModal()"
                        class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition"
                    >
                        Unggah & Verifikasi Berkas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openUploadGalleyModal() {
        const modal = document.getElementById('modal-upload-galley');
        if (modal) modal.classList.remove('hidden');
    }
    function closeUploadGalleyModal() {
        const modal = document.getElementById('modal-upload-galley');
        if (modal) modal.classList.add('hidden');
    }
</script>
