@extends('layouts.sysadmin')

@section('title', 'Pengaturan Platform & Kesehatan Server — Portal System Administrator')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight font-sans">
                Pengaturan Platform &amp; Kesehatan Server
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Konfigurasi parameter global situs SIRIDA, kebijakan pemeliharaan, serta diagnostik infrastruktur server BRIDA Kota Makassar.
            </p>
        </div>
    </div>

    <!-- 4 Metrik Ringkas Operasional Server -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach ($viewModel->summaryMetrics() as $metric)
            <div class="bg-white rounded-xl border border-gray-200/90 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 truncate">{{ $metric['title'] }}</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $metric['color_class'] }}">
                        {{ $metric['badge_text'] }}
                    </span>
                </div>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-xl font-bold text-gray-900 tracking-tight">
                        {{ $metric['value'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    @php
        $settings = $viewModel->settings();
        $health = $viewModel->serverHealth();
    @endphp

    <!-- Layout 2 Kolom: Form Pengaturan (Kiri) & Diagnostik Server (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Form Pengaturan Global Platform (2 Kolom Lebar) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h2 class="text-base font-bold text-gray-900 tracking-tight font-sans">
                        Konfigurasi Parameter Global Situs (system_settings)
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Pengaturan ini berlaku universal bagi seluruh pengunjung dan instansi jurnal yang bernaung di platform.
                    </p>
                </div>

                <form action="{{ route('sysadmin.settings.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Nama Situs Platform -->
                    <div>
                        <label for="site_name" class="block text-xs font-semibold text-gray-700 mb-1">
                            Nama Resmi Platform Portal <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="site_name"
                            name="site_name"
                            value="{{ $settings['site_name'] }}"
                            required
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
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
                            rows="2"
                            class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                        >{{ $settings['site_description'] }}</textarea>
                    </div>

                    <!-- Mode Pemeliharaan (Maintenance Mode) -->
                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-xs font-bold text-amber-900 block">
                                    Mode Pemeliharaan Sistem (Maintenance Mode)
                                </span>
                                <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                                    Saat diaktifkan, seluruh pengguna publik, penulis, dan reviewer diblokir sementara dengan halaman pemberitahuan HTTP 503. Hanya pemegang peran System Administrator yang dapat masuk.
                                </p>
                            </div>
                            <div class="shrink-0 flex items-center">
                                <input type="hidden" name="maintenance_mode" value="0">
                                <input
                                    type="checkbox"
                                    id="maintenance_mode"
                                    name="maintenance_mode"
                                    value="1"
                                    {{ $settings['maintenance_mode'] ? 'checked' : '' }}
                                    class="w-4 h-4 rounded text-brand-maroon focus:ring-brand-maroon cursor-pointer"
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
                                value="{{ $settings['maintenance_message'] }}"
                                class="w-full text-xs text-gray-900 bg-white border border-amber-300 rounded-lg p-2 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Batas Ukuran Berkas -->
                        <div>
                            <label for="max_upload_size_mb" class="block text-xs font-semibold text-gray-700 mb-1">
                                Maks. Unggahan Berkas (MB)
                            </label>
                            <input
                                type="number"
                                id="max_upload_size_mb"
                                name="max_upload_size_mb"
                                value="{{ $settings['max_upload_size_mb'] }}"
                                min="5"
                                max="100"
                                class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                            >
                        </div>

                        <!-- Ekstensi Berkas Diizinkan -->
                        <div>
                            <label for="allowed_file_extensions" class="block text-xs font-semibold text-gray-700 mb-1">
                                Ekstensi Berkas
                            </label>
                            <input
                                type="text"
                                id="allowed_file_extensions"
                                name="allowed_file_extensions"
                                value="{{ $settings['allowed_file_extensions'] }}"
                                class="w-full text-xs font-mono text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                            >
                        </div>

                        <!-- Masa Berlaku Sesi -->
                        <div>
                            <label for="session_lifetime_minutes" class="block text-xs font-semibold text-gray-700 mb-1">
                                Masa Berlaku Sesi (Menit)
                            </label>
                            <input
                                type="number"
                                id="session_lifetime_minutes"
                                name="session_lifetime_minutes"
                                value="{{ $settings['session_lifetime_minutes'] }}"
                                min="15"
                                max="120"
                                class="w-full text-xs text-gray-900 bg-white border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon transition shadow-2xs"
                            >
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-gray-100">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 min-h-11 text-xs font-bold text-white bg-brand-maroon hover:bg-brand-maroon-dark rounded-xl transition duration-150 shadow-2xs cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Simpan Konfigurasi Platform</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Diagnostik Server & Pemeliharaan (1 Kolom) -->
        <div class="space-y-6">

            <!-- Kartu Kesehatan Server -->
            <div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-2xs space-y-4">
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
            <div class="bg-white rounded-xl border border-gray-200/90 p-5 shadow-2xs space-y-3">
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
                        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 min-h-11 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition shadow-2xs cursor-pointer"
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
@endsection
