<div 
    id="{{ $modalId }}" 
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-950/50 backdrop-blur-xs transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="{{ $modalId }}-title"
>
    <div class="relative w-full max-w-lg bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 text-xs">
        <!-- Header Modal -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/70">
            <div class="flex items-center gap-2.5 text-gray-900">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h3 id="{{ $modalId }}-title" class="text-sm font-bold text-gray-900 leading-snug">Verifikasi Pembayaran APC</h3>
                    <p class="text-[11px] text-gray-500">{{ $item['submission_number'] ?? ($item['number'] ?? 'Naskah') }} • {{ Str::limit($item['title'], 40) }}</p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="document.getElementById('{{ $modalId }}').classList.add('hidden'); document.getElementById('{{ $modalId }}').classList.remove('flex');"
                class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                aria-label="Tutup dialog"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            <!-- Ringkasan Tagihan & Status -->
            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200/90 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Status Pembayaran:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $item['payment_badge'] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $item['payment_label'] ?? 'Belum Dibayar' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Nomor Tagihan:</span>
                    <span class="font-mono font-bold text-gray-900">{{ $item['invoice_number'] ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Nominal Tagihan:</span>
                    <span class="font-extrabold text-sm text-gray-900">{{ $item['amount_formatted'] ?? 'Rp 0' }}</span>
                </div>
            </div>

            <!-- Bukti Pembayaran -->
            @if(!empty($item['has_proof']) && !empty($item['proof_url']))
                <div class="p-3.5 bg-sky-50/70 rounded-xl border border-sky-200/80 space-y-2.5">
                    <div class="flex items-center justify-between border-b border-sky-200/60 pb-2">
                        <span class="font-bold text-sky-900 text-xs">Rincian Bukti Transfer yang Diunggah</span>
                        <a 
                            href="{{ $item['proof_url'] }}" 
                            target="_blank"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-800 hover:text-sky-950 underline"
                        >
                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Buka / Unduh Berkas</span>
                        </a>
                    </div>

                    <div class="space-y-1.5 text-gray-700 text-[11px]">
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-gray-500 shrink-0">Bank Pengirim</span>
                            <span class="font-semibold text-gray-900 text-right truncate">{{ $item['sender_bank_name'] ?? '-' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-gray-500 shrink-0">Nama Pengirim</span>
                            <span class="font-semibold text-gray-900 text-right leading-tight max-w-[65%]">{{ $item['sender_account_name'] ?? '-' }}</span>
                        </div>
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-gray-500 shrink-0">Tanggal Transfer</span>
                            <span class="font-semibold text-gray-900 text-right tabular-nums">{{ $item['transfer_date'] ?? '-' }}</span>
                        </div>
                        @if(!empty($item['author_notes']))
                            <div class="pt-1.5 border-t border-sky-100/80 text-[11px]">
                                <span class="text-gray-500 block mb-0.5">Catatan Penulis:</span>
                                <p class="text-gray-700 italic bg-white/70 p-2 rounded border border-sky-100/60 leading-relaxed text-[11px]">
                                    "{{ $item['author_notes'] }}"
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Formulir Aksi Verifikasi / Tolak -->
                @if(($item['payment_status'] ?? '') !== 'verified')
                    <div class="pt-2 border-t border-gray-100 space-y-3">
                        <form action="{{ route('journalmanager.submissions.payment.verify', $item['submission_id'] ?? $item['id']) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="text" name="notes" placeholder="Catatan verifikasi sah (opsional)..." class="w-full px-3 py-1.5 text-xs border border-gray-300 rounded-lg">
                            <button 
                                type="submit" 
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Verifikasi Pembayaran & Nyatakan Lunas</span>
                            </button>
                        </form>

                        <details class="text-xs group">
                            <summary class="text-rose-700 font-semibold cursor-pointer hover:underline">
                                Tolak Bukti Transfer Ini
                            </summary>
                            <form action="{{ route('journalmanager.submissions.payment.reject', $item['submission_id'] ?? $item['id']) }}" method="POST" class="pt-2 space-y-2">
                                @csrf
                                <textarea name="reason" rows="2" required placeholder="Jelaskan alasan penolakan (misal bukti buram, nominal kurang)..." class="w-full px-3 py-1.5 text-xs border border-rose-300 rounded-lg"></textarea>
                                <button 
                                    type="submit" 
                                    class="w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition cursor-pointer"
                                >
                                    Konfirmasi Penolakan Bukti
                                </button>
                            </form>
                        </details>
                    </div>
                @else
                    <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200 text-xs text-emerald-900">
                        <span class="font-bold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Pembayaran Lunas
                        </span>
                        Diverifikasi pada: {{ $item['verified_at'] ?? 'Baru saja' }}.
                    </div>
                @endif
            @else
                <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-900 leading-relaxed">
                    Penulis belum mengunggah berkas bukti transfer untuk tagihan naskah ini.
                </div>
            @endif

            <!-- Opsi Keringanan / Pembebasan Biaya (Waiver) -->
            @if(($item['payment_status'] ?? '') !== 'verified' && ($item['payment_status'] ?? '') !== 'waived')
                <details class="text-xs group border-t border-gray-100 pt-2">
                    <summary class="text-purple-700 font-semibold cursor-pointer hover:underline flex items-center justify-between">
                        <span>Berikan Pembebasan Biaya (Grant Waiver)</span>
                        <span class="text-[10px] text-purple-500 font-normal">Keringanan Redaksi</span>
                    </summary>
                    <form action="{{ route('journalmanager.submissions.payment.waive', $item['submission_id'] ?? $item['id']) }}" method="POST" class="pt-2 space-y-2">
                        @csrf
                        <input type="text" name="waiver_reason" required placeholder="Alasan pembebasan biaya (misal: penulis mahasiswa, naskah undangan)..." class="w-full px-3 py-1.5 text-xs border border-purple-300 rounded-lg">
                        <button 
                            type="submit" 
                            class="w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-lg transition cursor-pointer"
                        >
                            Bebaskan Biaya APC (Waiver)
                        </button>
                    </form>
                </details>
            @endif
        </div>

        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button 
                type="button" 
                onclick="document.getElementById('{{ $modalId }}').classList.add('hidden'); document.getElementById('{{ $modalId }}').classList.remove('flex');"
                class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
