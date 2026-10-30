@extends('layouts.reviewer')

@section('title', 'Ruang Telaah Ilmiah: ' . $viewModel->submissionNumber() . ' — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('reviewer.assignments.index') }}" class="hover:text-gray-900 flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Daftar Penugasan</span>
                </a>
                <span>/</span>
                <span class="font-mono text-gray-700 font-bold">{{ $viewModel->submissionNumber() }}</span>
                <span>/</span>
                <span class="font-medium text-indigo-700 font-semibold">Putaran Telaah {{ $viewModel->assignment->reviewRound->round }}</span>
            </div>
            <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900 leading-snug">
                {{ $viewModel->title() }}
            </h1>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $viewModel->assignment->status_badge_class }}">
                {{ $viewModel->assignment->status_label }}
            </span>
        </div>
    </div>

    <!-- Double-Blind Notice Banner -->
    <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 flex items-center gap-3 text-xs text-indigo-900">
        <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
        </svg>
        <div>
            <strong class="font-bold">Protokol Double-Blind Peer Review:</strong> Seluruh informasi identitas penulis, institusi asal, dan korespondensi telah disaring secara otomatis demi objektivitas penilaian saintifik.
        </div>
    </div>

    <!-- Main Workspace Grid: 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- KOLOM KIRI (5 Grid): Metadata Naskah, Berkas Manuskrip & Batas Waktu -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Informasi Naskah Anonim -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-100 pb-3">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Informasi Manuskrip Anonim</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase">Bidang Kajian (Section):</span>
                        <span class="font-medium text-gray-800 text-sm">{{ $viewModel->sectionName() }}</span>
                    </div>

                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase mb-1">Abstrak Naskah:</span>
                        <div class="text-gray-700 leading-relaxed bg-gray-50 p-3.5 rounded-lg border border-gray-100 text-[11px] max-h-56 overflow-y-auto">
                            {{ $viewModel->abstract() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Berkas Manuskrip Bersih Identitas -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-100 pb-3">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Berkas Manuskrip Telaah</span>
                </h3>

                <div class="space-y-2 text-xs">
                    @forelse($viewModel->assignment->reviewRound->submission->files as $file)
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-2 truncate">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="font-medium text-gray-800 truncate">{{ $file->file?->original_name ?? 'Naskah_Manuskrip_Anonim.pdf' }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 shrink-0 uppercase bg-gray-100 px-1.5 py-0.5 rounded font-mono">
                                PDF
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-400 italic text-[11px]">Belum ada berkas terlampir.</p>
                    @endforelse
                </div>
            </div>

            <!-- Batas Waktu & Permohonan Extension (UC-08) -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Tenggat Waktu Penelaahan</span>
                    </h3>
                    @if($viewModel->canSubmit() && ! $viewModel->pendingExtension())
                        <button type="button" onclick="document.getElementById('modalExtension').classList.remove('hidden')" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">
                            + Minta Perpanjangan
                        </button>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-gray-50">
                        <span class="text-gray-500">Batas Respon Undangan:</span>
                        <span class="font-semibold text-gray-800">{{ $viewModel->assignment->response_due_at ? $viewModel->assignment->response_due_at->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500">Batas Selesai Telaah:</span>
                        <span class="font-bold text-gray-900">{{ $viewModel->assignment->review_due_at ? $viewModel->assignment->review_due_at->format('d M Y') : '-' }}</span>
                    </div>

                    @if($viewModel->pendingExtension())
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-3 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-amber-900">Permohonan Perpanjangan Terkirim</span>
                                <span class="text-[10px] font-bold bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded">Menunggu Editor</span>
                            </div>
                            <p class="text-[11px] text-amber-800">
                                Dimohonkan hingga: <strong>{{ $viewModel->pendingExtension()->requested_due_at->format('d M Y') }}</strong><br>
                                Alasan: <span class="italic">"{{ $viewModel->pendingExtension()->reason }}"</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN (7 Grid): Form Evaluasi Rubrik, Keputusan & Anotasi (UC-09) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- KASUS 1: Undangan Masih Menunggu Respon (Status: invited) -->
            @if($viewModel->isInvited())
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-2xs space-y-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h3 class="font-serif font-bold text-gray-900 text-lg">Konfirmasi Kesediaan Menelaah Naskah</h3>
                        <p class="text-xs text-gray-600 max-w-md mx-auto">
                            Dewan Redaksi mengundang Anda untuk menelaah naskah ini berdasarkan kepakaran ilmiah Anda. Silakan konfirmasi kesediaan Anda sebelum batas waktu respon (<strong>{{ $viewModel->assignment->response_due_at->format('d M Y') }}</strong>).
                        </p>
                    </div>

                    @if($viewModel->assignment->invitation_message)
                        <div class="bg-gray-50 border border-gray-200 p-3.5 rounded-xl text-left text-xs max-w-lg mx-auto">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Pesan dari Editor:</span>
                            <p class="text-gray-700 italic">"{{ $viewModel->assignment->invitation_message }}"</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-center gap-3 pt-3">
                        <form action="{{ route('reviewer.assignments.respond', $viewModel->assignment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="response" value="accepted">
                            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all cursor-pointer">
                                Terima Undangan Telaah
                            </button>
                        </form>

                        <form action="{{ route('reviewer.assignments.respond', $viewModel->assignment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="response" value="declined">
                            <button type="submit" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                                Tolak Undangan
                            </button>
                        </form>
                    </div>
                </div>

            <!-- KASUS 2 & 3: Undangan Diterima (Status: accepted, overdue, completed) -->
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-2xs space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="font-serif font-bold text-lg text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Lembar Evaluasi Penelaahan Sejawat</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Isi penilaian rubrik secara objektif, berikan masukan konstruktif untuk penulis, dan tentukan rekomendasi putusan.
                        </p>
                    </div>

                    @if($viewModel->isCompleted())
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-2">
                            <div class="flex items-center justify-between font-bold">
                                <span>Hasil Telaah Telah Dikirim Resmi (Immutable)</span>
                                <span class="bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded uppercase">
                                    {{ $viewModel->existingReview()?->recommendation }}
                                </span>
                            </div>
                            <p class="text-emerald-800">
                                Diserahkan pada: {{ $viewModel->existingReview()?->submitted_at?->format('d M Y H:i') }} UTC. Terima kasih atas kontribusi Anda!
                            </p>
                        </div>
                    @endif

                    <form action="{{ route('reviewer.assignments.submit', $viewModel->assignment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
                        @csrf

                        <!-- 1. Kriteria Rubrik Penilaian -->
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-900 uppercase text-[11px] tracking-wider border-b border-gray-100 pb-2">
                                1. Rubrik Penilaian Kualitas Naskah (Skala 1 - 100)
                            </h4>

                            <div class="space-y-3">
                                @foreach($viewModel->rubricCriteria as $key => $criteria)
                                    @php
                                        $existingScore = $viewModel->existingReview()?->scores[$key] ?? 80;
                                    @endphp
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-800">{{ $criteria['label'] }} ({{ $criteria['weight'] }}%)</span>
                                            <div class="flex items-center gap-1.5">
                                                <input type="number" min="0" max="100" name="scores[{{ $key }}]" value="{{ old('scores.'.$key, $existingScore) }}" {{ $viewModel->isCompleted() ? 'disabled' : '' }} required class="w-16 rounded border border-gray-300 p-1 text-center font-bold text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                                                <span class="text-gray-400 font-medium">/ 100</span>
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-gray-500">{{ $criteria['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 2. Masukan untuk Penulis (Comments for Author) -->
                        <div class="space-y-2">
                            <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                2. Masukan Konstruktif untuk Penulis <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[11px] text-gray-500">
                                Catatan ini akan diteruskan kepada penulis secara anonim untuk panduan revisi.
                            </p>
                            <textarea name="comments_for_author" rows="5" required {{ $viewModel->isCompleted() ? 'disabled' : '' }} placeholder="Tuliskan evaluasi mengenai kekuatan, kelemahan metodologi, literatur yang perlu diperkaya, dan saran perbaikan teknis..." class="w-full rounded-lg border border-gray-300 p-2.5 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">{{ old('comments_for_author', $viewModel->existingReview()?->comments_for_author) }}</textarea>
                        </div>

                        <!-- 3. Catatan Rahasia untuk Dewan Redaksi (Confidential Comments for Editor) -->
                        <div class="space-y-2">
                            <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                3. Catatan Rahasia untuk Dewan Redaksi (Opsional)
                            </label>
                            <p class="text-[11px] text-gray-500">
                                Catatan tertutup yang <strong>hanya dapat dibaca oleh Section Editor dan Pemimpin Redaksi</strong> (tidak diperlihatkan ke penulis).
                            </p>
                            <textarea name="confidential_comments_for_editor" rows="3" {{ $viewModel->isCompleted() ? 'disabled' : '' }} placeholder="Informasi rahasia atau keraguan mengenai orisinalitas/plagiarisme yang perlu diperhatikan dewan editor..." class="w-full rounded-lg border border-gray-300 p-2.5 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">{{ old('confidential_comments_for_editor', $viewModel->existingReview()?->confidential_comments_for_editor) }}</textarea>
                        </div>

                        <!-- 4. Rekomendasi Putusan (Recommendation) -->
                        <div class="space-y-2">
                            <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                4. Rekomendasi Putusan Ilmiah <span class="text-rose-500">*</span>
                            </label>
                            <select name="recommendation" required {{ $viewModel->isCompleted() ? 'disabled' : '' }} class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                                <option value="">-- Pilih Rekomendasi --</option>
                                <option value="accept" {{ old('recommendation', $viewModel->existingReview()?->recommendation) === 'accept' ? 'selected' : '' }}>
                                    Diterima (Accept Submission - Naskah layak terbit tanpa revisi mayor)
                                </option>
                                <option value="revisions_required" {{ old('recommendation', $viewModel->existingReview()?->recommendation) === 'revisions_required' ? 'selected' : '' }}>
                                    Perlu Revisi (Revisions Required - Penulis wajib merevisi sesuai catatan)
                                </option>
                                <option value="resubmit" {{ old('recommendation', $viewModel->existingReview()?->recommendation) === 'resubmit' ? 'selected' : '' }}>
                                    Kirim Ulang untuk Ditelaah (Resubmit for Review - Perlu perombakan besar dan round baru)
                                </option>
                                <option value="decline" {{ old('recommendation', $viewModel->existingReview()?->recommendation) === 'decline' ? 'selected' : '' }}>
                                    Ditolak (Decline Submission - Tidak memenuhi standar keilmuan jurnal)
                                </option>
                            </select>
                        </div>

                        <!-- 5. Unggah Berkas Manuskrip Beranotasi (Optional) -->
                        @if(! $viewModel->isCompleted())
                            <div class="space-y-2">
                                <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                    5. Unggah Berkas Naskah Beranotasi (Opsional)
                                </label>
                                <input type="file" name="annotated_file" accept=".pdf,.docx" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                <p class="text-[10px] text-gray-400">Format: PDF atau DOCX, maksimal 20 MB.</p>
                            </div>
                        @endif

                        <!-- 6. Pernyataan Bebas Konflik Kepentingan (Wajib True) -->
                        @if(! $viewModel->isCompleted())
                            <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="certify_no_conflict" value="1" required class="mt-0.5 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon">
                                    <span class="text-xs text-amber-950 font-medium">
                                        <strong>Pernyataan Integritas Saintifik:</strong> Saya menyatakan dengan sungguh-sungguh bahwa saya tidak memiliki konflik kepentingan (*conflict of interest*) baik secara profesional, finansial, maupun personal terhadap naskah maupun penulis yang dievaluasi.
                                    </span>
                                </label>
                            </div>
                        @endif

                        <!-- Tombol Aksi Simpan Draf & Submit -->
                        @if(! $viewModel->isCompleted())
                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                                <button type="submit" formaction="{{ route('reviewer.assignments.draft', $viewModel->assignment->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition-all cursor-pointer">
                                    Simpan Draf Evaluasi
                                </button>
                                <button type="submit" class="px-5 py-2 bg-brand-maroon hover:bg-brand-maroon/90 text-white font-bold rounded-lg shadow-2xs transition-all cursor-pointer">
                                    Kirim Hasil Telaah Resmi
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL: Permohonan Perpanjangan Waktu (Extension Request - UC-08) -->
@if($viewModel->canSubmit() && ! $viewModel->pendingExtension())
    <div id="modalExtension" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-serif font-bold text-gray-900 text-base">Permohonan Perpanjangan Waktu</h3>
                <button type="button" onclick="document.getElementById('modalExtension').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('reviewer.assignments.extension', $viewModel->assignment->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Tanggal Batas Waktu Baru yang Dimohonkan <span class="text-rose-500">*</span></label>
                    <input type="date" name="requested_due_at" required min="{{ now()->addDay()->format('Y-m-d') }}" value="{{ $viewModel->assignment->review_due_at->addDays(7)->format('Y-m-d') }}" class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Alasan Permohonan <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="Jelaskan alasan permohonan waktu tambahan (misal: perlu pengecekan ulang data laboratorium/lapangan)..." class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modalExtension').classList.add('hidden')" class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-semibold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-2xs transition-all cursor-pointer">
                        Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
