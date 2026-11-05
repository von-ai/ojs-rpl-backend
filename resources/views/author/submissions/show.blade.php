@extends('layouts.author')

@section('title', $submission['submission_number'] . ' — ' . $submission['title'] . ' — Portal Penulis BRIDA')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12 transition-all duration-150">

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @include('author.submissions.partials.copyedit-approval')
    <!-- Header & Breadcrumbs Navigasi -->
    @include('author.submissions.partials.detail-header', ['submission' => $submission])

    <!-- Workflow Tracker Stepper (6 Tahapan Alur Kerja Editorial OJS 3) -->
    @include('author.submissions.partials.detail-workflow-tracker', ['submission' => $submission])

    <!-- Tata Letak 2 Kolom Responsif (2/3 Konten Manuskrip, 1/3 Sidebar Editorial Sticky) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Kolom Konten Utama (2/3) -->
        <div class="lg:col-span-2 space-y-6 min-w-0">
            <!-- Metadata & Abstrak (Tipografi Lora Serif Sesuai guide-code.md) -->
            @include('author.submissions.partials.detail-metadata', ['submission' => $submission])

            <!-- Daftar Penulis & Kontributor Lengkap -->
            @include('author.submissions.partials.detail-contributors', ['submission' => $submission])

            <!-- Berkas Manuskrip, SHA-256, & Pindai Antivirus Microsoft Defender -->
            @include('author.submissions.partials.detail-files', ['submission' => $submission])
        </div>

        <!-- Kolom Samping Sticky (1/3): Status Kontekstual, Info Penerbit, & Linimasa Audit -->
        <aside class="lg:col-span-1 min-w-0 lg:sticky lg:top-6 self-start space-y-6">
            @include('author.submissions.partials.detail-sidebar', ['submission' => $submission])
        </aside>
    </div>

    <!-- Modals Interaktif Penulis (Unggah Revisi & Bukti Pembayaran) di Level Root agar Bebas Stacking Context -->
    @include('author.submissions.partials.detail-modals', ['submission' => $submission])

</div>

@push('scripts')
<script>
    /**
     * Helper Salin Teks ke Clipboard dengan Umpan Balik Visual Toast
     */
    function copyTextToClipboard(text, successMessage = 'Teks berhasil disalin ke papan klip') {
        if (!text) return;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                if (window.toast && window.toast.success) {
                    window.toast.success(successMessage, 'Tersalin');
                }
            }).catch(() => {
                fallbackCopyText(text, successMessage);
            });
        } else {
            fallbackCopyText(text, successMessage);
        }
    }

    function fallbackCopyText(text, successMessage) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            if (window.toast && window.toast.success) {
                window.toast.success(successMessage, 'Tersalin');
            }
        } catch (err) {
            if (window.toast && window.toast.error) {
                window.toast.error('Gagal menyalin teks ke papan klip.', 'Kesalahan');
            }
        }
        document.body.removeChild(textArea);
    }
</script>
@endpush
@endsection
