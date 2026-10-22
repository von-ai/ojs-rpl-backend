<!-- Kolom Samping (Sidebar Editorial, Konteks Status, & Linimasa Audit) -->
<div class="space-y-6">

    <!-- 1. Panel Kontekstual Status Naskah -->

    <!-- Kasus A: Perlu Revisi Penulis -->
    @if($submission['status'] === 'revisions_requested' && !empty($submission['editorial_feedback']))
        <div id="panel-revisi" class="bg-gradient-to-b from-red-50/90 via-red-50/40 to-white rounded-2xl border border-red-200 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between gap-2 border-b border-red-200/60 pb-3">
                <div class="flex items-center gap-2 text-red-800">
                    <div class="w-7 h-7 rounded-lg bg-red-100/80 text-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold leading-tight">Perlu Tindakan Penulis</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800 tabular-nums">
                    Revisi Mayor
                </span>
            </div>

            <div class="space-y-2 text-xs text-red-950">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-slate-600 font-medium">Keputusan Redaksi:</span>
                    <strong class="text-red-900 font-bold text-right">{{ $submission['editorial_feedback']['decision'] }}</strong>
                </div>
                <div class="flex items-start justify-between gap-2">
                    <span class="text-slate-600 font-medium">Batas Waktu Revisi:</span>
                    <span class="font-bold text-red-700 underline tabular-nums text-right">{{ $submission['editorial_feedback']['deadline'] }}</span>
                </div>
            </div>

            <div class="p-4 bg-white rounded-xl border border-red-200/80 text-xs text-slate-700 leading-relaxed space-y-1.5 shadow-2xs">
                <span class="block font-bold text-slate-900 text-[11px] uppercase tracking-wider">Catatan Dewan Redaksi:</span>
                <div class="prose prose-xs max-w-none text-slate-700 text-[12px] leading-relaxed">
                    {!! $submission['editorial_feedback']['comments'] !!}
                </div>
            </div>

            <div class="pt-1 space-y-2">
                <button 
                    type="button" 
                    onclick="if(window.showAlertModal) { window.showAlertModal({ title: 'Unggah Revisi Naskah', message: 'Silakan siapkan berkas manuskrip revisi (anonymized tanpa identitas penulis untuk menjaga blind review) beserta dokumen respon atas masukan mitra bestari (Response Letter).', buttonText: 'Mengerti', type: 'warning' }); } else { alert('Fitur unggah revisi terhubung ke modal SIRIDA.'); }"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 active:scale-[0.98] rounded-xl transition duration-150 shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Unggah Revisi Naskah & Response Letter</span>
                </button>

                <p class="text-[11px] text-slate-500 text-center leading-tight">
                    Unggah naskah bersih tanpa informasi identitas penulis.
                </p>
            </div>
        </div>
    @endif

    <!-- Kasus B: Naskah Berstatus Draf -->
    @if($submission['status'] === 'draft')
        <div class="bg-gradient-to-b from-amber-50/90 via-amber-50/40 to-white rounded-2xl border border-amber-200 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-amber-900 border-b border-amber-200/60 pb-3">
                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold">Naskah Draf Parsial</h3>
            </div>
            <p class="text-xs text-amber-900 leading-relaxed">
                Naskah ini tersimpan sebagai draf dan belum diajukan secara resmi ke dewan redaksi. Anda dapat melanjutkan melengkapi berkas dan metadata kapan saja.
            </p>
            <a 
                href="{{ route('author.submissions.create') }}" 
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active active:scale-[0.98] rounded-xl transition duration-150 shadow-xs"
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
        <div id="panel-publikasi" class="bg-gradient-to-b from-emerald-50/90 via-emerald-50/40 to-white rounded-2xl border border-emerald-200 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-emerald-200/60 pb-3">
                <div class="flex items-center gap-2 text-emerald-900">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold">Telah Terbit Resmi</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                    Open Access
                </span>
            </div>

            <div class="text-xs text-slate-700 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Terbitan:</span>
                    <strong class="text-slate-900 font-semibold">{{ $submission['publication']['volume'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Edisi:</span>
                    <strong class="text-slate-900 font-semibold">{{ $submission['publication']['issue_title'] }}</strong>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Halaman:</span>
                    <span class="tabular-nums font-semibold text-slate-900">{{ $submission['publication']['pages'] }}</span>
                </div>
                <div class="flex items-center justify-between gap-2 pt-1 border-t border-emerald-100">
                    <span class="text-slate-500">Digital Object Identifier (DOI):</span>
                    <a 
                        href="{{ $submission['publication']['doi_url'] }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="underline font-mono text-emerald-800 hover:text-emerald-950 font-bold tabular-nums"
                        title="Buka publikasi via CrossRef DOI Resolver"
                    >
                        {{ $submission['publication']['doi'] }}
                    </a>
                </div>
            </div>
            
            <!-- Tombol Unduh Galai Cetak PDF -->
            <a 
                href="{{ $submission['publication']['galley']['download_url'] }}" 
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 active:scale-[0.98] rounded-xl transition duration-150 shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Galai Cetak PDF ({{ $submission['publication']['galley']['file_size'] }})</span>
            </a>

            <!-- Generator Format Sitasi Ilmiah dengan Pilihan Tab -->
            <div class="pt-2 border-t border-emerald-200/60 space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-900">Kutipan Ilmiah (Sitasi APA 7th)</h4>
                    <span class="text-[10px] text-slate-400">Pilih Gaya</span>
                </div>

                <!-- Tab Selector Sitasi -->
                <div class="flex items-center gap-1 bg-white p-1 rounded-lg border border-emerald-200/80 text-[11px] font-semibold text-slate-600">
                    <button 
                        type="button" 
                        id="tab-btn-apa"
                        onclick="switchCitationTab('apa')"
                        class="flex-1 py-1 px-2 rounded text-center bg-emerald-100 text-emerald-900 font-bold transition cursor-pointer"
                    >
                        APA 7th
                    </button>
                    <button 
                        type="button" 
                        id="tab-btn-ieee"
                        onclick="switchCitationTab('ieee')"
                        class="flex-1 py-1 px-2 rounded text-center hover:bg-slate-100 transition cursor-pointer"
                    >
                        IEEE
                    </button>
                    <button 
                        type="button" 
                        id="tab-btn-bibtex"
                        onclick="switchCitationTab('bibtex')"
                        class="flex-1 py-1 px-2 rounded text-center hover:bg-slate-100 transition cursor-pointer"
                    >
                        BibTeX
                    </button>
                </div>

                <!-- Kotak Pratinjau Sitasi -->
                <div class="relative group">
                    <div id="citation-content-apa" class="citation-tab-panel p-3 bg-white rounded-xl border border-emerald-200 text-[11px] font-serif text-slate-800 leading-relaxed select-all">
                        {{ $submission['publication']['citations']['apa'] }}
                    </div>
                    <div id="citation-content-ieee" class="citation-tab-panel hidden p-3 bg-white rounded-xl border border-emerald-200 text-[11px] font-mono text-slate-800 leading-relaxed select-all">
                        {{ $submission['publication']['citations']['ieee'] }}
                    </div>
                    <div id="citation-content-bibtex" class="citation-tab-panel hidden p-3 bg-white rounded-xl border border-emerald-200 text-[10px] font-mono text-slate-800 leading-relaxed whitespace-pre-line select-all max-h-36 overflow-y-auto">
                        {{ $submission['publication']['citations']['bibtex'] }}
                    </div>

                    <!-- Tombol Salin Sitasi Aktif -->
                    <div class="mt-2 flex justify-end">
                        <button 
                            type="button" 
                            id="btn-copy-active-citation"
                            onclick="copyActiveCitation()"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 transition cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                
                // Switch tab buttons
                ['apa', 'ieee', 'bibtex'].forEach(fmt => {
                    const btn = document.getElementById('tab-btn-' + fmt);
                    const panel = document.getElementById('citation-content-' + fmt);
                    if (fmt === format) {
                        btn.className = 'flex-1 py-1 px-2 rounded text-center bg-emerald-100 text-emerald-900 font-bold transition cursor-pointer';
                        panel.classList.remove('hidden');
                    } else {
                        btn.className = 'flex-1 py-1 px-2 rounded text-center hover:bg-slate-100 transition cursor-pointer';
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

    <!-- 2. Informasi Jurnal & Penerbit -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                Identitas Penerbit
            </h3>
            <span class="w-2 h-2 rounded-full bg-emerald-500" title="Jurnal Aktif"></span>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px]">Jurnal:</span>
                <span class="font-bold text-slate-900 leading-snug block pt-0.5">{{ $submission['journal_name'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <span class="text-slate-500 text-[11px]">e-ISSN:</span>
                <span class="font-mono font-bold text-slate-800 tabular-nums">{{ $submission['journal_issn'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <span class="text-slate-500 text-[11px]">Seksi Rubrik:</span>
                <span class="font-semibold text-slate-800">{{ $submission['section'] }}</span>
            </div>
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <span class="text-slate-500 text-[11px]">Penerbit Resmi:</span>
                <span class="font-semibold text-brand-maroon">BRIDA Kota Makassar</span>
            </div>
        </div>

        <!-- Badges Pengindeksan Ilmiah -->
        <div class="pt-3 border-t border-slate-100">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-2">Terindeks & Berkolaborasi:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 border border-slate-200">SINTA 2</span>
                <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 border border-slate-200">Garuda</span>
                <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 border border-slate-200">Crossref</span>
                <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 border border-slate-200">Google Scholar</span>
            </div>
        </div>
    </div>

    <!-- 3. Linimasa Aktivitas Editorial (Audit Trail Kronologis Aman Penulis) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    Linimasa Aktivitas
                </h3>
            </div>
            <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                Audit Trail
            </span>
        </div>

        <div class="relative pl-5 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
            @foreach($submission['timeline'] as $event)
                <div class="relative text-xs space-y-1">
                    <span class="absolute -left-5 top-1 w-2.5 h-2.5 rounded-full bg-brand-maroon ring-4 ring-white"></span>
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-bold text-slate-900 leading-tight">{{ $event['title'] }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400 tabular-nums">{{ $event['date'] }}</p>
                    <p class="text-[11px] text-slate-600 leading-snug">{{ $event['description'] }}</p>
                    <span class="inline-block text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/60 mt-0.5">
                        Aktor: {{ $event['actor'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. Tombol Penarikan Naskah (Withdrawal - Hanya jika masih dalam alur) -->
    @if(!in_array($submission['status'], ['draft', 'published', 'declined'], true))
        <div class="pt-1">
            <button 
                type="button" 
                onclick="handleWithdrawalClick()"
                class="w-full text-center py-2.5 px-3 text-xs font-semibold text-slate-500 hover:text-red-700 hover:bg-red-50 rounded-xl transition duration-150 border border-dashed border-slate-300 hover:border-red-300 cursor-pointer active:scale-[0.98]"
            >
                Ajukan Penarikan Naskah (Withdraw)
            </button>
        </div>

        <script>
            async function handleWithdrawalClick() {
                if (window.showConfirmModal) {
                    const confirmed = await window.showConfirmModal({
                        title: 'Konfirmasi Permohonan Penarikan Naskah',
                        message: 'Apakah Anda yakin ingin mengajukan penarikan resmi naskah ini dari proses penelaahan jurnal? Permohonan akan diteruskan ke Editor-in-Chief untuk evaluasi.',
                        confirmText: 'Ya, Ajukan Penarikan',
                        cancelText: 'Batalkan',
                        type: 'danger'
                    });
                    if (confirmed && window.toast) {
                        window.toast.info('Permohonan penarikan naskah telah diajukan ke dewan redaksi.', 'Pengajuan Diterima');
                    }
                } else {
                    if (confirm('Konfirmasi penarikan naskah (Withdrawal Request) memerlukan persetujuan dewan redaksi. Lanjutkan?')) {
                        alert('Permohonan penarikan naskah telah diajukan.');
                    }
                }
            }
        </script>
    @endif

</div>
