{{-- resources/views/section-editor/submissions/partials/review-rounds-manager.blade.php --}}
@php
    $activeRound = $viewModel->activeRound;
    $completedCount = $viewModel->completedReviewsCount();
    $minReq = $viewModel->minimumCompletedReviews();
    $isFinalized = $viewModel->isWorkflowFinalized();
@endphp

<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3.5">
        <div 
            class="flex items-center gap-2.5 min-w-0 cursor-help"
            data-tooltip="Manajemen Putaran Telaah Sejawat"
            data-tooltip-desc="Pengelolaan mitra bestari, pemantauan kuorum telaah, dan evaluasi hasil review"
        >
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
                Putaran Telaah Sejawat (Round #{{ $activeRound->round }})
            </h2>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <span class="px-2.5 py-1 rounded-md text-xs font-semibold border tabular-nums {{ $completedCount >= $minReq ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                Kuorum Telaah: {{ $completedCount }}/{{ $minReq }} Selesai
            </span>

            @if(!$isFinalized && $submission->revisions->isNotEmpty() && $completedCount >= $minReq)
                <form action="{{ route('section-editor.rounds.store', $submission->id) }}" 
                      method="POST" 
                      class="inline m-0"
                      data-confirm="Apakah Anda yakin ingin membuka putaran telaah baru untuk naskah ini? Putaran evaluasi berikutnya akan diaktifkan."
                      data-confirm-title="Buka Putaran Telaah Baru?"
                      data-confirm-btn="Buka Putaran Baru"
                      data-confirm-cancel="Batal"
                      data-confirm-type="primary">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-lg transition cursor-pointer"
                        title="Buka putaran telaah baru pasca-revisi"
                    >
                        Buka Putaran Baru
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Banner Rekomendasi Editorial Section Editor jika sudah diberikan -->
    @if($viewModel->hasSeRecommendation())
        @php
            $seBadge = match($activeRound->se_recommendation) {
                'accept' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                'revisions_required' => 'bg-amber-100 text-amber-900 border-amber-300',
                'resubmit' => 'bg-orange-100 text-orange-900 border-orange-300',
                'decline' => 'bg-rose-100 text-rose-900 border-rose-300',
                default => 'bg-blue-100 text-blue-900 border-blue-300',
            };
            $seLabel = match($activeRound->se_recommendation) {
                'accept' => 'Terima Naskah',
                'revisions_required' => 'Perlu Revisi Penulis',
                'resubmit' => 'Kirim Ulang untuk Telaah',
                'decline' => 'Tolak Naskah',
                default => 'Rekomendasi Diberikan',
            };
        @endphp
        <div class="p-4 rounded-xl bg-indigo-50/90 border border-indigo-200 space-y-2 text-xs">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="font-bold text-indigo-950 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Rekomendasi Resmi Anda untuk Pemimpin Redaksi:</span>
                </span>
                <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $seBadge }}">
                    {{ $seLabel }}
                </span>
            </div>
            @if($activeRound->se_recommendation_note)
                @php
                    $isNoteLong = mb_strlen($activeRound->se_recommendation_note) > 160 || str_contains($activeRound->se_recommendation_note, "\n");
                @endphp
                <div class="text-indigo-900 leading-relaxed text-left bg-white/90 p-3 rounded-lg border border-indigo-100">
                    @if($isNoteLong)
                        <p id="se-mgr-note-preview-{{ $activeRound->id }}" class="italic line-clamp-2">
                            "{{ Str::limit($activeRound->se_recommendation_note, 150) }}"
                        </p>
                        <p id="se-mgr-note-full-{{ $activeRound->id }}" class="italic hidden whitespace-pre-line">
                            "{{ $activeRound->se_recommendation_note }}"
                        </p>
                        <button 
                            type="button" 
                            onclick="toggleSeMgrNote('{{ $activeRound->id }}', this)" 
                            class="mt-1.5 text-[11px] font-bold text-indigo-700 hover:text-indigo-900 inline-flex items-center gap-1 cursor-pointer"
                            aria-expanded="false"
                        >
                            <span class="note-btn-text">Lihat Detail Catatan</span>
                            <svg class="w-3 h-3 transform transition-transform duration-200 note-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    @else
                        <p class="italic">"{{ $activeRound->se_recommendation_note }}"</p>
                    @endif
                </div>
            @endif
            @if($activeRound->se_recommended_at)
                <span class="block text-[11px] text-indigo-700 tabular-nums">
                    Diserahkan pada: {{ $activeRound->se_recommended_at->format('d M Y, H:i') }}
                </span>
            @endif
        </div>
    @endif

    <!-- Daftar Mitra Bestari Ditugaskan -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Daftar Mitra Bestari Ditugaskan</h3>
            <span class="text-xs text-gray-500 tabular-nums">{{ $activeRound->assignments->count() }} Penelaah Terdaftar</span>
        </div>

        @if($activeRound->assignments->isEmpty())
            <div class="p-8 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-center space-y-2">
                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold text-gray-700">Belum Ada Mitra Bestari Ditugaskan</p>
                <p class="text-xs text-gray-500 max-w-sm mx-auto">
                    Gunakan formulir "Undang Mitra Bestari" pada panel samping kanan untuk mengundang penelaah independen.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($activeRound->assignments as $idx => $assignment)
                    @php
                        $isExtraRevCard = ($idx >= 2);
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
                        $pendingExt = $assignment->extensions->where('status', 'pending')->first();
                        $rev = $assignment->review;
                        $revRecLabel = $rev ? match($rev->recommendation) {
                            'accept' => 'Terima Naskah',
                            'revisions_required' => 'Perlu Revisi',
                            'resubmit' => 'Telaah Ulang',
                            'decline' => 'Tolak Naskah',
                            default => 'Selesai',
                        } : null;
                    @endphp
                    <div class="p-4 sm:p-5 rounded-xl border border-gray-200 bg-white shadow-2xs space-y-3.5 {{ $isExtraRevCard ? 'extra-reviewer-card hidden' : '' }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-maroon/10 text-brand-maroon font-bold flex items-center justify-center text-xs shrink-0 tabular-nums">
                                    {{ strtoupper(substr($assignment->reviewer?->name ?? 'MB', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-gray-900 text-xs block truncate">
                                        {{ $assignment->reviewer?->name ?? 'Mitra Bestari #' . ($idx + 1) }}
                                    </span>
                                    <span class="text-[11px] text-gray-500 block truncate">
                                        {{ $assignment->reviewer?->email ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $assignment->status_badge_class }}">
                                    {{ $assignStatusLabel }}
                                </span>
                                @if($rev && $revRecLabel)
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $rev->recommendation_badge_class }}">
                                        {{ $revRecLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Jadwal Batas Waktu -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-gray-50/80 p-3 rounded-lg text-xs text-gray-600">
                            <div>
                                <span class="text-gray-400 block text-[10px] font-semibold uppercase">Batas Respon Undangan:</span>
                                <span class="font-medium text-gray-800 tabular-nums">{{ $assignment->response_due_at ? $assignment->response_due_at->format('d M Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] font-semibold uppercase">Batas Selesai Telaah:</span>
                                <span class="font-medium text-gray-800 tabular-nums">{{ $assignment->review_due_at ? $assignment->review_due_at->format('d M Y') : '-' }}</span>
                            </div>
                        </div>

                        @php
                            $reminders = $assignment->reminders;
                            $remindersCount = $reminders ? $reminders->count() : 0;
                            $latestReminder = $remindersCount > 0 ? $reminders->first() : null;
                        @endphp

                        <!-- Riwayat Pengingat Terkirim -->
                        @if($remindersCount > 0 && $latestReminder)
                            <div class="flex items-center justify-between flex-wrap gap-2 px-3 py-2 rounded-lg bg-amber-50/70 border border-amber-200/70 text-[11px] text-amber-900">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>
                                        Pengingat terakhir dikirim: <strong>{{ $latestReminder->queued_at ? $latestReminder->queued_at->format('d M Y, H:i') : $latestReminder->created_at->format('d M Y, H:i') }}</strong>
                                        @if($latestReminder->sender)
                                            <span class="text-amber-800 font-normal">oleh {{ $latestReminder->sender->name }}</span>
                                        @endif
                                    </span>
                                </div>
                                <span class="px-1.5 py-0.5 rounded bg-amber-200/60 font-semibold text-[10px] tabular-nums">
                                    {{ $remindersCount }}&times; Terkirim
                                </span>
                            </div>
                        @endif

                        <!-- Permohonan Perpanjangan Waktu (Pending Extension) -->
                        @if($pendingExt && !$isFinalized)
                            <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-amber-900 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Permohonan Perpanjangan Waktu Telaah
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-200 text-amber-900">Pending</span>
                                </div>
                                <p class="text-amber-900 leading-relaxed text-left">
                                    Dimohonkan hingga: <strong class="tabular-nums">{{ $pendingExt->requested_due_at->format('d M Y') }}</strong><br>
                                    Alasan: <span class="italic">"{{ $pendingExt->reason }}"</span>
                                </p>
                                <div class="flex items-center gap-2 pt-1">
                                    <form action="{{ route('section-editor.extensions.decide', $pendingExt->id) }}" 
                                          method="POST" 
                                          class="inline m-0"
                                          data-confirm="Apakah Anda yakin ingin menyetujui permohonan perpanjangan waktu telaah hingga {{ $pendingExt->requested_due_at->format('d M Y') }}?"
                                          data-confirm-title="Setujui Perpanjangan Waktu?"
                                          data-confirm-btn="Setujui Perpanjangan"
                                          data-confirm-cancel="Batal"
                                          data-confirm-type="primary">
                                        @csrf
                                        <input type="hidden" name="decision" value="approved">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition cursor-pointer text-xs">
                                            Setujui Perpanjangan
                                        </button>
                                    </form>
                                    <form action="{{ route('section-editor.extensions.decide', $pendingExt->id) }}" 
                                          method="POST" 
                                          class="inline m-0"
                                          data-confirm="Apakah Anda yakin ingin menolak permohonan perpanjangan waktu telaah dari mitra bestari ini?"
                                          data-confirm-title="Tolak Perpanjangan Waktu?"
                                          data-confirm-btn="Tolak Perpanjangan"
                                          data-confirm-cancel="Batal"
                                          data-confirm-type="danger">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg transition cursor-pointer text-xs">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        <!-- Hasil Evaluasi Mitra Bestari -->
                        @if($rev)
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 overflow-hidden text-xs">
                                <button 
                                    type="button" 
                                    class="w-full p-3 flex items-center justify-between text-left hover:bg-emerald-100/60 transition cursor-pointer"
                                    onclick="toggleReviewDetail('rev-detail-{{ $assignment->id }}', this)"
                                    aria-expanded="false"
                                >
                                    <div class="flex items-center gap-2 min-w-0">
                                        <svg class="w-4 h-4 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="font-bold text-emerald-950 truncate">Laporan Evaluasi Substantif</span>
                                        <span class="text-emerald-800 text-[11px] tabular-nums shrink-0">
                                            ({{ $rev->submitted_at ? $rev->submitted_at->format('d M Y, H:i') : '-' }})
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-emerald-800 font-semibold text-[11px] shrink-0">
                                        <span class="detail-label">Lihat Detail</span>
                                        <svg class="w-4 h-4 transform transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <div id="rev-detail-{{ $assignment->id }}" class="hidden p-3.5 pt-0 space-y-2.5 border-t border-emerald-100">
                                    @if(!empty($rev->scores) && is_array($rev->scores))
                                        <div class="flex flex-wrap gap-2 pt-2.5">
                                            @foreach($rev->scores as $criterion => $scoreVal)
                                                <span class="px-2 py-0.5 rounded bg-white text-gray-800 text-[11px] font-medium border border-emerald-200 tabular-nums">
                                                    {{ ucfirst(str_replace('_', ' ', (string) $criterion)) }}: <strong>{{ $scoreVal }}/5</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-1">
                                        <div class="p-3 rounded-lg bg-white border border-emerald-100 space-y-1">
                                            <span class="text-[11px] font-bold text-gray-500 uppercase block">Catatan untuk Penulis</span>
                                            <p class="text-xs text-gray-700 leading-relaxed text-left break-words">
                                                {{ $rev->comments_for_author }}
                                            </p>
                                        </div>

                                        <div class="p-3 rounded-lg bg-purple-50/70 border border-purple-200 space-y-1">
                                            <span class="text-[11px] font-bold text-purple-800 uppercase block">Catatan Rahasia untuk Editor</span>
                                            <p class="text-xs text-purple-950 leading-relaxed text-left break-words">
                                                {{ $rev->confidential_comments_for_editor ?: 'Tidak ada catatan rahasia tambahan.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Tombol Aksi per Reviewer -->
                        @if(!$isFinalized)
                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 text-xs">
                                <!-- Kirim Pengingat -->
                                @if(in_array($assignment->effective_status, ['invited', 'accepted', 'overdue']))
                                    <form action="{{ route('section-editor.assignments.reminder', $assignment->id) }}" 
                                          method="POST" 
                                          class="inline m-0"
                                          data-confirm="Kirimkan notifikasi surel pengingat batas waktu telaah kepada mitra bestari ini?"
                                          data-confirm-title="Kirim Pengingat Mitra Bestari?"
                                          data-confirm-btn="Kirim Pengingat"
                                          data-confirm-cancel="Batal"
                                          data-confirm-type="primary">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg font-medium transition cursor-pointer flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <span>{{ $remindersCount > 0 ? 'Kirim Pengingat Ulang' : 'Kirim Pengingat' }}</span>
                                        </button>
                                    </form>
                                @endif

                                <!-- Batalkan Undangan (Hanya status invited) -->
                                @if($assignment->status === 'invited')
                                    <form action="{{ route('section-editor.assignments.cancel', $assignment->id) }}" 
                                          method="POST" 
                                          class="inline m-0"
                                          data-confirm="Apakah Anda yakin ingin membatalkan undangan mitra bestari ini?"
                                          data-confirm-title="Batalkan Undangan Mitra Bestari?"
                                          data-confirm-btn="Batalkan Undangan"
                                          data-confirm-cancel="Batal"
                                          data-confirm-type="danger">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg font-medium transition cursor-pointer">
                                            Batalkan Undangan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach

                @if($activeRound->assignments->count() > 2)
                    <div class="pt-2 text-center">
                        <button 
                            type="button" 
                            onclick="toggleExtraReviewers(this)" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-brand-maroon hover:bg-brand-maroon/5 rounded-lg border border-brand-maroon/20 transition cursor-pointer"
                            data-expanded="false"
                            data-count="{{ $activeRound->assignments->count() - 2 }}"
                        >
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span class="btn-text">Lihat Semua Mitra Bestari ({{ $activeRound->assignments->count() - 2 }} Lainnya)</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>

@pushOnce('scripts')
<script>
    function toggleReviewDetail(detailId, btn) {
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

    function toggleExtraReviewers(btn) {
        const isExpanded = btn.getAttribute('data-expanded') === 'true';
        const count = btn.getAttribute('data-count');
        const extraCards = document.querySelectorAll('.extra-reviewer-card');
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

    function toggleSeMgrNote(roundId, btn) {
        const previewEl = document.getElementById('se-mgr-note-preview-' + roundId);
        const fullEl = document.getElementById('se-mgr-note-full-' + roundId);
        const label = btn.querySelector('.note-btn-text');
        const icon = btn.querySelector('.note-chevron');

        if (!previewEl || !fullEl) return;

        const isFullHidden = fullEl.classList.contains('hidden');
        if (isFullHidden) {
            fullEl.classList.remove('hidden');
            previewEl.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'true');
            if (label) label.textContent = 'Tutup Catatan';
            if (icon) icon.classList.add('rotate-180');
        } else {
            fullEl.classList.add('hidden');
            previewEl.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'false');
            if (label) label.textContent = 'Lihat Detail Catatan';
            if (icon) icon.classList.remove('rotate-180');
        }
    }
</script>
@endpushOnce
