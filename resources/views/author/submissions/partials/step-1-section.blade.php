<!-- ========================================== -->
<!-- LANGKAH 1: KETENTUAN & SEKSI JURNAL -->
<!-- ========================================== -->
<div id="step-panel-1" class="wizard-step-panel bg-white rounded-xl border border-gray-200 p-5 sm:p-7 shadow-2xs space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-maroon">Langkah 1 dari 5</span>
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mt-1">Panduan, Ketentuan, & Pemilihan Seksi Jurnal</h2>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
            Pilih kategori naskah yang sesuai dengan fokus artikel ilmiah Anda serta setujui ketentuan etika publikasi BRIDA.
        </p>
    </div>

    <!-- Pemilihan Seksi Jurnal -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label for="section-combobox-button" class="block text-xs sm:text-sm font-bold text-gray-900">
                Seksi / Bidang Jurnal <span class="text-red-600">*</span>
            </label>
            <span class="text-[11px] text-gray-400">Pilih seksi sesuai fokus artikel ilmiah Anda</span>
        </div>

        <!-- Sinkronisasi Native Select Tersembunyi untuk Kompatibilitas Form & Testing -->
        <select 
            name="section_id" 
            id="section_id" 
            required 
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        >
            <option value="" disabled {{ old('section_id') ? '' : 'selected' }}>-- Pilih Seksi / Bidang Jurnal --</option>
            @foreach($viewModel->wizardJournalSections() as $section)
                <option 
                    value="{{ $section['id'] }}"
                    data-name="{{ $section['name'] }}"
                    data-description="{{ $section['description'] }}"
                    data-policy="{{ $section['policy'] }}"
                    {{ old('section_id', 'sec-riset-orisinil') === $section['id'] ? 'selected' : '' }}
                >
                    {{ $section['name'] }}
                </option>
            @endforeach
        </select>

        <!-- Custom Searchable Combobox Component -->
        <div class="relative" id="section-combobox-wrapper">
            <!-- Trigger Button -->
            <button 
                type="button" 
                id="section-combobox-button" 
                aria-haspopup="listbox" 
                aria-expanded="false" 
                class="w-full text-left bg-white hover:bg-gray-50/90 border border-gray-300 hover:border-gray-400 rounded-xl py-3 px-4 flex items-center justify-between gap-3 shadow-2xs focus:outline-none focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition cursor-pointer group"
            >
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <span id="section-combobox-display" class="text-xs sm:text-sm font-semibold text-gray-900 truncate">
                        <!-- Diisi via JS -->
                    </span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0 text-gray-400 group-hover:text-gray-600 transition">
                    <span class="text-[11px] font-medium hidden md:inline text-gray-400">Pilih Seksi</span>
                    <svg id="section-combobox-chevron" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            <!-- Popover Dropdown Panel dengan Search Input Terintegrasi -->
            <div 
                id="section-combobox-dropdown" 
                class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white rounded-xl border border-gray-200 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.06),0px_1px_1px_-0.5px_rgba(0,0,0,0.06),0px_3px_3px_-1.5px_rgba(0,0,0,0.06),0px_6px_6px_-3px_rgba(0,0,0,0.06),0px_12px_12px_-6px_rgba(0,0,0,0.06),0px_24px_24px_-12px_rgba(0,0,0,0.06)] overflow-hidden z-40 transition-all duration-150"
            >
                <!-- Kotak Pencarian Seksi -->
                <div class="p-2.5 border-b border-gray-100 bg-gray-50/80">
                    <div class="relative flex items-center">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input 
                            type="text" 
                            id="section-combobox-search" 
                            placeholder="Ketik untuk mencari seksi atau bidang jurnal..." 
                            autocomplete="off"
                            class="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-white border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-maroon/70 focus:ring-0"
                        >
                        <button 
                            type="button" 
                            id="section-combobox-clear-btn" 
                            class="hidden not-[.hidden]:flex absolute right-1.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-2 min-w-9 min-h-9 items-center justify-center rounded-lg transition cursor-pointer"
                            title="Hapus pencarian"
                            aria-label="Hapus pencarian"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Daftar Opsi Seksi Jurnal -->
                <div 
                    id="section-combobox-list" 
                    role="listbox" 
                    class="max-h-64 overflow-y-auto p-1.5 space-y-1"
                >
                    @foreach($viewModel->wizardJournalSections() as $section)
                        <div 
                            role="option"
                            tabindex="0"
                            data-value="{{ $section['id'] }}"
                            data-name="{{ $section['name'] }}"
                            data-description="{{ $section['description'] }}"
                            data-policy="{{ $section['policy'] }}"
                            class="section-combobox-option group flex items-start justify-between gap-3 p-3 rounded-lg text-left transition cursor-pointer hover:bg-gray-50 {{ old('section_id', 'sec-riset-orisinil') === $section['id'] ? 'bg-red-50/70 text-brand-maroon is-selected font-semibold' : 'text-gray-700' }}"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="option-name font-semibold text-xs sm:text-sm leading-snug">{{ $section['name'] }}</span>
                                </div>
                                <p class="option-desc text-[11px] text-gray-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $section['description'] }}
                                </p>
                            </div>
                            <svg class="option-check w-4 h-4 text-brand-maroon shrink-0 self-center {{ old('section_id', 'sec-riset-orisinil') === $section['id'] ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endforeach

                    <!-- Indikator Memuat Data (Lazy Loading / Infinite Scroll) -->
                    <div id="section-combobox-loading" class="hidden not-[.hidden]:flex py-2.5 text-center text-[11px] text-gray-400 items-center justify-center gap-2">
                        <svg class="animate-spin w-3.5 h-3.5 text-brand-maroon" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memuat seksi lainnya...</span>
                    </div>
                </div>

                <!-- Status Jika Tidak Ditemukan -->
                <div id="section-combobox-empty" class="hidden p-6 text-center text-xs text-gray-400">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Tidak ada seksi jurnal yang cocok dengan kata kunci.</span>
                </div>

                <!-- Footer Keterangan Jumlah -->
                <div class="px-3 py-1.5 bg-gray-50/70 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400 font-medium">
                    <span id="section-combobox-status">Menampilkan <span id="section-combobox-loaded-count">{{ count($viewModel->wizardJournalSections()) }}</span> dari <span id="section-combobox-count">{{ count($viewModel->wizardJournalSections()) }}</span> seksi</span>
                    <span class="hidden sm:inline">Navigasi panah & enter</span>
                </div>
            </div>
        </div>

        <!-- Panel Kartu Detail Kontekstual Dinamis dari Seksi Terpilih -->
        <div id="section-detail-panel" class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 sm:p-5 transition-all duration-200">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200/80 pb-3">
                <div class="min-w-0 flex-1">
                    <h3 id="section-detail-name" class="text-xs sm:text-sm font-bold text-gray-900 leading-snug truncate">
                        <!-- Populated dynamically -->
                    </h3>
                </div>
                <!-- Hanya 1 Tombol Lihat PDF Panduan -->
                <a 
                    href="{{ asset('docs/panduan-penulisan-jurnal-brida.pdf') }}" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-brand-maroon bg-white hover:bg-red-50/80 border border-gray-200 hover:border-red-200 rounded-lg shadow-2xs transition group shrink-0"
                    title="Buka Dokumen PDF Panduan Penulisan di tab baru"
                >
                    <svg class="w-4 h-4 text-brand-maroon group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Lihat PDF Panduan</span>
                </a>
            </div>
            
            <p id="section-detail-desc" class="text-xs text-gray-600 mt-3 leading-relaxed">
                <!-- Populated dynamically -->
            </p>

            <div class="mt-3 p-3.5 rounded-lg bg-white border border-gray-200 text-xs text-gray-600 space-y-1">
                <span class="font-bold text-gray-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Ketentuan Khusus Seksi:</span>
                </span>
                <p id="section-detail-policy" class="text-gray-500 leading-relaxed pl-5.5">
                    <!-- Populated dynamically -->
                </p>
            </div>
        </div>
    </div>

    <!-- Checklist Kepatuhan & Etika Publikasi (5 Poin) -->
    <div class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <label class="block text-xs sm:text-sm font-bold text-gray-900">
                Checklist Persyaratan Pengajuan Naskah <span class="text-red-600">*</span>
            </label>
            <button 
                type="button" 
                id="btn-check-all" 
                class="inline-flex items-center justify-center min-w-9 min-h-9 sm:min-w-10 sm:min-h-10 p-2 rounded-lg text-gray-500 hover:text-gray-800 bg-white hover:bg-gray-100 border border-gray-200 hover:border-gray-300 transition shadow-2xs cursor-pointer group active:scale-[0.96]"
                title="Centang Semua Persyaratan"
                aria-label="Centang Semua Persyaratan"
            >
                <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 7 17l-5-5" />
                    <path d="m22 10-7.5 7.5L13 16" />
                </svg>
            </button>
        </div>
        <div class="bg-gray-50/50 rounded-xl border border-gray-200 p-4 sm:p-5 space-y-3.5">
            @foreach($viewModel->submissionChecklist() as $chk)
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input 
                        type="checkbox" 
                        name="checklist_items[]" 
                        value="{{ $chk['id'] }}" 
                        class="checklist-item mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon shrink-0 cursor-pointer"
                    >
                    <div class="text-xs">
                        <span class="font-bold text-gray-900 group-hover:text-brand-maroon transition">{{ $chk['title'] }}</span>
                        <p class="text-gray-500 mt-0.5 leading-relaxed text-[11px]">{{ $chk['description'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>
        <!-- Master Hidden Checkbox untuk Form Request OpenAPI author_guidelines_accepted -->
        <input type="checkbox" name="author_guidelines_accepted" id="author_guidelines_accepted" value="1" class="sr-only" {{ old('author_guidelines_accepted') ? 'checked' : '' }}>
    </div>

    <!-- Catatan Pengantar untuk Dewan Redaksi / Editor -->
    <div class="space-y-1.5 pt-2">
        <div class="flex items-center justify-between">
            <label for="comments_for_editor" class="block text-xs sm:text-sm font-bold text-gray-900">
                Catatan Pengantar untuk Editor <span class="text-gray-400 font-normal">(Opsional)</span>
            </label>
            <span class="text-[11px] text-gray-400">Maks. 2.000 karakter</span>
        </div>
        <textarea 
            name="comments_for_editor" 
            id="comments_for_editor" 
            rows="3" 
            maxlength="2000"
            placeholder="Tuliskan catatan khusus, konteks hibah riset, atau permohonan pengecualian kepada dewan redaksi..."
            class="w-full p-3 sm:p-3.5 text-xs sm:text-sm rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition-all"
        >{{ old('comments_for_editor') }}</textarea>
    </div>
</div>
