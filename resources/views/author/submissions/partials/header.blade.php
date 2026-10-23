<!-- Breadcrumb & Navigasi Kembali -->
<div class="flex items-center justify-between gap-4">
    <nav class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 font-medium">
        <a href="{{ route('author.submissions.index') }}" class="hover:text-brand-maroon transition inline-flex items-center gap-1.5">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Naskah Saya</span>
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold truncate">Kirim Naskah Baru</span>
    </nav>
</div>

<!-- Alert Notifikasi Draf Formulir -->
<div id="draft-restore-alert" class="hidden rounded-lg bg-amber-50 border border-amber-200 p-4 transition-all duration-200 shadow-2xs">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
            <div class="shrink-0 text-amber-600 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-xs sm:text-sm font-bold text-amber-900">Draf Formulir Sebelumnya Terdeteksi</h3>
                <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                    Data isian formulir sebelumnya telah dipulihkan otomatis agar Anda dapat langsung melanjutkan pengisian naskah.
                </p>
            </div>
        </div>
        <!-- Tombol Bersihkan Draf (Icon Delete dengan Keterangan saat Hover) -->
        <div class="relative shrink-0 flex items-center group">
            <button 
                type="button" 
                id="btn-discard-draft" 
                class="p-1.5 text-amber-700 hover:text-red-700 hover:bg-amber-100/80 rounded-lg transition active:scale-[0.96] cursor-pointer"
                title="Bersihkan Draf"
                aria-label="Bersihkan Draf"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18" />
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    <line x1="10" y1="11" x2="10" y2="17" />
                    <line x1="14" y1="11" x2="14" y2="17" />
                </svg>
            </button>
            <!-- Tooltip Keterangan saat di-hover -->
            <div class="pointer-events-none absolute right-0 bottom-full mb-1.5 hidden group-hover:flex items-center px-2.5 py-1 rounded-md bg-gray-900 text-white text-[11px] font-medium shadow-md whitespace-nowrap z-30 transition-opacity">
                <span>Bersihkan Draf</span>
                <div class="absolute -bottom-1 right-2.5 w-2 h-2 bg-gray-900 rotate-45"></div>
            </div>
        </div>
    </div>
</div>

<!-- Error Validation Feedback Alert -->
@if($errors->any())
    <div class="rounded-lg bg-red-50 border border-red-200 p-4 shadow-2xs" role="alert">
        <div class="flex items-start gap-3">
            <div class="shrink-0 text-red-600 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-red-900">Periksa Kembali Isian Formulir Anda</h3>
                <ul class="mt-1.5 list-disc list-inside text-xs text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Header Judul Halaman -->
<div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-2xs">
    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">
        Formulir Pengajuan Naskah Baru
    </h1>
    <p class="mt-1 text-xs sm:text-sm text-gray-500 leading-relaxed">
        Silakan lengkapi seluruh 5 langkah tahapan pengajuan naskah ilmiah sesuai ketentuan editorial Jurnal BRIDA Kota Makassar.
    </p>

    <!-- Stepper Desktop (Horizontal) -->
    <div class="hidden md:block mt-6 pt-4 border-t border-gray-100">
        <nav aria-label="Progress">
            <ol role="list" class="flex items-center w-full">
                @foreach($viewModel->wizardSteps() as $stepItem)
                    <li class="relative {{ ! $loop->last ? 'flex-1' : '' }} flex items-center min-w-0" data-step-nav="{{ $stepItem['step'] }}">
                        <div class="flex items-center gap-2 sm:gap-2.5 min-w-0 shrink-0">
                            <div class="step-badge flex items-center justify-center w-8 h-8 rounded-full border-2 text-xs font-bold transition duration-200 shrink-0 {{ $stepItem['step'] === 1 ? 'border-brand-maroon bg-brand-maroon text-white shadow-2xs' : 'border-gray-300 bg-white text-gray-500' }}">
                                <span class="step-num tabular-nums">{{ $stepItem['step'] }}</span>
                                <svg class="step-check hidden w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="step-title text-xs font-bold transition truncate {{ $stepItem['step'] === 1 ? 'text-brand-maroon' : 'text-gray-500' }}">
                                    {{ $stepItem['short_title'] }}
                                </p>
                            </div>
                        </div>
                        @if(! $loop->last)
                            <div class="step-line flex-1 h-0.5 bg-gray-200 mx-2.5 sm:mx-3" aria-hidden="true"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    <!-- Stepper Mobile (Bar Progres) -->
    <div class="block md:hidden mt-4 pt-4 border-t border-gray-100">
        <div class="flex items-center justify-between text-xs font-semibold mb-2">
            <span class="text-brand-maroon">
                Langkah <span id="mobile-step-num" class="tabular-nums">1</span> dari 5:
                <span id="mobile-step-name" class="text-gray-900 font-bold ml-1">Ketentuan & Seksi</span>
            </span>
            <span id="mobile-step-pct" class="text-gray-500 tabular-nums font-bold">20%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
            <div id="mobile-progress-bar" class="bg-brand-maroon h-2 rounded-full transition-all duration-300" style="width: 20%;"></div>
        </div>
    </div>
</div>
