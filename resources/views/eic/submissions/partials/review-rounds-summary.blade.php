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
        <div class="space-y-5">
            @foreach($submission->reviewRounds as $round)
                @php
                    $completedCount = $round->assignments->where('status', 'completed')->count();
                    $minReq = max(1, (int) $round->minimum_completed_reviews);
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
                        <span class="text-xs font-medium text-gray-500">
                            Skema Telaah Dua Arah
                        </span>
                    </div>

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
                        @endphp
                        <div class="p-3.5 rounded-lg bg-indigo-50/90 border border-indigo-200 space-y-1.5 text-xs">
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
                                <p class="text-indigo-900 leading-relaxed text-left bg-white/80 p-2.5 rounded-lg border border-indigo-100">
                                    "{{ $round->se_recommendation_note }}"
                                </p>
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
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($round->assignments as $idx => $assignment)
                                @php
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
                                <div class="p-3.5 rounded-lg bg-white border border-gray-200 space-y-2.5 text-xs">
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
                                        @if(!empty($assignment->review->scores) && is_array($assignment->review->scores))
                                            <div class="flex flex-wrap gap-2 pt-1">
                                                @foreach($assignment->review->scores as $criterion => $scoreVal)
                                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-[11px] font-medium border border-gray-200 tabular-nums">
                                                        {{ ucfirst(str_replace('_', ' ', (string) $criterion)) }}: <strong>{{ $scoreVal }}/5</strong>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-1">
                                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-200/80 space-y-1">
                                                <span class="text-[11px] font-bold text-gray-500 uppercase block">Catatan untuk Penulis</span>
                                                <p class="text-xs text-gray-700 leading-relaxed text-left break-words">
                                                    {{ $assignment->review->comments_for_author }}
                                                </p>
                                            </div>

                                            <div class="p-3 rounded-lg bg-purple-50/70 border border-purple-200/80 space-y-1">
                                                <span class="text-[11px] font-bold text-purple-800 uppercase block">Catatan Rahasia untuk Editor</span>
                                                <p class="text-xs text-purple-950 leading-relaxed text-left break-words">
                                                    {{ $assignment->review->confidential_comments_for_editor ?: 'Tidak ada catatan rahasia tambahan.' }}
                                                </p>
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
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
