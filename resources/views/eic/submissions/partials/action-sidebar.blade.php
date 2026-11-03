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

                <!-- Custom Dropdown: Delegasikan Langsung ke Editor Bagian -->
                <div id="direct_se_wrapper" class="space-y-1.5 {{ $currentDeskStatus === 'passed' ? '' : 'hidden' }}">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-700">Delegasikan ke Editor Bagian</label>
                        <span class="text-[11px] text-gray-400">Opsional</span>
                    </div>
                    <div class="relative custom-dropdown-container" data-dropdown="desk-se">
                        <select id="desk_section_editor_id" name="section_editor_id" class="sr-only" tabindex="-1">
                            <option value="">Tugaskan Nanti Secara Terpisah</option>
                            @foreach($availableEditors as $editor)
                                <option value="{{ $editor->id }}" {{ $currentEditorId === (string) $editor->id ? 'selected' : '' }}>
                                    {{ $editor->name }}
                                </option>
                            @endforeach
                        </select>
                        <button
                            type="button"
                            class="custom-dropdown-btn w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-lg px-3.5 py-2.5 text-gray-800 font-medium flex items-center justify-between gap-2 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span class="custom-dropdown-label truncate">
                                {{ $selectedEditorObj ? $selectedEditorObj->name : 'Tugaskan Nanti Secara Terpisah' }}
                            </span>
                            <svg class="custom-dropdown-chevron w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full max-h-60 overflow-y-auto bg-white rounded-xl border border-gray-200 shadow-lg p-1.5 z-30 space-y-1 absolute">
                            <div
                                class="custom-dropdown-item flex items-center justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $currentEditorId === '' ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                data-value=""
                                data-label="Tugaskan Nanti Secara Terpisah"
                            >
                                <span>Tugaskan Nanti Secara Terpisah</span>
                                <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 {{ $currentEditorId === '' ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            @foreach($availableEditors as $editor)
                                @php $isEdSelected = ($currentEditorId === (string) $editor->id); @endphp
                                <div
                                    class="custom-dropdown-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isEdSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-value="{{ $editor->id }}"
                                    data-label="{{ $editor->name }}"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold truncate">{{ $editor->name }}</div>
                                        <div class="text-[11px] text-gray-500 font-normal truncate">{{ $editor->email }}</div>
                                    </div>
                                    <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isEdSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @endforeach
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

                <!-- Custom Dropdown: Pilih Editor Bagian -->
                <div class="space-y-1.5">
                    <label class="font-bold text-gray-700 block">Pilih Editor Bagian <span class="text-rose-500">*</span></label>
                    <div class="relative custom-dropdown-container" data-dropdown="assign-se">
                        <select id="section_editor_id" name="section_editor_id" class="sr-only" tabindex="-1" required>
                            <option value="">Pilih Editor Penanggung Jawab</option>
                            @foreach($availableEditors as $editor)
                                <option value="{{ $editor->id }}" {{ $currentEditorId === (string) $editor->id ? 'selected' : '' }}>
                                    {{ $editor->name }}
                                </option>
                            @endforeach
                        </select>
                        <button
                            type="button"
                            class="custom-dropdown-btn w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-lg px-3.5 py-2.5 text-gray-900 font-semibold flex items-center justify-between gap-2 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span class="custom-dropdown-label truncate">
                                {{ $selectedEditorObj ? $selectedEditorObj->name : 'Pilih Editor Penanggung Jawab' }}
                            </span>
                            <svg class="custom-dropdown-chevron w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full max-h-60 overflow-y-auto bg-white rounded-xl border border-gray-200 shadow-lg p-1.5 z-30 space-y-1 absolute">
                            @foreach($availableEditors as $editor)
                                @php $isEdSelected = ($currentEditorId === (string) $editor->id); @endphp
                                <div
                                    class="custom-dropdown-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isEdSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-value="{{ $editor->id }}"
                                    data-label="{{ $editor->name }}"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold truncate">{{ $editor->name }}</div>
                                        <div class="text-[11px] text-gray-500 font-normal truncate">{{ $editor->email }}</div>
                                    </div>
                                    <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isEdSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @endforeach
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
                <div class="p-3 rounded-lg border text-xs font-semibold {{ $reviewSummary['se_recommendation_badge'] }}">
                    {{ $reviewSummary['se_recommendation_label'] }}
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
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-1.5 z-30 space-y-1 absolute">
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
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs shadow-xs transition duration-150 focus:outline-hidden focus:ring-2 focus:ring-emerald-700 focus:ring-offset-1"
                    onclick="return confirm('Apakah Anda yakin ingin mengesahkan galai naskah ini? Status naskah akan otomatis beralih menjadi Siap Terbit (ready_for_issue).')"
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
        <div class="flex items-start justify-between gap-2 border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-snug">
                        Status Pembiayaan & APC
                    </h3>
                    <span class="text-[11px] text-gray-400 font-medium block">Biaya Pengelolaan Publikasi</span>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold border shrink-0 text-center leading-tight {{ $paymentInfo['status_badge'] }}">
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

                    @if($paymentInfo['can_verify'])
                        <form action="{{ route('eic.submissions.payment.verify', $submission->id) }}" method="POST" class="pt-0.5">
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
                @else
                    <div class="p-3 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-[11px] text-gray-500 text-center leading-relaxed">
                        {{ $paymentInfo['status'] === 'verified' ? 'Pembayaran telah lunas dan terverifikasi.' : 'Penulis belum mengunggah bukti transfer pembayaran.' }}
                    </div>
                @endif
            </div>
        @endif
    </section>

</aside>

@pushOnce('scripts')
<script>
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

    document.addEventListener('DOMContentLoaded', function () {
        const deskStatus = document.getElementById('desk_status');
        if (deskStatus) toggleDirectSeSelect(deskStatus.value);

        const decisionSelect = document.getElementById('decision');
        if (decisionSelect) handleDecisionChange(decisionSelect.value);

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
