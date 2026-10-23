<!-- Kolom Samping (Sidebar Editorial, Konteks Status, & Linimasa Audit) -->
<div class="space-y-5">

    <!-- 1. Panel Kontekstual Status Naskah -->

    <!-- Kasus A: Perlu Revisi Penulis -->
    @if($submission['status'] === 'revisions_requested' && !empty($submission['editorial_feedback']))
        <div id="panel-revisi" class="bg-white rounded-xl border border-red-200 p-5 space-y-4">
            <div class="flex items-center justify-between gap-2 border-b border-red-100 pb-3">
                <div class="flex items-center gap-2 text-red-800 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold leading-tight truncate">Perlu Tindakan Penulis</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200 tabular-nums shrink-0">
                    Revisi Mayor
                </span>
            </div>

            <div class="space-y-2 text-xs text-gray-700">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Keputusan Redaksi:</span>
                    <strong class="text-gray-900 font-bold text-right break-words">{{ $submission['editorial_feedback']['decision'] }}</strong>
                </div>
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Batas Waktu Revisi:</span>
                    <span class="font-bold text-red-700 tabular-nums text-right shrink-0">{{ $submission['editorial_feedback']['deadline'] }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-700 leading-relaxed space-y-1.5">
                <span class="block font-bold text-gray-900 text-[11px] uppercase tracking-wider">Catatan Dewan Redaksi:</span>
                <div class="text-gray-700 text-xs leading-relaxed break-words [overflow-wrap:anywhere]">
                    {!! nl2br(e(str_replace(['<br>', '<br/>', '<br />'], "\n", $submission['editorial_feedback']['comments']))) !!}
                </div>
            </div>

            <div class="pt-1 space-y-2">
                <button 
                    type="button" 
                    onclick="if(window.showAlertModal) { window.showAlertModal({ title: 'Unggah Revisi Naskah', message: 'Silakan siapkan berkas manuskrip revisi (anonymized tanpa identitas penulis untuk menjaga blind review) beserta dokumen respon atas masukan mitra bestari (Response Letter).', buttonText: 'Mengerti', type: 'warning' }); } else { alert('Fitur unggah revisi terhubung ke modal SIRIDA.'); }"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 rounded-lg transition duration-150 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Unggah Revisi Naskah & Response Letter</span>
                </button>

                <p class="text-[11px] text-gray-500 text-center leading-tight">
                    Unggah naskah bersih tanpa informasi identitas penulis.
                </p>
            </div>
        </div>
    @endif

    <!-- Kasus B: Naskah Berstatus Draf -->
    @if($submission['status'] === 'draft')
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center gap-2 text-gray-900 border-b border-gray-100 pb-3">
                <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xs sm:text-sm font-bold">Naskah Draf Parsial</h3>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Naskah ini tersimpan sebagai draf dan belum diajukan secara resmi ke dewan redaksi. Anda dapat melanjutkan melengkapi berkas dan metadata kapan saja.
            </p>
            <a 
                href="{{ route('author.submissions.create') }}" 
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg transition duration-150"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Lanjutkan Pengisian Formulir</span>
            </a>
        </div>
    @endif

    <!-- Kasus C: Naskah Telah Terbit (Publikasi & Sitasi Ilmiah) -->
    @if($submission['status'] === 'published' && !empty($submission['publication']))
        <div id="panel-publikasi" class="bg-white rounded-xl border border-emerald-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                <div class="flex items-center gap-2 text-emerald-900 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold truncate">Telah Terbit Resmi</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                    Open Access
                </span>
            </div>

            <div class="text-xs text-gray-700 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Terbitan:</span>
                    <strong class="text-gray-900 font-semibold text-right break-words">{{ $submission['publication']['volume'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Edisi:</span>
                    <strong class="text-gray-900 font-semibold text-right break-words">{{ $submission['publication']['issue_title'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Halaman:</span>
                    <span class="tabular-nums font-semibold text-gray-900 shrink-0">{{ $submission['publication']['pages'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-2 pt-1 border-t border-gray-100">
                    <span class="text-gray-500 shrink-0">DOI:</span>
                    <a 
                        href="{{ $submission['publication']['doi_url'] }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="underline font-mono text-emerald-800 hover:text-emerald-950 font-bold tabular-nums truncate max-w-44 text-right"
                        title="Buka publikasi via CrossRef DOI"
                    >
                        {{ $submission['publication']['doi'] }}
                    </a>
                </div>
            </div>
            
            <!-- Tombol Unduh Galai Cetak PDF -->
            <a 
                href="{{ $submission['publication']['galley']['download_url'] }}" 
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 rounded-lg transition duration-150"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Galai Cetak PDF ({{ $submission['publication']['galley']['file_size'] }})</span>
            </a>

            <!-- Generator Format Sitasi Ilmiah dengan Pilihan Tab -->
            <div class="pt-2 border-t border-emerald-100 space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-gray-900">Kutipan Ilmiah (Sitasi APA 7th)</h4>
                    <span class="text-[10px] text-gray-400">Pilih Gaya</span>
                </div>

                <!-- Tab Selector Sitasi -->
                <div class="flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-200 text-[11px] font-semibold text-gray-600">
                    <button 
                        type="button" 
                        id="tab-btn-apa"
                        onclick="switchCitationTab('apa')"
                        class="flex-1 py-1 px-2 rounded text-center bg-white text-emerald-900 font-bold border border-gray-200 transition cursor-pointer"
                    >
                        APA 7th
                    </button>
                    <button 
                        type="button" 
                        id="tab-btn-ieee"
                        onclick="switchCitationTab('ieee')"
                        class="flex-1 py-1 px-2 rounded text-center hover:bg-gray-100 transition cursor-pointer"
                    >
                        IEEE
                    </button>
                    <button 
                        type="button" 
                        id="tab-btn-bibtex"
                        onclick="switchCitationTab('bibtex')"
                        class="flex-1 py-1 px-2 rounded text-center hover:bg-gray-100 transition cursor-pointer"
                    >
                        BibTeX
                    </button>
                </div>

                <!-- Kotak Pratinjau Sitasi -->
                <div class="relative group">
                    <div id="citation-content-apa" class="citation-tab-panel p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs font-serif text-gray-800 leading-relaxed select-all">
                        {{ $submission['publication']['citations']['apa'] }}
                    </div>
                    <div id="citation-content-ieee" class="citation-tab-panel hidden p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs font-mono text-gray-800 leading-relaxed select-all">
                        {{ $submission['publication']['citations']['ieee'] }}
                    </div>
                    <div id="citation-content-bibtex" class="citation-tab-panel hidden p-3 bg-gray-50 rounded-lg border border-gray-200 text-[11px] font-mono text-gray-800 leading-relaxed whitespace-pre-line select-all max-h-36 overflow-y-auto">
                        {{ $submission['publication']['citations']['bibtex'] }}
                    </div>

                    <!-- Tombol Salin Sitasi Aktif -->
                    <div class="mt-2 flex justify-end">
                        <button 
                            type="button" 
                            id="btn-copy-active-citation"
                            onclick="copyActiveCitation()"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Salin Sitasi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let currentCitationFormat = 'apa';
            const citationData = {
                apa: {{ json_encode($submission['publication']['citations']['apa']) }},
                ieee: {{ json_encode($submission['publication']['citations']['ieee']) }},
                bibtex: {{ json_encode($submission['publication']['citations']['bibtex']) }}
            };

            function switchCitationTab(format) {
                currentCitationFormat = format;
                
                ['apa', 'ieee', 'bibtex'].forEach(fmt => {
                    const btn = document.getElementById('tab-btn-' + fmt);
                    const panel = document.getElementById('citation-content-' + fmt);
                    if (fmt === format) {
                        btn.className = 'flex-1 py-1 px-2 rounded text-center bg-white text-emerald-900 font-bold border border-gray-200 transition cursor-pointer';
                        panel.classList.remove('hidden');
                    } else {
                        btn.className = 'flex-1 py-1 px-2 rounded text-center hover:bg-gray-100 transition cursor-pointer';
                        panel.classList.add('hidden');
                    }
                });
            }

            function copyActiveCitation() {
                const text = citationData[currentCitationFormat] || '';
                copyTextToClipboard(text, 'Sitasi format ' + currentCitationFormat.toUpperCase() + ' disalin!');
            }
        </script>
    @endif

    <!-- Kasus D: Pemeriksaan Awal Redaksi -->
    @if($submission['status'] === 'submitted' && !empty($submission['initial_screening']))
        <div id="panel-pemeriksaan" class="bg-white rounded-xl border border-blue-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                <div class="flex items-center gap-2 text-blue-900 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold truncate">{{ $submission['initial_screening']['stage_title'] }}</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200 shrink-0">
                    Desk Review
                </span>
            </div>

            <div class="text-xs text-gray-700 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Evaluasi Ruang Lingkup:</span>
                    <strong class="text-gray-900 font-semibold text-right break-words">{{ $submission['initial_screening']['scope_status'] }}</strong>
                </div>
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Uji Kesamaan:</span>
                    <div class="text-right shrink-0">
                        <span class="font-bold text-emerald-700 block">{{ $submission['initial_screening']['similarity_score'] }}</span>
                        <span class="text-[10px] text-gray-400 block">{{ $submission['initial_screening']['similarity_tool'] }}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Estimasi Keputusan:</span>
                    <span class="tabular-nums font-semibold text-gray-800 shrink-0">{{ $submission['initial_screening']['estimated_days'] }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-600 leading-relaxed break-words [overflow-wrap:anywhere]">
                {{ $submission['initial_screening']['notes'] }}
            </div>
        </div>
    @endif

    <!-- Kasus E: Proses Telaah -->
    @if($submission['status'] === 'in_review' && !empty($submission['review_info']))
        <div id="panel-review" class="bg-white rounded-xl border border-sky-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-sky-100 pb-3">
                <div class="flex items-center gap-2 text-sky-900 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-sky-50 text-sky-700 flex items-center justify-center shrink-0 border border-sky-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold truncate">{{ $submission['review_info']['stage_title'] }}</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-50 text-sky-800 border border-sky-200 shrink-0">
                    Putaran {{ $submission['review_info']['round'] }}
                </span>
            </div>

            <div class="text-xs text-gray-700 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Metode Telaah:</span>
                    <strong class="text-gray-900 font-semibold text-right break-words">{{ $submission['review_info']['method'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Mitra Bestari Ditugaskan:</span>
                    <span class="font-bold text-gray-800 text-right shrink-0">{{ $submission['review_info']['reviewers_assigned'] }} Penilai Independen</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Estimasi Durasi:</span>
                    <span class="tabular-nums font-semibold text-gray-800 shrink-0">{{ $submission['review_info']['estimated_duration'] }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-600 leading-relaxed space-y-1 break-words [overflow-wrap:anywhere]">
                <span class="block font-bold text-gray-800 uppercase tracking-wider text-[10px]">Privasi Mitra Bestari:</span>
                <p>{{ $submission['review_info']['confidentiality_assurance'] }}</p>
            </div>
        </div>
    @endif

    <!-- Kasus F: Tahap Produksi & Pemeriksaan Galai -->
    @if(in_array($submission['status'], ['in_production', 'accepted'], true) && !empty($submission['production_info']))
        <div id="panel-produksi" class="bg-white rounded-xl border border-purple-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-purple-100 pb-3">
                <div class="flex items-center gap-2 text-purple-900 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-purple-50 text-purple-700 flex items-center justify-center shrink-0 border border-purple-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold truncate">{{ $submission['production_info']['stage_title'] }}</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-800 border border-purple-200 shrink-0">
                    Penerbitan
                </span>
            </div>

            <div class="text-xs text-gray-700 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Status Keputusan:</span>
                    <strong class="text-emerald-700 font-bold text-right break-words">{{ $submission['production_info']['decision'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Target Edisi:</span>
                    <strong class="text-gray-900 font-semibold text-right break-words">{{ $submission['production_info']['target_issue'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Tata Bahasa:</span>
                    <span class="font-semibold text-emerald-700 shrink-0">{{ $submission['production_info']['copyediting_status'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Tata Letak:</span>
                    <span class="font-semibold text-purple-800 shrink-0">{{ $submission['production_info']['layout_status'] }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-600 leading-relaxed break-words [overflow-wrap:anywhere]">
                {{ $submission['production_info']['instructions'] }}
            </div>
        </div>
    @endif

    <!-- Kasus G: Keputusan Editorial Naskah Ditolak -->
    @if($submission['status'] === 'declined' && !empty($submission['declined_info']))
        <div id="panel-ditolak" class="bg-white rounded-xl border border-rose-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                <div class="flex items-center gap-2 text-rose-900 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold truncate">{{ $submission['declined_info']['stage_title'] }}</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200 shrink-0">
                    Arsip
                </span>
            </div>

            <div class="text-xs text-gray-700 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Status Keputusan:</span>
                    <strong class="text-rose-900 font-bold text-right break-words">{{ $submission['declined_info']['decision'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Tanggal Keputusan:</span>
                    <span class="tabular-nums font-semibold text-gray-800 shrink-0">{{ $submission['declined_info']['decision_date'] }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-700 leading-relaxed space-y-1.5">
                <span class="block font-bold text-gray-900 text-[11px] uppercase tracking-wider">Catatan Dewan Redaksi:</span>
                <div class="text-gray-700 text-xs leading-relaxed break-words [overflow-wrap:anywhere]">
                    {!! nl2br(e(str_replace(['<br>', '<br/>', '<br />'], "\n", $submission['declined_info']['reason']))) !!}
                </div>
            </div>

            <p class="text-[11px] text-gray-500 leading-tight break-words [overflow-wrap:anywhere]">
                {{ $submission['declined_info']['guidance'] }}
            </p>
        </div>
    @endif

    <!-- Kasus H: Naskah Telah Diarsipkan / Ditarik Resmi -->
    @if(in_array($submission['status'], ['archived', 'withdrawn'], true) && !empty($submission['withdrawn_info']))
        <div id="panel-ditarik" class="bg-white rounded-xl border border-zinc-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-100 pb-3">
                <div class="flex items-center gap-2 text-zinc-900 min-w-0">
                    <div class="w-6 h-6 rounded-md bg-zinc-100 text-zinc-700 flex items-center justify-center shrink-0 border border-zinc-200">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-bold truncate">{{ $submission['withdrawn_info']['stage_title'] }}</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-zinc-100 text-zinc-800 border border-zinc-200 shrink-0">
                    {{ $submission['status'] === 'archived' ? 'Arsip' : 'Ditarik' }}
                </span>
            </div>

            <div class="text-xs text-gray-700 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Status Penarikan:</span>
                    <strong class="text-zinc-900 font-bold text-right break-words">{{ $submission['withdrawn_info']['status'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-gray-500 shrink-0">Tanggal Disetujui:</span>
                    <span class="tabular-nums font-semibold text-gray-800 shrink-0">{{ $submission['withdrawn_info']['confirmed_at'] }}</span>
                </div>
            </div>

            <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-600 leading-relaxed break-words [overflow-wrap:anywhere]">
                {{ $submission['withdrawn_info']['statement'] }}
            </div>
        </div>
    @endif

    <!-- 2. Informasi Jurnal & Penerbit -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">
                Identitas Penerbit
            </h3>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200">Aktif</span>
        </div>

        <div class="space-y-2.5 text-xs">
            <div>
                <span class="text-gray-400 block text-[11px]">Jurnal:</span>
                <span class="font-bold text-gray-900 leading-snug block pt-0.5">{{ $submission['journal_name'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                <span class="text-gray-500 text-[11px]">e-ISSN:</span>
                <span class="font-mono font-bold text-gray-800 tabular-nums">{{ $submission['journal_issn'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                <span class="text-gray-500 text-[11px]">Seksi Rubrik:</span>
                <span class="font-semibold text-gray-800">{{ $submission['section'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100">
                <span class="text-gray-500 text-[11px]">Penerbit Resmi:</span>
                <span class="font-semibold text-brand-maroon">BRIDA Kota Makassar</span>
            </div>
        </div>

        <!-- Badges Pengindeksan Ilmiah -->
        <div class="pt-3 border-t border-gray-100">
            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider block mb-2">Terindeks & Berkolaborasi:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600 border border-gray-200">SINTA 2</span>
                <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600 border border-gray-200">Garuda</span>
                <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600 border border-gray-200">Crossref</span>
                <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600 border border-gray-200">Google Scholar</span>
            </div>
        </div>
    </div>

    <!-- 3. Linimasa Aktivitas Editorial (Audit Trail Kronologis Aman Penulis) -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded bg-gray-100 text-gray-600 flex items-center justify-center shrink-0">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">
                    Linimasa Aktivitas
                </h3>
            </div>
            <span class="text-[10px] font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md border border-gray-200">
                Audit Trail
            </span>
        </div>

        <div class="border-l-2 border-brand-maroon/25 pl-3.5 space-y-4">
            @foreach($submission['timeline'] as $event)
                <div class="relative text-xs space-y-1">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-bold text-gray-900 leading-tight">{{ $event['title'] }}</span>
                    </div>
                    <p class="text-[11px] text-gray-400 tabular-nums">{{ $event['date'] }}</p>
                    <p class="text-[11px] text-gray-600 leading-snug">{{ $event['description'] }}</p>
                    <span class="inline-block text-[10px] font-semibold text-gray-600 bg-gray-50 px-2 py-0.5 rounded border border-gray-200 mt-0.5">
                        Aktor: {{ $event['actor'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. Pengajuan Penarikan Naskah (Hanya jika belum masuk review dan belum ada tindak lanjut role lain) -->
    @if($submission['can_withdraw'] ?? false)
        <div class="pt-1">
            <button 
                type="button" 
                onclick="handleWithdrawalClick()"
                class="w-full text-center py-2.5 px-3 text-xs font-semibold text-gray-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition duration-150 border border-dashed border-gray-300 hover:border-red-300 cursor-pointer active:scale-[0.98]"
            >
                Ajukan Penarikan Naskah
            </button>
        </div>

        <form id="withdrawal-form" action="{{ route('author.submissions.withdraw', $submission['id']) }}" method="POST" class="hidden">
            @csrf
        </form>

        <script>
            async function handleWithdrawalClick() {
                const withdrawalForm = document.getElementById('withdrawal-form');
                if (window.showConfirmModal) {
                    const confirmed = await window.showConfirmModal({
                        title: 'Konfirmasi Pengajuan Penarikan Naskah',
                        message: 'Apakah Anda yakin ingin mengajukan penarikan resmi naskah ini? Penarikan hanya dapat diajukan sebelum naskah masuk tahap penelaahan (review) dan belum ada tindak lanjut dari dewan redaksi/mitra bestari.',
                        confirmText: 'Ya, Ajukan Penarikan',
                        cancelText: 'Batalkan',
                        type: 'danger'
                    });
                    if (confirmed) {
                        if (withdrawalForm) {
                            withdrawalForm.submit();
                        } else if (window.toast) {
                            window.toast.info('Permohonan penarikan naskah telah diajukan ke dewan redaksi.', 'Pengajuan Diterima');
                        }
                    }
                } else {
                    if (confirm('Apakah Anda yakin ingin mengajukan penarikan naskah ini? Tindakan ini hanya dapat dilakukan sebelum naskah masuk tahap review.')) {
                        if (withdrawalForm) {
                            withdrawalForm.submit();
                        } else {
                            alert('Permohonan penarikan naskah telah diajukan.');
                        }
                    }
                }
            }
        </script>
    @elseif(!in_array($submission['status'], ['draft', 'published', 'declined', 'archived', 'withdrawn'], true))
        <div class="pt-1">
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-center">
                <div class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Naskah Tidak Dapat Ditarik</span>
                </div>
                <p class="mt-1 text-[11px] text-gray-500 leading-snug">
                    {{ $submission['cannot_withdraw_reason'] ?? 'Naskah telah masuk tahap telaah atau sedang dalam proses penanganan oleh dewan redaksi/mitra bestari sehingga tidak dapat ditarik kembali.' }}
                </p>
            </div>
        </div>
    @endif

</div>
