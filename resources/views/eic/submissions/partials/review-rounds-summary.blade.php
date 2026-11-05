{{-- resources/views/eic/submissions/partials/review-rounds-summary.blade.php --}}
@php
    $reviewSummary = $viewModel->reviewSummary();
@endphp

<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-5">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3.5">
        <div 
            class="flex items-center gap-2.5 min-w-0 cursor-help"
            data-tooltip="Ringkasan Telaah Sejawat & Rekomendasi Editor Bagian"
            data-tooltip-desc="Rekapitulasi penilaian mitra bestari dan rekomendasi editor bagian sebagai dasar keputusan akhir redaksi"
        >
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
                Ringkasan Telaah Sejawat & Rekomendasi Editor Bagian
            </h2>
        </div>

        @if($reviewSummary['has_round'])
            <span class="px-2.5 py-1 rounded-md text-xs font-semibold border tabular-nums shrink-0 {{ $reviewSummary['threshold_met'] ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                Progres Telaah: {{ $reviewSummary['completed_count'] }}/{{ $reviewSummary['minimum_required'] }} Selesai
            </span>
        @endif
    </div>

    @if($submission->reviewRounds->isEmpty())
        <div class="p-6 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-center space-y-1.5">
            <p class="text-xs font-semibold text-gray-700">Putaran Telaah Sejawat Belum Dibuka</p>
            <p class="text-xs text-gray-500 max-w-md mx-auto">
                Setelah naskah dinyatakan lolos skrining awal dan editor bagian ditugaskan, progres evaluasi mitra bestari akan tampil pada bagian ini.
            </p>
        </div>
    @else
        @php
            $roundsCount = $submission->reviewRounds->count();
            $latestRoundId = $submission->reviewRounds->sortByDesc('round')->first()?->id;
        @endphp

        <div class="space-y-5">
            @foreach($submission->reviewRounds as $round)
                @php
                    $completedCount = $round->assignments->where('status', 'completed')->count();
                    $minReq = max(1, (int) $round->minimum_completed_reviews);
                    $isOlderRound = ($roundsCount > 1 && $round->id !== $latestRoundId);
                @endphp
                <div class="rounded-lg border border-gray-200 p-4 sm:p-5 space-y-4 bg-gray-50/40">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200/80 pb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded bg-gray-900 text-white text-xs font-bold tabular-nums">
                                Putaran #{{ $round->round }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold border {{ $round->status_badge_class }}">
                                {{ $round->status_label }}
                            </span>
                            <span class="text-xs text-gray-500 tabular-nums">
                                {{ $completedCount }} dari minimal {{ $minReq }} penelaah selesai
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-500">
                                Skema Telaah Ganda Tertutup
                            </span>
                            @if($isOlderRound)
                                <button 
                                    type="button" 
                                    onclick="toggleEicRound('eic-round-content-{{ $round->id }}', this)" 
                                    class="text-xs font-semibold text-brand-maroon hover:underline flex items-center gap-1 cursor-pointer ml-1"
                                    aria-expanded="false"
                                >
                                    <span class="round-toggle-text">Lihat Detail Putaran</span>
                                    <svg class="w-3.5 h-3.5 transform transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div id="eic-round-content-{{ $round->id }}" class="{{ $isOlderRound ? 'hidden' : '' }} space-y-4">
                        <!-- Banner Rekomendasi Editor Bagian jika sudah ada -->
                        @if($round->se_recommendation)
                            @php
                                $seBadge = match($round->se_recommendation) {
                                    'accept' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                    'revisions_required' => 'bg-amber-100 text-amber-900 border-amber-300',
                                    'resubmit' => 'bg-orange-100 text-orange-900 border-orange-300',
                                    'decline' => 'bg-rose-100 text-rose-900 border-rose-300',
                                    default => 'bg-blue-100 text-blue-900 border-blue-300',
                                };
                                $seLabel = match($round->se_recommendation) {
                                    'accept' => 'Terima Naskah',
                                    'revisions_required' => 'Perlu Revisi Penulis',
                                    'resubmit' => 'Kirim Ulang untuk Telaah',
                                    'decline' => 'Tolak Naskah',
                                    default => 'Menunggu Evaluasi',
                                };
                                $isNoteLong = $round->se_recommendation_note && (mb_strlen($round->se_recommendation_note) > 160 || str_contains($round->se_recommendation_note, "\n"));
                            @endphp
                            <div class="p-3.5 rounded-lg bg-indigo-50/90 border border-indigo-200 space-y-2 text-xs">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-bold text-indigo-950 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Rekomendasi Resmi Editor Bagian:</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $seBadge }}">
                                        {{ $seLabel }}
                                    </span>
                                </div>
                                @if($round->se_recommendation_note)
                                    <div class="text-indigo-900 leading-relaxed text-left bg-white/80 p-2.5 rounded-lg border border-indigo-100">
                                        @if($isNoteLong)
                                            <p id="se-note-preview-{{ $round->id }}" class="italic line-clamp-2">
                                                "{{ Str::limit($round->se_recommendation_note, 150) }}"
                                            </p>
                                            <p id="se-note-full-{{ $round->id }}" class="italic hidden whitespace-pre-line">
                                                "{{ $round->se_recommendation_note }}"
                                            </p>
                                            <button 
                                                type="button" 
                                                onclick="toggleEicSeNote('{{ $round->id }}', this)" 
                                                class="mt-1.5 text-[11px] font-bold text-indigo-700 hover:text-indigo-900 inline-flex items-center gap-1 cursor-pointer"
                                                aria-expanded="false"
                                            >
                                                <span class="note-btn-text">Lihat Detail Catatan Editor</span>
                                                <svg class="w-3 h-3 transform transition-transform duration-200 note-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        @else
                                            <p class="italic">"{{ $round->se_recommendation_note }}"</p>
                                        @endif
                                    </div>
                                @endif
                                @if($round->se_recommended_at)
                                    <span class="block text-[11px] text-indigo-700 tabular-nums">
                                        Diserahkan kepada Pemimpin Redaksi pada {{ $round->se_recommended_at->format('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Daftar Penugasan & Evaluasi Mitra Bestari -->
                        @if($round->assignments->isEmpty())
                            <p class="text-xs text-gray-500">Belum ada mitra bestari yang ditugaskan oleh editor bagian pada putaran ini.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($round->assignments as $idx => $assignment)
                                    @php
                                        $isExtraCard = ($idx >= 2);
                                        $assignStatusLabel = match($assignment->effective_status) {
                                            'invited' => 'Undangan Terkirim',
                                            'accepted' => 'Sedang Menelaah',
                                            'completed' => 'Telaah Selesai',
                                            'overdue' => 'Melewati Batas Waktu',
                                            'no_response' => 'Tidak Ada Respon',
                                            'declined' => 'Menolak Undangan',
                                            'cancelled' => 'Dibatalkan',
                                            default => 'Dalam Proses',
                                        };
                                        $revRecLabel = $assignment->review ? match($assignment->review->recommendation) {
                                            'accept' => 'Terima Naskah',
                                            'revisions_required' => 'Perlu Revisi',
                                            'resubmit' => 'Telaah Ulang',
                                            'decline' => 'Tolak Naskah',
                                            default => 'Selesai',
                                        } : null;
                                    @endphp
                                    <div class="p-3.5 rounded-lg bg-white border border-gray-200 space-y-2.5 text-xs {{ $isExtraCard ? 'extra-eic-rev-'.$round->id.' hidden' : '' }}">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-md bg-brand-navy/10 text-brand-navy font-bold flex items-center justify-center text-xs shrink-0 tabular-nums">
                                                    R{{ $idx + 1 }}
                                                </span>
                                                <div>
                                                    <span class="font-bold text-gray-900 block">
                                                        {{ $assignment->reviewer?->name ?? 'Mitra Bestari #' . ($idx + 1) }}
                                                    </span>
                                                    <span class="text-[11px] text-gray-500">
                                                        {{ $assignment->reviewer?->email ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $assignment->status_badge_class }}">
                                                    {{ $assignStatusLabel }}
                                                </span>
                                                @if($assignment->review && $revRecLabel)
                                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $assignment->review->recommendation_badge_class }}">
                                                        {{ $revRecLabel }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($assignment->review)
                                            <!-- Laporan Evaluasi Substantif (Collapsible Detail untuk Optimasi UX) -->
                                            <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 overflow-hidden text-xs">
                                                <button 
                                                    type="button" 
                                                    class="w-full p-2.5 sm:p-3 flex items-center justify-between text-left hover:bg-emerald-100/60 transition cursor-pointer"
                                                    onclick="toggleEicReviewDetail('eic-rev-detail-{{ $assignment->id }}', this)"
                                                    aria-expanded="false"
                                                >
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        <span class="font-bold text-emerald-950 truncate">Laporan Evaluasi Substantif</span>
                                                        <span class="text-emerald-800 text-[11px] tabular-nums shrink-0">
                                                            ({{ $assignment->review->submitted_at ? $assignment->review->submitted_at->format('d M Y, H:i') : '-' }})
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 text-emerald-800 font-semibold text-[11px] shrink-0">
                                                        <span class="detail-label">Lihat Detail</span>
                                                        <svg class="w-4 h-4 transform transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </div>
                                                </button>

                                                <div id="eic-rev-detail-{{ $assignment->id }}" class="hidden p-3.5 pt-0 space-y-2.5 border-t border-emerald-100">
                                                    @if(!empty($assignment->review->scores) && is_array($assignment->review->scores))
                                                        <div class="flex flex-wrap gap-2 pt-2.5">
                                                            @foreach($assignment->review->scores as $criterion => $scoreVal)
                                                                <span class="px-2 py-0.5 rounded bg-white text-gray-800 text-[11px] font-medium border border-emerald-200 tabular-nums">
                                                                    {{ ucfirst(str_replace('_', ' ', (string) $criterion)) }}: <strong>{{ $scoreVal }}</strong>/100
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-1">
                                                        <div class="p-3 rounded-lg bg-white border border-emerald-100 space-y-1">
                                                            <span class="text-[11px] font-bold text-gray-500 uppercase block">Catatan untuk Penulis</span>
                                                            <p class="text-xs text-gray-700 leading-relaxed text-left break-words whitespace-pre-line">
                                                                {{ $assignment->review->comments_for_author }}
                                                            </p>
                                                        </div>

                                                        <div class="p-3 rounded-lg bg-purple-50/70 border border-purple-200/80 space-y-1">
                                                            <span class="text-[11px] font-bold text-purple-800 uppercase block">Catatan Rahasia untuk Editor</span>
                                                            <p class="text-xs text-purple-950 leading-relaxed text-left break-words whitespace-pre-line">
                                                                {{ $assignment->review->confidential_comments_for_editor ?: 'Tidak ada catatan rahasia tambahan.' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-[11px] text-gray-500 flex items-center gap-4 tabular-nums">
                                                <span>Batas Respon: <strong>{{ $assignment->response_due_at?->format('d M Y') ?? '-' }}</strong></span>
                                                <span class="text-gray-300">/</span>
                                                <span>Batas Telaah: <strong>{{ $assignment->review_due_at?->format('d M Y') ?? '-' }}</strong></span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                @if($round->assignments->count() > 2)
                                    <div class="pt-2 text-center">
                                        <button 
                                            type="button" 
                                            onclick="toggleEicExtraReviewers('{{ $round->id }}', this)" 
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-brand-maroon hover:bg-brand-maroon/5 rounded-lg border border-brand-maroon/20 transition cursor-pointer"
                                            data-expanded="false"
                                            data-count="{{ $round->assignments->count() - 2 }}"
                                        >
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            <span class="btn-text">Lihat Semua Mitra Bestari ({{ $round->assignments->count() - 2 }} Lainnya)</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

@pushOnce('scripts')
<script>
    function toggleEicReviewDetail(detailId, btn) {
        const detailEl = document.getElementById(detailId);
        if (!detailEl) return;

        const isHidden = detailEl.classList.contains('hidden');
        const label = btn.querySelector('.detail-label');
        const icon = btn.querySelector('.chevron-icon');

        if (isHidden) {
            detailEl.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            if (label) label.textContent = 'Tutup Detail';
            if (icon) icon.classList.add('rotate-180');
        } else {
            detailEl.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            if (label) label.textContent = 'Lihat Detail';
            if (icon) icon.classList.remove('rotate-180');
        }
    }

    function toggleEicExtraReviewers(roundId, btn) {
        const isExpanded = btn.getAttribute('data-expanded') === 'true';
        const count = btn.getAttribute('data-count');
        const extraCards = document.querySelectorAll('.extra-eic-rev-' + roundId);
        const textSpan = btn.querySelector('.btn-text');
        const icon = btn.querySelector('.chevron-icon');

        if (isExpanded) {
            extraCards.forEach(el => el.classList.add('hidden'));
            btn.setAttribute('data-expanded', 'false');
            if (textSpan) textSpan.textContent = `Lihat Semua Mitra Bestari (${count} Lainnya)`;
            if (icon) icon.classList.remove('rotate-180');
        } else {
            extraCards.forEach(el => el.classList.remove('hidden'));
            btn.setAttribute('data-expanded', 'true');
            if (textSpan) textSpan.textContent = 'Tutup Mitra Bestari Tambahan';
            if (icon) icon.classList.add('rotate-180');
        }
    }

    function toggleEicSeNote(roundId, btn) {
        const previewEl = document.getElementById('se-note-preview-' + roundId);
        const fullEl = document.getElementById('se-note-full-' + roundId);
        const label = btn.querySelector('.note-btn-text');
        const icon = btn.querySelector('.note-chevron');

        if (!previewEl || !fullEl) return;

        const isFullHidden = fullEl.classList.contains('hidden');
        if (isFullHidden) {
            fullEl.classList.remove('hidden');
            previewEl.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'true');
            if (label) label.textContent = 'Tutup Catatan Editor';
            if (icon) icon.classList.add('rotate-180');
        } else {
            fullEl.classList.add('hidden');
            previewEl.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'false');
            if (label) label.textContent = 'Lihat Detail Catatan Editor';
            if (icon) icon.classList.remove('rotate-180');
        }
    }

    function toggleEicRound(contentId, btn) {
        const contentEl = document.getElementById(contentId);
        if (!contentEl) return;

        const isHidden = contentEl.classList.contains('hidden');
        const label = btn.querySelector('.round-toggle-text');
        const icon = btn.querySelector('.chevron-icon');

        if (isHidden) {
            contentEl.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            if (label) label.textContent = 'Tutup Detail Putaran';
            if (icon) icon.classList.add('rotate-180');
        } else {
            contentEl.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            if (label) label.textContent = 'Lihat Detail Putaran';
            if (icon) icon.classList.remove('rotate-180');
        }
    }
</script>
@endpushOnce
