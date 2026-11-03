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
            @if($viewModel->hasOverrideRecommendation())
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 shrink-0">
                    <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Dispensasi Kuorum</span>
                </span>
            @elseif($viewModel->hasSeRecommendation())
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

        @if($isFinalized)
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 leading-relaxed">
                Rekomendasi editorial telah ditutup karena naskah telah berstatus final.
            </div>
        @else
            @if($viewModel->minimumReviewOverrideReason())
                <div class="p-3 rounded-lg bg-amber-50/80 border border-amber-200 text-xs text-amber-950 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-amber-900">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Alasan Dispensasi Kuorum Diajukan:</span>
                    </div>
                    <p class="italic text-[11px] text-amber-900 pl-5 leading-relaxed">
                        "{{ $viewModel->minimumReviewOverrideReason() }}"
                    </p>
                </div>
            @endif

            <form id="se-recommendation-form"
                  action="{{ route('section-editor.rounds.recommendation', $activeRound->id) }}" 
                  method="POST" 
                  class="space-y-4"
                  data-confirm="Apakah Anda yakin ingin menyerahkan rekomendasi editorial ini kepada Pemimpin Redaksi (EIC)?"
                  data-confirm-title="Kirim Rekomendasi Editorial ke EIC?"
                  data-confirm-btn="Kirim Rekomendasi"
                  data-confirm-cancel="Batal"
                  data-confirm-type="primary">
                @csrf

                @if(!$canRecommend)
                    @php
                        $isOverrideChecked = (bool) old('is_override', $viewModel->minimumReviewOverrideReason() ? '1' : '');
                    @endphp
                    <div class="p-3.5 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-900 space-y-2.5">
                        <div class="flex items-center gap-2 font-bold text-amber-950">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Kuorum Telaah Belum Terpenuhi ({{ $completedCount }}/{{ $minReq }} Selesai)</span>
                        </div>
                        <p class="leading-relaxed text-[11px] text-amber-900">
                            Sesuai standar penelaahan sejawat OJS &amp; COPE, rekomendasi normal membutuhkan minimal {{ $minReq }} laporan mitra bestari. Anda dapat menggunakan opsi dispensasi/override jika terdapat alasan substantif khusus (seperti dugaan plagiarisme berat, penarikan oleh penulis, atau mitra bestari tidak merespons).
                        </p>
                        <div class="pt-2 border-t border-amber-200/80">
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input 
                                    type="checkbox" 
                                    id="enable-override-checkbox" 
                                    name="is_override" 
                                    value="1"
                                    class="mt-0.5 rounded border-amber-300 text-brand-maroon focus:ring-brand-maroon/20 focus:ring-offset-0 h-4 w-4 transition cursor-pointer"
                                    {{ $isOverrideChecked ? 'checked' : '' }}
                                >
                                <span class="text-[11px] font-bold text-amber-950 leading-tight">
                                    Gunakan Dispensasi / Override Kuorum
                                    <span class="block font-normal text-amber-800 text-[10.5px] mt-0.5">
                                        Centang untuk menyertakan alasan khusus dan membuka pengiriman rekomendasi.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Input Alasan Dispensasi Kuorum -->
                    <div id="override-reason-container" class="space-y-1.5 transition-all duration-200 {{ $isOverrideChecked ? '' : 'hidden' }}">
                        <label for="override_reason" class="font-bold text-amber-950 block">
                            Alasan Pengabaian Kuorum Minimum (Override Reason) <span class="text-rose-500">*</span>
                        </label>
                        <textarea 
                            id="override_reason" 
                            name="override_reason" 
                            rows="3" 
                            placeholder="Jelaskan alasan substantif mengapa rekomendasi diajukan sebelum kuorum terpenuhi (contoh: dugaan plagiarisme, penarikan naskah, atau reviewer tidak dapat dihubungi)..."
                            class="w-full p-3 text-xs bg-amber-50/40 focus:bg-white border border-amber-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-amber-500/20 focus:border-amber-600 leading-relaxed transition"
                            {{ $isOverrideChecked ? 'required minlength=10' : '' }}
                        >{{ old('override_reason', $viewModel->minimumReviewOverrideReason()) }}</textarea>
                        <p class="text-[10px] text-gray-500 leading-snug">
                            Wajib diisi minimal 10 karakter. Alasan ini akan tercatat resmi pada rekam jejak editorial (<span class="font-mono">editorial_decisions</span>) untuk diverifikasi oleh Pemimpin Redaksi (EIC).
                        </p>
                    </div>
                @endif

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
                        <div class="custom-dropdown-panel invisible opacity-0 scale-95 pointer-events-none transition-all duration-150 ease-out origin-top left-0 right-0 top-full mt-1.5 w-full bg-white rounded-xl border border-gray-200 shadow-xl p-1.5 z-50 space-y-1 absolute">
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
                        minlength="10"
                        placeholder="Tuliskan sintesis akademis, konsistensi masukan penelaah, dan dasar rekomendasi editorial ini..."
                        class="w-full p-3 text-xs bg-gray-50/70 focus:bg-white border border-gray-300 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed transition"
                    >{{ old('note', $activeRound->se_recommendation_note) }}</textarea>
                    <p class="text-[10px] text-gray-500 leading-snug">
                        Wajib diisi minimal 10 karakter sebagai pertimbangan substantif bagi Pemimpin Redaksi.
                    </p>
                </div>

                <div class="pt-1">
                    @if(!$canRecommend)
                        <button 
                            type="submit" 
                            id="se-recommendation-submit-btn"
                            class="w-full min-h-10 py-2.5 font-semibold rounded-lg transition flex items-center justify-center gap-1.5 text-xs {{ $isOverrideChecked ? 'bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white cursor-pointer' : 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed' }}"
                            {{ $isOverrideChecked ? '' : 'disabled' }}
                        >
                            <svg class="w-3.5 h-3.5 {{ $isOverrideChecked ? 'hidden' : '' }}" id="se-submit-lock-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span id="se-submit-btn-text">
                                {{ $isOverrideChecked ? ($viewModel->hasSeRecommendation() ? 'Perbarui Rekomendasi (Dispensasi)' : 'Kirim Rekomendasi (Dengan Dispensasi)') : 'Kuorum Belum Terpenuhi (Perlu Dispensasi)' }}
                            </span>
                        </button>
                    @else
                        <button 
                            type="submit" 
                            id="se-recommendation-submit-btn"
                            class="w-full min-h-10 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white font-semibold rounded-lg transition cursor-pointer text-xs"
                        >
                            {{ $viewModel->hasSeRecommendation() ? 'Perbarui Rekomendasi ke EIC' : 'Kirim Rekomendasi ke EIC' }}
                        </button>
                    @endif
                </div>
            </form>
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
                if (window.closeAllCustomDropdowns) window.closeAllCustomDropdowns();
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

        // Tutup combobox reviewer jika dropdown rekomendasi diklik
        const dropdownBtn = document.querySelector('.custom-dropdown-container .custom-dropdown-btn');
        if (dropdownBtn) {
            dropdownBtn.addEventListener('click', function () {
                if (window.closeReviewerCombobox) window.closeReviewerCombobox();
            }, true);
        }

        // --- 2. Override Kuorum Handler ---
        const overrideCheckbox = document.getElementById('enable-override-checkbox');
        const overrideContainer = document.getElementById('override-reason-container');
        const overrideTextarea = document.getElementById('override_reason');
        const submitBtn = document.getElementById('se-recommendation-submit-btn');
        const submitBtnText = document.getElementById('se-submit-btn-text');
        const submitLockIcon = document.getElementById('se-submit-lock-icon');
        const recForm = document.getElementById('se-recommendation-form');

        if (overrideCheckbox && overrideContainer && submitBtn) {
            const hasExistingRec = {{ $viewModel->hasSeRecommendation() ? 'true' : 'false' }};

            overrideCheckbox.addEventListener('change', function () {
                if (this.checked) {
                    overrideContainer.classList.remove('hidden');
                    if (overrideTextarea) {
                        overrideTextarea.setAttribute('required', 'required');
                        overrideTextarea.setAttribute('minlength', '10');
                        overrideTextarea.focus();
                    }
                    submitBtn.removeAttribute('disabled');
                    submitBtn.classList.remove('bg-gray-100', 'text-gray-400', 'border', 'border-gray-200', 'cursor-not-allowed');
                    submitBtn.classList.add('bg-brand-maroon', 'hover:bg-brand-maroon-hover', 'active:bg-brand-maroon-active', 'text-white', 'cursor-pointer');
                    if (submitLockIcon) submitLockIcon.classList.add('hidden');
                    if (submitBtnText) {
                        submitBtnText.textContent = hasExistingRec ? 'Perbarui Rekomendasi (Dispensasi)' : 'Kirim Rekomendasi (Dengan Dispensasi)';
                    }
                } else {
                    overrideContainer.classList.add('hidden');
                    if (overrideTextarea) {
                        overrideTextarea.removeAttribute('required');
                        overrideTextarea.removeAttribute('minlength');
                    }
                    submitBtn.setAttribute('disabled', 'disabled');
                    submitBtn.classList.add('bg-gray-100', 'text-gray-400', 'border', 'border-gray-200', 'cursor-not-allowed');
                    submitBtn.classList.remove('bg-brand-maroon', 'hover:bg-brand-maroon-hover', 'active:bg-brand-maroon-active', 'text-white', 'cursor-pointer');
                    if (submitLockIcon) submitLockIcon.classList.remove('hidden');
                    if (submitBtnText) {
                        submitBtnText.textContent = 'Kuorum Belum Terpenuhi (Perlu Dispensasi)';
                    }
                }
            });

            if (recForm) {
                recForm.addEventListener('submit', function (e) {
                    if (overrideCheckbox.checked && overrideTextarea) {
                        const val = overrideTextarea.value.trim();
                        if (val.length < 10) {
                            e.preventDefault();
                            if (window.customModal) {
                                window.customModal.alert({
                                    title: 'Alasan Dispensasi Wajib Diisi',
                                    message: 'Pengajuan rekomendasi mendahului kuorum wajib menyertakan alasan dispensasi/override minimal 10 karakter.',
                                    type: 'warning',
                                    btnText: 'Tutup'
                                });
                            } else {
                                alert('Pengajuan rekomendasi mendahului kuorum wajib menyertakan alasan dispensasi/override minimal 10 karakter.');
                            }
                            overrideTextarea.focus();
                        }
                    }
                });
            }
        }

        // Pastikan custom dropdown diinisialisasi oleh app.js
        if (window.initCustomDropdowns) {
            window.initCustomDropdowns();
        }

        // Initialize Reviewer Combobox
        initReviewerCombobox();
    });
</script>
@endpushOnce
