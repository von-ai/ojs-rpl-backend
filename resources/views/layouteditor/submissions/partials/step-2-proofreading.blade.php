{{-- resources/views/layouteditor/submissions/partials/step-2-proofreading.blade.php --}}
<div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
    <!-- Header Langkah 2 -->
    <div class="p-4 sm:p-5 border-b border-gray-200/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
        <div class="flex items-start gap-3">
            <span class="w-6 h-6 rounded-full bg-blue-700 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                2
            </span>
            <div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Putaran Proofreading & Catatan Koreksi
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pemeriksaan ketepatan tipografi, ejaan, tata letak tabel, dan gambar oleh Proofreader.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-center">
            @if($submission['proofreading']['unresolved_count'] > 0)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $submission['proofreading']['unresolved_count'] }} Catatan Perlu Revisi
                </span>
            @elseif($submission['proofreading']['total_notes_count'] > 0 && $submission['proofreading']['unresolved_count'] === 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Semua Catatan Selesai</span>
                </span>
            @endif
        </div>
    </div>

    <!-- Konten Proofreading -->
    <div class="p-4 sm:p-5 space-y-4">
        <!-- Informasi Proofreader Ditugaskan -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200/70 text-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-gray-600">Proofreader Ditugaskan:</span>
                <strong class="text-gray-900">{{ $submission['proofreading']['proofreader_name'] }}</strong>
            </div>

            <!-- Tombol Pengajuan Proofreading Baru Jika Diperlukan -->
            @if(count($submission['galleys']) > 0 && $submission['proofreading']['status'] === 'waiting_galley')
                <form action="{{ route('layouteditor.request-proofread') }}" method="POST">
                    @csrf
                    <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Kirim Galai ke Proofreader</span>
                    </button>
                </form>
            @endif
        </div>

        @if(count($submission['proofreading']['notes']) > 0)
            <!-- Daftar Catatan Koreksi Proofreader -->
            <div class="space-y-3">
                <span class="text-xs font-bold text-gray-700 block uppercase tracking-wider">
                    Daftar Catatan Koreksi ({{ count($submission['proofreading']['notes']) }})
                </span>

                @foreach($submission['proofreading']['notes'] as $note)
                    <div class="p-3.5 rounded-lg border {{ $note['resolved'] ? 'bg-emerald-50/20 border-emerald-200/80' : 'bg-white border-gray-200 shadow-2xs' }} transition">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-[10px] font-bold text-gray-400">#{{ $note['id'] }}</span>
                                    @if($note['page'])
                                        <span class="inline-flex items-center px-1.5 py-0.2 text-[10px] font-bold rounded bg-gray-100 text-gray-700">
                                            Halaman {{ $note['page'] }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-1.5 py-0.2 text-[10px] font-semibold rounded bg-gray-100 text-gray-700">
                                        {{ $note['category_label'] }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.2 text-[10px] font-bold rounded border uppercase {{ $note['severity_badge'] }}">
                                        {{ $note['severity'] }}
                                    </span>
                                    @if($note['resolved'])
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Selesai Diperbaiki</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">
                                            Belum Selesai
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-800 leading-relaxed font-sans">
                                    {{ $note['description'] }}
                                </p>

                                @if($note['resolved'] && !empty($note['resolution']))
                                    <div class="mt-2 pt-2 border-t border-emerald-100 text-xs text-emerald-900 bg-emerald-50/50 p-2 rounded">
                                        <div class="flex items-center gap-1 font-semibold text-[11px] text-emerald-800">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Tindakan Layout Editor:</span>
                                            <span class="font-normal text-gray-500 text-[10px] ml-auto">{{ $note['resolved_at'] }}</span>
                                        </div>
                                        <p class="text-[11px] mt-0.5 text-gray-700">
                                            {{ $note['resolution'] }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if(!$note['resolved'] && !$submission['is_completed'])
                                <button 
                                    type="button" 
                                    onclick="openResolveNoteModal('{{ $note['id'] }}', '{{ addslashes($note['description']) }}')"
                                    class="inline-flex items-center justify-center gap-1 px-2.5 py-1 text-xs font-semibold text-brand-maroon bg-brand-maroon/5 hover:bg-brand-maroon hover:text-white rounded-lg border border-brand-maroon/20 transition whitespace-nowrap self-start sm:self-center"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Tandai Selesai</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- State Belum Ada Catatan -->
            <div class="p-5 text-center bg-gray-50/60 rounded-lg border border-gray-200/70 text-xs text-gray-500">
                @if(count($submission['galleys']) === 0)
                    <span>Silakan unggah draf galai PDF terlebih dahulu untuk memulai putaran proofreading.</span>
                @else
                    <span>Belum ada catatan koreksi yang dimasukkan oleh Proofreader untuk putaran ini.</span>
                @endif
            </div>
        @endif
    </div>
</div>
