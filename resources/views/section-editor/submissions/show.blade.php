@extends('layouts.section-editor')

@section('title', 'Ruang Telaah Naskah: ' . $viewModel->submissionNumber() . ' — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('section-editor.submissions.index') }}" class="hover:text-gray-900 flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Antrean Telaah Naskah</span>
                </a>
                <span>/</span>
                <span class="font-mono text-gray-700 font-bold">{{ $viewModel->submissionNumber() }}</span>
                <span>/</span>
                <span class="font-medium text-emerald-700 font-semibold">Putaran Telaah {{ $viewModel->activeRound->round }}</span>
            </div>
            <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900 leading-snug">
                {{ $viewModel->title() }}
            </h1>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $viewModel->submission->status_badge_class }}">
                {{ $viewModel->submission->status_label }}
            </span>
        </div>
    </div>

    <!-- Main Workspace Grid: 2 Kolom (Kiri: Metadata & Berkas, Kanan: Manajemen Reviewer & Rekomendasi) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- KOLOM KIRI (5 Grid): Metadata Naskah, Penulis & Berkas -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Kartu Metadata Naskah -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-100 pb-3">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Informasi Manuskrip</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase">Bidang Kajian (Section):</span>
                        <span class="font-medium text-gray-800 text-sm">{{ $viewModel->sectionName() }}</span>
                    </div>

                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase">Penulis Korespondensi:</span>
                        <span class="font-medium text-gray-900 text-sm">{{ $viewModel->authorName() }}</span>
                        <span class="text-gray-500 block text-[11px]">{{ $viewModel->authorEmail() }}</span>
                    </div>

                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase mb-1">Abstrak Ilmiah:</span>
                        <div class="text-gray-700 leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-100 text-[11px] max-h-48 overflow-y-auto">
                            {{ $viewModel->abstract() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Berkas Manuskrip & Revisi -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-100 pb-3">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Berkas Naskah & Revisi</span>
                </h3>

                <div class="space-y-2 text-xs">
                    @forelse($viewModel->submission->files as $file)
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="font-medium text-gray-800 truncate">{{ $file->file?->original_name ?? 'Naskah_Manuskrip.pdf' }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 shrink-0 uppercase bg-gray-100 px-1.5 py-0.5 rounded font-mono">
                                {{ $file->file_type ?? 'manuscript' }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 italic text-[11px]">Belum ada berkas terlampir.</p>
                    @endforelse
                </div>

                <!-- Riwayat Putaran Revisi Penulis -->
                @if($viewModel->submission->revisions->isNotEmpty())
                    <div class="pt-3 border-t border-gray-100 space-y-2">
                        <span class="text-[11px] font-bold text-gray-700 block uppercase tracking-wider">Berkas Revisi Penulis:</span>
                        @foreach($viewModel->submission->revisions as $rev)
                            <div class="bg-amber-50/60 p-2.5 rounded-lg border border-amber-200/60 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-amber-900">Revisi Putaran {{ $rev->round }}</span>
                                    <span class="text-[10px] text-amber-700">{{ $rev->submitted_at ? $rev->submitted_at->format('d M Y') : '-' }}</span>
                                </div>
                                <p class="text-[11px] text-amber-800 italic">"{{ $rev->note ?? 'Perbaikan sesuai catatan reviewer.' }}"</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- KOLOM KANAN (7 Grid): Manajemen Putaran Telaah & Penugasan Reviewer (UC-11) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- KOTAK 1: Putaran Review Aktif & Penugasan Mitra Bestari (UC-11) -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-serif font-bold text-base text-gray-900">
                                Putaran Telaah Sejawat (Round {{ $viewModel->activeRound->round }})
                            </h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $viewModel->activeRound->status_badge_class }}">
                                {{ $viewModel->activeRound->status_label }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Target: <span class="font-bold text-gray-700">{{ $viewModel->minimumCompletedReviews() }} review selesai</span> sebelum rekomendasi dapat diajukan (Progres: {{ $viewModel->completedReviewsCount() }}/{{ $viewModel->minimumCompletedReviews() }}).
                        </p>
                    </div>

                    <!-- Tombol Trigger Modal Undang Reviewer -->
                    <button type="button" onclick="document.getElementById('modalAssignReviewer').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition-all cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Undang Mitra Bestari</span>
                    </button>
                </div>

                <!-- Daftar Reviewer yang Ditugaskan -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Daftar Mitra Bestari Ditugaskan:</h4>

                    @if($viewModel->activeRound->assignments->isEmpty())
                        <div class="p-6 rounded-lg bg-gray-50 border border-dashed border-gray-200 text-center space-y-2">
                            <svg class="w-8 h-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-xs text-gray-600 font-medium">Belum ada mitra bestari yang diundang pada putaran ini.</p>
                            <p class="text-[11px] text-gray-400">Klik tombol "Undang Mitra Bestari" di atas untuk mengirimkan penugasan telaah.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($viewModel->activeRound->assignments as $assignment)
                                <div class="p-4 rounded-xl border border-gray-200 hover:border-gray-300 transition-all bg-white shadow-2xs space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($assignment->reviewer?->name ?? 'R', 0, 2)) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 text-xs block">{{ $assignment->reviewer?->name }}</span>
                                                <span class="text-[11px] text-gray-500">{{ $assignment->reviewer?->email }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $assignment->status_badge_class }}">
                                                {{ $assignment->status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Jadwal & Deadline (Mesin Waktu Review) -->
                                    <div class="grid grid-cols-2 gap-2 bg-gray-50 p-2.5 rounded-lg text-[11px] text-gray-600">
                                        <div>
                                            <span class="text-gray-400 block text-[10px] font-semibold uppercase">Batas Respon Undangan:</span>
                                            <span class="font-medium text-gray-800">{{ $assignment->response_due_at ? $assignment->response_due_at->format('d M Y') : '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px] font-semibold uppercase">Batas Selesai Telaah:</span>
                                            <span class="font-medium text-gray-800">{{ $assignment->review_due_at ? $assignment->review_due_at->format('d M Y') : '-' }}</span>
                                        </div>
                                    </div>

                                    <!-- Permohonan Perpanjangan Waktu (Extension Request - UC-08) -->
                                    @if($assignment->pendingExtension)
                                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 space-y-2">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-bold text-amber-900 flex items-center gap-1.5">
                                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Permohonan Perpanjangan Waktu (Extension)
                                                </span>
                                                <span class="text-[10px] font-semibold bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded">Pending</span>
                                            </div>
                                            <p class="text-[11px] text-amber-800">
                                                Dimohonkan hingga: <strong class="text-amber-950">{{ $assignment->pendingExtension->requested_due_at->format('d M Y') }}</strong><br>
                                                Alasan: <span class="italic">"{{ $assignment->pendingExtension->reason }}"</span>
                                            </p>
                                            <div class="flex items-center gap-2 pt-1">
                                                <form action="{{ route('section-editor.extensions.decide', $assignment->pendingExtension->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="approved">
                                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded cursor-pointer">
                                                        Setujui Perpanjangan
                                                    </button>
                                                </form>
                                                <form action="{{ route('section-editor.extensions.decide', $assignment->pendingExtension->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-[11px] font-bold rounded cursor-pointer">
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Hasil Evaluasi Reviewer jika sudah selesai -->
                                    @if($assignment->review)
                                        <div class="bg-emerald-50/70 border border-emerald-200 rounded-lg p-3 space-y-2">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-bold text-emerald-900">Hasil Evaluasi Mitra Bestari:</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $assignment->review->recommendation_badge_class }}">
                                                    {{ $assignment->review->recommendation_label }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold text-emerald-800 uppercase block">Masukan Untuk Penulis:</span>
                                                <p class="text-[11px] text-gray-700 bg-white p-2 rounded border border-emerald-100 mt-0.5 max-h-24 overflow-y-auto">
                                                    {{ $assignment->review->comments_for_author }}
                                                </p>
                                            </div>
                                            @if($assignment->review->confidential_comments_for_editor)
                                                <div>
                                                    <span class="text-[10px] font-bold text-purple-800 uppercase block">Catatan Rahasia Dewan Editor:</span>
                                                    <p class="text-[11px] text-gray-700 bg-purple-50 p-2 rounded border border-purple-100 mt-0.5">
                                                        {{ $assignment->review->confidential_comments_for_editor }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Tombol Aksi per Reviewer -->
                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 text-xs">
                                        <!-- Kirim Pengingat -->
                                        @if(in_array($assignment->effective_status, ['invited', 'accepted', 'overdue']))
                                            <form action="{{ route('section-editor.assignments.reminder', $assignment->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded font-medium cursor-pointer flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                    </svg>
                                                    <span>Kirim Reminder</span>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Batalkan Undangan (Hanya status invited) -->
                                        @if($assignment->status === 'invited')
                                            <form action="{{ route('section-editor.assignments.cancel', $assignment->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan undangan reviewer ini?');">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded font-medium cursor-pointer">
                                                    Batalkan Undangan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- KOTAK 2: Form Rekomendasi Editorial Section Editor ke EIC -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="font-serif font-bold text-base text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Rekomendasi Editorial untuk Pemimpin Redaksi (EIC)</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Setelah menelaah masukan mitra bestari, berikan rekomendasi resmi Section Editor kepada Pemimpin Redaksi untuk keputusan final.
                    </p>
                </div>

                @if($viewModel->hasSeRecommendation())
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-blue-900">Rekomendasi Terkirim ke EIC:</span>
                            <span class="font-bold px-2.5 py-0.5 rounded bg-blue-200 text-blue-900 uppercase">
                                {{ $viewModel->activeRound->se_recommendation }}
                            </span>
                        </div>
                        <p class="text-xs text-blue-800">
                            <strong>Catatan:</strong> {{ $viewModel->activeRound->se_recommendation_note }}
                        </p>
                        <span class="text-[10px] text-blue-600 block">
                            Diberikan pada: {{ $viewModel->activeRound->se_recommended_at?->format('d M Y H:i') }} UTC
                        </span>
                    </div>
                @endif

                <form action="{{ route('section-editor.rounds.recommendation', $viewModel->activeRound->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Rekomendasi Keputusan <span class="text-rose-500">*</span></label>
                        <select name="recommendation" required class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                            <option value="">-- Pilih Rekomendasi --</option>
                            <option value="accept" {{ old('recommendation', $viewModel->activeRound->se_recommendation) === 'accept' ? 'selected' : '' }}>
                                Diterima (Accept Submission - Lanjut Copyediting)
                            </option>
                            <option value="revisions_required" {{ old('recommendation', $viewModel->activeRound->se_recommendation) === 'revisions_required' ? 'selected' : '' }}>
                                Perlu Revisi (Revisions Required - Minta Perbaikan Penulis)
                            </option>
                            <option value="resubmit" {{ old('recommendation', $viewModel->activeRound->se_recommendation) === 'resubmit' ? 'selected' : '' }}>
                                Kirim Ulang untuk Telaah Lanjutan (Resubmit for Review)
                            </option>
                            <option value="decline" {{ old('recommendation', $viewModel->activeRound->se_recommendation) === 'decline' ? 'selected' : '' }}>
                                Ditolak (Decline Submission)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Catatan Evaluasi Substantif untuk EIC <span class="text-rose-500">*</span></label>
                        <textarea name="note" rows="4" required placeholder="Tuliskan ringkasan pertimbangan akademis, konsistensi hasil review, dan alasan rekomendasi ini..." class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">{{ old('note', $viewModel->activeRound->se_recommendation_note) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-4 py-2 bg-brand-maroon hover:bg-brand-maroon/90 text-white font-bold rounded-lg shadow-2xs transition-all cursor-pointer">
                            Kirim Rekomendasi ke EIC
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- MODAL: Undang Mitra Bestari (Reviewer Assignment Modal) -->
<div id="modalAssignReviewer" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="font-serif font-bold text-gray-900 text-base">Undang Mitra Bestari (Reviewer)</h3>
            <button type="button" onclick="document.getElementById('modalAssignReviewer').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('section-editor.reviewers.assign', $viewModel->activeRound->id) }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-gray-700 mb-1">Pilih Mitra Bestari <span class="text-rose-500">*</span></label>
                <select name="reviewer_id" required class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                    <option value="">-- Pilih dari Direktori Reviewer --</option>
                    @foreach($viewModel->availableReviewers as $reviewer)
                        <option value="{{ $reviewer->id }}">
                            {{ $reviewer->name }} ({{ $reviewer->email }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-500 mt-1">Penulis naskah otomatis dikecualikan untuk mencegah konflik kepentingan.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Batas Respon Undangan <span class="text-rose-500">*</span></label>
                    <input type="date" name="response_due_at" required value="{{ now()->addDays(7)->format('Y-m-d') }}" min="{{ now()->addDay()->format('Y-m-d') }}" class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Batas Selesai Telaah <span class="text-rose-500">*</span></label>
                    <input type="date" name="review_due_at" required value="{{ now()->addDays(21)->format('Y-m-d') }}" min="{{ now()->addDays(2)->format('Y-m-d') }}" class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-1">Pesan Khusus Undangan (Opsional)</label>
                <textarea name="invitation_message" rows="3" placeholder="Pesan pengantar dari Section Editor mengenai relevansi kepakaran atau penekanan telaah..." class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modalAssignReviewer').classList.add('hidden')" class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-semibold cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-2xs transition-all cursor-pointer">
                    Kirim Undangan Reviewer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
