{{-- resources/views/eic/submissions/partials/action-sidebar.blade.php --}}
@php
    $latestDesk = $viewModel->latestDeskReview();
    $latestDecision = $viewModel->latestEditorialDecision();
    $reviewSummary = $viewModel->reviewSummary();
    $availableEditors = $viewModel->availableSectionEditors();

    $canEditDesk = $viewModel->canEditDeskReview();
    $isWaitingDesk = $viewModel->isWaitingDeskReview();
    $canAssignSe = $viewModel->canAssignSectionEditor();
    $canDecide = $viewModel->canMakeEditorialDecision();
    $isFinalized = $viewModel->isWorkflowFinalized();

    $currentDeskStatus = old('status', $latestDesk?->status ?? 'passed');
    $deskStatusOptions = [
        'passed' => [
            'label' => 'Lolos Skrining Awal',
            'desc' => 'Teruskan naskah ke tahap penelaahan',
        ],
        'revision_required' => [
            'label' => 'Minta Perbaikan Format',
            'desc' => 'Kembalikan ke penulis untuk dilengkapi',
        ],
        'rejected' => [
            'label' => 'Tolak pada Skrining Awal',
            'desc' => 'Hentikan alur naskah karena tidak memenuhi syarat',
        ],
    ];

    $currentEditorId = (string) old('section_editor_id', $submission->activeEditorAssignment?->editor_id ?? '');
    $selectedEditorObj = $availableEditors->firstWhere('id', $currentEditorId);

    $defaultDec = (string) old('decision', $reviewSummary['se_recommendation'] ?? 'accept');
    $decisionOptions = [
        'accept' => [
            'label' => 'Terima Naskah',
            'desc' => 'Lanjutkan naskah ke tahap penyuntingan dan produksi',
        ],
        'revisions_required' => [
            'label' => 'Perlu Revisi Penulis',
            'desc' => 'Minta perbaikan naskah dengan batas waktu tertentu',
        ],
        'resubmit' => [
            'label' => 'Kirim Ulang untuk Telaah',
            'desc' => 'Wajib dinilai ulang pada putaran telaah berikutnya',
        ],
        'decline' => [
            'label' => 'Tolak Naskah',
            'desc' => 'Hentikan proses editorial untuk naskah ini',
        ],
    ];
@endphp

<aside class="lg:col-span-1 min-w-0 space-y-6">
    
    <!-- Kartu 1: Skrining Awal -->
    <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2 min-w-0 cursor-help" 
                data-tooltip="Skrining Awal" 
                data-tooltip-desc="Pemeriksaan kelayakan ruang lingkup, kelengkapan berkas, dan indeks kemiripan naskah"
            >
                <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                    Skrining Awal
                </h3>
            </div>
            @if($canEditDesk)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 shrink-0">
                    Tahap 1
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 shrink-0">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Selesai</span>
                </span>
            @endif
        </div>

        @if($canEditDesk)
            <form action="{{ route('eic.submissions.desk-review', $submission->id) }}" method="POST" class="space-y-4">
                @csrf

                <!-- Checklist 3 Kriteria Kelayakan Awal -->
                <div class="space-y-1.5 bg-gray-50/80 p-3 rounded-lg border border-gray-200/80">
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block pb-1">Daftar Periksa Kelayakan</span>

                    <input type="hidden" name="scope_fit" value="0">
                    <label class="flex items-center gap-2.5 p-1.5 rounded-md hover:bg-white cursor-pointer font-medium text-gray-800 transition">
                        <input 
                            type="checkbox" 
                            name="scope_fit" 
                            value="1" 
                            {{ old('scope_fit', $latestDesk?->scope_fit ?? true) ? 'checked' : '' }} 
                            class="w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon shrink-0"
                        >
                        <span class="leading-snug">Sesuai Ruang Lingkup & Fokus Jurnal</span>
                    </label>

                    <input type="hidden" name="files_complete" value="0">
                    <label class="flex items-center gap-2.5 p-1.5 rounded-md hover:bg-white cursor-pointer font-medium text-gray-800 transition">
                        <input 
                            type="checkbox" 
                            name="files_complete" 
                            value="1" 
                            {{ old('files_complete', $latestDesk?->files_complete ?? true) ? 'checked' : '' }} 
                            class="w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon shrink-0"
                        >
                        <span class="leading-snug">Berkas Manuskrip Lengkap & Anonim</span>
                    </label>

                    <input type="hidden" name="guidelines_compliant" value="0">
                    <label class="flex items-center gap-2.5 p-1.5 rounded-md hover:bg-white cursor-pointer font-medium text-gray-800 transition">
                        <input 
                            type="checkbox" 
                            name="guidelines_compliant" 
                            value="1" 
                            {{ old('guidelines_compliant', $latestDesk?->guidelines_compliant ?? true) ? 'checked' : '' }} 
                            class="w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon shrink-0"
                        >
                        <span class="leading-snug">Mematuhi Pedoman & Gaya Selingkung</span>
                    </label>
                </div>

                <!-- Skor Uji Kemiripan -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="similarity_score" class="font-bold text-gray-700">Indeks Kemiripan Naskah</label>
                        <span class="text-[11px] text-gray-400 font-medium">Maksimal 20%</span>
                    </div>
                    <div class="relative">
                        <input 
                            type="number" 
                            id="similarity_score" 
                            name="similarity_score" 
                            step="0.1" 
                            min="0" 
                            max="100" 
                            value="{{ old('similarity_score', $latestDesk?->similarity_score ?? '12.5') }}"
                            placeholder="Contoh: 12.5"
                            class="w-full pl-3.5 pr-10 py-2.5 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon tabular-nums font-semibold text-gray-900 transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-gray-400 pointer-events-none">%</span>
                    </div>
                </div>

                <!-- Custom Dropdown: Keputusan Skrining Awal -->
                <div class="space-y-1.5">
                    <label class="font-bold text-gray-700 block">Keputusan Skrining Awal</label>
                    <div class="relative custom-dropdown-container" data-dropdown="desk-status">
                        <select id="desk_status" name="status" class="sr-only" tabindex="-1" required>
                            @foreach($deskStatusOptions as $val => $opt)
                                <option value="{{ $val }}" {{ $currentDeskStatus === $val ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                        <button 
                            type="button" 
                            class="custom-dropdown-btn w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-lg px-3.5 py-2.5 text-gray-900 font-semibold flex items-center justify-between gap-2 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span class="custom-dropdown-label truncate">
                                {{ $deskStatusOptions[$currentDeskStatus]['label'] ?? 'Lolos Skrining Awal' }}
                            </span>
                            <svg class="custom-dropdown-chevron w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-1.5 z-30 space-y-1 absolute">
                            @foreach($deskStatusOptions as $val => $opt)
                                @php $isSelected = ($currentDeskStatus === $val); @endphp
                                <div 
                                    class="custom-dropdown-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-value="{{ $val }}"
                                    data-label="{{ $opt['label'] }}"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold leading-tight">{{ $opt['label'] }}</div>
                                        <div class="text-[11px] text-gray-500 font-normal mt-0.5 leading-snug">{{ $opt['desc'] }}</div>
                                    </div>
                                    <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Combobox: Delegasikan Langsung ke Editor Bagian -->
                <div id="direct_se_wrapper" class="space-y-1.5 {{ $currentDeskStatus === 'passed' ? '' : 'hidden' }}">
                    <div class="flex items-center justify-between">
                        <label for="desk_se_search_input" class="font-bold text-gray-700">Delegasikan ke Editor Bagian</label>
                        <span class="text-[11px] text-gray-400">Opsional</span>
                    </div>

                    <div class="editor-combobox-container relative"
                         data-endpoint="{{ route('eic.submissions.editors.search', $submission->id) }}"
                         data-input-id="desk_section_editor_id"
                         data-optional="true">
                        
                        <select id="desk_section_editor_id" name="section_editor_id" class="sr-only" tabindex="-1">
                            <option value="">Tugaskan Nanti Secara Terpisah</option>
                            @if($selectedEditorObj)
                                <option value="{{ $selectedEditorObj->id }}" selected>{{ $selectedEditorObj->name }}</option>
                            @endif
                            @foreach($availableEditors as $editor)
                                @if(!$selectedEditorObj || (string)$selectedEditorObj->id !== (string)$editor->id)
                                    <option value="{{ $editor->id }}">{{ $editor->name }}</option>
                                @endif
                            @endforeach
                        </select>

                        <div class="relative flex items-center">
                            <div class="absolute left-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <input 
                                id="desk_se_search_input"
                                type="text" 
                                class="editor-combobox-input w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 focus:bg-white border border-gray-300 focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 rounded-lg pl-9 pr-16 py-2.5 text-gray-900 font-semibold placeholder:font-normal placeholder:text-gray-400 transition cursor-text focus:outline-hidden"
                                placeholder="Ketik nama atau pilih editor..."
                                value="{{ $selectedEditorObj ? $selectedEditorObj->name : '' }}"
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-expanded="false"
                                aria-controls="desk-se-listbox"
                            >

                            <div class="absolute right-2 flex items-center gap-1">
                                <button 
                                    type="button" 
                                    class="editor-combobox-clear p-1 text-gray-400 hover:text-gray-600 rounded transition cursor-pointer {{ $selectedEditorObj ? '' : 'hidden' }}"
                                    title="Hapus pilihan"
                                    tabindex="-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <div class="editor-combobox-spinner hidden animate-spin text-brand-maroon p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </div>

                                <button type="button" class="editor-combobox-toggle p-1 text-gray-400 hover:text-gray-600 transition cursor-pointer" tabindex="-1">
                                    <svg class="editor-combobox-chevron w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="desk-se-listbox" role="listbox" class="editor-combobox-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full max-h-64 overflow-y-auto bg-white rounded-xl border border-gray-200 shadow-xl p-1.5 z-40 space-y-1 absolute">
                            <div class="editor-combobox-items space-y-1">
                                <div 
                                    class="editor-combobox-item flex items-center justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $currentEditorId === '' ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-value=""
                                    data-name=""
                                    role="option"
                                    aria-selected="{{ $currentEditorId === '' ? 'true' : 'false' }}"
                                >
                                    <span>Tugaskan Nanti Secara Terpisah</span>
                                    <svg class="editor-check w-4 h-4 text-brand-maroon shrink-0 {{ $currentEditorId === '' ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                @foreach($availableEditors as $editor)
                                    @php 
                                        $isEdSelected = ($currentEditorId === (string) $editor->id);
                                    @endphp
                                    <div 
                                        class="editor-combobox-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isEdSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                        data-value="{{ $editor->id }}"
                                        data-name="{{ $editor->name }}"
                                        data-email="{{ $editor->email }}"
                                        role="option"
                                        aria-selected="{{ $isEdSelected ? 'true' : 'false' }}"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="editor-name font-semibold truncate">{{ $editor->name }}</span>
                                            </div>
                                            <div class="editor-email text-[11px] text-gray-500 font-normal truncate">{{ $editor->email }}</div>
                                        </div>
                                        <svg class="editor-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isEdSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endforeach
                            </div>

                            <div class="editor-combobox-footer px-2 py-1.5 text-center text-[11px] text-gray-400 border-t border-gray-100 hidden">
                                <span class="editor-combobox-meta"></span>
                                <button type="button" class="editor-combobox-load-more block w-full mt-1 text-brand-maroon font-semibold hover:underline">
                                    Muat lebih banyak editor...
                                </button>
                            </div>

                            <div class="editor-combobox-empty p-4 text-center text-xs text-gray-500 hidden">
                                Tidak ada Editor Bagian yang cocok dengan pencarian.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Skrining -->
                <div class="space-y-1.5">
                    <label for="notes" class="font-bold text-gray-700 block">Catatan Evaluasi Skrining</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="2" 
                        placeholder="Tuliskan catatan pemeriksaan ruang lingkup, kelengkapan berkas, dan indeks kemiripan..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    >{{ old('notes', $latestDesk?->notes) }}</textarea>
                </div>

                <button 
                    type="submit" 
                    class="w-full min-h-10 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white font-semibold rounded-lg transition cursor-pointer"
                >
                    Simpan Evaluasi Skrining Awal
                </button>
            </form>
        @else
            <!-- Tampilan Terkunci: Tahap Skrining Awal Telah Dilewati -->
            @php
                $lockedDeskLabel = match($latestDesk?->status) {
                    'rejected' => 'Ditolak pada Skrining Awal',
                    'revision_required' => 'Diminta Perbaikan Format',
                    default => 'Lolos Skrining Awal',
                };
                $lockedDeskColor = match($latestDesk?->status) {
                    'rejected' => 'text-rose-700 bg-rose-50 border-rose-200',
                    'revision_required' => 'text-amber-800 bg-amber-50 border-amber-200',
                    default => 'text-emerald-800 bg-emerald-50 border-emerald-200',
                };
            @endphp
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500">Hasil Skrining:</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $lockedDeskColor }}">
                        {{ $lockedDeskLabel }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500">Indeks Kemiripan:</span>
                    <strong class="text-gray-900 tabular-nums">
                        {{ $latestDesk?->similarity_score !== null ? number_format((float) $latestDesk->similarity_score, 1) . '%' : 'Memenuhi Syarat (< 20%)' }}
                    </strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500">Kelayakan Awal:</span>
                    <strong class="text-gray-800">Terverifikasi Lengkap</strong>
                </div>
                @if($latestDesk?->notes)
                    <div class="pt-2 border-t border-gray-200/80 text-gray-600 leading-relaxed">
                        "{{ $latestDesk->notes }}"
                    </div>
                @endif
            </div>
            <p class="text-[11px] text-gray-500 leading-snug">
                Tahap skrining awal telah selesai dan dikunci karena naskah sudah memasuki tahapan berikutnya.
            </p>
        @endif
    </section>

    <!-- Kartu 2: Penugasan Section Editor -->
    <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2 min-w-0 cursor-help" 
                data-tooltip="Penugasan Section Editor" 
                data-tooltip-desc="Delegasi pimpinan telaah sejawat untuk mengevaluasi substansi naskah"
            >
                <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                    Penugasan Section Editor
                </h3>
            </div>
            @if($canAssignSe)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-sky-800 bg-sky-50 border border-sky-200 shrink-0">
                    Tahap 2
                </span>
            @elseif($isWaitingDesk)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-gray-600 bg-gray-100 border border-gray-200 shrink-0">
                    Menunggu
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 shrink-0">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Selesai</span>
                </span>
            @endif
        </div>

        @if($submission->activeEditorAssignment?->editor)
            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 space-y-1">
                <span class="text-[11px] text-gray-500 font-bold uppercase tracking-wider block">Editor Bagian Penanggung Jawab</span>
                <div class="font-bold text-gray-900 text-xs flex items-center justify-between gap-2">
                    <span class="truncate">{{ $submission->activeEditorAssignment->editor->name }}</span>
                    <span class="text-[11px] text-gray-500 font-normal tabular-nums shrink-0">
                        {{ $submission->activeEditorAssignment->assigned_at?->format('d M Y') }}
                    </span>
                </div>
                <div class="text-[11px] text-gray-500 truncate">{{ $submission->activeEditorAssignment->editor->email }}</div>
                @if($submission->activeEditorAssignment->note)
                    <p class="text-xs text-gray-600 pt-1 border-t border-gray-200/70 mt-1">"{{ $submission->activeEditorAssignment->note }}"</p>
                @endif
            </div>
        @endif

        @if($canAssignSe)
            <form action="{{ route('eic.submissions.assign-editor', $submission->id) }}" method="POST" class="space-y-4">
                @csrf

                <!-- Combobox: Pilih Editor Bagian -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="section_editor_search_input" class="font-bold text-gray-700 block">Pilih Editor Bagian <span class="text-rose-500">*</span></label>
                        <span class="text-[11px] text-gray-400">Pencarian instan</span>
                    </div>

                    <div class="editor-combobox-container relative"
                         data-endpoint="{{ route('eic.submissions.editors.search', $submission->id) }}"
                         data-input-id="section_editor_id">
                        
                        <!-- Form Select Standar (Tersembunyi untuk kompatibilitas form submission) -->
                        <select id="section_editor_id" name="section_editor_id" class="sr-only" tabindex="-1" required>
                            <option value="">Pilih Editor Penanggung Jawab</option>
                            @if($selectedEditorObj)
                                <option value="{{ $selectedEditorObj->id }}" selected>{{ $selectedEditorObj->name }}</option>
                            @endif
                            @foreach($availableEditors as $editor)
                                @if(!$selectedEditorObj || (string)$selectedEditorObj->id !== (string)$editor->id)
                                    <option value="{{ $editor->id }}">{{ $editor->name }}</option>
                                @endif
                            @endforeach
                        </select>

                        <!-- Input Pencarian Combobox Interaktif -->
                        <div class="relative flex items-center">
                            <div class="absolute left-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <input 
                                id="section_editor_search_input"
                                type="text" 
                                class="editor-combobox-input w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 focus:bg-white border border-gray-300 focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 rounded-lg pl-9 pr-16 py-2.5 text-gray-900 font-semibold placeholder:font-normal placeholder:text-gray-400 transition cursor-text focus:outline-hidden"
                                placeholder="Ketik nama atau email editor bagian..."
                                value="{{ $selectedEditorObj ? $selectedEditorObj->name : '' }}"
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-expanded="false"
                                aria-controls="assign-se-listbox"
                            >

                            <!-- Kontrol Kanan: Clear, Spinner, Toggle Chevron -->
                            <div class="absolute right-2 flex items-center gap-1">
                                <button 
                                    type="button" 
                                    class="editor-combobox-clear p-1 text-gray-400 hover:text-gray-600 rounded transition cursor-pointer {{ $selectedEditorObj ? '' : 'hidden' }}"
                                    title="Hapus pilihan"
                                    tabindex="-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <div class="editor-combobox-spinner hidden animate-spin text-brand-maroon p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </div>

                                <button type="button" class="editor-combobox-toggle p-1 text-gray-400 hover:text-gray-600 transition cursor-pointer" tabindex="-1">
                                    <svg class="editor-combobox-chevron w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Panel Dropdown Listbox (Floating Popover) -->
                        <div id="assign-se-listbox" role="listbox" class="editor-combobox-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full max-h-64 overflow-y-auto bg-white rounded-xl border border-gray-200 shadow-xl p-1.5 z-40 space-y-1 absolute">
                            <div class="editor-combobox-items space-y-1">
                                @foreach($availableEditors as $editor)
                                    @php 
                                        $isEdSelected = ($currentEditorId === (string) $editor->id);
                                    @endphp
                                    <div 
                                        class="editor-combobox-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isEdSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                        data-value="{{ $editor->id }}"
                                        data-name="{{ $editor->name }}"
                                        data-email="{{ $editor->email }}"
                                        role="option"
                                        aria-selected="{{ $isEdSelected ? 'true' : 'false' }}"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="editor-name font-semibold truncate">{{ $editor->name }}</span>
                                            </div>
                                            <div class="editor-email text-[11px] text-gray-500 font-normal truncate">{{ $editor->email }}</div>
                                        </div>
                                        <svg class="editor-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isEdSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Footer Pagination / Infinite scroll hint -->
                            <div class="editor-combobox-footer px-2 py-1.5 text-center text-[11px] text-gray-400 border-t border-gray-100 hidden">
                                <span class="editor-combobox-meta"></span>
                                <button type="button" class="editor-combobox-load-more block w-full mt-1 text-brand-maroon font-semibold hover:underline">
                                    Muat lebih banyak editor...
                                </button>
                            </div>

                            <!-- Empty state -->
                            <div class="editor-combobox-empty p-4 text-center text-xs text-gray-500 hidden">
                                Tidak ada Editor Bagian yang cocok dengan pencarian.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="assign_note" class="font-bold text-gray-700 block">Instruksi Penugasan</label>
                    <textarea 
                        id="assign_note" 
                        name="note" 
                        rows="2" 
                        placeholder="Contoh: Mohon ditugaskan minimal 2 mitra bestari di bidang kebijakan publik..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    ></textarea>
                </div>

                <button 
                    type="submit" 
                    class="w-full min-h-10 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-lg transition cursor-pointer"
                >
                    {{ $submission->activeEditorAssignment ? 'Perbarui Penugasan Editor Bagian' : 'Tugaskan Editor Bagian' }}
                </button>
            </form>
        @elseif($isWaitingDesk)
            <div class="p-3.5 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-gray-500 leading-relaxed">
                Selesaikan evaluasi <strong>Skrining Awal</strong> pada kartu di atas terlebih dahulu (atau pilih delegasi langsung saat menyimpan skrining awal).
            </div>
        @else
            @if(! $submission->activeEditorAssignment?->editor)
                <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-500">
                    Naskah ini diputuskan secara langsung oleh Pemimpin Redaksi tanpa pendelegasian Editor Bagian.
                </div>
            @endif
            <p class="text-[11px] text-gray-500 leading-snug">
                Penugasan Editor Bagian telah dikunci karena tahap penelaahan naskah telah berakhir.
            </p>
        @endif
    </section>

    <!-- Kartu 3: Keputusan Editorial Resmi -->
    <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2 min-w-0 cursor-help" 
                data-tooltip="Keputusan Editorial Resmi" 
                data-tooltip-desc="Penetapan hasil akhir evaluasi naskah oleh Pemimpin Redaksi"
            >
                <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                    Keputusan Editorial Resmi
                </h3>
            </div>
            @if($canDecide)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-brand-maroon bg-red-50 border border-red-200 shrink-0">
                    Tahap 3
                </span>
            @elseif($isWaitingDesk)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-gray-600 bg-gray-100 border border-gray-200 shrink-0">
                    Menunggu
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 shrink-0">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Final</span>
                </span>
            @endif
        </div>

        @if($canDecide)
            @if($reviewSummary['se_recommendation'])
                <div class="p-3.5 rounded-xl border text-xs space-y-1.5 {{ $reviewSummary['se_recommendation_badge'] }}">
                    <div class="font-bold flex items-center justify-between">
                        <span>{{ $reviewSummary['se_recommendation_label'] }}</span>
                        @if($reviewSummary['se_recommended_at'])
                            <span class="text-[10px] font-normal opacity-75 tabular-nums">{{ $reviewSummary['se_recommended_at'] }}</span>
                        @endif
                    </div>
                    @if($reviewSummary['se_recommendation_note'])
                        <div class="text-[11px] font-normal leading-relaxed pt-1.5 border-t border-current/20">
                            <strong>Catatan Rekomendasi Section Editor:</strong><br>
                            "{{ $reviewSummary['se_recommendation_note'] }}"
                        </div>
                    @endif
                </div>
            @endif

            @if($viewModel->minimumReviewOverrideReason())
                <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                    <span class="font-bold flex items-center gap-1.5 text-amber-950">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Catatan Dispensasi Kuorum oleh SE:</span>
                    </span>
                    <p class="italic text-[11px] pl-5">"{{ $viewModel->minimumReviewOverrideReason() }}"</p>
                </div>
            @endif

            <form id="eic-decision-form" action="{{ route('eic.submissions.decision', $submission->id) }}" method="POST" class="space-y-4">
                @csrf

                <!-- Custom Dropdown: Jenis Keputusan Akhir -->
                <div class="space-y-1.5">
                    <label class="font-bold text-gray-700 block">Jenis Keputusan Akhir <span class="text-rose-500">*</span></label>
                    <div class="relative custom-dropdown-container" data-dropdown="final-decision">
                        <select id="decision" name="decision" class="sr-only" tabindex="-1" required>
                            @foreach($decisionOptions as $val => $opt)
                                <option value="{{ $val }}" {{ $defaultDec === $val ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                        <button 
                            type="button" 
                            class="custom-dropdown-btn w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-lg px-3.5 py-2.5 text-gray-900 font-semibold flex items-center justify-between gap-2 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span class="custom-dropdown-label truncate">
                                {{ $decisionOptions[$defaultDec]['label'] ?? 'Terima Naskah' }}
                            </span>
                            <svg class="custom-dropdown-chevron w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-xl p-1.5 z-50 space-y-1 absolute">
                            @foreach($decisionOptions as $val => $opt)
                                @php $isDecSelected = ($defaultDec === $val); @endphp
                                <div 
                                    class="custom-dropdown-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isDecSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-value="{{ $val }}"
                                    data-label="{{ $opt['label'] }}"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold leading-tight">{{ $opt['label'] }}</div>
                                        <div class="text-[11px] text-gray-500 font-normal mt-0.5 leading-snug">{{ $opt['desc'] }}</div>
                                    </div>
                                    <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isDecSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Batas Waktu Revisi (Hanya tampil jika keputusan butuh revisi) -->
                <div id="revision_due_wrapper" class="space-y-1.5 {{ in_array($defaultDec, ['revisions_required', 'resubmit'], true) ? '' : 'hidden' }}">
                    <label for="revision_due_at" class="font-bold text-gray-700 block">
                        Batas Waktu Pengumpulan Revisi <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="revision_due_at" 
                        name="revision_due_at" 
                        value="{{ old('revision_due_at', now()->addDays(14)->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon tabular-nums font-medium transition"
                    >
                </div>

                <!-- Surat Keputusan untuk Penulis -->
                <div class="space-y-1.5">
                    <label for="note_for_author" class="font-bold text-gray-700 block">
                        Surat Keputusan untuk Penulis <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        id="note_for_author" 
                        name="note_for_author" 
                        rows="4" 
                        required 
                        placeholder="Sampaikan keputusan resmi dewan redaksi beserta rangkuman pertimbangan mitra bestari..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    >{{ old('note_for_author', 'Dewan redaksi telah menelaah naskah ilmiah Anda secara saksama. Berdasarkan pertimbangan mitra bestari dan editor bagian, kami menyampaikan keputusan resmi berikut.') }}</textarea>
                </div>

                <!-- Catatan Internal Dewan Redaksi -->
                <div class="space-y-1.5">
                    <label for="internal_note" class="font-bold text-gray-700 block">
                        Catatan Internal Redaksi
                    </label>
                    <textarea 
                        id="internal_note" 
                        name="internal_note" 
                        rows="2" 
                        placeholder="Catatan internal untuk editor bagian, penyunting naskah, atau arsip redaksi..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    >{{ old('internal_note') }}</textarea>
                </div>

                <button 
                    type="submit" 
                    class="w-full min-h-10 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white font-semibold rounded-lg transition cursor-pointer"
                >
                    Terbitkan Keputusan Editorial Resmi
                </button>
            </form>
        @elseif($isWaitingDesk)
            <div class="p-3.5 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-gray-500 leading-relaxed">
                Keputusan editorial akhir baru dapat diterbitkan setelah naskah melewati tahap <strong>Skrining Awal</strong> dan evaluasi redaksi.
            </div>
        @else
            <!-- Tampilan Terkunci: Keputusan Editorial Final Telah Diterbitkan -->
            @php
                $finalStatusInfo = $viewModel->statusInfo();
            @endphp
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500">Keputusan Akhir:</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $finalStatusInfo['badge_class'] }}">
                        {{ $finalStatusInfo['label'] }}
                    </span>
                </div>
                @if($latestDecision)
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500">Tanggal Penetapan:</span>
                        <strong class="text-gray-900 tabular-nums">{{ $latestDecision->sent_at?->format('d M Y, H:i') ?? '-' }}</strong>
                    </div>
                    <div class="pt-2 border-t border-gray-200/80 text-gray-700 leading-relaxed">
                        "{{ $latestDecision->note_for_author }}"
                    </div>
                @endif
            </div>
            <p class="text-[11px] text-gray-500 leading-snug">
                Keputusan editorial akhir telah ditetapkan secara permanen dan tidak dapat diubah lagi.
            </p>
        @endif
    </section>

    @php
        $activeGalley = $viewModel->activeGalley();
        $hasFinalApproval = $viewModel->hasFinalGalleyApproval();
        $finalApproval = $viewModel->finalGalleyApproval();
        $canApproveFinal = $viewModel->canApproveFinalGalley();
    @endphp

    @if($activeGalley || in_array($submission->status, ['accepted', 'in_production', 'ready_for_issue', 'published'], true))
    <!-- Kartu 4: Persetujuan Akhir Galai (Ready for Issue) -->
    <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2 min-w-0 cursor-help" 
                data-tooltip="Persetujuan Akhir Galai" 
                data-tooltip-desc="Pengesahan galai publikasi resmi (PDF) oleh Pemimpin Redaksi sebelum naskah masuk ke edisi terbitan"
            >
                <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                    Persetujuan Akhir Galai
                </h3>
            </div>
            @if($submission->status === 'ready_for_issue' || $hasFinalApproval)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 shrink-0">
                    Siap Terbit
                </span>
            @elseif($canApproveFinal)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 shrink-0">
                    Perlu Pengesahan
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-gray-600 bg-gray-50 border border-gray-200 shrink-0">
                    Produksi
                </span>
            @endif
        </div>

        @if($submission->status === 'ready_for_issue' || $hasFinalApproval)
            <!-- Status: Telah Disahkan Resmi (ready_for_issue) -->
            <div class="p-3.5 rounded-lg bg-emerald-50/70 border border-emerald-200 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-emerald-800 font-medium">Status Publikasi:</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                        Siap Masuk Terbitan
                    </span>
                </div>
                @if($finalApproval)
                    <div class="flex items-center justify-between gap-2 text-emerald-900">
                        <span class="text-emerald-700">Tanggal Pengesahan:</span>
                        <strong class="tabular-nums">{{ $finalApproval->approved_at?->format('d M Y, H:i') ?? '-' }}</strong>
                    </div>
                    @if($finalApproval->approvedBy)
                        <div class="flex items-center justify-between gap-2 text-emerald-900">
                            <span class="text-emerald-700">Disahkan Oleh:</span>
                            <strong>{{ $finalApproval->approvedBy->name }}</strong>
                        </div>
                    @endif
                    @if($finalApproval->note)
                        <div class="pt-2 border-t border-emerald-200/80 text-emerald-800 leading-relaxed italic">
                            "{{ $finalApproval->note }}"
                        </div>
                    @endif
                @endif
                @if($activeGalley)
                    <div class="pt-2 border-t border-emerald-200/80 flex items-center justify-between gap-2">
                        <span class="text-emerald-700 font-mono text-[10px] truncate">
                            SHA: {{ substr((string) $activeGalley->checksum_sha256, 0, 16) }}...
                        </span>
                        <a 
                            href="{{ route('layouteditor.galleys.download', $activeGalley->id) }}" 
                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-navy hover:underline shrink-0"
                            target="_blank"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Galai</span>
                        </a>
                    </div>
                @endif
            </div>
            <p class="text-[11px] text-gray-500 leading-snug">
                Naskah ini telah memenuhi seluruh kriteria mutu proofreading dan siap dimasukkan ke dalam daftar isi edisi terbitan berkala oleh Manajer Jurnal.
            </p>
        @elseif($activeGalley)
            <!-- Form Pengesahan Akhir Galai EIC -->
            <form action="{{ route('eic.submissions.approve-galley', $submission->id) }}" method="POST" class="space-y-4">
                @csrf

                <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500">Berkas Galai:</span>
                        <strong class="text-gray-900">{{ $activeGalley->label }} (v{{ $activeGalley->version }})</strong>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500">Format:</span>
                        <span class="uppercase font-semibold text-gray-800">{{ $activeGalley->format }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200/80 flex items-center justify-between gap-2">
                        <span class="text-gray-500 text-[10px] font-mono truncate">
                            {{ substr((string) $activeGalley->checksum_sha256, 0, 16) }}...
                        </span>
                        <a 
                            href="{{ route('layouteditor.galleys.download', $activeGalley->id) }}" 
                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-maroon hover:underline shrink-0"
                            target="_blank"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Periksa Galai</span>
                        </a>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="galley_approval_note" class="block font-semibold text-gray-800">
                        Catatan Pengesahan Galai (Opsional)
                    </label>
                    <textarea 
                        name="note" 
                        id="galley_approval_note" 
                        rows="2" 
                        class="w-full rounded-lg border border-gray-300 p-2.5 text-xs text-gray-900 focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon outline-hidden placeholder:text-gray-400"
                        placeholder="Contoh: Galai telah diperiksa dan disetujui untuk penjadwalan terbitan."
                    ></textarea>
                </div>

                <button 
                    type="submit" 
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs shadow-xs transition duration-150 focus:outline-hidden focus:ring-2 focus:ring-emerald-700 focus:ring-offset-1 cursor-pointer"
                    data-confirm="Apakah Anda yakin ingin mengesahkan galai naskah ini? Status naskah akan otomatis beralih menjadi Siap Terbit (ready_for_issue)."
                    data-confirm-title="Sahkan Persetujuan Galai?"
                    data-confirm-btn="Sahkan Galai"
                    data-confirm-cancel="Batal"
                    data-confirm-type="primary"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Sahkan Persetujuan Akhir Galai</span>
                </button>
            </form>
        @else
            <!-- Belum Ada Galai Aktif -->
            <div class="p-3.5 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-gray-500 leading-relaxed">
                Naskah sedang dalam tahap tata letak. Persetujuan akhir galai dapat diterbitkan setelah <strong>Layout Editor</strong> mengunggah berkas galai final dan menyelesaikan seluruh putaran koreksi proofreading.
            </div>
        @endif
    </section>
    @endif

    <!-- SEKSI 5: Status Pembiayaan Artikel (APC) & Bukti Pembayaran -->
    @php
        $paymentInfo = $viewModel->paymentInfo();
    @endphp
    <section class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2.5 min-w-0 flex-1 cursor-help"
                data-tooltip="Status Pembiayaan & APC"
                data-tooltip-desc="Status tagihan biaya pemrosesan artikel (APC) dan verifikasi bukti transfer pembayaran"
            >
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate" title="Status Pembiayaan & APC">
                        Status Pembiayaan & APC
                    </h3>
                    <span class="text-[11px] text-gray-400 font-medium block truncate" title="Biaya Pengelolaan Publikasi">
                        Biaya Pengelolaan Publikasi
                    </span>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border shrink-0 text-center leading-tight whitespace-nowrap {{ $paymentInfo['status_badge'] }}">
                {{ $paymentInfo['status_label'] }}
            </span>
        </div>

        @if($paymentInfo['is_free'])
            <div class="p-3 bg-emerald-50/70 rounded-lg border border-emerald-100 text-xs text-emerald-900 leading-relaxed">
                <span class="font-bold block mb-0.5">Diamond Open Access:</span>
                Jurnal ini tidak memungut biaya publikasi kepada penulis. Naskah bebas biaya pemrosesan (APC: Rp 0).
            </div>
        @else
            <div class="text-xs text-gray-700 space-y-3">
                <!-- Ringkasan Tarif & Nomor Tagihan -->
                <div class="bg-gray-50/80 rounded-lg p-3 border border-gray-100 grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-0.5">No. Tagihan</span>
                        <span class="font-mono text-xs font-bold text-gray-800 block truncate" title="{{ $paymentInfo['invoice_number'] }}">
                            {{ $paymentInfo['invoice_number'] }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block mb-0.5">Total Tarif (APC)</span>
                        <span class="text-xs sm:text-sm font-extrabold text-gray-900 block">
                            {{ $paymentInfo['amount_formatted'] }}
                        </span>
                    </div>
                </div>

                @if($paymentInfo['has_proof'])
                    <!-- Panel Rincian Bukti Transfer yang Diunggah Penulis -->
                    <div class="p-3.5 bg-sky-50/60 rounded-xl border border-sky-200/70 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-900 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Rincian Bukti Transfer
                            </span>
                            <span class="text-[10px] text-sky-700 font-medium">Unggahan Penulis</span>
                        </div>

                        <div class="space-y-1.5 text-xs pt-1 border-t border-sky-100">
                            <div class="flex items-baseline justify-between gap-2 text-[11px]">
                                <span class="text-gray-500 shrink-0">Bank Pengirim</span>
                                <span class="font-semibold text-gray-900 text-right truncate">{{ $paymentInfo['sender_bank_name'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-start justify-between gap-2 text-[11px]">
                                <span class="text-gray-500 shrink-0">Nama Pengirim</span>
                                <span class="font-semibold text-gray-900 text-right leading-tight max-w-[65%]">{{ $paymentInfo['sender_account_name'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-2 text-[11px]">
                                <span class="text-gray-500 shrink-0">Tanggal Transfer</span>
                                <span class="font-semibold text-gray-900 text-right tabular-nums">{{ $paymentInfo['transfer_date'] ?? '-' }}</span>
                            </div>
                            @if(!empty($paymentInfo['author_notes']))
                                <div class="pt-1.5 border-t border-sky-100 text-[11px]">
                                    <span class="text-gray-500 block mb-1">Catatan Penulis:</span>
                                    <p class="text-gray-700 italic bg-white/80 p-2 rounded-md border border-sky-100 text-[11px] leading-relaxed">
                                        "{{ $paymentInfo['author_notes'] }}"
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="pt-1">
                            <a 
                                href="{{ $paymentInfo['proof_url'] }}" 
                                target="_blank"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-sky-800 bg-white hover:bg-sky-50 border border-sky-200 rounded-lg shadow-2xs transition group"
                            >
                                <svg class="w-3.5 h-3.5 text-sky-600 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Lihat Berkas Bukti Transfer</span>
                            </a>
                        </div>
                    </div>

                    <!-- Tombol Aksi Verifikasi & Tolak Bukti Transfer -->
                    @if($paymentInfo['can_verify'] || $paymentInfo['can_reject'])
                        <div class="space-y-2 pt-0.5">
                            @if($paymentInfo['can_verify'])
                            <form action="{{ route('eic.submissions.payment.verify', $submission->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="notes" value="Pembayaran diverifikasi sah oleh Pemimpin Redaksi (EIC).">
                                <button 
                                    type="submit" 
                                    class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-xs hover:shadow transition duration-150 cursor-pointer"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Verifikasi Pembayaran (Sah)</span>
                                </button>
                            </form>
                            @endif

                            @if($paymentInfo['can_reject'])
                                <button
                                    type="button"
                                    onclick="openEicRejectPaymentModal()"
                                    class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 border border-rose-200 rounded-lg transition duration-150 cursor-pointer"
                                >
                                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Tolak Bukti Transfer</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Status Bukti Ditolak -->
                    @if($paymentInfo['status'] === 'rejected')
                        <div class="p-3.5 bg-rose-50/80 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-1.5">
                            <div class="flex items-center gap-1.5 font-bold text-rose-800">
                                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Bukti Transfer Ditolak</span>
                            </div>
                            @if(!empty($paymentInfo['verified_at']))
                                <span class="text-[11px] text-rose-700 block">Ditolak pada: {{ $paymentInfo['verified_at'] }}</span>
                            @endif
                            @if(!empty($paymentInfo['verification_notes']))
                                <div class="pt-1 text-[11px] text-rose-800">
                                    <span class="font-semibold block text-gray-700">Alasan Penolakan:</span>
                                    <p class="italic bg-white/70 p-2 rounded border border-rose-100 mt-0.5 leading-relaxed">
                                        "{{ $paymentInfo['verification_notes'] }}"
                                    </p>
                                </div>
                            @endif
                            <p class="text-[11px] text-rose-600 pt-1">
                                Menunggu penulis mengunggah ulang berkas bukti transfer yang valid.
                            </p>
                        </div>
                    @endif

                    <!-- Status Pembayaran Lunas -->
                    @if($paymentInfo['status'] === 'verified')
                        <div class="p-3.5 bg-emerald-50/80 rounded-xl border border-emerald-200 text-xs text-emerald-900 space-y-1.5">
                            <div class="flex items-center gap-1.5 font-bold text-emerald-800">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Pembayaran Lunas & Terverifikasi</span>
                            </div>
                            @if(!empty($paymentInfo['verified_at']))
                                <span class="text-[11px] text-emerald-700 block">Diverifikasi pada: {{ $paymentInfo['verified_at'] }}</span>
                            @endif
                            @if(!empty($paymentInfo['verification_notes']))
                                <div class="pt-1 text-[11px] text-emerald-800">
                                    <span class="font-semibold block text-gray-700">Catatan Verifikasi:</span>
                                    <p class="italic bg-white/70 p-2 rounded border border-emerald-100 mt-0.5 leading-relaxed">
                                        "{{ $paymentInfo['verification_notes'] }}"
                                    </p>
                                </div>
                            @endif
                        </div>

                    @endif

                    <!-- Status Pembebasan Biaya (Waiver) -->
                    @if($paymentInfo['status'] === 'waived')
                        <div class="p-3.5 bg-purple-50/80 rounded-xl border border-purple-200 text-xs text-purple-900 space-y-1.5">
                            <div class="flex items-center gap-1.5 font-bold text-purple-800">
                                <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Bebas Biaya Publikasi (Waiver)</span>
                            </div>
                            @if(!empty($paymentInfo['verified_at']))
                                <span class="text-[11px] text-purple-700 block">Diberikan pada: {{ $paymentInfo['verified_at'] }}</span>
                            @endif
                            @if(!empty($paymentInfo['verification_notes']))
                                <div class="pt-1 text-[11px] text-purple-800">
                                    <span class="font-semibold block text-gray-700">Alasan Keringanan:</span>
                                    <p class="italic bg-white/70 p-2 rounded border border-purple-100 mt-0.5 leading-relaxed">
                                        "{{ $paymentInfo['verification_notes'] }}"
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    @if($paymentInfo['status'] === 'verified')
                        <div class="p-3.5 bg-emerald-50/80 rounded-xl border border-emerald-200 text-xs text-emerald-900 space-y-1">
                            <span class="font-bold flex items-center gap-1 text-emerald-800">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Pembayaran Lunas
                            </span>
                            <p class="text-[11px] text-emerald-700">Pembayaran telah terverifikasi sah.</p>
                        </div>
                    @elseif($paymentInfo['status'] === 'waived')
                        <div class="p-3.5 bg-purple-50/80 rounded-xl border border-purple-200 text-xs text-purple-900 space-y-1">
                            <span class="font-bold flex items-center gap-1 text-purple-800">
                                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pembebasan Biaya (Waiver)
                            </span>
                            <p class="text-[11px] text-purple-700">Biaya APC naskah ini telah dibebaskan penuh.</p>
                        </div>
                    @else
                        <div class="p-3 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-[11px] text-gray-500 text-center leading-relaxed">
                            Penulis belum mengunggah bukti transfer pembayaran.
                        </div>
                    @endif
                @endif

                <!-- Opsi Pembebasan Biaya (Grant Waiver) oleh EIC -->
                @if($paymentInfo['can_waive'])
                    <details class="text-xs group border-t border-gray-100 pt-2">
                        <summary class="text-purple-700 font-semibold cursor-pointer hover:underline flex items-center justify-between">
                            <span>Berikan Pembebasan Biaya (Grant Waiver)</span>
                            <span class="text-[10px] text-purple-500 font-normal">Keringanan Redaksi</span>
                        </summary>
                        <form action="{{ route('eic.submissions.payment.waive', $submission->id) }}" method="POST" class="pt-2 space-y-2">
                            @csrf
                            <input 
                                type="text" 
                                name="waiver_reason" 
                                required 
                                placeholder="Alasan pembebasan biaya (misal: naskah undangan, afirmasi BRIDA)..." 
                                class="w-full px-3 py-1.5 text-xs border border-purple-300 rounded-lg focus:ring-1 focus:ring-purple-400 focus:outline-hidden"
                            >
                            <button 
                                type="submit" 
                                class="w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-lg transition cursor-pointer"
                            >
                                Bebaskan Biaya APC (Waiver)
                            </button>
                        </form>
                    </details>
                @endif
            </div>
        @endif
    </section>

    <!-- Modal Tolak Bukti Transfer Pembayaran -->
    <div 
        id="modal-eic-reject-payment" 
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs transition-opacity duration-200"
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="modal-eic-reject-payment-title"
    >
        <div class="relative w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 text-xs">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-rose-50/50">
                <div class="flex items-center gap-2.5 text-rose-900">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-eic-reject-payment-title" class="text-sm font-bold text-gray-900 leading-snug">Tolak Bukti Transfer</h3>
                        <p class="text-[11px] text-gray-500">{{ $submission->submission_number ?? "SUB-{$submission->id}" }}</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    onclick="closeEicRejectPaymentModal()"
                    class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('eic.submissions.payment.reject', $submission->id) }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label for="eic-reject-payment-reason" class="block font-semibold text-gray-800">
                        Alasan Penolakan Bukti Transfer <span class="text-rose-600">*</span>
                    </label>
                    <textarea 
                        id="eic-reject-payment-reason" 
                        name="reason" 
                        rows="3" 
                        required 
                        placeholder="Jelaskan alasan penolakan berkas bukti transfer (misal: berkas buram/terpotong, nominal transfer tidak sesuai tagihan, mutasi bank tidak cocok, dsb)..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-hidden leading-relaxed"
                    ></textarea>
                    <p class="text-[11px] text-gray-500 leading-normal">
                        Catatan alasan penolakan ini akan langsung ditampilkan kepada penulis agar segera mengunggah berkas bukti transfer yang valid.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button 
                        type="button" 
                        onclick="closeEicRejectPaymentModal()"
                        class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-lg shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Konfirmasi Tolak Bukti</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </section>

</aside>

@pushOnce('scripts')
<script>
    function openEicRejectPaymentModal() {
        const modal = document.getElementById('modal-eic-reject-payment');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            const textarea = document.getElementById('eic-reject-payment-reason');
            if (textarea) textarea.focus();
        }
    }

    function closeEicRejectPaymentModal() {
        const modal = document.getElementById('modal-eic-reject-payment');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function toggleDirectSeSelect(statusValue) {
        const wrapper = document.getElementById('direct_se_wrapper');
        if (!wrapper) return;
        if (statusValue === 'passed') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function handleDecisionChange(decisionValue) {
        const dueWrapper = document.getElementById('revision_due_wrapper');
        const dueInput = document.getElementById('revision_due_at');
        if (!dueWrapper || !dueInput) return;

        const needsDue = (decisionValue === 'revisions_required' || decisionValue === 'resubmit');
        if (needsDue) {
            dueWrapper.classList.remove('hidden');
            dueInput.required = true;
            dueInput.disabled = false;
        } else {
            dueWrapper.classList.add('hidden');
            dueInput.required = false;
            dueInput.disabled = true;
        }
    }

    function initEditorComboboxes() {
        const containers = document.querySelectorAll('.editor-combobox-container');

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        containers.forEach(container => {
            const endpoint = container.getAttribute('data-endpoint');
            const hiddenSelect = container.querySelector('select');
            const input = container.querySelector('.editor-combobox-input');
            const clearBtn = container.querySelector('.editor-combobox-clear');
            const spinner = container.querySelector('.editor-combobox-spinner');
            const toggleBtn = container.querySelector('.editor-combobox-toggle');
            const chevron = container.querySelector('.editor-combobox-chevron');
            const panel = container.querySelector('.editor-combobox-panel');
            const itemsWrapper = container.querySelector('.editor-combobox-items');
            const emptyState = container.querySelector('.editor-combobox-empty');
            const footer = container.querySelector('.editor-combobox-footer');
            const metaSpan = container.querySelector('.editor-combobox-meta');
            const loadMoreBtn = container.querySelector('.editor-combobox-load-more');
            const isOptional = container.getAttribute('data-optional') === 'true';

            let searchTimeout = null;
            let currentAbort = null;
            const cache = new Map();
            let currentPage = 1;
            let hasMore = false;
            let currentQuery = '';
            let activeIndex = -1;

            function openPanel() {
                // Tutup panel lain
                document.querySelectorAll('.editor-combobox-panel').forEach(p => {
                    if (p !== panel) {
                        p.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
                        p.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
                    }
                });
                document.querySelectorAll('.editor-combobox-chevron').forEach(c => {
                    if (c !== chevron) c.classList.remove('rotate-180');
                });

                panel.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
                panel.classList.add('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
                input.setAttribute('aria-expanded', 'true');
                if (chevron) chevron.classList.add('rotate-180');
            }

            function closePanel() {
                panel.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
                panel.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
                input.setAttribute('aria-expanded', 'false');
                if (chevron) chevron.classList.remove('rotate-180');
                activeIndex = -1;
            }

            function isOpen() {
                return panel.classList.contains('visible');
            }

            function updateClearBtn() {
                if (clearBtn) {
                    if (hiddenSelect.value || input.value) {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                }
            }

            function selectItem(val, name) {
                let opt = hiddenSelect.querySelector(`option[value="${val}"]`);
                if (!opt && val) {
                    opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = name;
                    hiddenSelect.appendChild(opt);
                }
                hiddenSelect.value = val;
                input.value = val ? name : '';

                const allItems = itemsWrapper.querySelectorAll('.editor-combobox-item');
                allItems.forEach(it => {
                    const isMatch = it.getAttribute('data-value') === val;
                    const check = it.querySelector('.editor-check');
                    if (isMatch) {
                        it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                        it.classList.remove('text-gray-700', 'hover:bg-gray-50');
                        it.setAttribute('aria-selected', 'true');
                        if (check) check.classList.remove('hidden');
                    } else {
                        it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                        it.classList.add('text-gray-700', 'hover:bg-gray-50');
                        it.setAttribute('aria-selected', 'false');
                        if (check) check.classList.add('hidden');
                    }
                });

                updateClearBtn();
                closePanel();
            }

            function renderItems(items, append = false, query = '') {
                if (!append) {
                    itemsWrapper.innerHTML = '';
                }

                if (isOptional && !append && query === '') {
                    const noneSelected = !hiddenSelect.value;
                    const noneDiv = document.createElement('div');
                    noneDiv.className = `editor-combobox-item flex items-center justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition ${noneSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50'}`;
                    noneDiv.setAttribute('data-value', '');
                    noneDiv.setAttribute('data-name', '');
                    noneDiv.setAttribute('role', 'option');
                    noneDiv.setAttribute('aria-selected', noneSelected ? 'true' : 'false');
                    noneDiv.innerHTML = `
                        <span>Tugaskan Nanti Secara Terpisah</span>
                        <svg class="editor-check w-4 h-4 text-brand-maroon shrink-0 ${noneSelected ? '' : 'hidden'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    `;
                    noneDiv.addEventListener('click', () => selectItem('', ''));
                    itemsWrapper.appendChild(noneDiv);
                }

                if (items.length === 0 && !append && (!isOptional || query !== '')) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                }

                items.forEach(editor => {
                    const isSelected = String(editor.id) === String(hiddenSelect.value);
                    const itemDiv = document.createElement('div');
                    itemDiv.className = `editor-combobox-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition ${isSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50'}`;
                    itemDiv.setAttribute('data-value', editor.id);
                    itemDiv.setAttribute('data-name', editor.name);
                    itemDiv.setAttribute('data-email', editor.email);
                    itemDiv.setAttribute('role', 'option');
                    itemDiv.setAttribute('aria-selected', isSelected ? 'true' : 'false');

                    let displayName = escapeHtml(editor.name);
                    if (query.trim() !== '') {
                        const reg = new RegExp(`(${escapeRegExp(query.trim())})`, 'gi');
                        displayName = displayName.replace(reg, '<mark class="bg-amber-100 text-amber-900 font-bold px-0.5 rounded">$1</mark>');
                    }

                    itemDiv.innerHTML = `
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="editor-name font-semibold truncate">${displayName}</span>
                            </div>
                            <div class="editor-email text-[11px] text-gray-500 font-normal truncate">${escapeHtml(editor.email)}</div>
                        </div>
                        <svg class="editor-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 ${isSelected ? '' : 'hidden'}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    `;

                    itemDiv.addEventListener('click', () => {
                        selectItem(editor.id, editor.name);
                    });

                    itemsWrapper.appendChild(itemDiv);
                });
            }

            async function fetchEditors(query = '', page = 1, append = false) {
                const cacheKey = `${query.toLowerCase()}_p${page}`;
                if (cache.has(cacheKey)) {
                    const res = cache.get(cacheKey);
                    currentPage = res.pagination.current_page;
                    hasMore = res.pagination.has_more;
                    renderItems(res.data, append, query);
                    updatePaginationUI(res.pagination);
                    return;
                }

                if (currentAbort) {
                    currentAbort.abort();
                }
                currentAbort = new AbortController();

                if (spinner) spinner.classList.remove('hidden');

                try {
                    const url = new URL(endpoint, window.location.origin);
                    if (query) url.searchParams.set('q', query);
                    url.searchParams.set('page', page);
                    url.searchParams.set('per_page', 15);

                    const response = await fetch(url.toString(), {
                        signal: currentAbort.signal,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) throw new Error('Gagal memuat editor');
                    const json = await response.json();

                    cache.set(cacheKey, json);
                    currentPage = json.pagination.current_page;
                    hasMore = json.pagination.has_more;

                    renderItems(json.data, append, query);
                    updatePaginationUI(json.pagination);
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.error('Combobox fetch error:', err);
                    }
                } finally {
                    if (spinner) spinner.classList.add('hidden');
                }
            }

            function updatePaginationUI(pagination) {
                if (!footer) return;
                if (pagination && pagination.has_more) {
                    footer.classList.remove('hidden');
                    if (metaSpan) {
                        metaSpan.textContent = `Menampilkan ${pagination.current_page * pagination.per_page} dari ${pagination.total} editor`;
                    }
                } else {
                    footer.classList.add('hidden');
                }
            }

            // Pasang event ke elemen SSR bawaan
            itemsWrapper.querySelectorAll('.editor-combobox-item').forEach(it => {
                it.addEventListener('click', () => {
                    selectItem(it.getAttribute('data-value'), it.getAttribute('data-name') || it.innerText.trim());
                });
            });

            // Pencarian debounced saat mengetik
            input.addEventListener('input', () => {
                openPanel();
                updateClearBtn();
                const q = input.value.trim();
                currentQuery = q;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchEditors(q, 1, false);
                }, 250);
            });

            // Klik input fokus & buka panel
            input.addEventListener('focus', () => {
                openPanel();
                input.select();
                if (itemsWrapper.children.length === 0) {
                    fetchEditors(input.value.trim(), 1, false);
                }
            });

            // Tombol bersihkan (Clear)
            if (clearBtn) {
                clearBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectItem('', '');
                    input.focus();
                    fetchEditors('', 1, false);
                });
            }

            // Tombol chevron toggle
            if (toggleBtn) {
                toggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (isOpen()) {
                        closePanel();
                    } else {
                        input.focus();
                    }
                });
            }

            // Tombol muat lebih banyak
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (hasMore) {
                        fetchEditors(currentQuery, currentPage + 1, true);
                    }
                });
            }

            // Infinite scroll saat scroll mencapai bawah panel
            panel.addEventListener('scroll', () => {
                if (panel.scrollTop + panel.clientHeight >= panel.scrollHeight - 25) {
                    if (hasMore && spinner && spinner.classList.contains('hidden')) {
                        fetchEditors(currentQuery, currentPage + 1, true);
                    }
                }
            });

            // Navigasi keyboard (ArrowDown, ArrowUp, Enter, Escape)
            input.addEventListener('keydown', (e) => {
                const items = itemsWrapper.querySelectorAll('.editor-combobox-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!isOpen()) openPanel();
                    activeIndex = Math.min(activeIndex + 1, items.length - 1);
                    highlightItem(items, activeIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!isOpen()) openPanel();
                    activeIndex = Math.max(activeIndex - 1, 0);
                    highlightItem(items, activeIndex);
                } else if (e.key === 'Enter') {
                    if (isOpen() && activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        items[activeIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    if (isOpen()) {
                        e.preventDefault();
                        closePanel();
                    }
                }
            });

            function highlightItem(items, idx) {
                items.forEach((it, i) => {
                    if (i === idx) {
                        it.classList.add('bg-gray-100');
                        it.scrollIntoView({ block: 'nearest' });
                    } else {
                        it.classList.remove('bg-gray-100');
                    }
                });
            }

            updateClearBtn();
        });

        // Tutup semua combobox ketika klik di luar
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.editor-combobox-container')) {
                document.querySelectorAll('.editor-combobox-panel').forEach(p => {
                    p.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
                    p.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
                });
                document.querySelectorAll('.editor-combobox-chevron').forEach(c => c.classList.remove('rotate-180'));
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initEditorComboboxes();

        const deskStatus = document.getElementById('desk_status');
        if (deskStatus) {
            deskStatus.addEventListener('change', (e) => toggleDirectSeSelect(e.target.value));
            toggleDirectSeSelect(deskStatus.value);
        }

        const decisionSelect = document.getElementById('decision');
        if (decisionSelect) {
            decisionSelect.addEventListener('change', (e) => handleDecisionChange(e.target.value));
            handleDecisionChange(decisionSelect.value);
        }

        const storageKey = 'eic_decision_draft_{{ $submission->id }}';
        const noteAuthor = document.getElementById('note_for_author');
        const noteInternal = document.getElementById('internal_note');

        try {
            const saved = sessionStorage.getItem(storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (noteAuthor && parsed.note_for_author) noteAuthor.value = parsed.note_for_author;
                if (noteInternal && parsed.internal_note) noteInternal.value = parsed.internal_note;
            }

            const saveDraft = () => {
                sessionStorage.setItem(storageKey, JSON.stringify({
                    note_for_author: noteAuthor ? noteAuthor.value : '',
                    internal_note: noteInternal ? noteInternal.value : ''
                }));
            };

            if (noteAuthor) noteAuthor.addEventListener('input', saveDraft);
            if (noteInternal) noteInternal.addEventListener('input', saveDraft);

            const decisionForm = document.getElementById('eic-decision-form');
            if (decisionForm) {
                decisionForm.addEventListener('submit', () => sessionStorage.removeItem(storageKey));
            }
        } catch (e) {
            // Abaikan bila sessionStorage dibatasi browser
        }
    });
</script>
@endPushOnce
