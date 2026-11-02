@extends('layouts.reviewer')

@section('title', 'Ruang Telaah: ' . $viewModel->submissionNumber() . ' — SIRIDA Kota Makassar')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('reviewer.assignments.index') }}" class="hover:text-gray-900 flex items-center gap-1 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Daftar Penugasan</span>
                </a>
                <span>/</span>
                <span class="font-mono text-gray-700 font-bold">{{ $viewModel->submissionNumber() }}</span>
                <span>/</span>
                <span class="text-indigo-700 font-semibold">Putaran Telaah {{ $viewModel->assignment->reviewRound->round }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight leading-snug">
                {{ $viewModel->title() }}
            </h1>
        </div>

        @php
            $info = $viewModel->statusInfo();
        @endphp
        <div class="flex items-center gap-2 shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $info['badge_class'] }}">
                {{ $info['label'] }}
            </span>
        </div>
    </div>

    <!-- Double-Blind Notice Banner -->
    <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 flex items-center gap-3 text-xs text-indigo-950">
        <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
        </svg>
        <div class="leading-relaxed">
            <strong>Protokol Telaah Ganda Tertutup (Double-Blind):</strong> Seluruh identitas penulis, institusi asal, dan korespondensi telah disaring demi menjamin objektivitas evaluasi saintifik.
        </div>
    </div>

    <!-- Main Workspace Grid: 2 Kolom -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- KOLOM KIRI (5 Grid): Metadata Naskah, Berkas Manuskrip & Batas Waktu -->
        <div class="lg:col-span-5 space-y-6">

            <!-- 1. Informasi Naskah Anonim -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 border-b border-gray-100 pb-3">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Informasi Manuskrip Anonim</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase">Bidang Kajian:</span>
                        <span class="font-medium text-gray-800 text-sm">{{ $viewModel->sectionName() }}</span>
                    </div>

                    <div>
                        <span class="text-gray-500 block text-[11px] font-semibold uppercase mb-1">Abstrak Naskah:</span>
                        <div class="text-gray-700 leading-relaxed bg-gray-50 p-3.5 rounded-lg border border-gray-100 text-[11px] max-h-64 overflow-y-auto whitespace-pre-line text-left">
                            {{ $viewModel->abstract() }}
                        </div>
                    </div>

                    @if(! empty($viewModel->keywords()))
                        <div>
                            <span class="text-gray-500 block text-[11px] font-semibold uppercase mb-1">Kata Kunci:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($viewModel->keywords() as $keyword)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                        {{ $keyword }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Berkas Manuskrip Bersih Identitas -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Berkas Manuskrip Telaah</span>
                    </h3>
                    <span class="text-[10px] text-gray-400 uppercase font-mono">Bebas Identitas</span>
                </div>

                @if(! $viewModel->canDownloadManuscript())
                    <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                        <p class="font-semibold">Unduhan Terkunci</p>
                        <p class="text-[11px] text-amber-800">
                            @if($viewModel->isDeclined())
                                Penugasan telah ditolak. Akses berkas naskah dinonaktifkan.
                            @elseif($viewModel->isCancelled())
                                Penugasan telah dibatalkan oleh dewan redaksi.
                            @elseif($viewModel->isNoResponse())
                                Batas waktu respon undangan telah berakhir tanpa konfirmasi.
                            @else
                                Sesuai protokol double-blind, berkas manuskrip baru dapat diunduh setelah Anda menerima surat undangan penugasan naskah ini.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="space-y-2 text-xs">
                        @php
                            $filesToDownload = collect();
                            if ($viewModel->assignment->reviewRound?->anonymizedFile) {
                                $filesToDownload->push($viewModel->assignment->reviewRound->anonymizedFile);
                            } elseif ($viewModel->assignment->reviewRound?->manuscriptFile) {
                                $filesToDownload->push($viewModel->assignment->reviewRound->manuscriptFile);
                            } else {
                                foreach($viewModel->assignment->reviewRound->submission->files as $sf) {
                                    if ($sf->file) {
                                        $filesToDownload->push($sf->file);
                                    }
                                }
                            }
                        @endphp

                        @forelse($filesToDownload as $fileEntry)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-2.5 truncate min-w-0 pr-2">
                                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/>
                                    </svg>
                                    <div class="truncate">
                                        <span class="font-medium text-gray-800 truncate block">
                                            {{ $fileEntry->original_name ?: 'Manuskrip_Anonim.pdf' }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-mono">
                                            {{ number_format(($fileEntry->byte_size ?? $fileEntry->file_size ?? 0) / 1024, 1) }} KB
                                        </span>
                                    </div>
                                </div>
                                
                                <a 
                                    href="{{ route('reviewer.files.download', ['id' => $viewModel->assignment->id, 'fileId' => $fileEntry->id]) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-semibold text-xs shadow-2xs shrink-0 transition"
                                >
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span>Unduh</span>
                                </a>
                            </div>
                        @empty
                            <p class="text-gray-400 italic text-[11px] text-center py-2">Berkas naskah belum diunggah dewan editor.</p>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- 3. Batas Waktu & Permohonan Perpanjangan Waktu -->
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Tenggat Waktu Penelaahan</span>
                    </h3>
                    @if($viewModel->canSubmit() && ! $viewModel->pendingExtension())
                        <button 
                            type="button" 
                            onclick="document.getElementById('modalExtension').classList.remove('hidden')" 
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold"
                        >
                            + Minta Perpanjangan
                        </button>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-gray-50">
                        <span class="text-gray-500">Batas Respon Undangan:</span>
                        <span class="font-semibold text-gray-800 tabular-nums">
                            {{ $viewModel->assignment->response_due_at ? $viewModel->assignment->response_due_at->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500">Batas Selesai Telaah:</span>
                        <span class="font-bold text-gray-900 tabular-nums">
                            {{ $viewModel->assignment->review_due_at ? $viewModel->assignment->review_due_at->format('d M Y') : '-' }}
                        </span>
                    </div>

                    @if($viewModel->assignment->responded_at)
                        <div class="flex justify-between items-center py-1 border-t border-gray-50">
                            <span class="text-gray-500">Waktu Respon:</span>
                            <span class="font-semibold text-gray-800 tabular-nums">
                                {{ $viewModel->assignment->responded_at->format('d M Y H:i') }}
                            </span>
                        </div>
                    @endif

                    @if($viewModel->daysRemaining() !== null && $viewModel->canSubmit())
                        @php $days = $viewModel->daysRemaining(); @endphp
                        <div class="pt-1">
                            @if($days < 0)
                                <div class="p-2 rounded bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold text-center">
                                    Batas waktu telah terlewati {{ abs($days) }} hari yang lalu.
                                </div>
                            @elseif($days <= 3)
                                <div class="p-2 rounded bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold text-center">
                                    Tenggat waktu tersisa {{ $days }} hari lagi.
                                </div>
                            @else
                                <div class="p-2 rounded bg-gray-50 text-gray-600 text-[11px] text-center">
                                    Tenggat waktu tersisa {{ $days }} hari.
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($viewModel->pendingExtension())
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mt-3 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-amber-900">Permohonan Perpanjangan Terkirim</span>
                                <span class="text-[10px] font-bold bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded">Menunggu Editor</span>
                            </div>
                            <p class="text-[11px] text-amber-800">
                                Dimohonkan hingga: <strong class="tabular-nums">{{ $viewModel->pendingExtension()->requested_due_at->format('d M Y') }}</strong><br>
                                Alasan: <span class="italic">"{{ $viewModel->pendingExtension()->reason }}"</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (7 Grid): Lembar Evaluasi & Rekomendasi -->
        <div class="lg:col-span-7 space-y-6">

            <!-- KASUS 1: Undangan Masih Menunggu Respon (Status: invited) -->
            @if($viewModel->isInvited())
                <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-2xs space-y-6 text-center">
                    <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-lg font-bold text-gray-900">
                            Konfirmasi Kesediaan Menelaah Naskah
                        </h2>
                        <p class="text-xs text-gray-600 max-w-md mx-auto leading-relaxed">
                            Dewan Redaksi mengundang Anda untuk menelaah naskah ini berdasarkan rekam jejak riset dan kepakaran ilmiah Anda. Silakan konfirmasi kesediaan sebelum batas respon: 
                            <strong class="text-gray-900 tabular-nums">{{ $viewModel->assignment->response_due_at ? $viewModel->assignment->response_due_at->format('d M Y') : '-' }}</strong>.
                        </p>
                    </div>

                    @if($viewModel->assignment->invitation_message)
                        <div class="bg-gray-50 border border-gray-200 p-4 rounded-xl text-left text-xs max-w-lg mx-auto space-y-1">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Pesan dari Editor Bagian:</span>
                            <p class="text-gray-700 italic">"{{ $viewModel->assignment->invitation_message }}"</p>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                        <form action="{{ route('reviewer.assignments.respond', $viewModel->assignment->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="response" value="accepted">
                            <button 
                                type="submit" 
                                class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-2xs transition"
                            >
                                Terima Undangan Telaah
                            </button>
                        </form>

                        <button 
                            type="button" 
                            onclick="document.getElementById('modalDecline').classList.remove('hidden')" 
                            class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition"
                        >
                            Tolak Undangan
                        </button>
                    </div>
                </div>

                <!-- Modal Penolakan Undangan -->
                <div id="modalDecline" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
                    <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-gray-900 text-sm">Konfirmasi Penolakan Undangan Telaah</h3>
                            <button type="button" onclick="document.getElementById('modalDecline').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                        </div>

                        <form action="{{ route('reviewer.assignments.respond', $viewModel->assignment->id) }}" method="POST" class="space-y-4 text-xs">
                            @csrf
                            <input type="hidden" name="response" value="declined">
                            
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Alasan Penolakan (Opsional)</label>
                                <textarea name="reason" rows="3" placeholder="Contoh: Berhalangan karena tugas riset lain / bidang kepakaran tidak sepenuhnya sesuai..." class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                                <button type="button" onclick="document.getElementById('modalDecline').classList.add('hidden')" class="px-3.5 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-semibold">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg shadow-2xs transition">
                                    Tolak Penugasan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <!-- KASUS 2: Undangan Ditolak (Status: declined) -->
            @elseif($viewModel->isDeclined())
                <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-2xs space-y-5 text-center">
                    <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-100">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-lg font-bold text-gray-900">
                            Undangan Penelaahan Ditolak
                        </h2>
                        <p class="text-xs text-gray-600 max-w-md mx-auto leading-relaxed">
                            Anda telah menolak undangan penelaahan untuk naskah ini. Akses pengisian lembar evaluasi dan unduhan berkas manuskrip dinonaktifkan.
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl max-w-md mx-auto text-xs text-left space-y-2">
                        <div class="flex items-center justify-between text-gray-500 text-[11px]">
                            <span>Status Penugasan:</span>
                            <span class="font-bold text-gray-700">Undangan Ditolak</span>
                        </div>
                        @if($viewModel->assignment->responded_at)
                            <div class="flex items-center justify-between text-gray-500 text-[11px]">
                                <span>Waktu Konfirmasi:</span>
                                <span class="font-semibold text-gray-800 tabular-nums">
                                    {{ $viewModel->assignment->responded_at->format('d M Y, H:i') }} UTC
                                </span>
                            </div>
                        @endif
                        <p class="text-[11px] text-gray-500 pt-1 border-t border-gray-200 leading-relaxed">
                            Dewan Redaksi telah menerima tanggapan Anda dan akan menugaskan mitra bestari pengganti. Terima kasih atas konfirmasi Anda.
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('reviewer.assignments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Kembali ke Daftar Penugasan</span>
                        </a>
                    </div>
                </div>

            <!-- KASUS 3: Penugasan Dibatalkan (Status: cancelled) -->
            @elseif($viewModel->isCancelled())
                <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-2xs space-y-5 text-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center mx-auto border border-gray-200">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-lg font-bold text-gray-900">
                            Penugasan Telaah Dibatalkan
                        </h2>
                        <p class="text-xs text-gray-600 max-w-md mx-auto leading-relaxed">
                            Penugasan penelaahan untuk naskah ini telah dibatalkan oleh Dewan Redaksi. Anda tidak perlu lagi melakukan evaluasi terhadap naskah ini.
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('reviewer.assignments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Kembali ke Daftar Penugasan</span>
                        </a>
                    </div>
                </div>

            <!-- KASUS 4: Batas Waktu Respon Telah Berakhir (Status: no_response) -->
            @elseif($viewModel->isNoResponse())
                <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-2xs space-y-5 text-center">
                    <div class="w-14 h-14 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mx-auto border border-orange-200">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-lg font-bold text-gray-900">
                            Batas Waktu Respon Undangan Telah Berakhir
                        </h2>
                        <p class="text-xs text-gray-600 max-w-md mx-auto leading-relaxed">
                            Batas waktu konfirmasi undangan penelaahan naskah ini telah berakhir tanpa konfirmasi. Akses lembar evaluasi dinonaktifkan.
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('reviewer.assignments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Kembali ke Daftar Penugasan</span>
                        </a>
                    </div>
                </div>

            <!-- KASUS 5: Undangan Diterima (Status: accepted, overdue, completed) -->
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-2xs space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Lembar Evaluasi Penelaahan Sejawat</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">
                            Isi penilaian rubrik secara objektif, berikan masukan konstruktif untuk penulis, dan tentukan rekomendasi putusan ilmiah.
                        </p>
                    </div>

                    @if($viewModel->isCompleted())
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 space-y-2">
                            <div class="flex items-center justify-between font-bold">
                                <span>Hasil Telaah Telah Dikirim Resmi (Terkunci)</span>
                                <span class="bg-emerald-200 text-emerald-900 px-2 py-0.5 rounded uppercase">
                                    {{ $viewModel->existingReview()?->recommendation }}
                                </span>
                            </div>
                            <p class="text-emerald-800">
                                Diserahkan pada: <span class="tabular-nums font-semibold">{{ $viewModel->existingReview()?->submitted_at?->format('d M Y H:i') }} UTC</span>. Terima kasih atas dedikasi dan kontribusi keilmuan Anda!
                            </p>
                        </div>
                    @endif

                    <form action="{{ route('reviewer.assignments.submit', $viewModel->assignment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
                        @csrf

                        <!-- 1. Kriteria Rubrik Penilaian -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <h3 class="font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                    1. Rubrik Penilaian Kualitas Naskah (Skor 0 - 100)
                                </h3>
                                <span class="text-[10px] text-gray-400">Skala Baku</span>
                            </div>

                            <div class="space-y-3">
                                @foreach($viewModel->rubricCriteria as $key => $criteria)
                                    @php
                                        $existingScore = $viewModel->existingReview()?->scores[$key] ?? 80;
                                    @endphp
                                    <div class="p-3.5 bg-gray-50/70 rounded-xl border border-gray-200/80 space-y-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-bold text-gray-800 text-xs">{{ $criteria['label'] }} ({{ $criteria['weight'] }}%)</span>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    max="100" 
                                                    name="scores[{{ $key }}]" 
                                                    value="{{ old('scores.'.$key, $existingScore) }}" 
                                                    {{ $viewModel->isCompleted() ? 'disabled' : '' }} 
                                                    required 
                                                    class="w-16 rounded-lg border border-gray-300 p-1.5 text-center font-bold text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon tabular-nums"
                                                >
                                                <span class="text-gray-400 font-medium">/ 100</span>
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-gray-500 leading-relaxed text-left">{{ $criteria['description'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 2. Masukan untuk Penulis (Comments for Author) -->
                        <div class="space-y-2">
                            <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                2. Masukan Konstruktif untuk Penulis <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[11px] text-gray-500">
                                Catatan ini akan diteruskan kepada penulis secara anonim sebagai panduan perbaikan naskah.
                            </p>
                            <textarea 
                                name="comments_for_author" 
                                rows="5" 
                                required 
                                {{ $viewModel->isCompleted() ? 'disabled' : '' }} 
                                placeholder="Tuliskan evaluasi mengenai kekuatan naskah, kelemahan metodologi, referensi yang perlu diperkaya, dan saran perbaikan spesifik..." 
                                class="w-full rounded-lg border border-gray-300 p-3 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon leading-relaxed text-left"
                            >{{ old('comments_for_author', $viewModel->existingReview()?->comments_for_author) }}</textarea>
                        </div>

                        <!-- 3. Catatan Rahasia untuk Dewan Redaksi (Confidential Comments for Editor) -->
                        <div class="space-y-2">
                            <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                3. Catatan Rahasia untuk Dewan Redaksi (Opsional)
                            </label>
                            <p class="text-[11px] text-gray-500">
                                Catatan tertutup yang hanya dapat dibaca oleh Editor Bagian dan Pemimpin Redaksi (tidak dapat dilihat oleh penulis).
                            </p>
                            <textarea 
                                name="confidential_comments_for_editor" 
                                rows="3" 
                                {{ $viewModel->isCompleted() ? 'disabled' : '' }} 
                                placeholder="Informasi rahasia, keraguan orisinalitas, atau catatan etis yang perlu diperhatikan dewan editor..." 
                                class="w-full rounded-lg border border-gray-300 p-3 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon leading-relaxed text-left"
                            >{{ old('confidential_comments_for_editor', $viewModel->existingReview()?->confidential_comments_for_editor) }}</textarea>
                        </div>

                        <!-- 4. Rekomendasi Putusan (Recommendation) -->
                        <div class="space-y-2">
                            <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                4. Rekomendasi Putusan Ilmiah <span class="text-rose-500">*</span>
                            </label>
                            <x-custom-dropdown
                                name="recommendation"
                                :value="old('recommendation', $viewModel->existingReview()?->recommendation)"
                                :disabled="$viewModel->isCompleted()"
                                :required="true"
                                :options="[
                                    '' => '-- Pilih Rekomendasi --',
                                    'accept' => 'Diterima (Accept Submission) — Naskah layak terbit tanpa revisi mayor',
                                    'revisions_required' => 'Perlu Revisi (Revisions Required) — Penulis wajib melakukan revisi minor',
                                    'resubmit' => 'Kirim Ulang untuk Telaah (Resubmit for Review) — Perlu perombakan besar dan putaran telaah baru',
                                    'decline' => 'Ditolak (Decline Submission) — Naskah tidak memenuhi standar ilmiah jurnal',
                                ]"
                                button-class="w-full bg-white border-gray-300 text-gray-900 rounded-lg p-2.5"
                                panel-class="w-full max-w-full"
                            />
                        </div>

                        <!-- 5. Unggah Berkas Manuskrip Beranotasi (Optional) -->
                        @if(! $viewModel->isCompleted())
                            <div class="space-y-2">
                                <label class="block font-bold text-gray-900 uppercase text-[11px] tracking-wider">
                                    5. Unggah Berkas Manuskrip Beranotasi (Opsional)
                                </label>
                                <input 
                                    type="file" 
                                    name="annotated_file" 
                                    accept=".pdf,.docx" 
                                    class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                                >
                                <p class="text-[10px] text-gray-400">Format yang diterima: PDF atau DOCX, maksimal 20 MB.</p>
                            </div>
                        @endif

                        <!-- 6. Pernyataan Bebas Konflik Kepentingan (Wajib Centang) -->
                        @if(! $viewModel->isCompleted())
                            <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="certify_no_conflict" 
                                        value="1" 
                                        required 
                                        class="mt-0.5 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon"
                                    >
                                    <span class="text-xs text-amber-950 font-medium leading-relaxed text-left">
                                        <strong>Pernyataan Integritas Saintifik:</strong> Saya menyatakan dengan sungguh-sungguh bahwa saya tidak memiliki konflik kepentingan (conflict of interest) baik secara profesional, finansial, maupun personal terhadap naskah dan penulis yang dievaluasi.
                                    </span>
                                </label>
                            </div>
                        @endif

                        <!-- Tombol Aksi Simpan Draf & Submit -->
                        @if(! $viewModel->isCompleted())
                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                                <button 
                                    type="submit" 
                                    formaction="{{ route('reviewer.assignments.draft', $viewModel->assignment->id) }}" 
                                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition"
                                >
                                    Simpan Draf Evaluasi
                                </button>
                                <button 
                                    type="submit" 
                                    class="px-5 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover text-white font-bold rounded-lg shadow-2xs transition"
                                >
                                    Kirim Hasil Telaah Resmi
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>

<!-- MODAL: Permohonan Perpanjangan Waktu (Extension Request) -->
@if($viewModel->canSubmit() && ! $viewModel->pendingExtension())
    <div id="modalExtension" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-gray-900 text-sm">Permohonan Perpanjangan Batas Waktu</h3>
                <button type="button" onclick="document.getElementById('modalExtension').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('reviewer.assignments.extension', $viewModel->assignment->id) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Batas Waktu Baru yang Dimohonkan <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="requested_due_at" 
                        required 
                        min="{{ now()->addDay()->format('Y-m-d') }}" 
                        value="{{ $viewModel->assignment->review_due_at ? $viewModel->assignment->review_due_at->addDays(7)->format('Y-m-d') : now()->addDays(7)->format('Y-m-d') }}" 
                        class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon"
                    >
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Alasan Permohonan Waktu Tambahan <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="reason" 
                        rows="3" 
                        required 
                        placeholder="Jelaskan alasan ilmiah permohonan waktu tambahan (misal: verifikasi metode empiris atau kajian pustaka lanjutan)..." 
                        class="w-full rounded-lg border border-gray-300 p-2 text-xs focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon leading-relaxed text-left"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modalExtension').classList.add('hidden')" class="px-3.5 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-2xs transition">
                        Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
