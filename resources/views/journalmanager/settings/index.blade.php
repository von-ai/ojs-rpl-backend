@extends('layouts.journalmanager')

@section('title', 'Pengaturan Jurnal & Kebijakan Editorial — Portal Journal Manager')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman & Kontrol Mode Tampilan / Edit -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                    Pengaturan Jurnal & Kebijakan Editorial
                </h1>
                <span id="mode-badge" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                    Mode Baca
                </span>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Konfigurasi identitas resmi jurnal ilmiah, registrasi ISSN, fokus ruang lingkup, dan kebijakan editorial.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Tombol Aktifkan Mode Edit (Tampil di Mode Baca) -->
            <button 
                id="btn-enter-edit"
                type="button" 
                onclick="enableEditMode()"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Ubah Pengaturan</span>
            </button>

            <!-- Tombol Batal & Simpan (Tampil di Mode Edit) -->
            <div id="edit-mode-actions" class="hidden flex items-center gap-2">
                <button 
                    type="button" 
                    onclick="cancelEditMode()"
                    class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer shadow-2xs"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    onclick="openConfirmSaveModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </div>

    @php
        $identity = $viewModel->journalIdentity();
        $policies = $viewModel->editorialPolicies();
        $announcements = $viewModel->announcements();
    @endphp

    @if ($errors->any())
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 text-xs space-y-1">
            <div class="font-bold">Terjadi kesalahan pada isian pengaturan:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ======================================================= -->
    <!-- 1. TAMPILAN MODE BACA (READ-ONLY VIEW / SUMMARY CARDS)   -->
    <!-- ======================================================= -->
    <div id="view-mode-container" class="space-y-6">

        <!-- Kartu 1: Identitas & Registrasi Jurnal -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                        1
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">
                        Identitas & Registrasi Jurnal
                    </h2>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    {{ $identity['status_label'] }}
                </span>
            </div>

            <div class="p-4 sm:p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-6 text-xs">
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 font-medium">Nama Lengkap Jurnal</dt>
                        <dd class="text-sm font-semibold text-gray-900 mt-0.5">{{ $identity['name'] }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Singkatan / Inisial</dt>
                        <dd class="text-sm font-semibold text-brand-maroon mt-0.5">{{ $identity['initials'] ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Nomor ISSN Cetak</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-0.5">{{ $identity['issn_print'] ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Nomor ISSN Elektronik</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-0.5">{{ $identity['issn_online'] ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Tautan Pengenal Jurnal</dt>
                        <dd class="text-xs text-gray-700 mt-0.5">/{{ $identity['slug'] }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 font-medium">Lembaga Penerbit Resmi</dt>
                        <dd class="text-xs font-semibold text-gray-800 mt-0.5">{{ $identity['publisher'] ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Email Kontak Redaksi</dt>
                        <dd class="text-xs text-gray-800 mt-0.5">
                            @if($identity['editorial_email'])
                                <a href="mailto:{{ $identity['editorial_email'] }}" class="text-brand-maroon hover:underline">
                                    {{ $identity['editorial_email'] }}
                                </a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500 font-medium">Nomor Telepon Kantor</dt>
                        <dd class="text-xs text-gray-800 mt-0.5">{{ $identity['contact_phone'] ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Kartu 2: Kebijakan Editorial & Standar Publikasi -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Kebijakan Editorial, Akses Terbuka & Hak Cipta
                </h2>
            </div>

            <div class="p-4 sm:p-5 space-y-4 text-xs">
                <div>
                    <h3 class="text-gray-500 font-medium mb-1">Fokus & Ruang Lingkup</h3>
                    <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/70 text-gray-700 leading-relaxed">
                        {{ $policies['focus_scope'] ?: 'Belum ada penjelasan fokus dan ruang lingkup riset.' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-3.5 bg-gray-50/60 rounded-xl border border-gray-200/70">
                        <span class="text-gray-500 font-medium block">Frekuensi Penerbitan</span>
                        <span class="text-xs font-semibold text-gray-900 block mt-1 leading-snug">
                            {{ $policies['frequency'] ?: '-' }}
                        </span>
                    </div>

                    <div class="p-3.5 bg-gray-50/60 rounded-xl border border-gray-200/70">
                        <span class="text-gray-500 font-medium block">Lisensi Publikasi</span>
                        <span class="text-xs font-semibold text-gray-900 block mt-1 leading-snug">
                            {{ $policies['license'] ?: 'Creative Commons Attribution' }}
                        </span>
                    </div>
                </div>

                <div class="pt-2">
                    <h3 class="text-gray-500 font-medium mb-1">Kebijakan Akses Terbuka</h3>
                    <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/70 text-gray-700 leading-relaxed">
                        {{ $policies['open_access_policy'] ?: 'Menyediakan akses terbuka penuh untuk pembaca.' }}
                    </div>
                </div>

                <div class="pt-2">
                    <h3 class="text-gray-500 font-medium mb-1">Kebijakan Penelaahan Sejawat</h3>
                    <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/70 text-gray-700 leading-relaxed">
                        {{ $policies['peer_review_policy'] ?: 'Penelaahan sejawat berpasangan tertutup.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu 3: Skema Pembiayaan & Rekening Pembayaran -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                        3
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">
                        Skema Pembiayaan & Rekening Bank
                    </h2>
                </div>
                @if($policies['apc_enabled'])
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                        Berbayar Melalui Biaya Pemrosesan Naskah
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Bebas Biaya Publikasi
                    </span>
                @endif
            </div>

            <div class="p-4 sm:p-5 space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-500 font-medium block">Keterangan Biaya Pemrosesan Naskah</span>
                        <span class="text-xs font-semibold text-gray-900 block mt-1 leading-relaxed">
                            {{ $policies['apc_fee'] ?: 'Gratis' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium block">Nominal Tarif Publikasi</span>
                        <span class="text-sm font-bold text-brand-maroon block mt-1">
                            @if($policies['apc_enabled'])
                                Rp {{ $policies['apc_amount'] ?: '0' }}
                            @else
                                Rp 0
                            @endif
                        </span>
                    </div>
                </div>

                @if($policies['apc_enabled'])
                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-3">
                        <h4 class="font-bold text-amber-900">Rekening Tujuan Pembayaran</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <span class="text-gray-500 text-[11px] block">Nama Bank</span>
                                <span class="font-semibold text-gray-800 text-xs block mt-0.5">{{ $policies['payment_bank_name'] ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 text-[11px] block">Nomor Rekening</span>
                                <span class="font-semibold text-gray-800 text-xs block mt-0.5">{{ $policies['payment_account_number'] ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 text-[11px] block">Atas Nama Rekening</span>
                                <span class="font-semibold text-gray-800 text-xs block mt-0.5">{{ $policies['payment_account_holder'] ?: '-' }}</span>
                            </div>
                        </div>

                        @if($policies['payment_instructions'])
                            <div class="pt-2 border-t border-amber-200/70">
                                <span class="text-gray-500 text-[11px] block">Instruksi Pembayaran</span>
                                <p class="text-xs text-gray-700 mt-0.5 leading-relaxed">{{ $policies['payment_instructions'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Kartu 4: Warta Pengumuman Publik Terkini -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                        4
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">
                        Warta Pengumuman Publik Terkini
                    </h2>
                </div>
                <span class="text-xs text-gray-500 font-medium">
                    {{ count($announcements) }} Pengumuman
                </span>
            </div>

            <div class="p-4 sm:p-5 space-y-3">
                @forelse($announcements as $ann)
                    <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-xs font-bold text-gray-900 truncate">{{ $ann['title'] }}</h3>
                            <span class="text-[10px] text-gray-400 shrink-0">Tayang: {{ $ann['published_at'] }}</span>
                        </div>
                        <p class="text-[11px] text-gray-600 leading-relaxed">{{ $ann['body'] }}</p>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada warta pengumuman publik yang diterbitkan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- 2. FORMULIR MODE EDIT (EDIT MODE FORM)                   -->
    <!-- ======================================================= -->
    <form id="form-settings" action="{{ route('journalmanager.settings.update') }}" method="POST" class="hidden space-y-6">
        @csrf

        <!-- Bagian 1: Identitas & Metadata Jurnal -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                        1
                    </div>
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">
                        Identitas & Registrasi Jurnal
                    </h2>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    {{ $identity['status_label'] }}
                </span>
            </div>

            <div class="p-4 sm:p-5 space-y-4 text-xs">
                <div>
                    <label for="journal_name" class="block font-semibold text-gray-700 mb-1">
                        Nama Lengkap Jurnal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="journal_name" value="{{ $identity['name'] }}" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="journal_initials" class="block font-semibold text-gray-700 mb-1">Singkatan / Inisial Jurnal</label>
                        <input type="text" name="initials" id="journal_initials" value="{{ $identity['initials'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="journal_slug" class="block font-semibold text-gray-700 mb-1">Tautan Pengenal Jurnal</label>
                        <input type="text" name="slug" id="journal_slug" value="{{ $identity['slug'] }}" readonly class="w-full px-3 py-2 text-xs border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="issn_print" class="block font-semibold text-gray-700 mb-1">Nomor ISSN Cetak</label>
                        <input type="text" name="issn_print" id="issn_print" value="{{ $identity['issn_print'] }}" placeholder="Contoh: 2746-8194" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="issn_online" class="block font-semibold text-gray-700 mb-1">Nomor ISSN Elektronik</label>
                        <input type="text" name="issn_online" id="issn_online" value="{{ $identity['issn_online'] }}" placeholder="Contoh: 2746-8208" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                </div>

                <div>
                    <label for="publisher" class="block font-semibold text-gray-700 mb-1">Lembaga Penerbit Resmi</label>
                    <input type="text" name="publisher" id="publisher" value="{{ $identity['publisher'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="editorial_email" class="block font-semibold text-gray-700 mb-1">Email Kontak Redaksi</label>
                        <input type="email" name="editorial_email" id="editorial_email" value="{{ $identity['editorial_email'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="contact_phone" class="block font-semibold text-gray-700 mb-1">Nomor Telepon Kantor</label>
                        <input type="text" name="contact_phone" id="contact_phone" value="{{ $identity['contact_phone'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Kebijakan Editorial & Standar Publikasi -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Kebijakan Editorial, Akses Terbuka & Hak Cipta
                </h2>
            </div>

            <div class="p-4 sm:p-5 space-y-4 text-xs">
                <div>
                    <label for="focus_scope" class="block font-semibold text-gray-700 mb-1">Fokus & Ruang Lingkup</label>
                    <textarea name="focus_scope" id="focus_scope" rows="3" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed">{{ $policies['focus_scope'] }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="frequency" class="block font-semibold text-gray-700 mb-1">Frekuensi Penerbitan</label>
                        <input type="text" name="frequency" id="frequency" value="{{ $policies['frequency'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="apc_enabled" class="block font-semibold text-gray-700 mb-1">Skema Pembiayaan Publikasi</label>
                        <x-custom-dropdown
                            name="apc_enabled"
                            id="apc_enabled"
                            :value="$policies['apc_enabled'] ? '1' : '0'"
                            :options="[
                                ['value' => '0', 'label' => 'Bebas Biaya Publikasi'],
                                ['value' => '1', 'label' => 'Berbayar Melalui Biaya Pemrosesan Naskah'],
                            ]"
                            onchange="toggleApcFields(this.value)"
                            container-class="w-full"
                            button-class="w-full px-3 py-2 text-xs border-gray-300 bg-white font-medium shadow-none rounded-lg"
                            panel-class="w-full"
                            :shadow="false"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="apc_fee" class="block font-semibold text-gray-700 mb-1">Keterangan Biaya Pemrosesan Naskah</label>
                        <input type="text" name="apc_fee" id="apc_fee" value="{{ $policies['apc_fee'] }}" placeholder="Gratis" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-medium text-gray-900 bg-white">
                    </div>
                    <div id="apc_amount_container">
                        <label for="apc_amount" class="block font-semibold text-gray-700 mb-1">Nominal Tarif Publikasi</label>
                        <div class="relative rounded-lg">
                            <div id="apc_rp_prefix" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none font-semibold text-xs {{ !$policies['apc_enabled'] ? 'text-gray-300' : 'text-gray-500' }}">
                                Rp
                            </div>
                            <input 
                                type="text" 
                                name="apc_amount" 
                                id="apc_amount" 
                                value="{{ $policies['apc_amount'] }}" 
                                data-saved-amount="{{ ($policies['apc_amount'] && $policies['apc_amount'] !== '0') ? $policies['apc_amount'] : '500.000' }}"
                                placeholder="500.000" 
                                {{ !$policies['apc_enabled'] ? 'disabled' : '' }}
                                class="w-full pl-9 pr-3 py-2 text-xs border rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-semibold tabular-nums {{ !$policies['apc_enabled'] ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-900 border-gray-300' }}"
                            >
                        </div>
                    </div>
                </div>

                <!-- Bagian Konfigurasi Rekening Pembayaran Jurnal -->
                <div id="apc_bank_settings" class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-3.5 {{ ! $policies['apc_enabled'] ? 'hidden' : '' }}">
                    <div class="flex items-center gap-2 text-amber-900 border-b border-amber-200/60 pb-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-xs font-bold">Rekening Bank Tujuan Pembayaran</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="payment_bank_name" class="block font-semibold text-gray-700 mb-1">Nama Bank</label>
                            <input type="text" name="payment_bank_name" id="payment_bank_name" value="{{ $policies['payment_bank_name'] }}" placeholder="Contoh: Bank Sulselbar" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <label for="payment_account_number" class="block font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="payment_account_number" id="payment_account_number" value="{{ $policies['payment_account_number'] }}" placeholder="Contoh: 130-001-009876-5" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white font-semibold">
                        </div>
                        <div>
                            <label for="payment_account_holder" class="block font-semibold text-gray-700 mb-1">Atas Nama Rekening</label>
                            <input type="text" name="payment_account_holder" id="payment_account_holder" value="{{ $policies['payment_account_holder'] }}" placeholder="Nama pemilik rekening / bendahara" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white">
                        </div>
                    </div>

                    <div>
                        <label for="payment_instructions" class="block font-semibold text-gray-700 mb-1">Instruksi Pembayaran untuk Penulis</label>
                        <textarea name="payment_instructions" id="payment_instructions" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white leading-relaxed">{{ $policies['payment_instructions'] }}</textarea>
                    </div>
                </div>

                <div>
                    <label for="open_access_policy" class="block font-semibold text-gray-700 mb-1">Kebijakan Akses Terbuka</label>
                    <textarea name="open_access_policy" id="open_access_policy" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed">{{ $policies['open_access_policy'] }}</textarea>
                </div>

                <div>
                    <label for="peer_review_policy" class="block font-semibold text-gray-700 mb-1">Kebijakan Penelaahan Sejawat</label>
                    <textarea name="peer_review_policy" id="peer_review_policy" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed">{{ $policies['peer_review_policy'] }}</textarea>
                </div>

                <div>
                    <label for="license" class="block font-semibold text-gray-700 mb-1">Lisensi Publikasi</label>
                    <input type="text" name="license" id="license" value="{{ $policies['license'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon text-gray-800">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Bawah Form -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
            <button 
                type="button" 
                onclick="cancelEditMode()"
                class="px-4 py-2.5 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-xl border border-gray-300 transition cursor-pointer shadow-2xs"
            >
                Batal
            </button>
            <button 
                type="button" 
                onclick="openConfirmSaveModal()"
                class="px-5 py-2.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-xl shadow-2xs transition cursor-pointer"
            >
                Simpan Perubahan Pengaturan
            </button>
        </div>
    </form>
</div>

<!-- ======================================================= -->
<!-- 3. MODAL DIALOG: KONFIRMASI SIMPAN PENGATURAN           -->
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
                        Simpan Perubahan Pengaturan Jurnal?
                    </h3>
                    <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                        Seluruh konfigurasi identitas jurnal, kontak redaksi, dan kebijakan editorial akan diperbarui dan langsung berlaku pada portal jurnal ilmiah.
                    </p>
                </div>
                <button type="button" onclick="closeConfirmSaveModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer shrink-0" aria-label="Tutup dialog">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-4 p-3 bg-gray-50/90 rounded-xl border border-gray-200/80 text-xs text-gray-600 space-y-1">
                <p class="font-medium text-gray-800">Harap diperhatikan:</p>
                <p class="text-[11px] leading-relaxed text-gray-500">
                    Perubahan pada identitas, nomor ISSN, maupun skema pembiayaan akan langsung tecermin pada panduan penulis dan formulir pengajuan artikel baru.
                </p>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeConfirmSaveModal()" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-lg border border-gray-300 transition cursor-pointer">
                    Periksa Kembali
                </button>
                <button type="button" onclick="executeSubmitSettings()" class="px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-lg shadow-2xs transition cursor-pointer">
                    Ya, Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function enableEditMode() {
        const viewMode = document.getElementById('view-mode-container');
        const form = document.getElementById('form-settings');
        const btnEnterEdit = document.getElementById('btn-enter-edit');
        const editActions = document.getElementById('edit-mode-actions');
        const badge = document.getElementById('mode-badge');

        if (viewMode) viewMode.classList.add('hidden');
        if (form) form.classList.remove('hidden');
        if (btnEnterEdit) btnEnterEdit.classList.add('hidden');
        if (editActions) editActions.classList.remove('hidden');
        if (badge) {
            badge.textContent = 'Mode Edit';
            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200';
        }

        const apcSelect = document.getElementById('apc_enabled');
        if (apcSelect) {
            toggleApcFields(apcSelect.value);
        }
    }

    function cancelEditMode() {
        const viewMode = document.getElementById('view-mode-container');
        const form = document.getElementById('form-settings');
        const btnEnterEdit = document.getElementById('btn-enter-edit');
        const editActions = document.getElementById('edit-mode-actions');
        const badge = document.getElementById('mode-badge');

        if (form) form.classList.add('hidden');
        if (viewMode) viewMode.classList.remove('hidden');
        if (editActions) editActions.classList.add('hidden');
        if (btnEnterEdit) btnEnterEdit.classList.remove('hidden');
        if (badge) {
            badge.textContent = 'Mode Baca';
            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200';
        }
    }

    function openConfirmSaveModal() {
        const modal = document.getElementById('modal-confirm-save');
        if (modal) modal.classList.remove('hidden');
    }

    function closeConfirmSaveModal() {
        const modal = document.getElementById('modal-confirm-save');
        if (modal) modal.classList.add('hidden');
    }

    function executeSubmitSettings() {
        const form = document.getElementById('form-settings');
        const apcAmountInput = document.getElementById('apc_amount');
        if (apcAmountInput && apcAmountInput.disabled) {
            apcAmountInput.disabled = false;
        }
        if (form) {
            form.submit();
        }
    }

    function toggleApcFields(val) {
        const bankSettings = document.getElementById('apc_bank_settings');
        const apcAmountInput = document.getElementById('apc_amount');
        const apcFeeInput = document.getElementById('apc_fee');
        const rpPrefix = document.getElementById('apc_rp_prefix');

        const isPaid = (String(val) === '1' || String(val).toLowerCase().includes('berbayar'));

        if (isPaid) {
            // Mode Berbayar Melalui Biaya Pemrosesan Naskah
            if (bankSettings) bankSettings.classList.remove('hidden');
            if (apcAmountInput) {
                apcAmountInput.disabled = false;
                apcAmountInput.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed', 'border-gray-200');
                apcAmountInput.classList.add('bg-white', 'text-gray-900', 'border-gray-300');
                if (!apcAmountInput.value || apcAmountInput.value === '0' || apcAmountInput.value === 'Rp 0') {
                    apcAmountInput.value = apcAmountInput.dataset.savedAmount || '500.000';
                }
            }
            if (rpPrefix) {
                rpPrefix.classList.remove('text-gray-300');
                rpPrefix.classList.add('text-gray-500');
            }
            if (apcFeeInput) {
                const current = apcFeeInput.value.trim().toLowerCase();
                if (!current || current === 'gratis' || current === 'rp 0' || current === 'bebas biaya') {
                    apcFeeInput.value = 'Biaya Publikasi';
                }
            }
        } else {
            // Mode Bebas Biaya Publikasi (Gratis)
            if (bankSettings) bankSettings.classList.add('hidden');
            if (apcAmountInput) {
                if (apcAmountInput.value && apcAmountInput.value !== '0') {
                    apcAmountInput.dataset.savedAmount = apcAmountInput.value;
                }
                apcAmountInput.disabled = true;
                apcAmountInput.classList.remove('bg-white', 'text-gray-900', 'border-gray-300');
                apcAmountInput.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed', 'border-gray-200');
                apcAmountInput.value = '0';
            }
            if (rpPrefix) {
                rpPrefix.classList.remove('text-gray-500');
                rpPrefix.classList.add('text-gray-300');
            }
            if (apcFeeInput) {
                const current = apcFeeInput.value.trim().toLowerCase();
                if (!current || current === 'biaya publikasi' || current === 'rp 0') {
                    apcFeeInput.value = 'Gratis';
                }
            }
        }
    }

    // Auto Format Rupiah dengan Titik Setiap 3 Digit
    document.addEventListener('DOMContentLoaded', function () {
        const apcInput = document.getElementById('apc_amount');
        if (apcInput) {
            function formatRupiahString(value) {
                const clean = value.replace(/\D/g, '');
                if (!clean) return '';
                return clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            apcInput.addEventListener('input', function (e) {
                const formatted = formatRupiahString(this.value);
                this.value = formatted;
                if (formatted && formatted !== '0') {
                    this.dataset.savedAmount = formatted;
                }
            });

            // Format awal saat dokumen dimuat
            if (apcInput.value && apcInput.value !== '0') {
                apcInput.value = formatRupiahString(apcInput.value);
            }
        }

        // Listener sinkronisasi perubahan skema pembiayaan
        const apcDropdown = document.querySelector('[data-dropdown="apc_enabled"]');
        if (apcDropdown) {
            apcDropdown.addEventListener('dropdown:change', function (e) {
                toggleApcFields(e.detail.value);
            });
        }
        const apcSelect = document.getElementById('apc_enabled');
        if (apcSelect) {
            toggleApcFields(apcSelect.value);
            apcSelect.addEventListener('change', function () {
                toggleApcFields(this.value);
            });
        }

        // Buka otomatis ke Mode Edit jika ada error validasi
        @if ($errors->any())
            enableEditMode();
        @endif
    });
</script>
@endsection
