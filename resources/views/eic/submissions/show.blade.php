@extends('layouts.eic')

@section('title', 'Ruang Kerja Naskah ' . $submission->submission_number . ' — Portal Editor-in-Chief')

@section('content')
<div class="space-y-6">

    <!-- Header Naskah & Breadcrumb -->
    <div class="space-y-2">
        <nav class="flex items-center text-xs text-gray-500 gap-2">
            <a href="{{ route('eic.submissions.index') }}" class="hover:text-brand-maroon">Antrean Editorial</a>
            <span>/</span>
            <span class="font-mono font-bold text-gray-700">{{ $submission->submission_number }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ $viewModel->statusInfo()['badge_class'] }}">
                        {{ $viewModel->statusInfo()['label'] }}
                    </span>
                    <span class="text-xs text-gray-500">•</span>
                    <span class="text-xs font-medium text-gray-600">{{ $submission->section?->name ?? 'Artikel Riset' }}</span>
                </div>
                <h1 class="text-lg sm:text-2xl font-extrabold text-gray-900 leading-snug">
                    {{ $submission->title }}
                </h1>
            </div>

            <a 
                href="{{ route('eic.submissions.index') }}" 
                class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 shrink-0 self-start sm:self-auto"
            >
                ← Kembali ke Antrean
            </a>
        </div>
    </div>

    <!-- Layout 2 Kolom (2/3 Info Manuskrip, 1/3 Panel Tindakan EIC) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Kolom Kiri: Informasi Manuskrip & Berkas (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Metadata & Abstrak -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-2xs">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">
                    Metadata Naskah & Abstrak
                </h2>
                
                <div class="space-y-2 text-xs">
                    <div class="text-gray-500 font-medium">Penulis / Submitter:</div>
                    <div class="font-bold text-gray-900">
                        {{ $submission->author?->name ?? 'Penulis Naskah' }} ({{ $submission->author?->email ?? '-' }})
                    </div>
                </div>

                <div class="space-y-1.5 pt-2">
                    <div class="text-xs font-bold text-gray-700">Abstrak:</div>
                    <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-xl border border-gray-100 text-justify">
                        {{ $submission->abstract ?? 'Tidak ada abstrak yang disertakan.' }}
                    </div>
                </div>

                @if(!empty($submission->keywords))
                    <div class="space-y-1.5 pt-2">
                        <div class="text-xs font-bold text-gray-700">Kata Kunci:</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach((is_array($submission->keywords) ? $submission->keywords : explode(',', (string) $submission->keywords)) as $kw)
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700">
                                    {{ trim($kw) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Berkas Manuskrip & Lampiran -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-2xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                    <h2 class="text-sm font-bold text-gray-900">
                        Berkas Manuskrip Terunggah
                    </h2>
                    <span class="text-xs text-gray-500 font-medium">{{ $submission->files->count() }} Berkas</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($submission->files as $subFile)
                        @php $f = $subFile->file; @endphp
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-700 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <span class="block font-bold text-gray-900 truncate">{{ $f?->original_name ?? 'Berkas Naskah' }}</span>
                                    <span class="text-[11px] text-gray-500">
                                        Genre: <strong>{{ ucfirst($subFile->genre ?? 'manuscript') }}</strong> • Versi {{ $subFile->version }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono text-gray-400 shrink-0">
                                SHA-256: {{ substr($f?->checksum_sha256 ?? 'verified', 0, 8) }}...
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic py-2">Belum ada berkas terunggah.</p>
                    @endforelse
                </div>
            </div>

            <!-- Riwayat Penilaian Desk Review & Keputusan Editorial -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 shadow-2xs text-xs">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">
                    Riwayat Audit Editorial & Keputusan
                </h2>

                <div class="space-y-3">
                    @forelse($submission->editorialDecisions as $decision)
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/70 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 uppercase tracking-wide">
                                    Keputusan: {{ $decision->decision }}
                                </span>
                                <span class="text-gray-500 text-[11px]">
                                    {{ $decision->sent_at ? $decision->sent_at->format('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                            <p class="text-gray-700 italic">"{{ $decision->note_for_author }}"</p>
                            @if($decision->revision_due_at)
                                <span class="block text-[11px] text-red-600 font-bold">
                                    Batas Waktu Revisi: {{ $decision->revision_due_at->format('d F Y') }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-400 italic">Belum ada keputusan editorial yang dirilis.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Panel Aksi EIC (1/3) -->
        <aside class="lg:col-span-1 space-y-6">
            
            <!-- 1. Formulir Desk Review (UC-10) -->
            <div class="bg-white rounded-xl border border-blue-200 p-5 space-y-4 shadow-xs text-xs">
                <div class="flex items-center justify-between border-b border-blue-100 pb-2.5">
                    <h3 class="font-bold text-gray-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>1. Skrining Awal (Desk Review)</span>
                    </h3>
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                        UC-10
                    </span>
                </div>

                <form action="{{ route('eic.submissions.desk-review', $submission->id) }}" method="POST" class="space-y-3.5">
                    @csrf

                    <!-- Checklist Penilaian Awal -->
                    <div class="space-y-2 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-800">
                            <input type="checkbox" name="scope_fit" value="1" checked class="rounded text-brand-maroon focus:ring-brand-maroon">
                            <span>Sesuai Ruang Lingkup & Fokus Jurnal</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-800">
                            <input type="checkbox" name="files_complete" value="1" checked class="rounded text-brand-maroon focus:ring-brand-maroon">
                            <span>Berkas Manuskrip Lengkap</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-800">
                            <input type="checkbox" name="guidelines_compliant" value="1" checked class="rounded text-brand-maroon focus:ring-brand-maroon">
                            <span>Mengikuti Template Penulisan</span>
                        </label>
                    </div>

                    <!-- Skor Uji Plagiasi Awal -->
                    <div class="space-y-1">
                        <label for="similarity_score" class="font-bold text-gray-700">Skor Plagiasi Awal (%)</label>
                        <input 
                            type="number" 
                            id="similarity_score" 
                            name="similarity_score" 
                            step="0.1" 
                            min="0" 
                            max="100" 
                            value="12.5"
                            placeholder="Contoh: 15.0"
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon"
                        >
                    </div>

                    <!-- Pilihan Hasil Skrining -->
                    <div class="space-y-1">
                        <label for="status" class="font-bold text-gray-700">Hasil Skrining Awal</label>
                        <select 
                            id="status" 
                            name="status" 
                            required 
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon bg-white"
                        >
                            <option value="passed">Lolos (Lanjut ke Penelaahan)</option>
                            <option value="rejected">Tolak Awal (Desk Reject)</option>
                            <option value="revision_required">Perlu Perbaikan Format</option>
                        </select>
                    </div>

                    <!-- Catatan Desk Review -->
                    <div class="space-y-1">
                        <label for="notes" class="font-bold text-gray-700">Catatan Skrining</label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="2" 
                            placeholder="Catatan hasil pemeriksaan awal..."
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon"
                        ></textarea>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold rounded-lg transition shadow-xs cursor-pointer"
                    >
                        Simpan Evaluasi Desk Review
                    </button>
                </form>
            </div>

            <!-- 2. Formulir Penugasan Section Editor (UC-11) -->
            <div class="bg-white rounded-xl border border-indigo-200 p-5 space-y-4 shadow-xs text-xs">
                <div class="flex items-center justify-between border-b border-indigo-100 pb-2.5">
                    <h3 class="font-bold text-gray-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                        <span>2. Penugasan Section Editor</span>
                    </h3>
                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                        UC-11
                    </span>
                </div>

                @if($submission->activeEditorAssignment?->editor)
                    <div class="p-3 bg-indigo-50/70 rounded-lg border border-indigo-100 space-y-1">
                        <span class="text-[11px] text-indigo-800 font-medium">Editor Penanggung Jawab Saat Ini:</span>
                        <div class="font-bold text-indigo-950 text-xs flex items-center justify-between">
                            <span>{{ $submission->activeEditorAssignment->editor->name }}</span>
                            <span class="text-[10px] text-gray-500 font-normal">
                                Ditugaskan: {{ $submission->activeEditorAssignment->assigned_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                @endif

                <form action="{{ route('eic.submissions.assign-editor', $submission->id) }}" method="POST" class="space-y-3.5">
                    @csrf

                    <div class="space-y-1">
                        <label for="section_editor_id" class="font-bold text-gray-700">Pilih Section Editor</label>
                        <select 
                            id="section_editor_id" 
                            name="section_editor_id" 
                            required 
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon bg-white"
                        >
                            <option value="">-- Pilih Editor Penanggung Jawab --</option>
                            @foreach($viewModel->availableSectionEditors() as $editor)
                                <option value="{{ $editor->id }}" {{ $submission->activeEditorAssignment?->editor_id === $editor->id ? 'selected' : '' }}>
                                    {{ $editor->name }} ({{ $editor->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="assign_note" class="font-bold text-gray-700">Catatan Penugasan</label>
                        <textarea 
                            id="assign_note" 
                            name="note" 
                            rows="2" 
                            placeholder="Instruksi khusus kepada Section Editor..."
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon"
                        ></textarea>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-lg transition shadow-xs cursor-pointer"
                    >
                        Tugaskan Section Editor
                    </button>
                </form>
            </div>

            <!-- 3. Formulir Keputusan Editorial Akhir (UC-12) -->
            <div class="bg-white rounded-xl border border-brand-maroon/30 p-5 space-y-4 shadow-xs text-xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                    <h3 class="font-bold text-gray-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                        <span>3. Keputusan Editorial Resmi</span>
                    </h3>
                    <span class="text-[10px] font-bold text-brand-maroon bg-red-50 px-2 py-0.5 rounded border border-red-200">
                        UC-12
                    </span>
                </div>

                <form action="{{ route('eic.submissions.decision', $submission->id) }}" method="POST" class="space-y-3.5">
                    @csrf

                    <div class="space-y-1">
                        <label for="decision" class="font-bold text-gray-700">Jenis Keputusan</label>
                        <select 
                            id="decision" 
                            name="decision" 
                            required 
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon bg-white"
                        >
                            <option value="accept">Terima Naskah (Accept Submission)</option>
                            <option value="revisions_required">Perlu Revisi (Revisions Required)</option>
                            <option value="resubmit">Kirim Ulang untuk Review (Resubmit for Review)</option>
                            <option value="decline">Tolak Naskah (Decline Submission)</option>
                        </select>
                    </div>

                    <!-- Batas Waktu Revisi (Wajib jika keputusan butuh revisi) -->
                    <div class="space-y-1">
                        <label for="revision_due_at" class="font-bold text-gray-700">
                            Batas Waktu Revisi (Due Date) <span class="text-red-500">*wajib jika revisi</span>
                        </label>
                        <input 
                            type="date" 
                            id="revision_due_at" 
                            name="revision_due_at" 
                            value="{{ now()->addDays(14)->format('Y-m-d') }}"
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon"
                        >
                    </div>

                    <!-- Surat Resmi untuk Penulis -->
                    <div class="space-y-1">
                        <label for="note_for_author" class="font-bold text-gray-700">
                            Surat / Catatan untuk Penulis <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="note_for_author" 
                            name="note_for_author" 
                            rows="4" 
                            required 
                            placeholder="Sampaikan keputusan editorial dan poin masukan reviewer..."
                            class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon"
                        >Dewan redaksi telah menelaah naskah ilmiah Anda. Berdasarkan pertimbangan mitra bestari, kami menyampaikan keputusan berikut.</textarea>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-2 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white font-bold rounded-lg transition shadow-xs cursor-pointer"
                    >
                        Kirim Keputusan Editorial Resmi
                    </button>
                </form>
            </div>

        </aside>

    </div>

</div>
@endsection
