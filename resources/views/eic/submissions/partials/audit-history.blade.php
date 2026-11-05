{{-- resources/views/eic/submissions/partials/audit-history.blade.php --}}
<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-6 text-xs">
    <div 
        class="flex items-center gap-2.5 border-b border-gray-100 pb-3.5 min-w-0 cursor-help"
        data-tooltip="Jejak Audit Skrining Awal & Keputusan Editorial"
        data-tooltip-desc="Rekaman riwayat pemeriksaan awal dan surat keputusan resmi dewan redaksi"
    >
        <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="text-sm font-bold text-gray-900 leading-tight truncate">
            Jejak Audit Skrining Awal & Keputusan Editorial
        </h2>
    </div>

    <!-- 1. Riwayat Penilaian Skrining Awal -->
    <div class="space-y-3">
        @php
            $deskReviews = $submission->deskReviews->sortByDesc('created_at')->values();
        @endphp

        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Riwayat Skrining Awal</h3>
            @if($deskReviews->isNotEmpty())
                <span class="text-[11px] text-gray-400 tabular-nums">{{ $deskReviews->count() }} rekaman skrining</span>
            @endif
        </div>

        @forelse($deskReviews as $idx => $desk)
            @php
                $isExtraDesk = ($idx >= 1);
                $deskBadge = match($desk->status) {
                    'passed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                    'revision_required' => 'bg-amber-50 text-amber-800 border-amber-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
                $deskLabel = match($desk->status) {
                    'passed' => 'Lolos Skrining Awal',
                    'rejected' => 'Ditolak pada Skrining Awal',
                    'revision_required' => 'Diminta Perbaikan Format',
                    default => 'Dalam Pemeriksaan',
                };
                $isDeskNoteLong = $desk->notes && (mb_strlen($desk->notes) > 160 || str_contains($desk->notes, "\n"));
            @endphp
            <div class="p-4 rounded-lg border border-gray-200 bg-gray-50/60 space-y-2.5 {{ $isExtraDesk ? 'extra-desk-reviews hidden' : '' }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $deskBadge }}">
                            {{ $deskLabel }}
                        </span>
                        @if($desk->similarity_score !== null)
                            <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border tabular-nums {{ $desk->similarity_score <= 20 ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                                Indeks Kemiripan: {{ number_format((float) $desk->similarity_score, 1) }}%
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-500 tabular-nums">
                        Oleh: <strong class="text-gray-800">{{ $desk->assessor?->name ?? 'Pemimpin Redaksi' }}</strong>
                        <span class="text-gray-300 mx-1">/</span>
                        {{ $desk->created_at ? $desk->created_at->format('d M Y, H:i') : '-' }}
                    </span>
                </div>

                <!-- Indikator 3 Kriteria Skrining -->
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="px-2.5 py-0.5 rounded border {{ $desk->scope_fit ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                        {{ $desk->scope_fit ? 'Sesuai Fokus & Ruang Lingkup' : 'Tidak Sesuai Ruang Lingkup' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded border {{ $desk->files_complete ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                        {{ $desk->files_complete ? 'Berkas Lengkap' : 'Berkas Belum Lengkap' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded border {{ $desk->guidelines_compliant ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                        {{ $desk->guidelines_compliant ? 'Sesuai Format Selingkung' : 'Format Belum Sesuai' }}
                    </span>
                </div>

                @if($desk->notes)
                    <div class="bg-white p-3 rounded-lg border border-gray-200/80 text-gray-700 leading-relaxed text-left">
                        @if($isDeskNoteLong)
                            <p id="desk-note-preview-{{ $desk->id }}" class="line-clamp-2">"{{ Str::limit($desk->notes, 150) }}"</p>
                            <p id="desk-note-full-{{ $desk->id }}" class="hidden whitespace-pre-line">"{{ $desk->notes }}"</p>
                            <button 
                                type="button" 
                                onclick="toggleAuditSnippet('desk-note', '{{ $desk->id }}', this)" 
                                class="mt-1.5 text-[11px] font-bold text-brand-maroon hover:text-brand-maroon-hover inline-flex items-center gap-1 cursor-pointer"
                                aria-expanded="false"
                            >
                                <span class="btn-text">Lihat Detail Catatan</span>
                                <svg class="w-3 h-3 transform transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @else
                            <p>"{{ $desk->notes }}"</p>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <p class="text-gray-400">Belum ada rekaman penilaian skrining awal pada naskah ini.</p>
        @endforelse

        @if($deskReviews->count() > 1)
            <div class="pt-1 text-center">
                <button 
                    type="button" 
                    onclick="toggleExtraDeskReviews(this)" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-brand-maroon hover:bg-brand-maroon/5 rounded-lg border border-brand-maroon/20 transition cursor-pointer"
                    data-expanded="false"
                    data-count="{{ $deskReviews->count() - 1 }}"
                >
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <span class="btn-text">Lihat Riwayat Skrining Sebelumnya ({{ $deskReviews->count() - 1 }} Lainnya)</span>
                </button>
            </div>
        @endif
    </div>

    <!-- 2. Riwayat Keputusan Editorial Resmi -->
    <div class="space-y-3 pt-4 border-t border-gray-100">
        @php
            $decisions = $submission->editorialDecisions->sortByDesc('sent_at')->values();
        @endphp

        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Riwayat Keputusan Editorial Resmi</h3>
            @if($decisions->isNotEmpty())
                <span class="text-[11px] text-gray-400 tabular-nums">{{ $decisions->count() }} surat keputusan</span>
            @endif
        </div>

        @forelse($decisions as $idx => $decision)
            @php
                $isExtraDec = ($idx >= 1);
                $decBadge = match($decision->decision) {
                    'accept' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    'revisions_required' => 'bg-amber-50 text-amber-800 border-amber-200',
                    'resubmit' => 'bg-orange-50 text-orange-800 border-orange-200',
                    'decline' => 'bg-rose-50 text-rose-800 border-rose-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };
                $decTitle = match($decision->decision) {
                    'accept' => 'Diterima untuk Publikasi',
                    'revisions_required' => 'Perlu Revisi Penulis',
                    'resubmit' => 'Kirim Ulang untuk Telaah',
                    'decline' => 'Naskah Ditolak',
                    default => 'Keputusan Redaksi',
                };
                $isAuthorNoteLong = $decision->note_for_author && (mb_strlen($decision->note_for_author) > 180 || str_contains($decision->note_for_author, "\n"));
                $isInternalNoteLong = $decision->internal_note && (mb_strlen($decision->internal_note) > 160 || str_contains($decision->internal_note, "\n"));
            @endphp
            <div class="p-4 rounded-lg border border-gray-200 bg-gray-50/70 space-y-2.5 {{ $isExtraDec ? 'extra-editorial-decisions hidden' : '' }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $decBadge }}">
                            Keputusan: {{ $decTitle }}
                        </span>
                    </div>
                    <span class="text-gray-500 text-xs tabular-nums">
                        Diterbitkan oleh <strong class="text-gray-800">{{ $decision->decider?->name ?? 'Pemimpin Redaksi' }}</strong>
                        <span class="text-gray-300 mx-1">/</span>
                        {{ $decision->sent_at ? $decision->sent_at->format('d M Y, H:i') : '-' }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-white border border-gray-200/80 space-y-1">
                    <span class="text-[11px] font-bold text-gray-500 uppercase block">Surat Keputusan untuk Penulis</span>
                    @if($isAuthorNoteLong)
                        <p id="dec-author-preview-{{ $decision->id }}" class="text-gray-700 leading-relaxed text-left line-clamp-2">"{{ Str::limit($decision->note_for_author, 160) }}"</p>
                        <p id="dec-author-full-{{ $decision->id }}" class="text-gray-700 leading-relaxed text-left whitespace-pre-line break-words hidden">"{{ $decision->note_for_author }}"</p>
                        <button 
                            type="button" 
                            onclick="toggleAuditSnippet('dec-author', '{{ $decision->id }}', this)" 
                            class="mt-1 text-[11px] font-bold text-brand-maroon hover:text-brand-maroon-hover inline-flex items-center gap-1 cursor-pointer"
                            aria-expanded="false"
                        >
                            <span class="btn-text">Lihat Detail Surat Keputusan</span>
                            <svg class="w-3 h-3 transform transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    @else
                        <p class="text-gray-700 leading-relaxed text-left whitespace-pre-line break-words">"{{ $decision->note_for_author }}"</p>
                    @endif
                </div>

                @if($decision->internal_note)
                    <div class="p-3 rounded-lg bg-purple-50/60 border border-purple-200/80 space-y-1">
                        <span class="text-[11px] font-bold text-purple-800 uppercase block">Catatan Internal Dewan Redaksi</span>
                        @if($isInternalNoteLong)
                            <p id="dec-internal-preview-{{ $decision->id }}" class="text-xs text-purple-950 text-left line-clamp-2">{{ Str::limit($decision->internal_note, 150) }}</p>
                            <p id="dec-internal-full-{{ $decision->id }}" class="text-xs text-purple-950 text-left whitespace-pre-line break-words hidden">{{ $decision->internal_note }}</p>
                            <button 
                                type="button" 
                                onclick="toggleAuditSnippet('dec-internal', '{{ $decision->id }}', this)" 
                                class="mt-1 text-[11px] font-bold text-purple-900 hover:text-purple-950 inline-flex items-center gap-1 cursor-pointer"
                                aria-expanded="false"
                            >
                                <span class="btn-text">Lihat Detail Catatan Internal</span>
                                <svg class="w-3 h-3 transform transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @else
                            <p class="text-xs text-purple-950 text-left break-words">{{ $decision->internal_note }}</p>
                        @endif
                    </div>
                @endif

                @if($decision->revision_due_at)
                    <span class="inline-flex items-center gap-1.5 text-xs text-rose-700 font-semibold tabular-nums">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Batas Waktu Pengumpulan Revisi: {{ $decision->revision_due_at->format('d F Y') }}</span>
                    </span>
                @endif
            </div>
        @empty
            <p class="text-gray-400">Belum ada keputusan editorial resmi yang diterbitkan untuk naskah ini.</p>
        @endforelse

        @if($decisions->count() > 1)
            <div class="pt-1 text-center">
                <button 
                    type="button" 
                    onclick="toggleExtraDecisions(this)" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-brand-maroon hover:bg-brand-maroon/5 rounded-lg border border-brand-maroon/20 transition cursor-pointer"
                    data-expanded="false"
                    data-count="{{ $decisions->count() - 1 }}"
                >
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <span class="btn-text">Lihat Keputusan Editorial Sebelumnya ({{ $decisions->count() - 1 }} Lainnya)</span>
                </button>
            </div>
        @endif
    </div>

    <!-- 3. Status Tahap Produksi & Galai jika naskah sudah diterima -->
    @if($submission->copyedit || $submission->activeLayoutAssignment || $submission->galleys->isNotEmpty())
        <div class="space-y-3 pt-4 border-t border-gray-100">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pemantauan Tahap Produksi Pasca-Keputusan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50">
                    <span class="text-[11px] font-bold text-gray-500 uppercase block">Penyuntingan Naskah</span>
                    <span class="font-bold text-gray-900 block mt-0.5">
                        {{ $submission->copyedit ? ucfirst(str_replace('_', ' ', $submission->copyedit->status)) : 'Belum Ditugaskan' }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ $submission->copyedit?->copyEditor?->name ?? '-' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50">
                    <span class="text-[11px] font-bold text-gray-500 uppercase block">Tata Letak Galai</span>
                    <span class="font-bold text-gray-900 block mt-0.5">
                        {{ $submission->activeLayoutAssignment ? 'Aktif Ditugaskan' : 'Menunggu Penyuntingan' }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ $submission->activeLayoutAssignment?->layoutEditor?->name ?? '-' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50">
                    <span class="text-[11px] font-bold text-gray-500 uppercase block">Berkas Galai Terbitan</span>
                    <span class="font-bold text-gray-900 block mt-0.5 tabular-nums">
                        {{ $submission->galleys->count() }} Galai Terdaftar
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ $submission->galleys->where('status', 'active')->count() > 0 ? 'Siap Masuk Edisi Jurnal' : 'Proses Koreksi Cetak' }}
                    </span>
                </div>
            </div>
        </div>
    @endif
</section>

@pushOnce('scripts')
<script>
    function toggleAuditSnippet(prefix, id, btn) {
        const previewEl = document.getElementById(prefix + '-preview-' + id);
        const fullEl = document.getElementById(prefix + '-full-' + id);
        const textSpan = btn.querySelector('.btn-text');
        const icon = btn.querySelector('.chevron-icon');

        if (!previewEl || !fullEl) return;

        const isFullHidden = fullEl.classList.contains('hidden');
        if (isFullHidden) {
            fullEl.classList.remove('hidden');
            previewEl.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'true');
            if (textSpan) textSpan.textContent = textSpan.textContent.replace('Lihat Detail', 'Tutup');
            if (icon) icon.classList.add('rotate-180');
        } else {
            fullEl.classList.add('hidden');
            previewEl.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'false');
            if (textSpan) textSpan.textContent = textSpan.textContent.replace('Tutup', 'Lihat Detail');
            if (icon) icon.classList.remove('rotate-180');
        }
    }

    function toggleExtraDeskReviews(btn) {
        const isExpanded = btn.getAttribute('data-expanded') === 'true';
        const count = btn.getAttribute('data-count');
        const extraCards = document.querySelectorAll('.extra-desk-reviews');
        const textSpan = btn.querySelector('.btn-text');
        const icon = btn.querySelector('.chevron-icon');

        if (isExpanded) {
            extraCards.forEach(el => el.classList.add('hidden'));
            btn.setAttribute('data-expanded', 'false');
            if (textSpan) textSpan.textContent = `Lihat Riwayat Skrining Sebelumnya (${count} Lainnya)`;
            if (icon) icon.classList.remove('rotate-180');
        } else {
            extraCards.forEach(el => el.classList.remove('hidden'));
            btn.setAttribute('data-expanded', 'true');
            if (textSpan) textSpan.textContent = 'Tutup Riwayat Skrining Sebelumnya';
            if (icon) icon.classList.add('rotate-180');
        }
    }

    function toggleExtraDecisions(btn) {
        const isExpanded = btn.getAttribute('data-expanded') === 'true';
        const count = btn.getAttribute('data-count');
        const extraCards = document.querySelectorAll('.extra-editorial-decisions');
        const textSpan = btn.querySelector('.btn-text');
        const icon = btn.querySelector('.chevron-icon');

        if (isExpanded) {
            extraCards.forEach(el => el.classList.add('hidden'));
            btn.setAttribute('data-expanded', 'false');
            if (textSpan) textSpan.textContent = `Lihat Keputusan Editorial Sebelumnya (${count} Lainnya)`;
            if (icon) icon.classList.remove('rotate-180');
        } else {
            extraCards.forEach(el => el.classList.remove('hidden'));
            btn.setAttribute('data-expanded', 'true');
            if (textSpan) textSpan.textContent = 'Tutup Keputusan Editorial Sebelumnya';
            if (icon) icon.classList.add('rotate-180');
        }
    }
</script>
@endpushOnce
