@if($copyeditApproval ?? null)
@php
    $copyeditor = $copyeditApproval->copyedit?->copyEditor;
    $copyeditorName = $copyeditor?->name ?? 'Tim Copyeditor SIRIDA';
@endphp
<section class="rounded-2xl border-2 border-amber-300/80 bg-white shadow-sm overflow-hidden transition-all duration-200" aria-labelledby="copyedit-approval-heading">
    <!-- Banner Header Aksen -->
    <div class="bg-linear-to-r from-amber-500/10 via-amber-500/5 to-transparent px-5 py-4 border-b border-amber-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                <svg class="w-3.5 h-3.5 mr-1.5 text-amber-700 animate-pulse" fill="currentColor" viewBox="0 0 8 8">
                    <circle cx="4" cy="4" r="3"/>
                </svg>
                Tindakan Penulis Diperlukan
            </span>
            <span class="text-xs font-medium text-amber-800">Tahap 5: Copyediting Naskah</span>
        </div>
        <div class="text-xs text-gray-500 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Permintaan: {{ $copyeditApproval->requested_at ? $copyeditApproval->requested_at->setTimezone('Asia/Makassar')->format('d M Y, H:i') . ' WITA' : now()->setTimezone('Asia/Makassar')->format('d M Y') }}</span>
        </div>
    </div>

    <div class="p-5 sm:p-6 space-y-6">
        <!-- Judul & Narasi -->
        <div class="space-y-1.5">
            <h2 id="copyedit-approval-heading" class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Tinjau & Verifikasi Hasil Suntingan Naskah
            </h2>
            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                Copyeditor (<strong class="text-gray-900 font-semibold">{{ $copyeditorName }}</strong>) telah selesai menyunting tata bahasa, pemformatan sitasi, dan kesesuaian gaya selingkung. Silakan periksa berkas hasil suntingan di bawah ini. Anda dapat menyetujui naskah untuk lanjut ke Layout Editor atau meminta perbaikan jika ada poin yang belum sesuai.
            </p>
        </div>

        @if(!empty($copyeditApproval->note))
            <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                <div>
                    <strong class="font-semibold block text-amber-950">Catatan dari Copyeditor:</strong>
                    <span class="mt-0.5 block leading-relaxed">{{ $copyeditApproval->note }}</span>
                </div>
            </div>
        @endif

        <!-- Daftar Berkas Hasil Suntingan Siap Unduh -->
        <div class="space-y-2.5">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Berkas Hasil Suntingan Copyeditor:</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($copyeditApproval->copyedit?->outputs ?? [] as $output)
                    @if($output->file)
                        @php
                            $ext = strtolower(pathinfo($output->file->original_name, PATHINFO_EXTENSION));
                            $isDoc = in_array($ext, ['doc', 'docx']);
                            $sizeFormatted = $output->file->byte_size ? number_format($output->file->byte_size / 1024, 1) . ' KB' : '-';
                        @endphp
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white hover:border-amber-300 transition-all flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg {{ $isDoc ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center shrink-0 font-bold text-xs">
                                    {{ strtoupper($ext ?: 'DOC') }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-gray-900 truncate" title="{{ $output->file->original_name }}">
                                        {{ $output->file->original_name }}
                                    </h4>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        {{ $sizeFormatted }} • Versi Suntingan #{{ $output->position }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('copyeditor.files.download', $output->file_id) }}"
                               class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-gray-300 text-gray-700 hover:text-brand-maroon hover:border-brand-maroon/30 shadow-2xs hover:shadow-xs transition">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh
                            </a>
                        </div>
                    @endif
                @empty
                    <div class="p-4 rounded-xl bg-gray-50 text-gray-500 text-xs italic">
                        Berkas suntingan sedang dipersiapkan oleh editor.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Formulir Keputusan Penulis Interaktif -->
        <form method="POST" action="{{ route('copyeditor.author-decision') }}" x-data="{ decision: 'approved' }" class="space-y-4 pt-4 border-t border-gray-100">
            @csrf
            <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">

            <div class="space-y-2">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Keputusan Anda atas Hasil Suntingan:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Opsi 1: Setujui -->
                    <label :class="decision === 'approved' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/30' : 'border-gray-200 bg-white hover:border-gray-300'"
                           class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition select-none">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="decision" value="approved" x-model="decision" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-xs sm:text-sm font-bold text-gray-900">Setujui Hasil Suntingan</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Direkomendasikan
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2 pl-6.5 leading-relaxed">
                            Naskah hasil suntingan telah sesuai. Naskah akan langsung diserahkan ke Layout Editor untuk penataan tata letak publikasi.
                        </p>
                    </label>

                    <!-- Opsi 2: Minta Koreksi Ulang -->
                    <label :class="decision === 'revisions_requested' ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30' : 'border-gray-200 bg-white hover:border-gray-300'"
                           class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition select-none">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="decision" value="revisions_requested" x-model="decision" class="text-rose-600 focus:ring-rose-500 w-4 h-4">
                            <span class="text-xs sm:text-sm font-bold text-gray-900">Minta Perbaikan / Koreksi Ulang</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2 pl-6.5 leading-relaxed">
                            Masih ada kesalahan tata bahasa, perubahan makna substantif, atau sitasi yang perlu diperbaiki kembali oleh Copyeditor.
                        </p>
                    </label>
                </div>
            </div>

            <!-- Catatan Apresiasi Opsional jika Disetujui -->
            <div x-show="decision === 'approved'" x-transition class="space-y-1.5">
                <label for="copyedit-note" class="block text-xs font-semibold text-gray-700">
                    Catatan untuk Redaksi / Copyeditor (Opsional)
                </label>
                <textarea name="note" id="copyedit-note" rows="2" maxlength="4000"
                          placeholder="Contoh: Seluruh perbaikan tata bahasa telah kami periksa dan kami setujui untuk lanjut ke proses produksi."
                          class="w-full rounded-xl border border-gray-300 p-3 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-hidden transition">{{ old('note') }}</textarea>
            </div>

            <!-- Alasan Perbaikan Wajib jika Minta Revisi -->
            <div x-show="decision === 'revisions_requested'" x-cloak x-transition class="space-y-1.5">
                <label for="copyedit-reason" class="block text-xs font-semibold text-rose-700">
                    Uraian Catatan Perbaikan <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" id="copyedit-reason" rows="3" maxlength="4000"
                          :required="decision === 'revisions_requested'"
                          placeholder="Jelaskan secara spesifik kalimat, halaman, tabel, atau bagian sitasi yang perlu diperbaiki ulang oleh Copyeditor..."
                          class="w-full rounded-xl border border-rose-300 p-3 text-xs sm:text-sm focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-hidden transition">{{ old('reason') }}</textarea>
                <p class="text-[11px] text-gray-500">Copyeditor akan menerima notifikasi dan memperbaiki berkas naskah sesuai catatan ini.</p>
            </div>

            <!-- Tombol Aksi Kirim -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="submit"
                        :class="decision === 'approved' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white'"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <template x-if="decision === 'approved'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui & Lanjutkan ke Tahap Produksi
                        </span>
                    </template>
                    <template x-if="decision === 'revisions_requested'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Kirim Permintaan Perbaikan ke Copyeditor
                        </span>
                    </template>
                </button>
            </div>
        </form>
    </div>
</section>
@endif
