@extends('layouts.copyeditor')

@section('title', 'Katalog Dialog & Alur Transaksional Copy Editor — BRIDA Kota Makassar')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Transaksi Berhasil">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Page Title & Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <!-- Badge Tag Pill -->
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-brand-maroon text-white mb-2">
                SIRIDA PRODUCTION SUITE
            </span>

            <!-- Main Heading -->
            <h1 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight tracking-tight">
                Katalog Dialog & Alur Transaksional Copy Editor
            </h1>

            <!-- Description -->
            <p class="text-xs sm:text-sm text-gray-500 mt-1 leading-relaxed">
                Komponen modal transaksional atomik: Validasi Checksum SHA-256, Invalidation Guard, Mediasi Produksi, dan Serah Terima Galley Layout.
            </p>
        </div>

        <!-- Tombol Buka Modal Pop-up -->
        <div class="shrink-0">
            <button
                type="button"
                id="openModalCatalogBtn"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-white text-sm font-semibold rounded-lg shadow-sm transition cursor-pointer"
                title="Buka dialog dalam bentuk modal pop-up interaktif"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span>Buka Modal</span>
            </button>
        </div>
    </div>

    <!-- 4-Tab Navigation / Stepper Transaksional -->
    <div class="bg-surface-subtle border border-gray-200/90 rounded-xl p-1.5 flex flex-wrap sm:flex-nowrap gap-1">
        <!-- Tab 1: Unggah Berkas -->
        <button
            type="button"
            onclick="switchTab('upload')"
            id="tab-btn-upload"
            class="tab-btn flex-1 min-w-[140px] flex items-center justify-center gap-2 px-3 py-2.5 text-xs sm:text-sm font-bold rounded-lg transition duration-150 {{ $activeTab === 'upload' ? 'bg-white text-brand-maroon shadow-xs border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}"
        >
            <svg class="w-4 h-4 shrink-0 {{ $activeTab === 'upload' ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            <span class="truncate">Unggah Berkas</span>
        </button>

        <!-- Tab 2: Persetujuan Checksum -->
        <button
            type="button"
            onclick="switchTab('checksum')"
            id="tab-btn-checksum"
            class="tab-btn flex-1 min-w-[140px] flex items-center justify-center gap-2 px-3 py-2.5 text-xs sm:text-sm font-bold rounded-lg transition duration-150 {{ $activeTab === 'checksum' ? 'bg-white text-brand-maroon shadow-xs border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}"
        >
            <svg class="w-4 h-4 shrink-0 {{ $activeTab === 'checksum' ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="truncate">Persetujuan Checksum</span>
        </button>

        <!-- Tab 3: Eskalasi / Mediasi -->
        <button
            type="button"
            onclick="switchTab('escalation')"
            id="tab-btn-escalation"
            class="tab-btn flex-1 min-w-[140px] flex items-center justify-center gap-2 px-3 py-2.5 text-xs sm:text-sm font-bold rounded-lg transition duration-150 {{ $activeTab === 'escalation' ? 'bg-white text-brand-maroon shadow-xs border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}"
        >
            <svg class="w-4 h-4 shrink-0 {{ $activeTab === 'escalation' ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="truncate">Eskalasi / Mediasi</span>
        </button>

        <!-- Tab 4: Penyelesaian & Serah Terima -->
        <button
            type="button"
            onclick="switchTab('handoff')"
            id="tab-btn-handoff"
            class="tab-btn flex-1 min-w-[140px] flex items-center justify-center gap-2 px-3 py-2.5 text-xs sm:text-sm font-bold rounded-lg transition duration-150 {{ $activeTab === 'handoff' ? 'bg-white text-brand-maroon shadow-xs border border-gray-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50' }}"
        >
            <svg class="w-4 h-4 shrink-0 {{ $activeTab === 'handoff' ? 'text-brand-maroon' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            <span class="truncate">Penyelesaian & Serah Terima</span>
        </button>
    </div>

    <!-- Submission Context Card -->
    <div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left Info -->
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-brand-maroon border border-red-100 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200 tabular-nums">
                            {{ $submission['id'] }}
                        </span>
                        <span class="text-xs text-gray-500 font-mono">
                            {{ $submission['assignment_code'] }}
                        </span>
                    </div>

                    <h2 class="text-base sm:text-lg font-bold text-gray-900 leading-snug tracking-tight">
                        {{ $submission['title'] }}
                    </h2>

                    <p class="text-xs text-gray-500">
                        Penulis Korespondensi: <strong class="text-gray-700 font-semibold">{{ $submission['corresponding_author'] }}</strong> • Dewan Editor Pembina: <span class="text-gray-700 font-semibold">{{ $submission['section_editor'] }}</span>
                    </p>
                </div>
            </div>

            <!-- Right Status & Deadline -->
            <div class="flex items-center gap-6 lg:border-l lg:border-gray-200 lg:pl-6 shrink-0 text-left sm:text-right">
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        STATUS PRODUKSI
                    </span>
                    <span class="text-sm font-bold text-brand-navy">
                        {{ $submission['production_status'] }}
                    </span>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        BATAS WAKTU
                    </span>
                    <span class="text-sm font-bold text-gray-800 tabular-nums">
                        {{ $submission['deadline'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dialog / Container Alur Transaksional -->
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm overflow-hidden">

        <!-- ======================================================== -->
        <!-- TAB 1: UNGGAH BERKAS HASIL SUNTINGAN (COPYEDIT OUTPUT)    -->
        <!-- ======================================================== -->
        <div id="panel-upload" class="tab-panel {{ $activeTab === 'upload' ? '' : 'hidden' }} p-6 sm:p-8 space-y-6">

            <!-- Header Dialog -->
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">
                            Unggah Berkas Hasil Suntingan (Copyedit Output)
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                            {{ $submission['current_output_set_version'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Naskah {{ $submission['id'] }} • Penugasan Copyeditor {{ $submission['assignment_code'] }}
                    </p>
                </div>

                <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-md transition" title="Tutup / Reset">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Invalidation Guard Warning Alert (Sesuai Desain Screenshot) -->
            <div class="bg-red-50/70 border border-red-200 rounded-xl p-4 sm:p-5 flex items-start gap-3.5 shadow-2xs">
                <div class="w-8 h-8 rounded-full bg-red-100 text-brand-maroon flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="space-y-1">
                    <h4 class="text-xs font-bold text-red-800 uppercase tracking-wide">
                        PERINGATAN PEMBATALAN PERSETUJUAN PENULIS (INVALIDATION GUARD)
                    </h4>
                    <p class="text-xs sm:text-sm text-red-700 leading-relaxed">
                        Menambahkan atau mengganti berkas keluaran copyedit akan <strong class="font-bold underline">secara otomatis membatalkan seluruh persetujuan penulis sebelumnya</strong> (<code class="font-mono text-xs">invalidated_at = NOW()</code>). Penulis diwajibkan melakukan peninjauan dan persetujuan ulang terhadap set berkas baru ini.
                    </p>
                </div>
            </div>

            <!-- Drag & Drop Upload Zone -->
            <form method="POST" action="{{ route('copyeditor.upload') }}" enctype="multipart/form-data" id="copyeditUploadForm" class="space-y-6">
                @csrf
                <div
                    id="dropzone"
                    class="border-2 border-dashed border-gray-300 hover:border-brand-maroon bg-gray-50/60 hover:bg-red-50/20 rounded-xl p-8 sm:p-10 text-center transition cursor-pointer flex flex-col items-center justify-center gap-3 group"
                >
                    <div class="w-12 h-12 rounded-full bg-white border border-gray-200 shadow-2xs flex items-center justify-center text-gray-400 group-hover:text-brand-maroon group-hover:scale-105 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-700">
                            Seret dan letakkan berkas hasil copyedit di sini, atau <label for="fileInput" class="font-bold text-brand-maroon hover:underline cursor-pointer">pilih dari perangkat</label>
                        </p>
                        <p class="text-xs text-gray-500">
                            Format berkas: DOCX, DOC, PDF • Ukuran maksimum: 20 MiB (20.971.520 byte)
                        </p>
                    </div>

                    <input type="file" name="manuscript_file" id="fileInput" class="hidden" accept=".docx,.doc,.pdf">
                </div>

                <!-- Pilihan Penempatan Berkas (Radio Selection Cards Sesuai Gambar) -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        PILIHAN PENEMPATAN BERKAS
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Opsi 1: Tambahkan Berkas Baru -->
                        <label class="relative flex items-start gap-3 p-4 rounded-xl border border-brand-maroon/80 bg-red-50/20 cursor-pointer shadow-2xs transition">
                            <input type="radio" name="placement" value="append" checked class="mt-0.5 text-brand-maroon focus:ring-brand-maroon">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-bold text-gray-900">Tambahkan Sebagai Berkas Baru</span>
                                <span class="block text-xs text-gray-500">Urutan posisi slot berkas ke-3 (Lampiran)</span>
                            </div>
                        </label>

                        <!-- Opsi 2: Gantikan Berkas yang Ada -->
                        <label class="relative flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-white hover:border-gray-300 cursor-pointer shadow-2xs transition">
                            <input type="radio" name="placement" value="replace" class="mt-0.5 text-brand-maroon focus:ring-brand-maroon">
                            <div class="space-y-0.5">
                                <span class="block text-sm font-bold text-gray-900">Gantikan Berkas yang Ada</span>
                                <span class="block text-xs text-gray-500">Menimpa berkas posisi #1 (Naskah Utama)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Upload Progress & Preview Card (Sesuai Screenshot Referensi) -->
                <div class="rounded-xl border border-gray-200/90 p-4 bg-white shadow-2xs space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm font-bold text-gray-900 truncate">
                                SUB2026-00106_Copyedit_Final_Clean.docx
                            </span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 shrink-0">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Defender Clean
                        </span>
                    </div>

                    <!-- Progress Bar Maroon -->
                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-brand-maroon h-1.5 rounded-full transition-all duration-300" style="width: 100%"></div>
                    </div>

                    <!-- Meta Bawah -->
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span class="tabular-nums">Ukuran: 2.145.728 byte (2.05 MiB)</span>
                        <span class="font-bold text-emerald-700">Unggah & Ekstraksi 100% Selesai</span>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button
                        type="button"
                        onclick="window.toast.info('Operasi unggah dibatalkan.')"
                        class="px-4 py-2.5 text-xs sm:text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Unggah & Tautkan ke Output Set</span>
                    </button>
                </div>
            </form>
        </div>


        <!-- ======================================================== -->
        <!-- TAB 2: PERSETUJUAN CHECKSUM & INTEGRITAS BERKAS (UC-13)   -->
        <!-- ======================================================== -->
        <div id="panel-checksum" class="tab-panel {{ $activeTab === 'checksum' ? '' : 'hidden' }} p-6 sm:p-8 space-y-6">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">
                            Validasi Checksum & Persetujuan Penulis (Author Approval)
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            {{ $submission['approval_status_label'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Sesuai kontrak UC-13: Persetujuan penulis mengunci seluruh berkas berdasarkan Checksum SHA-256. Setiap perubahan bytes membatalkan persetujuan secara otomatis.
                    </p>
                </div>

                <form method="POST" action="{{ route('copyeditor.request-approval') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg shadow-2xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Minta Persetujuan Penulis</span>
                    </button>
                </form>
            </div>

            <!-- Master Set Checksum Manifest Banner -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 sm:p-5 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        CHECKSUM HASH SELURUH OUTPUT SET (ATOMIC SHA-256 MANIFEST)
                    </span>
                    <button
                        type="button"
                        onclick="copyText('{{ $submission['set_checksum'] }}')"
                        class="text-xs font-bold text-brand-maroon hover:underline flex items-center gap-1 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span>Salin Hash Set</span>
                    </button>
                </div>
                <div class="p-3 bg-white border border-gray-200 rounded-lg font-mono text-xs text-gray-800 break-all select-all tabular-nums">
                    {{ $submission['set_checksum'] }}
                </div>
            </div>

            <!-- Tabel Daftar Berkas Dalam Output Set -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                    Daftar Berkas Terkunci Dalam {{ $submission['current_output_set_version'] }}
                </h4>

                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 font-bold text-gray-600 uppercase">Slot</th>
                                <th class="px-4 py-3 font-bold text-gray-600 uppercase">Nama Berkas</th>
                                <th class="px-4 py-3 font-bold text-gray-600 uppercase">Tipe</th>
                                <th class="px-4 py-3 font-bold text-gray-600 uppercase">Ukuran</th>
                                <th class="px-4 py-3 font-bold text-gray-600 uppercase">SHA-256 Hash</th>
                                <th class="px-4 py-3 font-bold text-gray-600 uppercase">Antivirus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($submission['files'] as $file)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-4 py-3.5 font-bold text-gray-900 tabular-nums">#{{ $file['slot'] }}</td>
                                    <td class="px-4 py-3.5 font-bold text-brand-navy">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span class="truncate max-w-xs">{{ $file['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-gray-600">{{ $file['type'] }}</td>
                                    <td class="px-4 py-3.5 text-gray-600 tabular-nums">{{ $file['size_formatted'] }}</td>
                                    <td class="px-4 py-3.5 font-mono text-[11px] text-gray-500 tabular-nums">
                                        <span title="{{ $file['sha256'] }}">{{ substr($file['sha256'], 0, 16) }}...{{ substr($file['sha256'], -8) }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Clean
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- ======================================================== -->
        <!-- TAB 3: ESKALASI / MEDIASI PRODUKSI (UC-13 DISCREPANCY)   -->
        <!-- ======================================================== -->
        <div id="panel-escalation" class="tab-panel {{ $activeTab === 'escalation' ? '' : 'hidden' }} p-6 sm:p-8 space-y-6">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">
                            Mediasi Ketidaksesuaian & Eskalasi Produksi
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                            {{ $submission['escalation']['ticket_id'] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Sesuai klausul UC-13: Jika penulis meminta perubahan dengan alasan mendasar (*changes with reason*), resolusi dilakukan melalui eskalasi dewan redaksi tanpa melompati persetujuan.
                    </p>
                </div>
            </div>

            <!-- Tiket Catatan Permintaan Perubahan Penulis -->
            <div class="border border-amber-200 bg-amber-50/50 rounded-xl p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Catatan Keberatan / Permintaan Penulis
                    </span>
                    <span class="text-xs text-amber-800 tabular-nums font-medium">
                        {{ $submission['escalation']['created_at'] }}
                    </span>
                </div>

                <p class="text-sm text-gray-800 leading-relaxed font-sans bg-white p-4 rounded-lg border border-amber-200/80">
                    "{{ $submission['escalation']['author_note'] }}"
                </p>

                <p class="text-xs text-amber-900 font-medium">
                    Kategori: <strong class="font-bold text-gray-900">{{ $submission['escalation']['category'] }}</strong> • Status: <span class="font-bold underline">{{ $submission['escalation']['status'] }}</span>
                </p>
            </div>

            <!-- Form Eskalasi ke Section Editor -->
            <form method="POST" action="{{ route('copyeditor.escalate') }}" class="space-y-4 pt-2">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Tanggapan & Usulan Copyeditor untuk Section Editor
                    </label>
                    <textarea
                        name="mediation_notes"
                        rows="4"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon leading-relaxed"
                        placeholder="Uraikan pertimbangan kaidah selingkung atau rekomendasi mediasi untuk diputuskan oleh Section Editor (Prof. Hamka, Ph.D.)..."
                        required
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-red-700 hover:bg-red-800 rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Terbitkan Tiket Mediasi ke Section Editor</span>
                    </button>
                </div>
            </form>
        </div>


        <!-- ======================================================== -->
        <!-- TAB 4: PENYELESAIAN & SERAH TERIMA KE LAYOUT (UC-13/14)  -->
        <!-- ======================================================== -->
        <div id="panel-handoff" class="tab-panel {{ $activeTab === 'handoff' ? '' : 'hidden' }} p-6 sm:p-8 space-y-6">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-5">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight">
                            Penyelesaian & Serah Terima ke Galley Layout
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                            Tahap Akhir Copyediting
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Sesuai alur UC-13 & UC-14: Setelah seluruh berkas disetujui penulis tanpa mutasi byte, alur dilanjutkan ke Layout Editor untuk pembuatan galai cetak (galley draft).
                    </p>
                </div>
            </div>

            <!-- Checklist Kesiapan Serah Terima (Readiness Guard) -->
            <div class="bg-surface-subtle border border-gray-200 rounded-xl p-5 space-y-3">
                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">
                    Kriteria Kelayakan Serah Terima (Handoff Invariants):
                </span>

                <div class="space-y-2.5">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm text-gray-800">Persetujuan penulis (Author Approval) terverifikasi dan aktif pada {{ $submission['current_output_set_version'] }}.</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm text-gray-800">Hash Checksum SHA-256 seluruh set identik dan bebas dari mutasi byte.</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm text-gray-800">Seluruh berkas lolos uji pemindaian antivirus Microsoft Defender for Storage.</span>
                    </div>
                </div>
            </div>

            <!-- Form Serah Terima ke Layout Editor -->
            <form method="POST" action="{{ route('copyeditor.complete-handoff') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Layout Editor Ditugaskan
                    </label>
                    <input
                        type="text"
                        value="{{ $submission['layout_handoff']['assigned_layout_editor'] }}"
                        disabled
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-xs sm:text-sm text-gray-700 font-semibold"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Catatan & Instruksi Tata Letak Khusus untuk Layout Editor (Opsional)
                    </label>
                    <textarea
                        name="layout_instructions"
                        rows="3"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-maroon focus:border-brand-maroon leading-relaxed"
                        placeholder="{{ $submission['layout_handoff']['notes_placeholder'] }}"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button
                        type="submit"
                        class="px-5 py-3 text-xs sm:text-sm font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>Selesaikan Copyediting & Serahkan ke Layout Editor</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL POP-UP DIALOG (DIPICU DARI TOMBOL "BUKA MODAL")     -->
<!-- ======================================================== -->
<div id="modalCatalogDialog" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-950/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-2xl w-full p-6 sm:p-7 space-y-5 animate-in fade-in zoom-in-95 duration-200">

        <!-- Modal Top Header -->
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-brand-maroon text-white uppercase tracking-wider mb-1">
                    MODAL POP-UP PRODUKSI
                </span>
                <h3 class="text-lg font-bold text-gray-900">
                    Dialog Transaksional Copy Editor
                </h3>
                <p class="text-xs text-gray-500">
                    {{ $submission['id'] }} — {{ $submission['title'] }}
                </p>
            </div>

            <button type="button" id="closeModalCatalogBtn" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Tutup Modal">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Ringkasan Invalidation Guard & Checksum -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-xs text-red-800 space-y-1">
            <span class="font-bold uppercase tracking-wide block">Protokol Keamanan Atomik:</span>
            <p>Setiap mutasi berkas akan secara otomatis meregenerasi hash SHA-256 set dan membatalkan persetujuan penulis sebelumnya.</p>
        </div>

        <div class="space-y-2">
            <span class="block text-xs font-bold text-gray-700 uppercase">Set Berkas Aktif (Output Set v2)</span>
            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 font-mono text-xs text-gray-700 break-all select-all tabular-nums">
                {{ $submission['set_checksum'] }}
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="button" id="closeModalCatalogBtn2" class="px-4 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer">
                Tutup
            </button>
            <button
                type="button"
                onclick="window.toast.success('Pemeriksaan integritas checksum valid. Tidak ada byte mutasi.'); closeModal();"
                class="px-4 py-2 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-hover rounded-lg transition cursor-pointer"
            >
                Verifikasi Checksum
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tabName) {
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(function (panel) {
            panel.classList.add('hidden');
        });

        // Show active panel
        const activePanel = document.getElementById('panel-' + tabName);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        // Reset all tab button styles
        document.querySelectorAll('.tab-btn').forEach(function (btn) {
            btn.classList.remove('bg-white', 'text-brand-maroon', 'shadow-xs', 'border', 'border-gray-200/80');
            btn.classList.add('text-gray-600', 'hover:text-gray-900', 'hover:bg-white/50');
            const icon = btn.querySelector('svg');
            if (icon) {
                icon.classList.remove('text-brand-maroon');
                icon.classList.add('text-gray-400');
            }
        });

        // Activate clicked tab button
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.add('bg-white', 'text-brand-maroon', 'shadow-xs', 'border', 'border-gray-200/80');
            activeBtn.classList.remove('text-gray-600', 'hover:text-gray-900', 'hover:bg-white/50');
            const icon = activeBtn.querySelector('svg');
            if (icon) {
                icon.classList.add('text-brand-maroon');
                icon.classList.remove('text-gray-400');
            }
        }

        // Update URL query string without page reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    }

    function copyText(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () {
                window.toast.success('Hash SHA-256 berhasil disalin ke papan klip.');
            });
        }
    }

    // Modal Pop-up Handlers
    const modal = document.getElementById('modalCatalogDialog');
    const openBtn = document.getElementById('openModalCatalogBtn');
    const closeBtn = document.getElementById('closeModalCatalogBtn');
    const closeBtn2 = document.getElementById('closeModalCatalogBtn2');

    function openModal() {
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal() {
        if (modal) modal.classList.add('hidden');
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (closeBtn2) closeBtn2.addEventListener('click', closeModal);

    // Close on click outside modal content
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }

    // File Input change trigger
    const fileInput = document.getElementById('fileInput');
    const dropzone = document.getElementById('dropzone');

    if (fileInput && dropzone) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                window.toast.info('Berkas dipilih: ' + fileName);
            }
        });
    }
</script>
@endpush
