@extends('layouts.sysadmin')

@section('title', 'Pengaturan Platform & Kesehatan Server — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Halaman & Kontrol Mode Tampilan / Edit -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-sans">
                    Pengaturan Platform &amp; Kesehatan Server
                </h1>
                <span id="mode-badge" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    Mode Baca
                </span>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Atur identitas situs, aktifkan mode pemeliharaan, atau periksa kondisi server.
            </p>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs flex items-center gap-2.5 shadow-none">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 text-xs space-y-1 shadow-none">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Terjadi kesalahan pada isian pengaturan:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 4 Metrik Ringkas Operasional Server -->
    @php
        $settingsIcons = [
            0 => 'server',
            1 => 'globe',
            2 => 'database',
            3 => 'shield-check',
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $idx => $metric)
            <x-kpi-card
                :title="$metric['title']"
                :value="$metric['value']"
                :description="$metric['description'] ?? $metric['badge_text'] ?? null"
                :icon="$settingsIcons[$idx] ?? 'server'"
                valueClass="text-base font-bold text-gray-900 tracking-tight"
                class="shadow-none"
            />
        @endforeach
    </div>

    @php
        $settings = $viewModel->settings();
        $health = $viewModel->serverHealth();
    @endphp

    <!-- Layout 2 Kolom: Pengaturan Platform (Kiri) & Diagnostik Server (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Konfigurasi Parameter Global Platform (2 Kolom Lebar) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- ======================================================= -->
            <!-- 1. TAMPILAN MODE BACA (READ-ONLY VIEW / SUMMARY)        -->
            <!-- ======================================================= -->
            <div id="view-mode-container" class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-none space-y-6">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                            Konfigurasi Parameter Global Situs
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pengaturan ini berlaku universal bagi seluruh pengunjung dan instansi jurnal yang bernaung di platform.
                        </p>
                    </div>
                    <button
                        id="btn-enter-edit"
                        type="button"
                        onclick="enableEditMode()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-brand-maroon hover:text-white bg-brand-maroon/10 hover:bg-brand-maroon rounded-lg transition cursor-pointer shrink-0"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        <span>Ubah</span>
                    </button>
                </div>

                <div class="space-y-5">
                    <!-- Nama Resmi Platform -->
                    <div>
                        <span class="block text-xs font-medium text-gray-500">Nama Resmi Platform Portal</span>
                        <div class="mt-1 text-sm font-bold text-gray-900">
                            {{ $settings['site_name'] }}
                        </div>
                    </div>

                    <!-- Deskripsi Portal -->
                    <div>
                        <span class="block text-xs font-medium text-gray-500">Deskripsi Portal Repositori &amp; Publikasi</span>
                        <div class="mt-1 text-xs text-gray-700 leading-relaxed bg-gray-50/70 p-3 rounded-lg border border-gray-100">
                            {{ $settings['site_description'] ?: 'Belum ada deskripsi yang ditetapkan.' }}
                        </div>
                    </div>

                    <!-- Mode Pemeliharaan (Maintenance Mode) -->
                    <div class="p-4 rounded-xl border {{ $settings['maintenance_mode'] ? 'border-amber-200 bg-amber-50/50' : 'border-gray-200 bg-gray-50/40' }} space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold {{ $settings['maintenance_mode'] ? 'text-amber-900' : 'text-gray-900' }}">
                                    Mode Pemeliharaan Sistem
                                </span>
                                @if ($settings['maintenance_mode'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Aktif (Akses Dibatasi)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Non-Aktif (Publik Terbuka)
                                    </span>
                                @endif
                            </div>
                        </div>
                        <p class="text-[11px] {{ $settings['maintenance_mode'] ? 'text-amber-800' : 'text-gray-600' }} leading-relaxed">
                            @if ($settings['maintenance_mode'])
                                Saat ini situs dalam pemeliharaan. Seluruh pengunjung dan pengguna reguler dialihkan ke halaman HTTP 503.
                            @else
                                Seluruh pengunjung umum, penulis, editor, dan mitra bestari dapat mengakses portal jurnal seperti biasa tanpa hambatan.
                            @endif
                        </p>
                        @if ($settings['maintenance_message'])
                            <div class="pt-1.5 border-t {{ $settings['maintenance_mode'] ? 'border-amber-200/60' : 'border-gray-200/60' }}">
                                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider block">Pesan Pemeliharaan:</span>
                                <span class="text-xs text-gray-800 italic">"{{ $settings['maintenance_message'] }}"</span>
                            </div>
                        @endif
                    </div>

                    <!-- Parameter Berkas & Sesi -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                        <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                            <span class="text-[11px] font-medium text-gray-500 block">Maks. Unggahan Berkas</span>
                            <span class="text-sm font-bold text-gray-900 mt-1 block">
                                {{ $settings['max_upload_size_mb'] }} MB
                            </span>
                            <span class="text-[10px] text-gray-400">Batas ukuran per dokumen</span>
                        </div>

                        <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-100 sm:col-span-1">
                            <span class="text-[11px] font-medium text-gray-500 block">Masa Berlaku Sesi</span>
                            <span class="text-sm font-bold text-gray-900 mt-1 block">
                                {{ $settings['session_lifetime_minutes'] }} Menit
                            </span>
                            <span class="text-[10px] text-gray-400">Batas waktu auto logout</span>
                        </div>

                        <div class="bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                            <span class="text-[11px] font-medium text-gray-500 block">Ekstensi Diizinkan</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach (array_filter(array_map('trim', explode(',', $settings['allowed_file_extensions']))) as $ext)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-white text-gray-700 border border-gray-200">
                                        .{{ ltrim($ext, '.') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================= -->
            <!-- 2. FORM EDIT MODE (DITAMPILKAN KETIKA MODE UBAH AKTIF)   -->
            <!-- ======================================================= -->
            <form
                id="form-settings"
                action="{{ route('sysadmin.settings.update') }}"
                method="POST"
                class="hidden bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-none space-y-5"
            >
                @csrf

                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                            Konfigurasi Parameter Global Situs
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Ubah parameter operasional sistem. Klik "Simpan Perubahan" untuk menerapkan konfigurasi baru.
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        Mode Ubah
                    </span>
                </div>

                <!-- Nama Situs Platform -->
                <div>
                    <label for="site_name" class="block text-xs font-semibold text-gray-700 mb-1">
                        Nama Resmi Platform Portal <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="site_name"
                        name="site_name"
                        value="{{ old('site_name', $settings['site_name']) }}"
                        required
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 shadow-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                    >
                </div>

                <!-- Deskripsi Portal -->
                <div>
                    <label for="site_description" class="block text-xs font-semibold text-gray-700 mb-1">
                        Deskripsi Portal Repositori &amp; Publikasi
                    </label>
                    <textarea
                        id="site_description"
                        name="site_description"
                        rows="3"
                        class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 shadow-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                    >{{ old('site_description', $settings['site_description']) }}</textarea>
                </div>

                <!-- Mode Pemeliharaan (Maintenance Mode) -->
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <label for="maintenance_mode" class="text-xs font-bold text-amber-900 block cursor-pointer">
                                Mode Pemeliharaan Sistem (Maintenance Mode)
                            </label>
                            <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                                Saat diaktifkan, seluruh pengguna publik, penulis, dan reviewer diblokir sementara dengan halaman pemberitahuan HTTP 503. Hanya pemegang peran System Administrator yang dapat masuk.
                            </p>
                        </div>
                        <div class="shrink-0 flex items-center pt-0.5">
                            <input type="hidden" name="maintenance_mode" value="0">
                            <input
                                type="checkbox"
                                id="maintenance_mode"
                                name="maintenance_mode"
                                value="1"
                                {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-brand-maroon focus:ring-brand-maroon cursor-pointer border-gray-300 shadow-none"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="maintenance_message" class="block text-[11px] font-semibold text-amber-900 mb-1">
                            Pesan Pemberitahuan Pemeliharaan
                        </label>
                        <input
                            type="text"
                            id="maintenance_message"
                            name="maintenance_message"
                            value="{{ old('maintenance_message', $settings['maintenance_message']) }}"
                            class="w-full text-xs text-gray-900 bg-white border border-amber-300 rounded-lg p-2.5 shadow-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Batas Ukuran Berkas -->
                    <div>
                        <label for="max_upload_size_mb" class="block text-xs font-semibold text-gray-700 mb-1">
                            Maks. Unggahan Berkas (MB) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="max_upload_size_mb"
                            name="max_upload_size_mb"
                            value="{{ old('max_upload_size_mb', $settings['max_upload_size_mb']) }}"
                            min="5"
                            max="100"
                            required
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 shadow-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                        >
                    </div>

                    <!-- Ekstensi Berkas Diizinkan -->
                    <div>
                        <label for="allowed_file_extensions" class="block text-xs font-semibold text-gray-700 mb-1">
                            Ekstensi Berkas <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="allowed_file_extensions"
                            name="allowed_file_extensions"
                            value="{{ old('allowed_file_extensions', $settings['allowed_file_extensions']) }}"
                            required
                            class="w-full text-xs font-mono text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 shadow-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                        >
                    </div>

                    <!-- Masa Berlaku Sesi -->
                    <div>
                        <label for="session_lifetime_minutes" class="block text-xs font-semibold text-gray-700 mb-1">
                            Masa Berlaku Sesi (Menit) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="session_lifetime_minutes"
                            name="session_lifetime_minutes"
                            value="{{ old('session_lifetime_minutes', $settings['session_lifetime_minutes']) }}"
                            min="15"
                            max="120"
                            required
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 shadow-none focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        onclick="cancelEditMode()"
                        class="px-4 py-2.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer shadow-none"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        onclick="openConfirmSaveModal()"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition duration-150 shadow-none cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan: Diagnostik Server & Pemeliharaan (1 Kolom) -->
        <div class="space-y-6">

            <!-- Kartu Kesehatan Server -->
            <div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-none space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 tracking-tight font-sans flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Diagnostik Kesehatan Server</span>
                    </h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Versi PHP:</span>
                        <span class="font-mono font-bold text-gray-800">{{ $health['php_version'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Framework:</span>
                        <span class="font-bold text-gray-800">{{ $health['laravel_version'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Database Driver:</span>
                        <span class="font-mono text-gray-800">{{ $health['database_driver'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Zona Waktu:</span>
                        <span class="font-medium text-gray-800">{{ $health['timezone'] }}</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Pemindai Malware:</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            {{ $health['malware_scanner_status'] }}
                        </span>
                    </div>

                    <!-- Progress Bar Disk Storage -->
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-gray-500 font-medium">Kapasitas Disk Storage:</span>
                            <span class="font-bold text-gray-800">{{ $health['disk_usage_formatted'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-brand-maroon h-2 rounded-full" style="width: {{ $health['disk_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Operasional Cache Sistem -->
            <div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-none space-y-3">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 tracking-tight font-sans flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>Pembersihan Cache Sistem</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Segarkan rute, view terkompilasi, dan konfigurasi cache aplikasi jika terjadi ketidaksesuaian tampilan.
                    </p>
                </div>

                <form action="{{ route('sysadmin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 min-h-11 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition shadow-none cursor-pointer"
                        title="Kosongkan seluruh cache aplikasi"
                    >
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Kosongkan Cache Aplikasi</span>
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

<!-- ======================================================= -->
<!-- 3. MODAL DIALOG: KONFIRMASI SIMPAN PENGATURAN PLATFORM  -->
<!-- ======================================================= -->
<div id="modal-confirm-save" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-confirm-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs" onclick="closeConfirmSaveModal()"></div>

        <!-- Panel Konten Modal -->
        <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md w-full p-5 sm:p-6 border border-gray-200">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-snug" id="modal-confirm-title">
                        Simpan Perubahan Konfigurasi Platform?
                    </h3>
                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                        Apakah Anda yakin ingin menerapkan perubahan parameter global sistem? Perubahan batas berkas, format ekstensi, dan mode pemeliharaan akan langsung berlaku universal bagi seluruh pengguna aktif.
                    </p>

                    <!-- Peringatan dinamis jika Mode Pemeliharaan dicentang -->
                    <div id="confirm-maintenance-warning" class="hidden mt-3 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="leading-relaxed">
                            <strong>Perhatian:</strong> Mode Pemeliharaan aktif! Seluruh pengguna publik dan penulis akan diblokir sementara dengan respon HTTP 503.
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="closeConfirmSaveModal()"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer shadow-none"
                >
                    Periksa Kembali
                </button>
                <button
                    id="btn-confirm-submit"
                    type="button"
                    onclick="executeSubmitSettings()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-lg transition cursor-pointer shadow-none"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Ya, Simpan Konfigurasi</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function enableEditMode() {
        const viewContainer = document.getElementById('view-mode-container');
        const formSettings = document.getElementById('form-settings');
        const btnEnterEdit = document.getElementById('btn-enter-edit');
        const badge = document.getElementById('mode-badge');

        if (viewContainer) viewContainer.classList.add('hidden');
        if (formSettings) formSettings.classList.remove('hidden');
        if (btnEnterEdit) btnEnterEdit.classList.add('hidden');

        if (badge) {
            badge.textContent = 'Mode Ubah';
            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200';
        }
    }

    function cancelEditMode() {
        const viewContainer = document.getElementById('view-mode-container');
        const formSettings = document.getElementById('form-settings');
        const btnEnterEdit = document.getElementById('btn-enter-edit');
        const badge = document.getElementById('mode-badge');

        if (viewContainer) viewContainer.classList.remove('hidden');
        if (formSettings) formSettings.classList.add('hidden');
        if (btnEnterEdit) btnEnterEdit.classList.remove('hidden');

        if (badge) {
            badge.textContent = 'Mode Baca';
            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200';
        }
    }

    function openConfirmSaveModal() {
        // Cek apakah mode pemeliharaan dicentang
        const maintenanceCheckbox = document.getElementById('maintenance_mode');
        const warningBox = document.getElementById('confirm-maintenance-warning');
        if (warningBox && maintenanceCheckbox) {
            if (maintenanceCheckbox.checked) {
                warningBox.classList.remove('hidden');
            } else {
                warningBox.classList.add('hidden');
            }
        }

        const modal = document.getElementById('modal-confirm-save');
        if (modal) modal.classList.remove('hidden');
    }

    function closeConfirmSaveModal() {
        const modal = document.getElementById('modal-confirm-save');
        if (modal) modal.classList.add('hidden');
    }

    function executeSubmitSettings() {
        const form = document.getElementById('form-settings');
        const btn = document.getElementById('btn-confirm-submit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menyimpan...</span>
            `;
        }
        if (form) {
            form.submit();
        }
    }

    // Tangani escape key untuk menutup modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeConfirmSaveModal();
        }
    });

    // Otomatis buka mode edit jika ada error validasi setelah submit
    document.addEventListener('DOMContentLoaded', function () {
        @if ($errors->any())
            enableEditMode();
        @endif
    });
</script>
@endsection
