{{-- resources/views/layouteditor/submissions/partials/modal-upload-galley.blade.php --}}
<div id="modal-upload-galley" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-upload-title" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 py-6 sm:py-10">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeUploadGalleyModal()"></div>

        <!-- Panel Konten -->
        <div class="relative bg-white rounded-2xl text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-5 sm:p-6 border border-gray-200 my-auto">
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

                <!-- Input Berkas Galai -->
                <div>
                    <label for="file_galley" id="galley-file-heading" class="block text-xs font-semibold text-gray-700 mb-1">
                        Pilih Berkas Galai (.pdf)
                    </label>

                    <!-- State 1: dropzone sebelum berkas dipilih -->
                    <label
                        for="file_galley"
                        id="galley-dropzone"
                        class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-brand-maroon transition cursor-pointer"
                    >
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="text-xs font-bold text-brand-maroon">Pilih berkas dari perangkat</p>
                            <p class="text-[10px] text-gray-400" id="galley-file-hint">PDF maksimal 20 MB</p>
                        </div>
                    </label>

                    <!-- State 2: berkas sudah dipilih -->
                    <div id="galley-file-selected" class="hidden mt-1"><div class="flex items-center gap-3 px-3 py-3 border border-green-200 bg-green-50/60 rounded-lg">
                        <div class="w-9 h-9 shrink-0 rounded-lg bg-white border border-green-200 text-green-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="galley-file-name" class="text-xs font-semibold text-gray-900 truncate"></p>
                            <p id="galley-file-size" class="text-[10px] text-gray-500"></p>
                        </div>
                        <button type="button" id="galley-file-clear" class="text-[11px] font-semibold text-brand-maroon hover:underline shrink-0">
                            Ganti
                        </button>
                    </div></div>

                    <p id="galley-file-error" class="hidden mt-1 text-[11px] font-medium text-red-600" role="alert"></p>

                    <input id="file_galley" name="file" type="file" accept=".pdf" required class="sr-only">
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
    (function () {
        const MAX_BYTES = 20 * 1024 * 1024; // selaras dengan validasi server (max:20480 KB)
        const FORMATS = {
            pdf:  { ext: '.pdf',  label: 'Pilih Berkas Galai (.pdf)',  hint: 'PDF maksimal 20 MB' },
            html: { ext: '.html', label: 'Pilih Berkas Galai (.html)', hint: 'HTML maksimal 20 MB' },
        };

        const input = document.getElementById('file_galley');
        const formatSelect = document.getElementById('format');
        if (!input || !formatSelect) return;

        const dropzone = document.getElementById('galley-dropzone');
        const selected = document.getElementById('galley-file-selected');
        const nameEl = document.getElementById('galley-file-name');
        const sizeEl = document.getElementById('galley-file-size');
        const errorEl = document.getElementById('galley-file-error');
        const heading = document.getElementById('galley-file-heading');
        const hint = document.getElementById('galley-file-hint');

        function formatSize(bytes) {
            if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            return Math.max(1, Math.round(bytes / 1024)) + ' KB';
        }

        function showError(message) {
            errorEl.textContent = message;
            errorEl.classList.toggle('hidden', !message);
        }

        function reset() {
            input.value = '';
            selected.classList.add('hidden');
            dropzone.classList.remove('hidden');
            showError('');
        }

        function applyFormat() {
            const cfg = FORMATS[formatSelect.value] || FORMATS.pdf;
            input.accept = cfg.ext;
            heading.textContent = cfg.label;
            hint.textContent = cfg.hint;
            if (input.files.length) validateAndRender();
        }

        function validateAndRender() {
            const file = input.files[0];
            if (!file) { reset(); return; }

            const cfg = FORMATS[formatSelect.value] || FORMATS.pdf;
            if (!file.name.toLowerCase().endsWith(cfg.ext)) {
                reset();
                showError('Format berkas tidak sesuai. Pilih berkas ' + cfg.ext + ' atau ubah Format Publikasi.');
                return;
            }
            if (file.size > MAX_BYTES) {
                reset();
                showError('Ukuran berkas ' + formatSize(file.size) + ' melebihi batas 20 MB.');
                return;
            }

            showError('');
            nameEl.textContent = file.name;
            nameEl.title = file.name;
            sizeEl.textContent = formatSize(file.size) + ' \u2022 siap diunggah';
            dropzone.classList.add('hidden');
            selected.classList.remove('hidden');
        }

        input.addEventListener('change', validateAndRender);
        formatSelect.addEventListener('change', applyFormat);
        document.getElementById('galley-file-clear').addEventListener('click', function () {
            reset();
            input.click();
        });

        // Pastikan formulir tidak terkirim tanpa berkas yang valid
        input.form.addEventListener('submit', function (e) {
            if (!input.files.length) {
                e.preventDefault();
                showError('Pilih berkas galai terlebih dahulu.');
            }
        });

        window.resetUploadGalleyFile = reset;
        applyFormat();
    })();

    function openUploadGalleyModal() {
        const modal = document.getElementById('modal-upload-galley');
        if (modal) modal.classList.remove('hidden');
    }
    function closeUploadGalleyModal() {
        const modal = document.getElementById('modal-upload-galley');
        if (modal) modal.classList.add('hidden');
        if (window.resetUploadGalleyFile) window.resetUploadGalleyFile();
    }
</script>
