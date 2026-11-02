{{-- resources/views/section-editor/submissions/partials/action-sidebar.blade.php --}}
@php
    $activeRound = $viewModel->activeRound;
    $availableReviewers = $viewModel->availableReviewers;
    $isFinalized = $viewModel->isWorkflowFinalized();
    $canRecommend = $viewModel->canRecommendToEic();
    $completedCount = $viewModel->completedReviewsCount();
    $minReq = $viewModel->minimumCompletedReviews();

    $currentRec = (string) old('recommendation', $activeRound->se_recommendation ?? 'accept');
    $recommendationOptions = [
        'accept' => [
            'label' => 'Terima Naskah',
            'desc' => 'Lanjutkan naskah ke tahap penyuntingan teks dan produksi galai',
        ],
        'revisions_required' => [
            'label' => 'Perlu Revisi Penulis',
            'desc' => 'Minta perbaikan naskah berdasarkan masukan mitra bestari',
        ],
        'resubmit' => [
            'label' => 'Kirim Ulang untuk Telaah',
            'desc' => 'Perombakan besar yang wajib dinilai ulang pada putaran berikutnya',
        ],
        'decline' => [
            'label' => 'Tolak Naskah',
            'desc' => 'Rekomendasikan penghentian alur naskah karena tidak memenuhi syarat',
        ],
    ];

    $selectedReviewerId = (string) old('reviewer_id', '');
    $selectedReviewerObj = $availableReviewers->firstWhere('id', $selectedReviewerId);
@endphp

<aside class="lg:col-span-1 min-w-0 space-y-6">

    <!-- Kartu 1: Undang Mitra Bestari -->
    <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2 min-w-0 cursor-help"
                data-tooltip="Undang Mitra Bestari"
                data-tooltip-desc="Kirimkan penugasan telaah sejawat independen dengan batas waktu respons dan evaluasi"
            >
                <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                    Undang Mitra Bestari
                </h3>
            </div>
            @if(!$isFinalized)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 shrink-0">
                    Putaran {{ $activeRound->round }}
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-gray-600 bg-gray-100 border border-gray-200 shrink-0">
                    Terkunci
                </span>
            @endif
        </div>

        @if(!$isFinalized)
            <form action="{{ route('section-editor.reviewers.assign', $activeRound->id) }}" method="POST" class="space-y-4">
                @csrf

                <!-- Combobox: Pilih Mitra Bestari (Standar Jurnal OJS) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-1">
                        <label for="reviewer-combobox-input" class="font-bold text-gray-700 block">
                            Pilih Mitra Bestari <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] font-semibold text-brand-maroon bg-red-50 px-2 py-0.5 rounded border border-red-100 truncate max-w-[190px]" title="Kategori Naskah: {{ $viewModel->sectionName() }}">
                            Kategori: {{ $viewModel->sectionName() }}
                        </span>
                    </div>

                    <div class="relative reviewer-combobox-container" data-combobox="assign-reviewer">
                        <!-- Hidden native select for standard HTML5 validation & form submit -->
                        <select id="reviewer_id" name="reviewer_id" class="sr-only" tabindex="-1" required>
                            <option value="">-- Pilih dari Direktori Reviewer --</option>
                            @foreach($availableReviewers as $reviewer)
                                <option value="{{ $reviewer->id }}" {{ $selectedReviewerId === (string) $reviewer->id ? 'selected' : '' }}>
                                    {{ $reviewer->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Combobox Input Bar -->
                        <div class="relative flex items-center">
                            <!-- Magnifier Icon -->
                            <div class="absolute left-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <input 
                                type="text" 
                                id="reviewer-combobox-input"
                                class="reviewer-combobox-input w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 focus:bg-white border border-gray-300 focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 rounded-lg pl-9 pr-16 py-2.5 text-gray-900 font-semibold placeholder:font-normal placeholder:text-gray-400 transition cursor-text focus:outline-hidden"
                                placeholder="Ketik nama atau email mitra bestari..."
                                value="{{ $selectedReviewerObj ? $selectedReviewerObj->name : '' }}"
                                autocomplete="off"
                                role="combobox"
                                aria-autocomplete="list"
                                aria-expanded="false"
                                aria-controls="reviewer-listbox"
                            >

                            <!-- Right Controls: Clear and Chevron -->
                            <div class="absolute right-2 flex items-center gap-1">
                                <button 
                                    type="button" 
                                    class="reviewer-combobox-clear p-1 text-gray-400 hover:text-gray-600 rounded transition cursor-pointer {{ $selectedReviewerObj ? '' : 'hidden' }}"
                                    title="Hapus pilihan mitra bestari"
                                    tabindex="-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <button 
                                    type="button" 
                                    class="reviewer-combobox-toggle p-1 text-gray-400 hover:text-gray-600 transition cursor-pointer" 
                                    tabindex="-1"
                                    title="Tampilkan daftar mitra bestari"
                                >
                                    <svg class="reviewer-combobox-chevron w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown Panel -->
                        <div id="reviewer-listbox" role="listbox" class="reviewer-combobox-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full max-h-80 overflow-y-auto bg-white rounded-xl border border-gray-200 shadow-xl p-1.5 z-40 space-y-1 absolute">
                            <!-- Info Bar Kategori Naskah & Jurnal (Sesuai ERD) -->
                            <div class="px-2.5 py-1.5 bg-gray-50 border border-gray-100 rounded-lg space-y-0.5 text-[11px] text-gray-600 mb-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-medium text-gray-500">Kategori:</span>
                                    <span class="font-bold text-gray-900 truncate max-w-[180px]" title="{{ $viewModel->sectionName() }}">{{ $viewModel->sectionName() }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-medium text-gray-500">Jurnal:</span>
                                    <span class="font-semibold text-brand-maroon truncate max-w-[180px]" title="{{ $submission->journal?->name }}">{{ $submission->journal?->name }}</span>
                                </div>
                            </div>

                            <!-- List Items -->
                            <div class="reviewer-combobox-items space-y-1">
                                @forelse($availableReviewers as $reviewer)
                                    @php 
                                        $isRevSelected = ($selectedReviewerId === (string) $reviewer->id);
                                        $searchKeywords = implode(' ', [$reviewer->name, $reviewer->email]);
                                    @endphp
                                    <div 
                                        class="reviewer-combobox-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isRevSelected ? 'bg-red-50 text-brand-maroon font-semibold is-selected' : 'text-gray-700 hover:bg-gray-50' }}"
                                        data-value="{{ $reviewer->id }}"
                                        data-name="{{ $reviewer->name }}"
                                        data-email="{{ $reviewer->email }}"
                                        data-search="{{ strtolower($searchKeywords) }}"
                                        role="option"
                                        aria-selected="{{ $isRevSelected ? 'true' : 'false' }}"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="reviewer-name font-semibold text-gray-900 leading-tight">{{ $reviewer->name }}</span>
                                            </div>

                                            <div class="reviewer-email text-[11px] text-gray-500 font-normal truncate mt-0.5">
                                                {{ $reviewer->email }}
                                            </div>
                                        </div>

                                        <svg class="reviewer-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isRevSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-500">
                                        Belum ada mitra bestari yang terdaftar untuk jurnal ini.
                                    </div>
                                @endforelse
                            </div>

                            <!-- State ketika filter tidak menemukan hasil -->
                            <div class="reviewer-combobox-empty p-4 text-center text-xs text-gray-500 hidden">
                                <svg class="w-6 h-6 mx-auto text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span>Tidak ada mitra bestari yang sesuai dengan pencarian.</span>
                            </div>

                            <!-- Footer Info Count -->
                            @if(count($availableReviewers) > 0)
                                <div class="reviewer-combobox-footer px-2.5 py-1 text-center text-[11px] text-gray-400 border-t border-gray-100">
                                    Menampilkan <span class="reviewer-count-visible font-semibold text-gray-600">{{ count($availableReviewers) }}</span> dari {{ count($availableReviewers) }} mitra bestari terdaftar
                                </div>
                            @endif
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500">Hanya menampilkan mitra bestari terdaftar di jurnal ini. Penulis dan kontributor otomatis dikecualikan guna kepatuhan etika telaah sejawat (COPE/OJS).</p>
                </div>

                <!-- Batas Waktu Respon & Batas Waktu Telaah -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label for="response_due_at" class="font-bold text-gray-700 block">Batas Respon <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            id="response_due_at" 
                            name="response_due_at" 
                            required 
                            value="{{ old('response_due_at', now()->addDays(7)->format('Y-m-d')) }}" 
                            min="{{ now()->addDay()->format('Y-m-d') }}" 
                            class="w-full px-3 py-2 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon tabular-nums font-medium transition"
                        >
                    </div>
                    <div class="space-y-1.5">
                        <label for="review_due_at" class="font-bold text-gray-700 block">Batas Telaah <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            id="review_due_at" 
                            name="review_due_at" 
                            required 
                            value="{{ old('review_due_at', now()->addDays(21)->format('Y-m-d')) }}" 
                            min="{{ now()->addDays(2)->format('Y-m-d') }}" 
                            class="w-full px-3 py-2 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon tabular-nums font-medium transition"
                        >
                    </div>
                </div>

                <!-- Pesan Undangan (Opsional) -->
                <div class="space-y-1.5">
                    <label for="invitation_message" class="font-bold text-gray-700 block">Pesan Undangan (Opsional)</label>
                    <textarea 
                        id="invitation_message" 
                        name="invitation_message" 
                        rows="2" 
                        placeholder="Pesan pengantar dari Editor Bagian mengenai relevansi kepakaran atau fokus evaluasi..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    >{{ old('invitation_message') }}</textarea>
                </div>

                <button 
                    type="submit" 
                    class="w-full min-h-10 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition cursor-pointer"
                >
                    Kirim Undangan Reviewer
                </button>
            </form>
        @else
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 leading-relaxed">
                Penugasan mitra bestari telah dikunci karena alur editorial naskah ini telah mencapai keputusan final.
            </div>
        @endif
    </section>

    <!-- Kartu 2: Rekomendasi Editorial untuk Pemimpin Redaksi (EIC) -->
    <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <div 
                class="flex items-center gap-2 min-w-0 cursor-help"
                data-tooltip="Rekomendasi ke EIC"
                data-tooltip-desc="Sintesis hasil telaah mitra bestari dan pengajuan rekomendasi resmi kepada Pemimpin Redaksi"
            >
                <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold text-gray-900 leading-tight truncate">
                    Rekomendasi ke EIC
                </h3>
            </div>
            @if($viewModel->hasSeRecommendation())
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 shrink-0">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Terkirim</span>
                </span>
            @elseif(!$canRecommend)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 shrink-0">
                    Menunggu Kuorum
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold text-sky-800 bg-sky-50 border border-sky-200 shrink-0">
                    Siap Rekomendasi
                </span>
            @endif
        </div>

        @if(!$canRecommend && !$viewModel->hasSeRecommendation())
            <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 leading-relaxed">
                Kuorum telaah belum tercapai (<strong class="tabular-nums">{{ $completedCount }}/{{ $minReq }} review selesai</strong>). Anda disarankan menunggu seluruh laporan penelaah sebelum mengirimkan rekomendasi akhir ke Pemimpin Redaksi.
            </div>
        @endif

        @if(!$isFinalized)
            <form action="{{ route('section-editor.rounds.recommendation', $activeRound->id) }}" 
                  method="POST" 
                  class="space-y-4"
                  data-confirm="Apakah Anda yakin ingin menyerahkan rekomendasi editorial ini kepada Pemimpin Redaksi (EIC)?"
                  data-confirm-title="Kirim Rekomendasi Editorial ke EIC?"
                  data-confirm-btn="Kirim Rekomendasi"
                  data-confirm-cancel="Batal"
                  data-confirm-type="primary">
                @csrf

                <!-- Custom Dropdown: Rekomendasi Keputusan -->
                <div class="space-y-1.5">
                    <label class="font-bold text-gray-700 block">Rekomendasi Keputusan <span class="text-rose-500">*</span></label>
                    <div class="relative custom-dropdown-container" data-dropdown="se-recommendation">
                        <select id="recommendation" name="recommendation" class="sr-only" tabindex="-1" required>
                            @foreach($recommendationOptions as $val => $opt)
                                <option value="{{ $val }}" {{ $currentRec === $val ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                        <button 
                            type="button" 
                            class="custom-dropdown-btn w-full text-xs bg-gray-50/90 hover:bg-gray-100/80 border border-gray-300 hover:border-gray-400 rounded-lg px-3.5 py-2.5 text-gray-900 font-semibold flex items-center justify-between gap-2 transition cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span class="custom-dropdown-label truncate">
                                {{ $recommendationOptions[$currentRec]['label'] ?? 'Terima Naskah' }}
                            </span>
                            <svg class="custom-dropdown-chevron w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-lg p-1.5 z-30 space-y-1 absolute">
                            @foreach($recommendationOptions as $val => $opt)
                                @php $isRecSelected = ($currentRec === $val); @endphp
                                <div 
                                    class="custom-dropdown-item flex items-start justify-between gap-2 px-3 py-2 text-xs rounded-lg cursor-pointer transition {{ $isRecSelected ? 'bg-red-50 text-brand-maroon font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                                    data-value="{{ $val }}"
                                    data-label="{{ $opt['label'] }}"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold leading-tight">{{ $opt['label'] }}</div>
                                        <div class="text-[11px] text-gray-500 font-normal mt-0.5 leading-snug">{{ $opt['desc'] }}</div>
                                    </div>
                                    <svg class="custom-dropdown-check w-4 h-4 text-brand-maroon shrink-0 mt-0.5 {{ $isRecSelected ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Catatan Evaluasi Substantif untuk EIC -->
                <div class="space-y-1.5">
                    <label for="note" class="font-bold text-gray-700 block">
                        Catatan Evaluasi Substantif untuk EIC <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        id="note" 
                        name="note" 
                        rows="4" 
                        required 
                        placeholder="Tuliskan sintesis akademis, konsistensi masukan penelaah, dan dasar rekomendasi editorial ini..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    >{{ old('note', $activeRound->se_recommendation_note) }}</textarea>
                </div>

                <button 
                    type="submit" 
                    class="w-full min-h-10 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white font-semibold rounded-lg transition cursor-pointer"
                >
                    {{ $viewModel->hasSeRecommendation() ? 'Perbarui Rekomendasi ke EIC' : 'Kirim Rekomendasi ke EIC' }}
                </button>
            </form>
        @else
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 leading-relaxed">
                Rekomendasi editorial telah ditutup karena naskah telah berstatus final.
            </div>
        @endif
    </section>

</aside>

@pushOnce('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. Reviewer Searchable Combobox (OJS Standard) ---
        function initReviewerCombobox() {
            const container = document.querySelector('.reviewer-combobox-container');
            if (!container) return;

            const input = container.querySelector('.reviewer-combobox-input');
            const clearBtn = container.querySelector('.reviewer-combobox-clear');
            const toggleBtn = container.querySelector('.reviewer-combobox-toggle');
            const chevron = container.querySelector('.reviewer-combobox-chevron');
            const panel = container.querySelector('.reviewer-combobox-panel');
            const items = container.querySelectorAll('.reviewer-combobox-item');
            const emptyState = container.querySelector('.reviewer-combobox-empty');
            const countVisible = container.querySelector('.reviewer-count-visible');
            const hiddenSelect = container.querySelector('select[name="reviewer_id"]');

            let activeIndex = -1;

            function openPanel() {
                closeAllDropdowns();
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
                clearHighlight();
            }

            function filterReviewers(query) {
                const q = query.trim().toLowerCase();
                let visibleCount = 0;

                items.forEach(item => {
                    const searchData = item.getAttribute('data-search') || '';
                    const match = (q === '') || searchData.includes(q);

                    if (match) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                if (countVisible) countVisible.textContent = visibleCount;
                if (emptyState) {
                    if (visibleCount === 0) {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }
                }
            }

            function selectReviewer(item) {
                const val = item.getAttribute('data-value') || '';
                const name = item.getAttribute('data-name') || '';

                if (hiddenSelect) {
                    hiddenSelect.value = val;
                    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }

                input.value = name;
                if (clearBtn) clearBtn.classList.remove('hidden');

                items.forEach(it => {
                    const check = it.querySelector('.reviewer-check');
                    if (it === item) {
                        it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold', 'is-selected');
                        it.classList.remove('text-gray-700');
                        if (check) check.classList.remove('hidden');
                        it.setAttribute('aria-selected', 'true');
                    } else {
                        it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold', 'is-selected');
                        it.classList.add('text-gray-700');
                        if (check) check.classList.add('hidden');
                        it.setAttribute('aria-selected', 'false');
                    }
                });

                closePanel();
            }

            function clearSelection() {
                if (hiddenSelect) {
                    hiddenSelect.value = '';
                    hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }

                input.value = '';
                if (clearBtn) clearBtn.classList.add('hidden');

                items.forEach(it => {
                    const check = it.querySelector('.reviewer-check');
                    it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold', 'is-selected');
                    it.classList.add('text-gray-700');
                    if (check) check.classList.add('hidden');
                    it.setAttribute('aria-selected', 'false');
                });

                filterReviewers('');
                input.focus();
            }

            function getVisibleItems() {
                return Array.from(items).filter(it => !it.classList.contains('hidden'));
            }

            function clearHighlight() {
                items.forEach(it => it.classList.remove('ring-1', 'ring-brand-maroon'));
            }

            function highlightItem(index) {
                clearHighlight();
                const visible = getVisibleItems();
                if (visible.length === 0) return;

                if (index < 0) index = 0;
                if (index >= visible.length) index = visible.length - 1;

                activeIndex = index;
                const target = visible[activeIndex];
                target.classList.add('ring-1', 'ring-brand-maroon');
                target.scrollIntoView({ block: 'nearest' });
            }

            // Event Listeners
            input.addEventListener('focus', function () {
                openPanel();
                filterReviewers(input.value);
            });

            input.addEventListener('input', function () {
                openPanel();
                filterReviewers(input.value);
                if (input.value.trim() !== '') {
                    if (clearBtn) clearBtn.classList.remove('hidden');
                } else if (!hiddenSelect.value) {
                    if (clearBtn) clearBtn.classList.add('hidden');
                }
            });

            input.addEventListener('keydown', function (e) {
                const visible = getVisibleItems();
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!panel.classList.contains('visible')) {
                        openPanel();
                    }
                    highlightItem(activeIndex + 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!panel.classList.contains('visible')) {
                        openPanel();
                    }
                    highlightItem(activeIndex - 1);
                } else if (e.key === 'Enter') {
                    if (panel.classList.contains('visible') && activeIndex >= 0 && visible[activeIndex]) {
                        e.preventDefault();
                        selectReviewer(visible[activeIndex]);
                    }
                } else if (e.key === 'Escape') {
                    closePanel();
                }
            });

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (panel.classList.contains('visible')) {
                        closePanel();
                    } else {
                        openPanel();
                        filterReviewers(input.value);
                        input.focus();
                    }
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    clearSelection();
                });
            }

            items.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectReviewer(item);
                });
            });

            // Tutup combobox jika klik di luar
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.reviewer-combobox-container')) {
                    closePanel();
                }
            });

            window.closeReviewerCombobox = closePanel;
        }

        // --- 2. Custom Dropdown untuk Rekomendasi EIC ---
        const dropdownContainers = document.querySelectorAll('.custom-dropdown-container');

        function openDropdown(panel, btn, chevron) {
            closeAllDropdowns();
            if (window.closeReviewerCombobox) window.closeReviewerCombobox();
            panel.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
            panel.classList.add('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
            btn.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.classList.add('rotate-180');
        }

        function closeDropdown(panel, btn, chevron) {
            panel.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto');
            panel.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none');
            btn.setAttribute('aria-expanded', 'false');
            if (chevron) chevron.classList.remove('rotate-180');
        }

        function closeAllDropdowns() {
            dropdownContainers.forEach(container => {
                const panel = container.querySelector('.custom-dropdown-panel');
                const btn = container.querySelector('.custom-dropdown-btn');
                const chevron = container.querySelector('.custom-dropdown-chevron');
                if (panel && btn) closeDropdown(panel, btn, chevron);
            });
        }

        dropdownContainers.forEach(container => {
            const btn = container.querySelector('.custom-dropdown-btn');
            const panel = container.querySelector('.custom-dropdown-panel');
            const chevron = container.querySelector('.custom-dropdown-chevron');
            const labelSpan = container.querySelector('.custom-dropdown-label');
            const selectEl = container.querySelector('select');
            const items = container.querySelectorAll('.custom-dropdown-item');

            if (!btn || !panel || !selectEl) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = panel.classList.contains('visible');
                if (isOpen) {
                    closeDropdown(panel, btn, chevron);
                } else {
                    openDropdown(panel, btn, chevron);
                }
            });

            items.forEach(item => {
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const val = item.getAttribute('data-value');
                    const label = item.getAttribute('data-label');

                    selectEl.value = val;
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));

                    if (labelSpan) labelSpan.textContent = label;

                    items.forEach(it => {
                        const check = it.querySelector('.custom-dropdown-check');
                        if (it === item) {
                            it.classList.add('bg-red-50', 'text-brand-maroon', 'font-semibold');
                            it.classList.remove('text-gray-700');
                            if (check) check.classList.remove('hidden');
                        } else {
                            it.classList.remove('bg-red-50', 'text-brand-maroon', 'font-semibold');
                            it.classList.add('text-gray-700');
                            if (check) check.classList.add('hidden');
                        }
                    });

                    closeDropdown(panel, btn, chevron);
                });
            });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.custom-dropdown-container')) {
                closeAllDropdowns();
            }
        });

        // Initialize Reviewer Combobox
        initReviewerCombobox();
    });
</script>
@endpushOnce
