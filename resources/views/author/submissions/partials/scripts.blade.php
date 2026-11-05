<script>
document.addEventListener('DOMContentLoaded', function () {
    const draftOwner = @js(auth()->id());
    const STORAGE_KEY = `sirida_submission_draft_v2:${draftOwner}`;
    let currentStep = 1;
    const totalSteps = 5;

    // Elements
    const form = document.getElementById('submission-wizard-form');
    const formAction = document.getElementById('form-action');
    const currentStepInput = document.getElementById('current_step');
    const btnPrev = document.getElementById('btn-prev-step');
    const btnNext = document.getElementById('btn-next-step');
    const btnSaveDraft = document.getElementById('btn-save-draft');
    const btnFinalSubmit = document.getElementById('btn-final-submit');
    const submitSpinner = document.getElementById('submit-spinner');
    const draftAlert = document.getElementById('draft-restore-alert');
    const btnDiscardDraft = document.getElementById('btn-discard-draft');

    // Step 1: Section & Checklist
    const sectionSelect = document.getElementById('section_id');
    const sectionDetailPanel = document.getElementById('section-detail-panel');
    const sectionDetailName = document.getElementById('section-detail-name');
    const sectionDetailDesc = document.getElementById('section-detail-desc');
    const sectionDetailPolicy = document.getElementById('section-detail-policy');

    // Custom Searchable Combobox elements with Optimized Lazy Loading & Infinite Scroll
    const comboboxWrapper = document.getElementById('section-combobox-wrapper');
    const comboboxBtn = document.getElementById('section-combobox-button');
    const comboboxDisplay = document.getElementById('section-combobox-display');
    const comboboxChevron = document.getElementById('section-combobox-chevron');
    const comboboxDropdown = document.getElementById('section-combobox-dropdown');
    const comboboxSearch = document.getElementById('section-combobox-search');
    const comboboxClearBtn = document.getElementById('section-combobox-clear-btn');
    const comboboxList = document.getElementById('section-combobox-list');
    const comboboxEmpty = document.getElementById('section-combobox-empty');
    const comboboxLoading = document.getElementById('section-combobox-loading');
    const comboboxCount = document.getElementById('section-combobox-count');
    const comboboxLoadedCount = document.getElementById('section-combobox-loaded-count');
    const SECTIONS_API_URL = "{{ route('author.submissions.sections') }}";

    let comboboxQuery = '';
    let comboboxPage = 1;
    let comboboxHasMore = true;
    let comboboxIsLoading = false;
    let comboboxDebounceTimer = null;
    let comboboxAbortController = null;
    let comboboxLoadedItems = [];
    const comboboxCache = new Map();

    function initLoadedItemsFromDOM() {
        if (!comboboxList) return;
        const domOptions = comboboxList.querySelectorAll('.section-combobox-option');
        comboboxLoadedItems = [];
        domOptions.forEach(opt => {
            comboboxLoadedItems.push({
                id: opt.getAttribute('data-value'),
                name: opt.getAttribute('data-name'),
                description: opt.getAttribute('data-description') || '',
                policy: opt.getAttribute('data-policy') || ''
            });
        });
        updateComboboxCounts(comboboxLoadedItems.length, comboboxLoadedItems.length);
    }
    initLoadedItemsFromDOM();

    function updateComboboxCounts(loaded, total) {
        if (comboboxLoadedCount) comboboxLoadedCount.textContent = loaded;
        if (comboboxCount) comboboxCount.textContent = total;
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function createOptionElement(sec, selectedVal) {
        const opt = document.createElement('div');
        opt.setAttribute('role', 'option');
        opt.setAttribute('tabindex', '0');
        opt.setAttribute('data-value', sec.id);
        opt.setAttribute('data-name', sec.name);
        opt.setAttribute('data-description', sec.description || '');
        opt.setAttribute('data-policy', sec.policy || '');

        const isSelected = (sec.id === selectedVal);
        opt.className = `section-combobox-option group flex items-start justify-between gap-3 p-3 rounded-lg text-left transition cursor-pointer hover:bg-gray-50 ${isSelected ? 'bg-red-50/70 text-brand-maroon is-selected font-semibold' : 'text-gray-700'}`;

        opt.innerHTML = `
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="option-name font-semibold text-xs sm:text-sm leading-snug">${escapeHtml(sec.name)}</span>
                </div>
                ${sec.description ? `<p class="option-desc text-[11px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">${escapeHtml(sec.description)}</p>` : ''}
            </div>
            <svg class="option-check w-4 h-4 text-brand-maroon shrink-0 self-center ${isSelected ? '' : 'hidden'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        `;

        return opt;
    }

    async function fetchComboboxSections(page = 1, query = '', append = false) {
        if (comboboxIsLoading && append) return;

        if (!append && comboboxAbortController) {
            comboboxAbortController.abort();
        }
        comboboxAbortController = new AbortController();

        comboboxIsLoading = true;
        if (comboboxLoading) comboboxLoading.classList.remove('hidden');

        const cacheKey = `${query}__${page}`;
        let resData = comboboxCache.get(cacheKey);

        if (!resData) {
            try {
                const url = new URL(SECTIONS_API_URL, window.location.origin);
                url.searchParams.set('page', page);
                url.searchParams.set('per_page', '8');
                if (query) url.searchParams.set('q', query);

                const response = await fetch(url.toString(), {
                    signal: comboboxAbortController.signal,
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    resData = await response.json();
                    comboboxCache.set(cacheKey, resData);
                }
            } catch (err) {
                if (err.name === 'AbortError') {
                    return;
                }
                console.warn('Gagal memuat seksi jurnal dari API:', err);
            } finally {
                comboboxIsLoading = false;
            }
        } else {
            comboboxIsLoading = false;
        }

        if (comboboxLoading) comboboxLoading.classList.add('hidden');
        if (!resData) return;

        comboboxPage = resData.current_page || page;
        comboboxHasMore = !!resData.has_more;
        const total = resData.total || 0;
        const items = resData.data || [];
        const currentSelectedVal = sectionSelect ? sectionSelect.value : '';

        if (!append) {
            if (comboboxList) {
                const existing = comboboxList.querySelectorAll('.section-combobox-option');
                existing.forEach(e => e.remove());
            }
            comboboxLoadedItems = [];
        }

        items.forEach(sec => {
            if (!comboboxLoadedItems.some(item => item.id === sec.id)) {
                comboboxLoadedItems.push(sec);
                if (comboboxList && comboboxLoading) {
                    const el = createOptionElement(sec, currentSelectedVal);
                    comboboxList.insertBefore(el, comboboxLoading);
                }
            }
        });

        if (comboboxEmpty) {
            if (comboboxLoadedItems.length === 0) {
                comboboxEmpty.classList.remove('hidden');
            } else {
                comboboxEmpty.classList.add('hidden');
            }
        }

        updateComboboxCounts(comboboxLoadedItems.length, total);
        syncComboboxUI();
    }

    function syncComboboxUI() {
        if (!sectionSelect) return;
        const val = sectionSelect.value;
        let matched = false;

        const options = comboboxList ? comboboxList.querySelectorAll('.section-combobox-option') : [];
        options.forEach(opt => {
            const optVal = opt.getAttribute('data-value');
            const isMatch = (optVal === val);
            const checkIcon = opt.querySelector('.option-check');

            if (isMatch) {
                matched = true;
                opt.classList.add('bg-red-50/70', 'text-brand-maroon', 'is-selected', 'font-semibold');
                opt.classList.remove('text-gray-700');
                if (checkIcon) checkIcon.classList.remove('hidden');
                if (comboboxDisplay) comboboxDisplay.textContent = opt.getAttribute('data-name');
            } else {
                opt.classList.remove('bg-red-50/70', 'text-brand-maroon', 'is-selected', 'font-semibold');
                opt.classList.add('text-gray-700');
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        });

        if (!matched) {
            const selectedOpt = sectionSelect.options[sectionSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                if (comboboxDisplay) comboboxDisplay.textContent = selectedOpt.getAttribute('data-name') || selectedOpt.text;
            } else {
                if (comboboxDisplay) comboboxDisplay.textContent = '-- Pilih Seksi / Bidang Jurnal --';
            }
        }
    }

    function openCombobox() {
        if (!comboboxDropdown) return;
        comboboxDropdown.classList.remove('hidden');
        if (comboboxBtn) comboboxBtn.setAttribute('aria-expanded', 'true');
        if (comboboxChevron) comboboxChevron.classList.add('rotate-180');
        if (comboboxSearch) {
            comboboxSearch.value = '';
            comboboxQuery = '';
            comboboxPage = 1;
            comboboxHasMore = true;
            if (comboboxClearBtn) comboboxClearBtn.classList.add('hidden');
            fetchComboboxSections(1, '', false);
            setTimeout(() => comboboxSearch.focus(), 50);
        }
    }

    function closeCombobox() {
        if (!comboboxDropdown) return;
        comboboxDropdown.classList.add('hidden');
        if (comboboxBtn) comboboxBtn.setAttribute('aria-expanded', 'false');
        if (comboboxChevron) comboboxChevron.classList.remove('rotate-180');
    }

    function selectComboboxOption(optElement) {
        const val = optElement.getAttribute('data-value');
        const name = optElement.getAttribute('data-name');
        const desc = optElement.getAttribute('data-description') || '';
        const policy = optElement.getAttribute('data-policy') || '';

        if (sectionSelect) {
            let exists = false;
            for (let i = 0; i < sectionSelect.options.length; i++) {
                if (sectionSelect.options[i].value === val) {
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                const newOpt = document.createElement('option');
                newOpt.value = val;
                newOpt.text = name;
                newOpt.setAttribute('data-name', name);
                newOpt.setAttribute('data-description', desc);
                newOpt.setAttribute('data-policy', policy);
                sectionSelect.appendChild(newOpt);
            }
            sectionSelect.value = val;
            sectionSelect.dispatchEvent(new Event('change'));
        }
        syncComboboxUI();
        updateSectionDetail();
        closeCombobox();
        if (comboboxBtn) comboboxBtn.focus();
    }

    if (comboboxList) {
        comboboxList.addEventListener('scroll', function () {
            if (comboboxIsLoading || !comboboxHasMore) return;
            const threshold = 35;
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - threshold) {
                fetchComboboxSections(comboboxPage + 1, comboboxQuery, true);
            }
        });

        comboboxList.addEventListener('click', function (e) {
            const opt = e.target.closest('.section-combobox-option');
            if (opt) {
                selectComboboxOption(opt);
            }
        });

        comboboxList.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const opt = e.target.closest('.section-combobox-option');
                if (opt) {
                    e.preventDefault();
                    selectComboboxOption(opt);
                }
            }
        });
    }

    if (comboboxBtn) {
        comboboxBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (comboboxDropdown && comboboxDropdown.classList.contains('hidden')) {
                openCombobox();
            } else {
                closeCombobox();
            }
        });
    }

    if (comboboxSearch) {
        comboboxSearch.addEventListener('input', (e) => {
            const val = e.target.value.trim();
            if (comboboxClearBtn) {
                if (val) comboboxClearBtn.classList.remove('hidden');
                else comboboxClearBtn.classList.add('hidden');
            }
            clearTimeout(comboboxDebounceTimer);
            comboboxDebounceTimer = setTimeout(() => {
                comboboxQuery = val;
                comboboxPage = 1;
                comboboxHasMore = true;
                fetchComboboxSections(1, comboboxQuery, false);
            }, 250);
        });

        comboboxSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCombobox();
                if (comboboxBtn) comboboxBtn.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const firstVisible = comboboxList ? comboboxList.querySelector('.section-combobox-option') : null;
                if (firstVisible) {
                    selectComboboxOption(firstVisible);
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const firstOption = comboboxList ? comboboxList.querySelector('.section-combobox-option') : null;
                if (firstOption) firstOption.focus();
            }
        });
    }

    if (comboboxClearBtn) {
        comboboxClearBtn.addEventListener('click', () => {
            if (comboboxSearch) {
                comboboxSearch.value = '';
                comboboxClearBtn.classList.add('hidden');
                comboboxQuery = '';
                comboboxPage = 1;
                comboboxHasMore = true;
                fetchComboboxSections(1, '', false);
                comboboxSearch.focus();
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (comboboxWrapper && !comboboxWrapper.contains(e.target)) {
            closeCombobox();
        }
    });

    function updateSectionDetail() {
        if (!sectionSelect) return;
        const selectedOpt = sectionSelect.options[sectionSelect.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            if (sectionDetailName) sectionDetailName.textContent = selectedOpt.getAttribute('data-name') || selectedOpt.text;
            if (sectionDetailDesc) sectionDetailDesc.textContent = selectedOpt.getAttribute('data-description') || '-';
            if (sectionDetailPolicy) sectionDetailPolicy.textContent = selectedOpt.getAttribute('data-policy') || '-';
            if (sectionDetailPanel) sectionDetailPanel.classList.remove('hidden');
        } else {
            if (sectionDetailPanel) sectionDetailPanel.classList.add('hidden');
        }
        syncComboboxUI();
    }

    if (sectionSelect) {
        sectionSelect.addEventListener('change', () => {
            updateSectionDetail();
            saveDraftToStorage();
        });
    }

    const checkAllBtn = document.getElementById('btn-check-all');
    const checklistItems = document.querySelectorAll('.checklist-item');
    const masterAccepted = document.getElementById('author_guidelines_accepted');

    // Step 2: File upload & Azure Direct SAS Upload (SRS NF-01)
    const manuscriptInput = document.getElementById('manuscript_file');
    const dropzonePrompt = document.getElementById('dropzone-prompt');
    const dropzoneUploading = document.getElementById('dropzone-uploading');
    const dropzoneSelected = document.getElementById('dropzone-selected');
    const uploadingFileName = document.getElementById('uploading-file-name');
    const uploadingFileStats = document.getElementById('uploading-file-stats');
    const uploadProgressBar = document.getElementById('upload-progress-bar');
    const btnCancelUpload = document.getElementById('btn-cancel-upload');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const btnReselectFile = document.getElementById('btn-reselect-file');
    const btnRemoveFile = document.getElementById('btn-remove-file');
    const fileErrorMsg = document.getElementById('file-error-msg');

    const blobPathInput = document.getElementById('blob_path');
    const blobNameInput = document.getElementById('blob_name');
    const blobSizeInput = document.getElementById('blob_size');
    const blobMimeInput = document.getElementById('blob_mime');
    const blobChecksumInput = document.getElementById('blob_checksum');

    const UPLOAD_SESSION_URL = "{{ route('author.submissions.upload-session') }}";
    let currentUploadXhr = null;
    let isUploadingToAzure = false;

    // Step 3: Metadata
    const titleInput = document.getElementById('title');
    const titleWordCount = document.getElementById('title-word-count');
    const abstractInput = document.getElementById('abstract');
    const abstractWordCount = document.getElementById('abstract-word-count');
    const keywordInputBox = document.getElementById('keyword-input-box');
    const keywordsChipList = document.getElementById('keywords-chip-list');
    const keywordsHiddenInput = document.getElementById('keywords-hidden-input');
    const keywordsCount = document.getElementById('keywords-count');

    // Step 4: Contributors
    let contributors = [];
    try {
        contributors = JSON.parse(keywordsHiddenInput.form.querySelector('#contributors-hidden-input').value || '[]');
    } catch (e) {
        contributors = [];
    }
    if (!contributors.length) {
        contributors = [{
            given_name: 'Andi Muhammad',
            family_name: 'Ilyas',
            email: 'andi.ilyas@unhas.ac.id',
            affiliation: 'Universitas Hasanuddin, Makassar',
            orcid_id: '0000-0002-1825-0097',
            sequence: 1,
            corresponding: true
        }];
    }
    const contributorsHiddenInput = document.getElementById('contributors-hidden-input');
    const contributorsContainer = document.getElementById('contributors-list-container');
    const btnOpenAddAuthor = document.getElementById('btn-open-add-author');
    const modalAuthor = document.getElementById('modal-author');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const btnCancelModal = document.getElementById('btn-cancel-modal');
    const btnSaveAuthor = document.getElementById('btn-save-author');

    // Keywords local state
    let keywords = [];
    try {
        keywords = JSON.parse(keywordsHiddenInput.value || '[]');
        if (!Array.isArray(keywords)) keywords = [];
    } catch (e) {
        keywords = [];
    }

    // Step 1: Checklist listeners
    function updateCheckAllButtonState() {
        if (!checkAllBtn) return;
        const allChecked = Array.from(checklistItems).length > 0 && Array.from(checklistItems).every(cb => cb.checked);
        if (allChecked) {
            checkAllBtn.title = 'Hapus Semua Centang (Deselect All)';
            checkAllBtn.setAttribute('aria-label', 'Hapus Semua Centang');
            checkAllBtn.className = 'inline-flex items-center justify-center w-7 h-7 rounded-lg text-gray-800 bg-gray-100 hover:bg-gray-200 border border-gray-300 transition shadow-2xs cursor-pointer group active:scale-[0.96]';
        } else {
            checkAllBtn.title = 'Centang Semua Persyaratan (Select All)';
            checkAllBtn.setAttribute('aria-label', 'Centang Semua Persyaratan');
            checkAllBtn.className = 'inline-flex items-center justify-center w-7 h-7 rounded-lg text-gray-500 hover:text-gray-800 bg-white hover:bg-gray-100 border border-gray-200 hover:border-gray-300 transition shadow-2xs cursor-pointer group active:scale-[0.96]';
        }
    }

    function updateMasterChecklist() {
        const allChecked = Array.from(checklistItems).length > 0 && Array.from(checklistItems).every(cb => cb.checked);
        if (masterAccepted) masterAccepted.checked = allChecked;
        updateCheckAllButtonState();
    }

    checklistItems.forEach(cb => {
        cb.addEventListener('change', () => {
            updateMasterChecklist();
            saveDraftToStorage();
        });
    });

    if (checkAllBtn) {
        checkAllBtn.addEventListener('click', () => {
            const allCurrentlyChecked = Array.from(checklistItems).every(cb => cb.checked);
            checklistItems.forEach(cb => cb.checked = !allCurrentlyChecked);
            updateMasterChecklist();
            saveDraftToStorage();
        });
    }

    // Inisialisasi state checklist saat load (termasuk jika data dari draf server tercentang)
    updateMasterChecklist();

    // ==========================================
    // INDEXEDDB FILE STORAGE UNTUK DRAF
    // ==========================================
    const DB_NAME = 'brida_ojs_draft_store';
    const DB_VERSION = 1;
    const DB_FILE_KEY = `manuscript_file:${draftOwner}`;

    function formatFileSize(bytes) {
        if (!bytes || isNaN(bytes) || bytes <= 0) return '0 KB';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function openDraftDB() {
        return new Promise((resolve) => {
            if (!window.indexedDB) {
                return resolve(null);
            }
            try {
                const request = window.indexedDB.open(DB_NAME, DB_VERSION);
                request.onupgradeneeded = function (e) {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('files')) {
                        db.createObjectStore('files');
                    }
                };
                request.onsuccess = function (e) {
                    resolve(e.target.result);
                };
                request.onerror = function () {
                    resolve(null);
                };
            } catch (e) {
                resolve(null);
            }
        });
    }

    async function saveFileToDraftDB(file) {
        try {
            const db = await openDraftDB();
            if (!db) return false;
            return new Promise((resolve) => {
                const tx = db.transaction('files', 'readwrite');
                const store = tx.objectStore('files');
                store.put(file, DB_FILE_KEY);
                tx.oncomplete = () => resolve(true);
                tx.onerror = () => resolve(false);
            });
        } catch (e) {
            console.warn('Gagal menyimpan berkas ke IndexedDB:', e);
            return false;
        }
    }

    async function getFileFromDraftDB() {
        try {
            const db = await openDraftDB();
            if (!db) return null;
            return new Promise((resolve) => {
                const tx = db.transaction('files', 'readonly');
                const store = tx.objectStore('files');
                const req = store.get(DB_FILE_KEY);
                req.onsuccess = () => resolve(req.result || null);
                req.onerror = () => resolve(null);
            });
        } catch (e) {
            console.warn('Gagal membaca berkas dari IndexedDB:', e);
            return null;
        }
    }

    async function clearFileFromDraftDB() {
        try {
            const db = await openDraftDB();
            if (!db) return false;
            return new Promise((resolve) => {
                const tx = db.transaction('files', 'readwrite');
                const store = tx.objectStore('files');
                store.delete(DB_FILE_KEY);
                tx.oncomplete = () => resolve(true);
                tx.onerror = () => resolve(false);
            });
        } catch (e) {
            console.warn('Gagal menghapus berkas dari IndexedDB:', e);
            return false;
        }
    }

    // Helper untuk mengecek apakah ada berkas naskah yang terlampir (baik baru dipilih, diunggah ke cloud, atau dari draf tersimpan)
    function hasManuscriptAttached() {
        const hasNewlySelected = !!(manuscriptInput && manuscriptInput.files && manuscriptInput.files[0]);
        const hasBlobUploaded = !!(blobPathInput && blobPathInput.value.trim() !== '');
        const existingIdInput = document.getElementById('existing_manuscript_id');
        const removeInput = document.getElementById('remove_manuscript');
        const hasExisting = !!(existingIdInput && existingIdInput.value.trim() !== '' && (!removeInput || removeInput.value !== '1'));
        return hasNewlySelected || hasBlobUploaded || hasExisting;
    }

    // Helper fungsi update status visual dropzone naskah
    function updateDropzoneUI(hasFile, fileName = '', fileSize = 0) {
        if (dropzoneUploading) dropzoneUploading.classList.add('hidden');
        if (hasFile) {
            if (selectedFileName) selectedFileName.textContent = fileName;
            if (selectedFileSize) selectedFileSize.textContent = formatFileSize(fileSize);
            if (dropzonePrompt) dropzonePrompt.classList.add('hidden');
            if (dropzoneSelected) {
                dropzoneSelected.classList.remove('hidden');
                dropzoneSelected.classList.add('flex');
            }
        } else {
            if (dropzonePrompt) dropzonePrompt.classList.remove('hidden');
            if (dropzoneSelected) {
                dropzoneSelected.classList.add('hidden');
                dropzoneSelected.classList.remove('flex');
            }
        }
    }

    async function handleUploadFailure() {
        isUploadingToAzure = false;
        currentUploadXhr = null;
        if (dropzoneUploading) dropzoneUploading.classList.add('hidden');
        if (blobPathInput) blobPathInput.value = '';
        if (blobNameInput) blobNameInput.value = '';
        if (blobSizeInput) blobSizeInput.value = '';
        if (blobMimeInput) blobMimeInput.value = '';
        if (blobChecksumInput) blobChecksumInput.value = '';

        const file = manuscriptInput?.files?.[0];
        updateDropzoneUI(!!file, file?.name, file?.size);
        if (file) {
            await saveFileToDraftDB(file);
            saveDraftToStorage();
        }
        if (fileErrorMsg) {
            fileErrorMsg.textContent = 'Unggahan langsung gagal. Berkas yang dipilih akan dikirim bersama formulir.';
            fileErrorMsg.classList.remove('hidden');
        }
    }

    async function uploadFileDirectToAzure(file) {
        isUploadingToAzure = true;
        if (fileErrorMsg) fileErrorMsg.classList.add('hidden');

        // Reset & tampilkan Progress Bar (SRS NF-01)
        if (uploadingFileName) uploadingFileName.textContent = file.name;
        if (uploadProgressBar) uploadProgressBar.style.width = '0%';
        if (uploadingFileStats) uploadingFileStats.textContent = `0% (0 KB / ${formatFileSize(file.size)})`;

        if (dropzonePrompt) dropzonePrompt.classList.add('hidden');
        if (dropzoneSelected) {
            dropzoneSelected.classList.add('hidden');
            dropzoneSelected.classList.remove('flex');
        }
        if (dropzoneUploading) dropzoneUploading.classList.remove('hidden');

        try {
            const csrfToken = document.querySelector('input[name="_token"]')?.value || '';
            const sessionRes = await fetch(UPLOAD_SESSION_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    filename: file.name,
                    byte_size: file.size,
                    mime_type: file.type || 'application/octet-stream'
                })
            });

            if (!sessionRes.ok) {
                const errData = await sessionRes.json().catch(() => ({}));
                throw new Error(errData.message || 'Gagal membuat sesi izin unggah ke penyimpanan cloud.');
            }

            const sessionData = await sessionRes.json();
            const uploadUrl = sessionData.upload_url;
            const headers = sessionData.headers || {};

            // Mengunggah langsung dari peramban ke Azure Blob Storage (Bypass server Laravel)
            const xhr = new XMLHttpRequest();
            currentUploadXhr = xhr;

            xhr.open('PUT', uploadUrl, true);
            for (const [k, v] of Object.entries(headers)) {
                xhr.setRequestHeader(k, v);
            }
            xhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    const pct = Math.min(100, Math.round((e.loaded / e.total) * 100));
                    if (uploadProgressBar) uploadProgressBar.style.width = pct + '%';
                    if (uploadingFileStats) {
                        uploadingFileStats.textContent = `${pct}% (${formatFileSize(e.loaded)} / ${formatFileSize(e.total)})`;
                    }
                }
            };

            xhr.onload = async function () {
                isUploadingToAzure = false;
                currentUploadXhr = null;

                if (xhr.status === 200 || xhr.status === 201) {
                    if (blobPathInput) blobPathInput.value = sessionData.blob_path;
                    if (blobNameInput) blobNameInput.value = sessionData.file_name;
                    if (blobSizeInput) blobSizeInput.value = sessionData.byte_size;
                    if (blobMimeInput) blobMimeInput.value = sessionData.mime_type;

                    updateDropzoneUI(true, file.name, file.size);
                    await saveFileToDraftDB(file);
                    saveDraftToStorage();

                    if (window.toast && typeof window.toast.success === 'function') {
                        window.toast.success('Berkas naskah berhasil diunggah langsung ke Azure Blob Storage.', 'Unggah Sukses');
                    }
                } else {
                    await handleUploadFailure();
                }
            };

            xhr.onerror = async function () {
                await handleUploadFailure();
            };

            xhr.onabort = function () {
                isUploadingToAzure = false;
                currentUploadXhr = null;
                updateDropzoneUI(false);
            };

            xhr.send(file);
        } catch (err) {
            await handleUploadFailure();
        }
    }

    // Step 2: File listeners & 20 MB validation
    if (manuscriptInput) {
        manuscriptInput.addEventListener('change', async function () {
            fileErrorMsg.classList.add('hidden');
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const maxSize = 20 * 1024 * 1024; // 20 MB
                const allowedExtensions = ['.pdf', '.doc', '.docx'];
                const ext = '.' + file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(ext)) {
                    fileErrorMsg.textContent = 'Jenis dokumen tidak sesuai. Harap pilih dokumen naskah dengan format PDF (.pdf) atau Microsoft Word (.docx, .doc).';
                    fileErrorMsg.classList.remove('hidden');
                    this.value = '';
                    updateDropzoneUI(false);
                    await clearFileFromDraftDB();
                    saveDraftToStorage();
                    return;
                }

                if (file.size > maxSize) {
                    fileErrorMsg.textContent = 'Ukuran berkas terlalu besar. Batas maksimal yang diperbolehkan adalah 20 MB. Silakan pilih berkas lain atau kurangi ukuran dokumen Anda.';
                    fileErrorMsg.classList.remove('hidden');
                    this.value = '';
                    updateDropzoneUI(false);
                    await clearFileFromDraftDB();
                    saveDraftToStorage();
                    return;
                }

                const removeManuscriptInput = document.getElementById('remove_manuscript');
                if (removeManuscriptInput) removeManuscriptInput.value = '0';

                // Jalankan proses unggah langsung ke Azure Blob Storage via SAS URL (NF-01)
                await uploadFileDirectToAzure(file);
            }
        });
    }

    if (btnCancelUpload) {
        btnCancelUpload.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentUploadXhr) {
                currentUploadXhr.abort();
            }
            isUploadingToAzure = false;
            updateDropzoneUI(false);
            if (manuscriptInput) manuscriptInput.value = '';
        });
    }

    if (btnReselectFile && manuscriptInput) {
        btnReselectFile.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            manuscriptInput.click();
        });
    }

    if (btnRemoveFile && manuscriptInput) {
        btnRemoveFile.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (currentUploadXhr) {
                currentUploadXhr.abort();
            }
            isUploadingToAzure = false;
            manuscriptInput.value = '';
            if (blobPathInput) blobPathInput.value = '';
            if (blobNameInput) blobNameInput.value = '';
            if (blobSizeInput) blobSizeInput.value = '';
            if (blobMimeInput) blobMimeInput.value = '';
            if (blobChecksumInput) blobChecksumInput.value = '';
            updateDropzoneUI(false);
            fileErrorMsg.classList.add('hidden');
            const removeManuscriptInput = document.getElementById('remove_manuscript');
            if (removeManuscriptInput) removeManuscriptInput.value = '1';
            const existingManuscriptId = document.getElementById('existing_manuscript_id');
            if (existingManuscriptId) existingManuscriptId.value = '';

            await clearFileFromDraftDB();
            saveDraftToStorage();
        });
    }

    // Drag and drop visual feedback pada dropzone naskah & kartu berkas
    const dropTargets = [dropzonePrompt, dropzoneSelected].filter(Boolean);
    dropTargets.forEach(target => {
        ['dragenter', 'dragover'].forEach(eventName => {
            target.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                target.classList.add('border-brand-maroon', 'bg-red-50/20');
            });
        });

        ['dragleave', 'dragend'].forEach(eventName => {
            target.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                target.classList.remove('border-brand-maroon', 'bg-red-50/20');
            });
        });

        target.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            target.classList.remove('border-brand-maroon', 'bg-red-50/20');
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                manuscriptInput.files = e.dataTransfer.files;
                manuscriptInput.dispatchEvent(new Event('change'));
            }
        });
    });

    // Helper hitung jumlah kata
    function countWords(str) {
        if (!str) return 0;
        const words = str.trim().split(/\s+/).filter(Boolean);
        return words.length;
    }

    const TITLE_MAX_WORDS = 20;
    const ABSTRACT_MAX_WORDS = 250;

    // Step 3: Word Counters (Batasan Per Kata)
    function updateTitleCounters() {
        if (titleInput && titleWordCount) {
            const words = countWords(titleInput.value);
            titleWordCount.textContent = words;
            if (words > TITLE_MAX_WORDS) {
                titleWordCount.classList.add('text-red-600', 'font-bold');
            } else {
                titleWordCount.classList.remove('text-red-600', 'font-bold');
            }
        }
    }

    function updateAbstractCounters() {
        if (abstractInput && abstractWordCount) {
            const words = countWords(abstractInput.value);
            abstractWordCount.textContent = words;
            if (words > ABSTRACT_MAX_WORDS) {
                abstractWordCount.classList.add('text-red-600', 'font-bold');
            } else {
                abstractWordCount.classList.remove('text-red-600', 'font-bold');
            }
        }
    }

    if (titleInput) {
        titleInput.addEventListener('input', () => {
            updateTitleCounters();
            saveDraftToStorage();
        });
    }

    if (abstractInput) {
        abstractInput.addEventListener('input', () => {
            updateAbstractCounters();
            saveDraftToStorage();
        });
    }

    const commentsForEditorInput = document.getElementById('comments_for_editor');
    if (commentsForEditorInput) {
        commentsForEditorInput.addEventListener('input', () => {
            saveDraftToStorage();
        });
    }

    const subjectAreaInput = document.getElementById('subject_area');
    if (subjectAreaInput) {
        subjectAreaInput.addEventListener('input', () => {
            saveDraftToStorage();
        });
    }

    const referencesInputBox = document.getElementById('references');
    if (referencesInputBox) {
        referencesInputBox.addEventListener('input', () => {
            saveDraftToStorage();
        });
    }

    // Step 3: Keywords Chips Management
    function renderKeywordChips() {
        keywordsChipList.innerHTML = '';
        keywords.forEach((kw, index) => {
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-brand-maroon border border-red-200';
            chip.innerHTML = `
                <span>${kw}</span>
                <button type="button" class="text-brand-maroon hover:text-red-900 transition p-0.5" data-kw-index="${index}" title="Hapus kata kunci">&times;</button>
            `;
            keywordsChipList.appendChild(chip);
        });

        keywordsHiddenInput.value = JSON.stringify(keywords);
        keywordsCount.textContent = keywords.length;

        // Add delete handlers
        keywordsChipList.querySelectorAll('button[data-kw-index]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-kw-index'), 10);
                keywords.splice(idx, 1);
                renderKeywordChips();
                saveDraftToStorage();
            });
        });
    }

    function addKeyword(keywordText) {
        const clean = keywordText.trim().replace(/^,+|,+$/g, '');
        if (!clean) return;

        if (keywords.length >= 20) {
            if (window.toast && typeof window.toast.warning === 'function') {
                window.toast.warning(
                    'Maksimal 20 kata kunci yang diperbolehkan. Hapus salah satu kata kunci jika ingin menambahkan istilah baru.',
                    'Batas Kata Kunci'
                );
            } else if (typeof window.showToast === 'function') {
                window.showToast({
                    title: 'Batas Kata Kunci',
                    message: 'Maksimal 20 kata kunci yang diperbolehkan. Hapus salah satu kata kunci jika ingin menambahkan istilah baru.',
                    type: 'warning'
                });
            }
            if (keywordInputBox) keywordInputBox.value = '';
            return;
        }

        // Jika kata kunci sudah ada, lewati tanpa memunculkan toast
        if (keywords.some(k => k.toLowerCase() === clean.toLowerCase())) {
            if (keywordInputBox) keywordInputBox.value = '';
            return;
        }

        keywords.push(clean);
        renderKeywordChips();
        saveDraftToStorage();
    }

    if (keywordInputBox) {
        keywordInputBox.addEventListener('paste', function (e) {
            const pasteData = (e.clipboardData || window.clipboardData)?.getData('text');
            if (pasteData && (pasteData.includes(',') || pasteData.includes(';') || pasteData.includes('\n'))) {
                e.preventDefault();
                const items = pasteData.split(/[,;\n]+/).map(s => s.trim()).filter(Boolean);
                items.forEach(item => addKeyword(item));
                this.value = '';
            }
        });

        keywordInputBox.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addKeyword(this.value);
                this.value = '';
            }
        });

        keywordInputBox.addEventListener('blur', function () {
            if (this.value.trim()) {
                addKeyword(this.value);
                this.value = '';
            }
        });
    }

    // Step 4: Contributors List & Modal
    function moveContributor(fromIdx, toIdx) {
        if (fromIdx < 0 || fromIdx >= contributors.length || toIdx < 0 || toIdx >= contributors.length || fromIdx === toIdx) return;
        const [moved] = contributors.splice(fromIdx, 1);
        contributors.splice(toIdx, 0, moved);
        contributors.forEach((a, i) => {
            a.sequence = i + 1;
        });
        renderContributors();
        saveDraftToStorage();
    }

    function renderContributors() {
        contributorsContainer.innerHTML = '';
        contributorsHiddenInput.value = JSON.stringify(contributors);

        contributors.forEach((author, index) => {
            const card = document.createElement('div');
            card.className = 'contributor-card group relative p-3.5 sm:p-4 rounded-xl border border-gray-200 bg-white hover:border-gray-300 hover:shadow-2xs transition flex flex-col sm:flex-row sm:items-center justify-between gap-3.5';
            card.setAttribute('draggable', 'true');
            card.setAttribute('data-index', index);

            const fullName = (author.given_name ? author.given_name + ' ' : '') + author.family_name;
            const isFirst = index === 0;
            const canDelete = contributors.length > 1;

            card.innerHTML = `
                <div class="flex items-start gap-2.5 sm:gap-3 min-w-0 flex-1">
                    <!-- Drag Handle (Desktop) -->
                    <div 
                        class="drag-handle cursor-grab active:cursor-grabbing p-1.5 -ml-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition shrink-0 self-center hidden sm:flex items-center justify-center" 
                        title="Tahan dan geser kartu untuk memindahkan urutan penulis"
                        aria-label="Geser urutan penulis"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="5" r="1.2" fill="currentColor" />
                            <circle cx="9" cy="12" r="1.2" fill="currentColor" />
                            <circle cx="9" cy="19" r="1.2" fill="currentColor" />
                            <circle cx="15" cy="5" r="1.2" fill="currentColor" />
                            <circle cx="15" cy="12" r="1.2" fill="currentColor" />
                            <circle cx="15" cy="19" r="1.2" fill="currentColor" />
                        </svg>
                    </div>

                    <!-- Nomor Urut / Pill Posisi -->
                    <div class="w-8 h-8 rounded-full ${isFirst ? 'bg-amber-600 text-white shadow-2xs ring-2 ring-amber-100' : 'bg-gray-100 text-gray-700'} text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                        ${index + 1}
                    </div>

                    <!-- Keterangan Penulis -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs sm:text-sm font-bold text-gray-900">${escapeHtml(fullName)}</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5 truncate">${escapeHtml(author.affiliation)} &bull; ${escapeHtml(author.email)}</p>
                        ${author.orcid_id ? `<p class="text-[10px] font-mono text-gray-400 mt-0.5">ORCID: ${escapeHtml(author.orcid_id)}</p>` : ''}
                    </div>
                </div>

                <!-- Kontrol Urutan & Aksi -->
                <div class="flex items-center flex-wrap gap-2 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 justify-between sm:justify-end">
                    
                    <!-- Tombol Naik / Turun -->
                    <div class="inline-flex items-center bg-gray-50 p-0.5 rounded-lg border border-gray-200 shadow-2xs">
                        <button 
                            type="button" 
                            class="btn-move-up p-1.5 text-gray-600 hover:text-gray-900 hover:bg-white rounded transition disabled:opacity-25 disabled:cursor-not-allowed cursor-pointer" 
                            data-index="${index}" 
                            ${index === 0 ? 'disabled' : ''} 
                            title="${index === 0 ? 'Sudah berada di posisi teratas' : 'Pindahkan ke atas (naikkan urutan)'}"
                            aria-label="Pindahkan penulis ke atas"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                            </svg>
                        </button>
                        <button 
                            type="button" 
                            class="btn-move-down p-1.5 text-gray-600 hover:text-gray-900 hover:bg-white rounded transition disabled:opacity-25 disabled:cursor-not-allowed cursor-pointer" 
                            data-index="${index}" 
                            ${index === contributors.length - 1 ? 'disabled' : ''} 
                            title="${index === contributors.length - 1 ? 'Sudah berada di posisi terbawah' : 'Pindahkan ke bawah (turunkan urutan)'}"
                            aria-label="Pindahkan penulis ke bawah"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>

                    <!-- Tombol Cepat: Jadikan Penulis Pertama -->
                    ${!isFirst ? `
                        <button 
                            type="button" 
                            class="btn-set-first text-[11px] font-semibold text-gray-700 hover:text-brand-maroon bg-white hover:bg-amber-50/70 border border-gray-200 hover:border-amber-300 px-2.5 py-1.5 rounded-lg transition active:scale-95 cursor-pointer shadow-2xs" 
                            data-index="${index}" 
                            title="Jadikan penulis ini sebagai Penulis Pertama (posisi nomor 1)"
                        >
                            Jadikan Penulis Pertama
                        </button>
                    ` : ''}

                    <!-- Radio Penunjuk Korespondensi -->
                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer bg-white hover:bg-gray-50 px-2.5 py-1.5 rounded-lg border border-gray-200 shadow-2xs transition" title="Tandai sebagai Penulis Korespondensi">
                        <input 
                            type="radio" 
                            name="corresponding_author_radio" 
                            value="${index}" 
                            ${author.corresponding ? 'checked' : ''} 
                            class="w-3.5 h-3.5 text-brand-maroon focus:ring-brand-maroon radio-corresponding cursor-pointer"
                        >
                        <span class="font-medium text-[11px] sm:text-xs">Korespondensi</span>
                    </label>

                    <!-- Tombol Hapus Penulis -->
                    ${canDelete ? `
                        <button 
                            type="button" 
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition btn-remove-author cursor-pointer" 
                            data-author-index="${index}" 
                            title="Hapus penulis dari daftar"
                            aria-label="Hapus penulis"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    ` : ''}
                </div>
            `;
            contributorsContainer.appendChild(card);
        });

        // Event listener for Drag and Drop
        let draggedCardIndex = null;
        contributorsContainer.querySelectorAll('.contributor-card').forEach(card => {
            card.addEventListener('dragstart', function(e) {
                draggedCardIndex = parseInt(this.getAttribute('data-index'), 10);
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedCardIndex);
                this.classList.add('opacity-40', 'border-brand-maroon', 'border-dashed');
            });

            card.addEventListener('dragend', function() {
                this.classList.remove('opacity-40', 'border-brand-maroon', 'border-dashed');
                contributorsContainer.querySelectorAll('.contributor-card').forEach(c => {
                    c.classList.remove('ring-2', 'ring-brand-maroon/30', 'border-brand-maroon');
                });
            });

            card.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('ring-2', 'ring-brand-maroon/30', 'border-brand-maroon');
            });

            card.addEventListener('dragleave', function() {
                this.classList.remove('ring-2', 'ring-brand-maroon/30', 'border-brand-maroon');
            });

            card.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('ring-2', 'ring-brand-maroon/30', 'border-brand-maroon');
                const targetIndex = parseInt(this.getAttribute('data-index'), 10);
                if (draggedCardIndex !== null && !isNaN(draggedCardIndex) && draggedCardIndex !== targetIndex) {
                    moveContributor(draggedCardIndex, targetIndex);
                }
            });
        });

        // Event listener for Move Up
        contributorsContainer.querySelectorAll('.btn-move-up').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-index'), 10);
                if (idx > 0) {
                    moveContributor(idx, idx - 1);
                }
            });
        });

        // Event listener for Move Down
        contributorsContainer.querySelectorAll('.btn-move-down').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-index'), 10);
                if (idx < contributors.length - 1) {
                    moveContributor(idx, idx + 1);
                }
            });
        });

        // Event listener for "Jadikan Penulis Pertama"
        contributorsContainer.querySelectorAll('.btn-set-first').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const idx = parseInt(this.getAttribute('data-index'), 10);
                const author = contributors[idx];
                const authorName = author ? ((author.given_name ? author.given_name + ' ' : '') + author.family_name) : 'Penulis';
                moveContributor(idx, 0);
                if (window.toast && typeof window.toast.info === 'function') {
                    window.toast.info(`"${authorName}" kini ditetapkan sebagai Penulis Pertama.`, 'Penulis Pertama Ditetapkan');
                }
            });
        });

        // Event listener for radio corresponding
        contributorsContainer.querySelectorAll('.radio-corresponding').forEach(radio => {
            radio.addEventListener('change', function () {
                const targetIdx = parseInt(this.value, 10);
                contributors.forEach((a, i) => {
                    a.corresponding = (i === targetIdx);
                });
                renderContributors();
                saveDraftToStorage();
            });
        });

        // Event listener for remove author
        contributorsContainer.querySelectorAll('.btn-remove-author').forEach(btn => {
            btn.addEventListener('click', async function () {
                const targetIdx = parseInt(this.getAttribute('data-author-index'), 10);
                const author = contributors[targetIdx];
                const authorName = author ? ((author.given_name ? author.given_name + ' ' : '') + author.family_name) : 'penulis ini';

                const confirmed = await window.showConfirmModal({
                    title: 'Hapus Penulis / Kontributor?',
                    message: `Apakah Anda yakin ingin menghapus "${authorName}" dari daftar penulis naskah ini?`,
                    confirmText: 'Hapus Penulis',
                    cancelText: 'Batal',
                    type: 'danger'
                });

                if (!confirmed) return;

                const wasCorresponding = author ? author.corresponding : false;
                contributors.splice(targetIdx, 1);
                contributors.forEach((a, i) => {
                    a.sequence = i + 1;
                });
                if (wasCorresponding && contributors.length > 0) {
                    contributors[0].corresponding = true;
                }
                renderContributors();
                saveDraftToStorage();
            });
        });
    }

    if (btnOpenAddAuthor) {
        btnOpenAddAuthor.addEventListener('click', () => {
            document.getElementById('input-author-given').value = '';
            document.getElementById('input-author-family').value = '';
            document.getElementById('input-author-email').value = '';
            document.getElementById('input-author-affiliation').value = '';
            document.getElementById('input-author-orcid').value = '';
            const firstCb = document.getElementById('input-author-first');
            if (firstCb) firstCb.checked = false;
            document.getElementById('input-author-corresponding').checked = false;
            document.getElementById('modal-author-error').classList.add('hidden');
            modalAuthor.classList.remove('hidden');
        });
    }

    function closeModal() {
        modalAuthor.classList.add('hidden');
    }

    if (btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
    if (btnCancelModal) btnCancelModal.addEventListener('click', closeModal);
    document.getElementById('modal-author-backdrop').addEventListener('click', closeModal);

    if (btnSaveAuthor) {
        btnSaveAuthor.addEventListener('click', () => {
            const familyName = document.getElementById('input-author-family').value.trim();
            const givenName = document.getElementById('input-author-given').value.trim();
            const email = document.getElementById('input-author-email').value.trim();
            const affiliation = document.getElementById('input-author-affiliation').value.trim();
            const orcid = document.getElementById('input-author-orcid').value.trim();
            const isCorresponding = document.getElementById('input-author-corresponding').checked;
            const isFirstChoice = document.getElementById('input-author-first')?.checked || false;
            const errorDiv = document.getElementById('modal-author-error');

            if (!familyName) {
                errorDiv.textContent = 'Nama belakang / lengkap kontributor wajib diisi.';
                errorDiv.classList.remove('hidden');
                return;
            }
            if (!email || !email.includes('@')) {
                errorDiv.textContent = 'Alamat email kontributor valid wajib diisi.';
                errorDiv.classList.remove('hidden');
                return;
            }
            if (!affiliation) {
                errorDiv.textContent = 'Afiliasi / universitas kontributor wajib diisi.';
                errorDiv.classList.remove('hidden');
                return;
            }
            if (orcid && !/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/.test(orcid)) {
                errorDiv.textContent = 'Format ORCID iD tidak valid (contoh: 0000-0002-1825-0097).';
                errorDiv.classList.remove('hidden');
                return;
            }

            if (isCorresponding) {
                contributors.forEach(a => a.corresponding = false);
            }

            const newAuthor = {
                given_name: givenName,
                family_name: familyName,
                email: email,
                affiliation: affiliation,
                orcid_id: orcid,
                sequence: 1,
                corresponding: isCorresponding
            };

            if (isFirstChoice) {
                contributors.unshift(newAuthor);
            } else {
                contributors.push(newAuthor);
            }

            // Sinkronisasi ulang nomor urut sequence
            contributors.forEach((a, idx) => {
                a.sequence = idx + 1;
            });

            closeModal();
            renderContributors();
            saveDraftToStorage();

            if (window.toast && typeof window.toast.success === 'function') {
                const authorName = (givenName ? givenName + ' ' : '') + familyName;
                window.toast.success(`"${authorName}" berhasil ditambahkan ke daftar penulis.`, 'Penulis Ditambahkan');
            }
        });
    }

    // Step 5: Populate Review Preview
    function populateStep5Review() {
        const selectedOpt = sectionSelect ? sectionSelect.options[sectionSelect.selectedIndex] : null;
        const sectionTitle = selectedOpt && selectedOpt.value ? (selectedOpt.getAttribute('data-name') || selectedOpt.text) : 'Belum dipilih';
        document.getElementById('review-section-title').textContent = sectionTitle;

        const fileName = (manuscriptInput && manuscriptInput.files && manuscriptInput.files[0]) 
            ? manuscriptInput.files[0].name 
            : (hasManuscriptAttached() && selectedFileName ? selectedFileName.textContent.trim() : 'Belum ada berkas diunggah (Draf)');
        const reviewFileEl = document.getElementById('review-file-name');
        if (reviewFileEl) reviewFileEl.textContent = fileName;

        document.getElementById('review-title').textContent = titleInput.value.trim() || '(Judul belum diisi)';
        document.getElementById('review-abstract').textContent = abstractInput.value.trim() || '(Abstrak belum diisi)';

        const reviewKeywords = document.getElementById('review-keywords');
        reviewKeywords.innerHTML = '';
        if (keywords.length > 0) {
            keywords.forEach(kw => {
                const badge = document.createElement('span');
                badge.className = 'px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 shadow-2xs';
                badge.textContent = kw;
                reviewKeywords.appendChild(badge);
            });
        } else {
            reviewKeywords.innerHTML = '<span class="text-gray-400 italic text-xs">Belum ada kata kunci</span>';
        }

        const reviewContributors = document.getElementById('review-contributors-list');
        reviewContributors.innerHTML = '';
        if (contributors.length > 0) {
            contributors.forEach((a, i) => {
                const item = document.createElement('div');
                item.className = 'flex items-start justify-between p-3 rounded-lg border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition gap-3';
                const fullName = [a.given_name, a.family_name].filter(Boolean).join(' ').trim() || 'Nama tidak tercantum';
                
                item.innerHTML = `
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            ${i + 1}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-gray-900 text-xs sm:text-sm">${escapeHtml(fullName)}</span>
                                ${a.corresponding ? '<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-md bg-red-50 text-brand-maroon border border-red-200">Korespondensi</span>' : ''}
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5">${escapeHtml(a.affiliation)}${a.email ? ' &bull; ' + escapeHtml(a.email) : ''}</p>
                            ${a.orcid_id ? `<p class="text-[10px] font-mono text-gray-400 mt-0.5">ORCID: ${escapeHtml(a.orcid_id)}</p>` : ''}
                        </div>
                    </div>
                `;
                reviewContributors.appendChild(item);
            });
        } else {
            reviewContributors.innerHTML = '<span class="text-gray-400 italic text-xs">Belum ada data penulis</span>';
        }
    }

    // Step Navigation logic
    function setStep(newStep) {
        if (newStep < 1 || newStep > totalSteps) return;

        // Hide all step panels
        document.querySelectorAll('.wizard-step-panel').forEach(p => p.classList.add('hidden'));
        
        // Show target step panel
        const targetPanel = document.getElementById(`step-panel-${newStep}`);
        if (targetPanel) targetPanel.classList.remove('hidden');

        currentStep = newStep;
        currentStepInput.value = currentStep;

        // Update Desktop Stepper Badges
        document.querySelectorAll('[data-step-nav]').forEach(li => {
            const stepNum = parseInt(li.getAttribute('data-step-nav'), 10);
            const badge = li.querySelector('.step-badge');
            const numSpan = li.querySelector('.step-num');
            const checkSvg = li.querySelector('.step-check');
            const titleSpan = li.querySelector('.step-title');

            if (stepNum < currentStep) {
                // Completed
                badge.className = 'step-badge flex items-center justify-center w-8 h-8 rounded-full border-2 border-emerald-600 bg-emerald-600 text-white transition duration-200 shrink-0';
                numSpan.classList.add('hidden');
                checkSvg.classList.remove('hidden');
                titleSpan.className = 'step-title text-xs font-bold text-gray-800 transition truncate';
            } else if (stepNum === currentStep) {
                // Active
                badge.className = 'step-badge flex items-center justify-center w-8 h-8 rounded-full border-2 border-brand-maroon bg-brand-maroon text-white shadow-2xs transition duration-200 shrink-0';
                numSpan.classList.remove('hidden');
                checkSvg.classList.add('hidden');
                titleSpan.className = 'step-title text-xs font-bold text-brand-maroon transition truncate';
            } else {
                // Inactive
                badge.className = 'step-badge flex items-center justify-center w-8 h-8 rounded-full border-2 border-gray-300 bg-white text-gray-500 transition duration-200 shrink-0';
                numSpan.classList.remove('hidden');
                checkSvg.classList.add('hidden');
                titleSpan.className = 'step-title text-xs font-bold text-gray-500 transition truncate';
            }
        });

        // Update Mobile Progress
        const mobileStepNames = [
            'Ketentuan & Seksi',
            'Unggah Berkas',
            'Metadata Naskah',
            'Kontributor',
            'Konfirmasi & Kirim'
        ];
        document.getElementById('mobile-step-num').textContent = currentStep;
        document.getElementById('mobile-step-name').textContent = mobileStepNames[currentStep - 1];
        const pct = Math.round((currentStep / totalSteps) * 100);
        document.getElementById('mobile-step-pct').textContent = pct + '%';
        document.getElementById('mobile-progress-bar').style.width = pct + '%';

        // Bottom buttons display
        if (currentStep === 1) {
            btnPrev.classList.add('hidden');
        } else {
            btnPrev.classList.remove('hidden');
        }

        if (currentStep === totalSteps) {
            btnNext.classList.add('hidden');
            btnFinalSubmit.classList.remove('hidden');
            populateStep5Review();
        } else {
            btnNext.classList.remove('hidden');
            btnFinalSubmit.classList.add('hidden');
        }

        // Scroll to form top smoothly inside main scroll container
        const mainScroll = document.querySelector('main');
        if (mainScroll) {
            mainScroll.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        saveDraftToStorage();
    }

    // Validation before proceeding to next step
    async function validateStep(step) {
        if (step === 1) {
            if (!sectionSelect || !sectionSelect.value) {
                await window.showAlertModal({
                    title: 'Seksi Jurnal Belum Dipilih',
                    message: 'Silakan pilih kategori seksi atau bidang jurnal terlebih dahulu.',
                    type: 'warning'
                });
                if (sectionSelect) sectionSelect.focus();
                return false;
            }
            const allChecked = Array.from(checklistItems).every(cb => cb.checked);
            if (!allChecked) {
                await window.showAlertModal({
                    title: 'Checklist Belum Lengkap',
                    message: 'Anda wajib mencentang seluruh 5 poin checklist kepatuhan penulisan dan etika publikasi.',
                    type: 'warning'
                });
                return false;
            }
        } else if (step === 2) {
            if (isUploadingToAzure) {
                await window.showAlertModal({
                    title: 'Unggahan Sedang Berjalan',
                    message: 'Mohon tunggu hingga proses unggah berkas ke penyimpanan cloud selesai 100%.',
                    type: 'warning'
                });
                return false;
            }
            // Manuscript file is required for final submit, warning here if empty
            if (!hasManuscriptAttached()) {
                const proceedWithoutFile = await window.showConfirmModal({
                    title: 'Lanjutkan Tanpa Berkas?',
                    message: 'Berkas naskah utama belum diunggah. Anda tetap dapat melanjutkan pengisian draf, namun berkas wajib diunggah sebelum pengiriman final.',
                    confirmText: 'Lanjutkan Pengisian',
                    cancelText: 'Unggah Sekarang',
                    type: 'warning'
                });
                if (!proceedWithoutFile) return false;
            }
        } else if (step === 3) {
            const titleWords = countWords(titleInput.value);
            if (titleWords === 0) {
                await window.showAlertModal({
                    title: 'Judul Naskah Wajib Diisi',
                    message: 'Silakan isi judul lengkap artikel ilmiah Anda.',
                    type: 'warning'
                });
                titleInput.focus();
                return false;
            }
            if (titleWords > TITLE_MAX_WORDS) {
                await window.showAlertModal({
                    title: 'Judul Melebihi Batas Kata',
                    message: `Judul naskah maksimal ${TITLE_MAX_WORDS} kata (saat ini terisi ${titleWords} kata). Harap persingkat judul naskah Anda.`,
                    type: 'warning'
                });
                titleInput.focus();
                return false;
            }

            const abstractWords = countWords(abstractInput.value);
            if (abstractWords === 0) {
                await window.showAlertModal({
                    title: 'Abstrak Naskah Wajib Diisi',
                    message: 'Silakan isi teks abstrak artikel ilmiah Anda.',
                    type: 'warning'
                });
                abstractInput.focus();
                return false;
            }
            if (abstractWords > ABSTRACT_MAX_WORDS) {
                await window.showAlertModal({
                    title: 'Abstrak Melebihi Batas Kata',
                    message: `Abstrak naskah maksimal ${ABSTRACT_MAX_WORDS} kata (saat ini terisi ${abstractWords} kata). Harap ringkas teks abstrak naskah Anda.`,
                    type: 'warning'
                });
                abstractInput.focus();
                return false;
            }

            if (keywords.length === 0) {
                await window.showAlertModal({
                    title: 'Kata Kunci Belum Ada',
                    message: 'Minimal satu kata kunci wajib dicantumkan.',
                    type: 'warning'
                });
                keywordInputBox.focus();
                return false;
            }
        } else if (step === 4) {
            if (contributors.length === 0) {
                await window.showAlertModal({
                    title: 'Kontributor Belum Ada',
                    message: 'Minimal satu penulis wajib dicantumkan.',
                    type: 'warning'
                });
                return false;
            }
            const hasCorresponding = contributors.some(a => a.corresponding);
            if (!hasCorresponding) {
                await window.showAlertModal({
                    title: 'Penulis Korespondensi Belum Ditetapkan',
                    message: 'Tepat satu penulis wajib ditandai sebagai Penulis Korespondensi.',
                    type: 'warning'
                });
                return false;
            }
        }
        return true;
    }

    // Step navigation buttons
    btnNext.addEventListener('click', async () => {
        if (await validateStep(currentStep)) {
            setStep(currentStep + 1);
        }
    });

    btnPrev.addEventListener('click', () => {
        setStep(currentStep - 1);
    });

    // Jump step buttons on Step 5 review
    document.querySelectorAll('.btn-jump-step').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = parseInt(this.getAttribute('data-target-step'), 10);
            setStep(target);
        });
    });

    let isDraftDiscarded = false;
    let isSubmitting = false;

    function clearDraftStorage() {
        try {
            sessionStorage.removeItem(STORAGE_KEY);
            clearFileFromDraftDB();
        } catch (e) {}
    }

    // Action button: Simpan Draf
    btnSaveDraft.addEventListener('click', async () => {
        const confirmSave = await window.showConfirmModal({
            title: 'Simpan Sebagai Draf?',
            message: 'Isian formulir saat ini akan disimpan sebagai draf ke sistem. Anda dapat melanjutkannya kembali kapan saja.',
            confirmText: 'Simpan Draf',
            cancelText: 'Lanjut Mengisi',
            type: 'primary'
        });

        if (!confirmSave) return;

        clearDraftStorage();
        formAction.value = 'draft';
        // Make sure manuscript file is not required during draft submit
        if (manuscriptInput) manuscriptInput.required = false;
        form.submit();
    });

    // Validasi klik tombol kirim naskah untuk Pernyataan Integritas
    if (btnFinalSubmit) {
        btnFinalSubmit.addEventListener('click', function (e) {
            if (formAction.value === 'submit') {
                const declaration = document.getElementById('confirm-declaration');
                if (declaration && !declaration.checked) {
                    e.preventDefault();
                    e.stopPropagation();

                    const declContainer = document.getElementById('declaration-container') || declaration.closest('label');
                    if (declContainer) {
                        declContainer.classList.add('ring-2', 'ring-brand-maroon', 'border-brand-maroon', 'bg-red-100/70');
                        declContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(() => {
                            declContainer.classList.remove('ring-2', 'ring-brand-maroon', 'border-brand-maroon', 'bg-red-100/70');
                        }, 3500);
                    }

                    if (window.toast && typeof window.toast.warning === 'function') {
                        window.toast.warning(
                            'Harap setujui dan centang Pernyataan Integritas Ilmiah sebelum Anda dapat mengirim naskah ke dewan redaksi.',
                            'Pernyataan Integritas Wajib Dicentang'
                        );
                    } else if (typeof window.showAlertModal === 'function') {
                        window.showAlertModal({
                            title: 'Pernyataan Integritas Ilmiah',
                            message: 'Harap setujui dan centang Pernyataan Integritas Ilmiah sebelum Anda dapat mengirim naskah ke dewan redaksi.',
                            type: 'warning'
                        });
                    }
                    declaration.focus();
                }
            }
        });
    }

    // Form final submit protection
    form.addEventListener('submit', async function (e) {
        if (formAction.value === 'submit') {
            if (isUploadingToAzure) {
                e.preventDefault();
                await window.showAlertModal({
                    title: 'Unggahan Sedang Berjalan',
                    message: 'Mohon tunggu hingga proses unggah berkas ke penyimpanan cloud selesai 100%.',
                    type: 'warning'
                });
                setStep(2);
                return;
            }
            if (!hasManuscriptAttached()) {
                e.preventDefault();
                await window.showAlertModal({
                    title: 'Berkas Naskah Wajib Diunggah',
                    message: 'Berkas naskah utama wajib diunggah sebelum Anda dapat mengirim naskah secara resmi ke dewan redaksi.',
                    type: 'warning'
                });
                setStep(2);
                return;
            }
            const declaration = document.getElementById('confirm-declaration');
            if (declaration && !declaration.checked) {
                e.preventDefault();
                e.stopPropagation();

                const declContainer = document.getElementById('declaration-container') || declaration.closest('label');
                if (declContainer) {
                    declContainer.classList.add('ring-2', 'ring-brand-maroon', 'border-brand-maroon', 'bg-red-100/70');
                    declContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        declContainer.classList.remove('ring-2', 'ring-brand-maroon', 'border-brand-maroon', 'bg-red-100/70');
                    }, 3500);
                }

                if (window.toast && typeof window.toast.warning === 'function') {
                    window.toast.warning(
                        'Harap setujui dan centang Pernyataan Integritas Ilmiah sebelum Anda dapat mengirim naskah ke dewan redaksi.',
                        'Pernyataan Integritas Wajib Dicentang'
                    );
                } else if (typeof window.showAlertModal === 'function') {
                    await window.showAlertModal({
                        title: 'Pernyataan Integritas Ilmiah',
                        message: 'Harap setujui dan centang Pernyataan Integritas Ilmiah sebelum Anda dapat mengirim naskah ke dewan redaksi.',
                        type: 'warning'
                    });
                }
                declaration.focus();
                return;
            }

            if (!isSubmitting) {
                e.preventDefault();
                const confirmed = await window.showConfirmModal({
                    title: 'Kirim Naskah Ilmiah ke Redaksi?',
                    message: 'Pastikan seluruh berkas dan metadata telah sesuai. Naskah yang dikirim akan segera masuk ke antrean Pemeriksaan Awal editorial BRIDA.',
                    confirmText: 'Ya, Kirim Sekarang',
                    cancelText: 'Periksa Kembali',
                    type: 'primary'
                });

                if (confirmed) {
                    isSubmitting = true;
                    btnFinalSubmit.disabled = true;
                    submitSpinner.classList.remove('hidden');
                    isDraftDiscarded = true;
                    clearDraftStorage();
                    form.submit();
                }
                return;
            }

            btnFinalSubmit.disabled = true;
            submitSpinner.classList.remove('hidden');
            // Clear storage upon successful formal submission
            isDraftDiscarded = true;
            clearDraftStorage();
        }
    });

    // ==========================================
    // AUTO-DRAFT PRESERVATION
    // ==========================================
    function hasMeaningfulDraft(data) {
        if (!data) return false;
        const hasSection = Boolean(data.section_id);
        const hasChecklist = Array.isArray(data.checklist) && data.checklist.some(Boolean);
        const hasComments = Boolean(data.comments_for_editor && data.comments_for_editor.trim());
        const hasTitle = Boolean(data.title && data.title.trim());
        const hasAbstract = Boolean(data.abstract && data.abstract.trim());
        const hasKeywords = Array.isArray(data.keywords) && data.keywords.length > 0;
        const hasSubject = Boolean(data.subject_area && data.subject_area.trim());
        const hasReferences = Boolean(data.references && data.references.trim());
        const hasExtraContributors = Array.isArray(data.contributors) && data.contributors.length > 1;
        const hasFile = Boolean(data.has_file);

        return hasSection || hasChecklist || hasComments || hasTitle || hasAbstract || hasKeywords || hasSubject || hasReferences || hasExtraContributors || hasFile;
    }

    function saveDraftToStorage() {
        if (isDraftDiscarded) return;
        try {
            const commentsVal = document.getElementById('comments_for_editor')?.value || '';
            const subjectVal = document.getElementById('subject_area')?.value || '';
            const referencesVal = document.getElementById('references')?.value || '';
            const titleVal = titleInput ? titleInput.value : '';
            const abstractVal = abstractInput ? abstractInput.value : '';
            const secVal = sectionSelect ? sectionSelect.value : '';
            const checkVals = Array.from(checklistItems).map(cb => cb.checked);
            const currentFile = manuscriptInput && manuscriptInput.files ? manuscriptInput.files[0] : null;

            let prevFileName = '';
            let prevFileSize = 0;
            try {
                const prev = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');
                prevFileName = prev.file_name || '';
                prevFileSize = prev.file_size || 0;
            } catch (e) {}

            const fileName = currentFile ? currentFile.name : prevFileName;
            const fileSize = currentFile ? currentFile.size : prevFileSize;
            const hasFile = Boolean(currentFile || fileName);

            const draftData = {
                section_id: secVal,
                checklist: checkVals,
                comments_for_editor: commentsVal,
                title: titleVal,
                abstract: abstractVal,
                keywords: keywords,
                subject_area: subjectVal,
                references: referencesVal,
                contributors: contributors,
                has_file: hasFile,
                file_name: fileName,
                file_size: fileSize,
                blob_path: blobPathInput?.value || '',
                blob_name: blobNameInput?.value || '',
                blob_size: blobSizeInput?.value || '',
                blob_mime: blobMimeInput?.value || '',
                blob_checksum: blobChecksumInput?.value || '',
                step: currentStep,
                updated_at: new Date().toISOString()
            };

            if (!hasMeaningfulDraft(draftData)) {
                clearDraftStorage();
                return;
            }

            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(draftData));
        } catch (err) {
            console.warn('Gagal menyimpan draf otomatis:', err);
        }
    }

    async function restoreDraftFromStorage() {
        if (isDraftDiscarded) return;
        const currentDraftId = document.getElementById('draft_id')?.value;
        if (currentDraftId) {
            clearDraftStorage();
            if (draftAlert) draftAlert.classList.add('hidden');
            return;
        }
        try {
            const saved = sessionStorage.getItem(STORAGE_KEY);
            if (!saved) return;
            const draftData = JSON.parse(saved);
            if (!draftData || !hasMeaningfulDraft(draftData)) {
                clearDraftStorage();
                return;
            }

            // Restore section
            if (draftData.section_id && sectionSelect) {
                sectionSelect.value = draftData.section_id;
                updateSectionDetail();
            }

            // Restore checklist
            if (Array.isArray(draftData.checklist)) {
                draftData.checklist.forEach((checked, i) => {
                    if (checklistItems[i]) checklistItems[i].checked = checked;
                });
                updateMasterChecklist();
            }

            // Restore comments
            if (draftData.comments_for_editor) {
                const c = document.getElementById('comments_for_editor');
                if (c) c.value = draftData.comments_for_editor;
            }

            // Restore metadata
            if (draftData.title && titleInput) {
                titleInput.value = draftData.title;
                updateTitleCounters();
            }
            if (draftData.abstract && abstractInput) {
                abstractInput.value = draftData.abstract;
                updateAbstractCounters();
            }
            if (Array.isArray(draftData.keywords) && draftData.keywords.length > 0) {
                keywords = draftData.keywords;
                renderKeywordChips();
            }
            if (draftData.subject_area) {
                const s = document.getElementById('subject_area');
                if (s) s.value = draftData.subject_area;
            }
            if (draftData.references) {
                const r = document.getElementById('references');
                if (r) r.value = draftData.references;
            }

            // Restore contributors
            if (Array.isArray(draftData.contributors) && draftData.contributors.length > 0) {
                contributors = draftData.contributors;
                renderContributors();
            }

            // Restore the file itself. A cached upload session can have expired.
            const storedFile = await getFileFromDraftDB();
            if (storedFile && manuscriptInput) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(storedFile);
                    manuscriptInput.files = dt.files;
                    updateDropzoneUI(true, storedFile.name, storedFile.size);
                    if (fileErrorMsg) fileErrorMsg.classList.add('hidden');
                } catch (dtError) {
                    console.warn('DataTransfer tidak didukung oleh browser:', dtError);
                }
            }

            // Show alert
            if (draftAlert) draftAlert.classList.remove('hidden');

        } catch (err) {
            console.warn('Gagal memulihkan draf otomatis:', err);
        }
    }

    if (btnDiscardDraft) {
        btnDiscardDraft.addEventListener('click', async () => {
            const confirmed = await window.showConfirmModal({
                title: 'Bersihkan Draf Formulir?',
                message: 'Apakah Anda yakin ingin menghapus seluruh isian draf naskah ini? Formulir akan dikosongkan kembali ke kondisi awal.',
                confirmText: 'Bersihkan Draf',
                cancelText: 'Batalkan',
                type: 'danger'
            });

            if (!confirmed) return;

            isDraftDiscarded = true;
            clearDraftStorage();
            await clearFileFromDraftDB();

            // Sembunyikan alert draf
            if (draftAlert) draftAlert.classList.add('hidden');

            // Reset form
            if (form) form.reset();

            // Reset Seksi & Combobox
            if (sectionSelect) {
                sectionSelect.value = '';
                updateSectionDetail();
            }
            if (comboboxDisplay) {
                comboboxDisplay.textContent = '-- Pilih Seksi / Bidang Jurnal --';
            }
            syncComboboxUI();

            // Reset Checklist
            checklistItems.forEach(cb => { cb.checked = false; });
            updateMasterChecklist();

            // Reset Textarea & Input
            if (titleInput) {
                titleInput.value = '';
                updateTitleCounters();
            }
            if (abstractInput) {
                abstractInput.value = '';
                updateAbstractCounters();
            }
            const commentsInput = document.getElementById('comments_for_editor');
            if (commentsInput) commentsInput.value = '';
            const subjectInput = document.getElementById('subject_area');
            if (subjectInput) subjectInput.value = '';
            const referencesInput = document.getElementById('references');
            if (referencesInput) referencesInput.value = '';

            // Reset Berkas
            if (manuscriptInput) manuscriptInput.value = '';
            if (blobPathInput) blobPathInput.value = '';
            if (blobNameInput) blobNameInput.value = '';
            if (blobSizeInput) blobSizeInput.value = '';
            if (blobMimeInput) blobMimeInput.value = '';
            if (blobChecksumInput) blobChecksumInput.value = '';
            updateDropzoneUI(false);
            if (fileErrorMsg) fileErrorMsg.classList.add('hidden');

            // Reset Kata Kunci
            keywords = [];
            renderKeywordChips();

            // Reset Kontributor
            contributors = [{
                given_name: 'Andi Muhammad',
                family_name: 'Ilyas',
                email: 'andi.ilyas@unhas.ac.id',
                affiliation: 'Universitas Hasanuddin, Makassar',
                orcid_id: '0000-0002-1825-0097',
                sequence: 1,
                corresponding: true
            }];
            renderContributors();

            // Kembalikan ke Langkah 1
            setStep(1);

            // Arahkan ke URL bersih untuk memastikan peramban membuka halaman kosong baru
            window.location.replace(window.location.pathname);
        });
    }

    // Initializations
    renderKeywordChips();
    renderContributors();
    updateTitleCounters();
    updateAbstractCounters();
    restoreDraftFromStorage();
    updateMasterChecklist();
    updateSectionDetail();
});
</script>
