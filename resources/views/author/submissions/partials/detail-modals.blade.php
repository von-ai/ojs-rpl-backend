@php
    $pay = $submission['payment_info'] ?? null;
@endphp

<!-- Modal Formulir Pengunggahan Berkas Revisi (UC-07) -->
<div 
    id="modal-upload-revisi" 
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm transition-opacity duration-200 select-none"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="modal-revisi-title"
>
    <div class="relative w-full max-w-lg bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header Modal Solid (Bebas Tembus Pandang) -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center gap-2.5 text-gray-900">
                <div class="w-8 h-8 rounded-lg bg-red-100 text-red-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-revisi-title" class="text-sm font-bold text-gray-900 leading-snug">Unggah Revisi Naskah</h3>
                    <p class="text-[11px] text-gray-500">Putaran telaah dan perbaikan naskah ilmiah</p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="document.getElementById('modal-upload-revisi').classList.add('hidden'); document.getElementById('modal-upload-revisi').classList.remove('flex');"
                class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                aria-label="Tutup dialog"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Form Pengunggahan -->
        <form 
            action="{{ route('author.submissions.revisions.store', $submission['id']) }}" 
            method="POST" 
            enctype="multipart/form-data" 
            class="p-6 space-y-4 text-xs"
        >
            @csrf

            <!-- Field Berkas Manuskrip Revisi (Wajib) -->
            <div class="space-y-1.5">
                <label for="manuscript_file" class="block font-bold text-gray-800">
                    Berkas Manuskrip Bersih (Revisi) <span class="text-red-500">*</span>
                </label>
                <input 
                    type="file" 
                    id="manuscript_file" 
                    name="manuscript_file" 
                    required 
                    accept=".pdf,.doc,.docx"
                    class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-red-500"
                >
                <p class="text-[11px] text-gray-500">Format PDF, DOC, atau DOCX. Maksimum 20 MiB. Pastikan identitas penulis telah disamarkan.</p>
            </div>

            <!-- Field Response Letter (Opsional) -->
            <div class="space-y-1.5">
                <label for="response_letter_file" class="block font-bold text-gray-800">
                    Berkas Respon / Response Letter (Opsional)
                </label>
                <input 
                    type="file" 
                    id="response_letter_file" 
                    name="response_letter_file" 
                    accept=".pdf,.doc,.docx"
                    class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-red-500"
                >
                <p class="text-[11px] text-gray-500">Tanggapan poin-demi-poin terhadap catatan penelaah (PDF/DOC/DOCX, maks 20 MiB).</p>
            </div>

            <!-- Catatan Ringkas untuk Editor -->
            <div class="space-y-1.5">
                <label for="revision_note" class="block font-bold text-gray-800">
                    Catatan untuk Dewan Redaksi (Opsional)
                </label>
                <textarea 
                    id="revision_note" 
                    name="note" 
                    rows="3" 
                    maxlength="2000"
                    placeholder="Jelaskan secara ringkas poin perbaikan utama yang telah Anda lakukan..."
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-gray-800"
                ></textarea>
            </div>

            <!-- Tombol Aksi Modal -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button 
                    type="button" 
                    onclick="document.getElementById('modal-upload-revisi').classList.add('hidden'); document.getElementById('modal-upload-revisi').classList.remove('flex');"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 active:bg-red-800 rounded-lg transition cursor-pointer shadow-xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Kirim Naskah Revisi</span>
                </button>
            </div>
        </form>
    </div>
</div>

@if($pay['can_upload'] ?? false)
    <!-- Modal Formulir Unggah Bukti Pembayaran APC -->
    <div 
        id="modal-upload-pembayaran" 
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm transition-opacity duration-200 select-none"
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="modal-pembayaran-title"
    >
        <div class="relative w-full max-w-lg bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Header Modal Solid -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center gap-2.5 text-gray-900">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-pembayaran-title" class="text-sm font-bold text-gray-900 leading-snug">Unggah Bukti Pembayaran APC</h3>
                        <p class="text-[11px] text-gray-500">Konfirmasi transfer biaya pemrosesan naskah</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    onclick="document.getElementById('modal-upload-pembayaran').classList.add('hidden'); document.getElementById('modal-upload-pembayaran').classList.remove('flex');"
                    class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form 
                action="{{ route('author.submissions.payment.store', $submission['id']) }}" 
                method="POST" 
                enctype="multipart/form-data" 
                class="p-6 space-y-4 text-xs"
            >
                @csrf

                <!-- Ringkasan Tagihan -->
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-700 block">Nomor Tagihan</span>
                        <span class="font-mono font-bold text-gray-800">{{ $pay['invoice_number'] ?? '-' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-amber-700 block">Nominal Wajib</span>
                        <span class="text-sm font-extrabold text-amber-900">{{ $pay['amount_formatted'] ?? 'Rp 0' }}</span>
                    </div>
                </div>

                <!-- Berkas Bukti Pembayaran (Wajib) -->
                <div class="space-y-1.5">
                    <label for="payment_proof" class="block font-bold text-gray-800">
                        Berkas Bukti Transfer (Resi / Struk) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="file" 
                        id="payment_proof" 
                        name="payment_proof" 
                        required 
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-xs text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-amber-500"
                    >
                    <p class="text-[11px] text-gray-500">Format PDF, JPG, JPEG, atau PNG. Ukuran maksimal 5 MB. Pastikan bukti terlihat jelas dan tidak terpotong.</p>
                </div>

                <!-- Info Rekening Pengirim -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label for="sender_bank_name" class="block font-semibold text-gray-700">Bank Pengirim</label>
                        <input 
                            type="text" 
                            id="sender_bank_name" 
                            name="sender_bank_name" 
                            value="{{ old('sender_bank_name') }}"
                            placeholder="Contoh: Mandiri / BCA / BNI"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                        >
                    </div>
                    <div class="space-y-1">
                        <label for="sender_account_name" class="block font-semibold text-gray-700">Nama Pemilik Rekening</label>
                        <input 
                            type="text" 
                            id="sender_account_name" 
                            name="sender_account_name" 
                            value="{{ old('sender_account_name') }}"
                            placeholder="Nama sesuai buku tabungan"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                        >
                    </div>
                </div>

                <!-- Tanggal & Nominal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label for="transfer_date" class="block font-semibold text-gray-700">Tanggal Transfer</label>
                        <input 
                            type="date" 
                            id="transfer_date" 
                            name="transfer_date" 
                            value="{{ old('transfer_date', date('Y-m-d')) }}"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                        >
                    </div>
                    <div class="space-y-1">
                        <label for="amount_paid" class="block font-semibold text-gray-700">Nominal Ditransfer (Rp)</label>
                        <input 
                            type="number" 
                            id="amount_paid" 
                            name="amount_paid" 
                            value="{{ old('amount_paid', $pay['amount'] ?? 0) }}"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                        >
                    </div>
                </div>

                <!-- Catatan Penulis (Opsional) -->
                <div class="space-y-1">
                    <label for="author_notes" class="block font-semibold text-gray-700">Catatan untuk Redaksi (Opsional)</label>
                    <textarea 
                        id="author_notes" 
                        name="author_notes" 
                        rows="2" 
                        placeholder="Tambahkan catatan jika diperlukan, misal referensi transaksi atau berita transfer..."
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"
                    ></textarea>
                </div>

                <!-- Tombol Aksi Modal -->
                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        onclick="document.getElementById('modal-upload-pembayaran').classList.add('hidden'); document.getElementById('modal-upload-pembayaran').classList.remove('flex');"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded-lg transition cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 active:bg-amber-800 rounded-lg transition cursor-pointer shadow-xs"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Kirim Bukti Pembayaran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Modal Detail Linimasa Aktivitas Editorial (Audit Trail Lengkap) -->
@if(!empty($submission['timeline']))
    <div 
        id="modal-detail-linimasa" 
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-gray-950/60 backdrop-blur-xs transition-opacity duration-200 select-none"
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="modal-linimasa-title"
    >
        <div class="relative w-full max-w-xl bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Header Modal -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <div class="flex items-center gap-2.5 text-gray-900">
                    <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-linimasa-title" class="text-sm font-bold text-gray-900 leading-snug">Detail Linimasa Aktivitas</h3>
                        <p class="text-[11px] text-gray-500">Jejak kronologis proses editorial naskah {{ $submission['submission_number'] }}</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    onclick="closeTimelineModal()"
                    class="w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition cursor-pointer"
                    aria-label="Tutup dialog"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body Modal: Linimasa Lengkap -->
            <div class="p-6 max-h-[65vh] overflow-y-auto space-y-4">
                <div class="border-l-2 border-brand-maroon/30 pl-4 space-y-5">
                    @foreach($submission['timeline'] as $event)
                        <div class="relative text-xs space-y-1">
                            <div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full border-2 border-white bg-brand-maroon"></div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <span class="font-bold text-gray-900 text-xs sm:text-sm">{{ $event['title'] }}</span>
                                <span class="text-[11px] text-gray-400 tabular-nums font-medium">{{ $event['date'] }}</span>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">{{ $event['description'] }}</p>
                            <div class="pt-0.5">
                                <span class="inline-block text-[10px] font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                                    Aktor: {{ $event['actor'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="flex items-center justify-between px-6 py-3.5 border-t border-gray-100 bg-gray-50/80">
                <span class="text-[11px] text-gray-500 font-medium">Total {{ count($submission['timeline']) }} aktivitas tercatat</span>
                <button 
                    type="button" 
                    onclick="closeTimelineModal()"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition cursor-pointer shadow-2xs"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endif
