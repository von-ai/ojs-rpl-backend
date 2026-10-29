{{-- resources/views/layouteditor/submissions/partials/step-3-final-approval.blade.php --}}
<div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
    <!-- Header Langkah 3 -->
    <div class="p-4 sm:p-5 border-b border-gray-200/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
        <div class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                3
            </span>
            <div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Persetujuan Akhir Galai Publikasi
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Persetujuan final naskah oleh Penulis dan Pemimpin Redaksi (Editor in Chief) untuk penerbitan resmi.
                </p>
            </div>
        </div>

        <div>
            @if($submission['approval']['editor_final']['ready_for_publication'])
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Siap Dijadwalkan Terbit</span>
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    Menunggu Persetujuan Final
                </span>
            @endif
        </div>
    </div>

    <!-- Konten Persetujuan -->
    <div class="p-4 sm:p-5 space-y-4">
        <!-- 1. Status Persetujuan Penulis (Author Sign-off) -->
        <div class="p-3.5 rounded-lg border {{ $submission['approval']['author_signoff']['approved'] ? 'bg-emerald-50/30 border-emerald-200' : 'bg-gray-50/80 border-gray-200' }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    @if($submission['approval']['author_signoff']['approved'])
                        <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @else
                        <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    @endif
                    <div>
                        <span class="text-xs font-bold text-gray-800 block">Persetujuan Penulis (Author Sign-off)</span>
                        <span class="text-[11px] text-gray-500">
                            @if($submission['approval']['author_signoff']['approved'])
                                Telah disetujui pada: <strong class="text-gray-700">{{ $submission['approval']['author_signoff']['approved_at'] }}</strong>
                            @else
                                Menunggu konfirmasi persetujuan dari penulis korespondensi.
                            @endif
                        </span>
                    </div>
                </div>

                @if($submission['approval']['author_signoff']['approved'])
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 self-start sm:self-center">
                        Disetujui Penulis
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200 self-start sm:self-center">
                        Menunggu Penulis
                    </span>
                @endif
            </div>

            @if(!empty($submission['approval']['author_signoff']['notes']))
                <div class="mt-2.5 pt-2 border-t border-emerald-100 text-xs text-gray-600">
                    <span class="font-semibold text-gray-700">Catatan Penulis:</span>
                    <span class="italic text-gray-600">"{{ $submission['approval']['author_signoff']['notes'] }}"</span>
                </div>
            @endif
        </div>

        <!-- 2. Status Persetujuan Pemimpin Redaksi (Editor in Chief) -->
        <div class="p-3.5 rounded-lg border {{ $submission['approval']['editor_final']['ready_for_publication'] ? 'bg-emerald-50 border-emerald-300' : 'bg-gray-50/80 border-gray-200' }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    @if($submission['approval']['editor_final']['ready_for_publication'])
                        <div class="w-7 h-7 rounded-full bg-emerald-200 text-emerald-800 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    @else
                        <div class="w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    @endif
                    <div>
                        <span class="text-xs font-bold text-gray-900 block">Persetujuan Akhir Editor in Chief</span>
                        <span class="text-[11px] text-gray-500">
                            @if($submission['approval']['editor_final']['ready_for_publication'])
                                Disetujui EIC pada: <strong class="text-emerald-900">{{ $submission['approval']['editor_final']['approved_at'] }}</strong>
                            @else
                                Otorisasi final publikasi nomor edisi jurnal oleh Editor in Chief.
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Tombol Aksi Permohonan Persetujuan EIC -->
                @if(!empty($submission['approval']['editor_final']['ready_for_publication']))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold bg-emerald-200 text-emerald-900 border border-emerald-300 self-start sm:self-center">
                        <svg class="w-3.5 h-3.5 text-emerald-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Galai Disetujui</span>
                    </span>
                @elseif(count($submission['galleys']) === 0)
                    <div class="text-[11px] text-gray-500 bg-gray-100 px-2.5 py-1 rounded self-start sm:self-center">
                        Draf galai belum tersedia.
                    </div>
                @elseif(empty($submission['proofreading']['has_assignment']) || ($submission['proofreading']['status'] ?? '') === 'waiting_galley' || ($submission['proofreading']['status'] ?? '') === 'waiting_review')
                    <div class="text-[11px] text-amber-700 bg-amber-50 px-2.5 py-1 rounded border border-amber-200 self-start sm:self-center">
                        Galai belum melalui putaran proofreading. Kirim galai ke Proofreader terlebih dahulu.
                    </div>
                @elseif(($submission['proofreading']['status'] ?? '') === 'under_review' || (empty($submission['proofreading']['is_completed']) && ($submission['proofreading']['total_notes_count'] ?? 0) === 0))
                    <div class="text-[11px] text-blue-700 bg-blue-50 px-2.5 py-1 rounded border border-blue-200 self-start sm:self-center">
                        Putaran proofreading sedang berlangsung. Menunggu hasil tinjauan Proofreader.
                    </div>
                @elseif(($submission['proofreading']['unresolved_count'] ?? 0) > 0)
                    <div class="text-[11px] text-amber-700 bg-amber-50 px-2.5 py-1 rounded border border-amber-200 self-start sm:self-center">
                        Selesaikan {{ $submission['proofreading']['unresolved_count'] }} catatan koreksi sebelum mengajukan persetujuan final.
                    </div>
                @elseif(!empty($submission['escalation']['has_active']))
                    <div class="text-[11px] text-purple-700 bg-purple-50 px-2.5 py-1 rounded border border-purple-200 self-start sm:self-center">
                        Terdapat tiket eskalasi aktif yang belum diselesaikan oleh Section Editor.
                    </div>
                @else
                    <form action="{{ route('layouteditor.request-final-approval') }}" method="POST">
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
                        <button 
                            type="submit" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition whitespace-nowrap"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Mohon Persetujuan Akhir EIC</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
