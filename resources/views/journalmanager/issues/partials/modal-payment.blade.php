<div 
    id="{{ $modalId }}" 
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-950/60 backdrop-blur-xs transition-opacity duration-200 p-3 sm:p-4 md:p-6 items-center justify-center"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="{{ $modalId }}-title"
>
    <!-- Wrapper Pemusatan Viewport Tanpa Pemotongan -->
    <div class="min-h-full w-full flex items-center justify-center py-2 sm:py-4 mx-auto">
        <!-- Backdrop Klik untuk Menutup Dialog -->
        <div 
            class="fixed inset-0 -z-10 cursor-pointer" 
            onclick="document.getElementById('{{ $modalId }}').classList.add('hidden'); document.getElementById('{{ $modalId }}').classList.remove('flex');"
            aria-hidden="true"
        ></div>

        <!-- Panel Konten Modal Responsif & Terkunci Proporsional di Tengah -->
        <div class="relative w-full max-w-2xl mx-auto my-auto bg-white rounded-2xl border border-gray-200/90 shadow-2xl overflow-hidden flex flex-col max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3.5rem)] text-xs animate-in fade-in zoom-in-95 duration-150">
            
            <!-- Header Modal (Pinned di Atas) -->
            <div class="flex items-center justify-between px-5 sm:px-6 py-3.5 border-b border-gray-100 bg-gray-50/80 shrink-0">
                <div class="flex items-center gap-3 min-w-0 pr-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="{{ $modalId }}-title" class="text-sm sm:text-base font-bold text-gray-900 leading-snug truncate">
                            Verifikasi Biaya Publikasi (APC)
                        </h3>
                        <p class="text-[11px] text-gray-500 truncate" title="{{ $item['title'] }}">
                            <span class="font-semibold text-gray-700">{{ $item['submission_number'] ?? ($item['number'] ?? 'Naskah') }}</span>
                            <span class="text-gray-300 mx-1">•</span>
                            <span>{{ $item['title'] }}</span>
                        </p>
                    </div>
                </div>
                <button 
                    type="button" 
                    onclick="document.getElementById('{{ $modalId }}').classList.add('hidden'); document.getElementById('{{ $modalId }}').classList.remove('flex');"
                    class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-200/70 flex items-center justify-center transition cursor-pointer shrink-0"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body Modal (Scrollable di Tengah, Terlindungi dari Cutoff) -->
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0 [scrollbar-width:thin]">
                
                <!-- Grid Informasi 2 Kolom: Tagihan & Bukti Transfer -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    
                    <!-- Kartu 1: Rincian Tagihan APC -->
                    <div class="p-3.5 bg-gray-50/90 rounded-xl border border-gray-200/90 flex flex-col justify-between space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-medium text-[11px]">Status Pembayaran</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold border {{ $item['payment_badge'] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $item['payment_label'] ?? 'Belum Dibayar' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 text-[10px] block font-medium">Nominal Tagihan:</span>
                            <span class="text-lg font-bold text-gray-900 tabular-nums leading-tight">
                                {{ $item['amount_formatted'] ?? 'Rp 0' }}
                            </span>
                        </div>
                        <div class="pt-2 border-t border-gray-200/70 flex items-center justify-between text-[11px]">
                            <span class="text-gray-500">Nomor Tagihan:</span>
                            <span class="font-mono font-bold text-gray-800">{{ $item['invoice_number'] ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Kartu 2: Bukti Transfer & Rekening Pengirim -->
                    <div class="p-3.5 bg-sky-50/70 rounded-xl border border-sky-200/80 flex flex-col justify-between space-y-2.5">
                        <div class="flex items-center justify-between border-b border-sky-200/60 pb-1.5">
                            <span class="font-bold text-sky-900 text-xs">Bukti Transfer</span>
                            @if(!empty($item['has_proof']) && !empty($item['proof_url']))
                                <a 
                                    href="{{ $item['proof_url'] }}" 
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-700 hover:text-sky-950 underline"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Buka / Unduh Berkas</span>
                                </a>
                            @else
                                <span class="text-[10px] text-amber-700 font-medium">Belum Diunggah</span>
                            @endif
                        </div>

                        @if(!empty($item['has_proof']) && !empty($item['proof_url']))
                            <div class="space-y-1 text-[11px] text-gray-700">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-gray-500 shrink-0">Bank Pengirim:</span>
                                    <span class="font-semibold text-gray-900 text-right truncate">{{ $item['sender_bank_name'] ?? '-' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-gray-500 shrink-0">Nama Pengirim:</span>
                                    <span class="font-semibold text-gray-900 text-right leading-tight max-w-[65%] truncate" title="{{ $item['sender_account_name'] ?? '-' }}">{{ $item['sender_account_name'] ?? '-' }}</span>
                                </div>
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-gray-500 shrink-0">Tanggal Transfer:</span>
                                    <span class="font-semibold text-gray-900 text-right tabular-nums">{{ $item['transfer_date'] ?? '-' }}</span>
                                </div>
                            </div>
                        @else
                            <p class="text-[11px] text-amber-800 leading-relaxed py-1">
                                Penulis naskah belum mengunggah bukti transfer untuk tagihan publikasi ini.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Catatan Penulis (Jika Ada) -->
                @if(!empty($item['author_notes']))
                    <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200/70 text-[11px] space-y-1">
                        <div class="flex items-center gap-1.5 text-blue-900 font-semibold">
                            <svg class="w-3.5 h-3.5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <span>Catatan Penulis:</span>
                        </div>
                        <p class="text-gray-700 italic pl-5 leading-relaxed">
                            "{{ $item['author_notes'] }}"
                        </p>
                    </div>
                @endif

                <!-- Bagian Tindakan Verifikasi & Alternatif -->
                @if(($item['payment_status'] ?? '') === 'verified')
                    <!-- State Sudah Diverifikasi Lunas -->
                    <div class="p-3.5 bg-emerald-50/80 rounded-xl border border-emerald-200 text-xs text-emerald-950 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-200/70 text-emerald-800 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-bold block text-emerald-900">Pembayaran APC Telah Diverifikasi Sah & Lunas</span>
                            <span class="text-[11px] text-emerald-700">Waktu verifikasi: {{ $item['verified_at'] ?? 'Baru saja' }}</span>
                        </div>
                    </div>
                @else
                    <!-- Formulir Verifikasi Utama (Jika ada bukti atau tagihan aktif) -->
                    <div class="p-4 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-900 text-xs">Persetujuan Redaksi</span>
                            <span class="text-[11px] text-gray-500">Konfirmasi mutasi rekening</span>
                        </div>

                        <form action="{{ route('journalmanager.submissions.payment.verify', $item['submission_id'] ?? $item['id']) }}" method="POST" class="space-y-2.5">
                            @csrf
                            <input 
                                type="text" 
                                name="notes" 
                                placeholder="Catatan verifikasi sah (opsional, misal: 'Telah dicek di mutasi BRI')..." 
                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 bg-white"
                            >
                            <button 
                                type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-2xs transition cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Verifikasi Pembayaran & Nyatakan Lunas</span>
                            </button>
                        </form>
                    </div>

                    <!-- Tindakan Alternatif: Tolak Bukti & Keringanan Pembebasan Biaya -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        
                        <!-- Accordion 1: Tolak Bukti Transfer -->
                        @if(!empty($item['has_proof']))
                            <details class="group bg-rose-50/60 border border-rose-200/80 rounded-xl p-3 text-xs">
                                <summary class="text-rose-700 font-semibold cursor-pointer hover:underline flex items-center justify-between list-none">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Tolak Bukti Transfer Ini</span>
                                    </span>
                                    <svg class="w-4 h-4 text-rose-400 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </summary>
                                <form action="{{ route('journalmanager.submissions.payment.reject', $item['submission_id'] ?? $item['id']) }}" method="POST" class="pt-2.5 space-y-2">
                                    @csrf
                                    <textarea 
                                        name="reason" 
                                        rows="2" 
                                        required 
                                        placeholder="Jelaskan alasan penolakan (misal: bukti buram, nominal kurang)..." 
                                        class="w-full px-3 py-1.5 text-xs border border-rose-300 rounded-lg bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"
                                    ></textarea>
                                    <button 
                                        type="submit" 
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition cursor-pointer"
                                    >
                                        Konfirmasi Penolakan Bukti
                                    </button>
                                </form>
                            </details>
                        @endif

                        <!-- Accordion 2: Berikan Pembebasan Biaya (Waiver) -->
                        @if(($item['payment_status'] ?? '') !== 'waived')
                            <details class="group bg-purple-50/60 border border-purple-200/80 rounded-xl p-3 text-xs {{ empty($item['has_proof']) ? 'sm:col-span-2' : '' }}">
                                <summary class="text-purple-700 font-semibold cursor-pointer hover:underline flex items-center justify-between list-none">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                        </svg>
                                        <span>Berikan Pembebasan Biaya</span>
                                    </span>
                                    <span class="text-[10px] text-purple-500 font-normal">Keringanan Redaksi</span>
                                </summary>
                                <form action="{{ route('journalmanager.submissions.payment.waive', $item['submission_id'] ?? $item['id']) }}" method="POST" class="pt-2.5 space-y-2">
                                    @csrf
                                    <input 
                                        type="text" 
                                        name="waiver_reason" 
                                        required 
                                        placeholder="Alasan pembebasan biaya (misal: penulis mahasiswa, naskah undangan)..." 
                                        class="w-full px-3 py-1.5 text-xs border border-purple-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600"
                                    >
                                    <button 
                                        type="submit" 
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg transition cursor-pointer"
                                    >
                                        Bebaskan Biaya Publikasi
                                    </button>
                                </form>
                            </details>
                        @endif

                    </div>
                @endif

            </div>

            <!-- Footer Modal (Pinned di Bawah, Selalu Terlihat Tanpa Terpotong) -->
            <div class="px-5 sm:px-6 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between shrink-0">
                <span class="text-[11px] text-gray-500 hidden sm:inline">
                    Pastikan mutasi rekening sesuai sebelum verifikasi.
                </span>
                <button 
                    type="button" 
                    onclick="document.getElementById('{{ $modalId }}').classList.add('hidden'); document.getElementById('{{ $modalId }}').classList.remove('flex');"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 active:bg-gray-200 border border-gray-300 rounded-lg shadow-2xs transition cursor-pointer ml-auto"
                >
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
