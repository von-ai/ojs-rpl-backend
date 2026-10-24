<!-- LANGKAH 2: Persetujuan Penulis (Author Approval) -->
<div class="bg-white rounded-2xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
    <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg {{ $submission['approval']['is_approved'] ? 'bg-emerald-600' : 'bg-brand-maroon' }} text-white font-bold text-xs flex items-center justify-center shrink-0">
                2
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 leading-tight">
                    Persetujuan Penulis
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pantau dan kelola persetujuan hasil suntingan oleh penulis naskah korespondensi.
                </p>
            </div>
        </div>
    </div>

    <!-- Kondisi 1: Penulis Telah Menyetujui Naskah -->
    @if ($submission['approval']['is_approved'])
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="text-xs text-emerald-900 flex-1">
                <strong class="font-bold block text-sm">Penulis Telah Menyetujui Naskah</strong>
                <p class="mt-0.5 text-emerald-800">
                    Seluruh berkas hasil suntingan telah disetujui oleh <strong>{{ $submission['corresponding_author']['name'] }}</strong>{{ $submission['approval']['approved_at'] ? ' pada ' . $submission['approval']['approved_at'] : '' }}. Checksum berkas telah dibekukan. Naskah siap diserahkan ke Layout Editor.
                </p>
                @if($submission['approval']['author_note'])
                    <p class="mt-2 p-2.5 bg-white/70 rounded border border-emerald-200 text-emerald-800 italic">
                        "{{ $submission['approval']['author_note'] }}"
                    </p>
                @endif
            </div>
        </div>

    <!-- Kondisi 2: Keputusan Mediasi Section Editor Telah Diterbitkan (Resolved) -->
    @elseif ($submission['escalation']['is_resolved'] ?? false)
        <div class="p-4 rounded-xl bg-indigo-50/80 border border-indigo-200 space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-xs text-indigo-950 flex-1">
                    <div class="flex items-center gap-2">
                        <strong class="font-bold text-sm">Keputusan Mediasi Section Editor</strong>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">
                            {{ $submission['escalation']['ticket_id'] }} • Selesai Diputuskan
                        </span>
                    </div>
                    <span class="text-[11px] text-indigo-700 block mt-0.5">
                        Diterbitkan oleh <strong>{{ $submission['section_editor'] }}</strong> pada {{ $submission['escalation']['resolved_at'] }}
                    </span>

                    <div class="mt-2.5 p-3 bg-white rounded-lg border border-indigo-200/90 text-gray-800 space-y-1">
                        <span class="font-bold text-[10px] text-indigo-900 uppercase tracking-wider block">
                            Arahan Resmi Editor:
                        </span>
                        <p class="text-xs leading-relaxed">
                            {{ $submission['escalation']['resolution'] }}
                        </p>
                    </div>

                    <p class="mt-2.5 text-[11px] text-indigo-800">
                        * Silakan sesuaikan berkas pada <strong>Langkah 1 (Unggah Berkas Baru)</strong> di atas sesuai arahan keputusan Section Editor, lalu kirim ulang permohonan persetujuan ke penulis.
                    </p>
                </div>
            </div>
        </div>

    <!-- Kondisi 3: Sedang Dalam Mediasi Redaksi (Eskalasi Terbuka / Investigating) -->
    @elseif ($submission['escalation']['has_active'])
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-red-100 text-red-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="text-xs text-red-900 flex-1">
                    <div class="flex items-center gap-2">
                        <strong class="font-bold text-sm">Tiket Mediasi Redaksi Aktif</strong>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-200 text-red-800">
                            {{ $submission['escalation']['ticket_id'] }}
                        </span>
                    </div>
                    <p class="mt-1 text-red-800">
                        Masalah ini sedang dalam penelaahan Section Editor (<strong>{{ $submission['section_editor'] }}</strong>).
                    </p>
                    <div class="mt-2 p-3 bg-white/80 rounded-lg border border-red-200 text-gray-800">
                        <span class="font-semibold block text-[11px] text-gray-500 uppercase tracking-wider mb-1">
                            Uraian Mediasi:
                        </span>
                        {{ $submission['escalation']['reason'] }}
                    </div>
                </div>
            </div>
        </div>

    <!-- Kondisi 4: Penulis Mengajukan Catatan / Permintaan Perbaikan -->
    @elseif ($submission['approval']['has_changes_requested'])
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="text-xs text-amber-900 flex-1">
                    <strong class="font-bold block text-sm">Penulis Mengajukan Catatan Revisi</strong>
                    <p class="mt-1 p-3 bg-white/80 rounded-lg border border-amber-200 text-gray-800 italic">
                        "{{ $submission['approval']['author_note'] }}"
                    </p>
                </div>
            </div>

            <!-- Opsi Solusi: Perbaiki atau Mediasi -->
            <div class="pt-2 border-t border-amber-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <span class="text-[11px] text-amber-800">
                    Anda dapat mengunggah berkas revisi baru atau meminta bantuan mediasi Section Editor.
                </span>

                <button 
                    type="button" 
                    onclick="openEscalateModal()"
                    class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition shrink-0 cursor-pointer"
                >
                    Minta Mediasi Editor
                </button>
            </div>
        </div>

    <!-- Kondisi 5: Sedang Menunggu Respon Penulis -->
    @else
        <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-xs text-blue-900">
                    <strong class="font-bold block text-sm">Menunggu Respon Penulis</strong>
                    <p class="mt-0.5 text-blue-800">
                        Berkas suntingan telah dikirimkan ke <strong>{{ $submission['corresponding_author']['name'] }}</strong>.
                    </p>
                </div>
            </div>

            <!-- Tombol Kirim Ulang Permintaan -->
            <form action="{{ route('copyeditor.request-approval') }}" method="POST" class="shrink-0">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition cursor-pointer"
                >
                    Kirim Ulang Permintaan
                </button>
            </form>
        </div>
    @endif
</div>
