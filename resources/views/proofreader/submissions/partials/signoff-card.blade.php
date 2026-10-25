<div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-2xs space-y-4">
    <div class="border-b border-gray-100 pb-3">
        <h3 class="text-sm font-bold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Persetujuan Akhir (Sign-off)</span>
        </h3>
        <p class="text-xs text-gray-500 mt-0.5">
            Validasi kelaikan galai publikasi sebelum diteruskan ke Editor in Chief.
        </p>
    </div>

    @if($submission['is_completed'])
        <!-- Status Sudah Selesai Sign-off -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center space-y-2">
            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h4 class="text-xs font-bold text-emerald-900">Proofreading Selesai &amp; Disetujui</h4>
            <p class="text-[11px] text-emerald-700 leading-relaxed">
                Anda telah memberikan persetujuan akhir pada berkas galai ini. Galai siap untuk proses penerbitan terbitan (issue).
            </p>
        </div>
    @elseif($submission['summary']['can_signoff'])
        <!-- Siap Diberikan Sign-off (Guard Rail Lolos) -->
        <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-4 space-y-3">
            <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-emerald-900">Kelaikan Galai Terpenuhi</h4>
                    <p class="text-[11px] text-emerald-700 mt-0.5 leading-relaxed">
                        Seluruh catatan koreksi telah dituntaskan atau berkas galai telah sesuai dengan standar tipografi dan tata bahasa EYD V.
                    </p>
                </div>
            </div>

            <form action="{{ route('proofreader.complete') }}" method="POST">
                @csrf
                <input type="hidden" name="submission_id" value="{{ $submission['id'] }}">
                <button 
                    type="submit" 
                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 min-h-11 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg transition duration-150 shadow-2xs cursor-pointer"
                    title="Kirim Persetujuan Akhir Galai"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Setujui &amp; Selesaikan Proofreading</span>
                </button>
            </form>
        </div>
    @else
        <!-- Guard Rail Aktif: Catatan Masih Terbuka -->
        <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4 space-y-3">
            <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-900">Sign-off Ditangguhkan</h4>
                    <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                        {{ $submission['summary']['signoff_guard_reason'] }}
                    </p>
                </div>
            </div>

            <button 
                type="button" 
                disabled 
                class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 min-h-11 text-xs font-semibold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed"
                title="Selesaikan catatan koreksi terlebih dahulu"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Sign-off Terkunci (Ada Catatan Terbuka)</span>
            </button>
        </div>
    @endif
</div>
