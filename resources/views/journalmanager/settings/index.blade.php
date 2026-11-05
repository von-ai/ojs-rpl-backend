@extends('layouts.journalmanager')

@section('title', 'Pengaturan Jurnal & Kebijakan — Portal Journal Manager')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 pb-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                Pengaturan Jurnal & Kebijakan Editorial
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                Konfigurasi identitas resmi jurnal ilmiah, registrasi ISSN, fokus ruang lingkup, dan warta pengumuman publik.
            </p>
        </div>
    </div>

    @php
        $identity = $viewModel->journalIdentity();
        $policies = $viewModel->editorialPolicies();
        $announcements = $viewModel->announcements();
    @endphp

    <form action="{{ route('journalmanager.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Bagian 1: Identitas & Metadata Jurnal -->
        <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
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
                    <label for="journal_name" class="block font-semibold text-gray-700 mb-1">Nama Lengkap Jurnal</label>
                    <input type="text" name="name" id="journal_name" value="{{ $identity['name'] }}" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="journal_initials" class="block font-semibold text-gray-700 mb-1">Singkatan / Inisial</label>
                        <input type="text" name="initials" id="journal_initials" value="{{ $identity['initials'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon">
                    </div>
                    <div>
                        <label for="journal_slug" class="block font-semibold text-gray-700 mb-1">URL Identifier (Slug)</label>
                        <input type="text" name="slug" id="journal_slug" value="{{ $identity['slug'] }}" readonly class="w-full px-3 py-2 text-xs border border-gray-200 bg-gray-50 rounded-lg text-gray-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="issn_print" class="block font-semibold text-gray-700 mb-1">Nomor Cetak ISSN (p-ISSN)</label>
                        <input type="text" name="issn_print" id="issn_print" value="{{ $identity['issn_print'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-mono">
                    </div>
                    <div>
                        <label for="issn_online" class="block font-semibold text-gray-700 mb-1">Nomor Elektronik ISSN (e-ISSN)</label>
                        <input type="text" name="issn_online" id="issn_online" value="{{ $identity['issn_online'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-mono">
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

        <!-- Bagian 2: Kebijakan Editorial & Publikasi -->
        <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    2
                </div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Kebijakan Editorial, Open Access & Hak Cipta
                </h2>
            </div>

            <div class="p-4 sm:p-5 space-y-4 text-xs">
                <div>
                    <label for="focus_scope" class="block font-semibold text-gray-700 mb-1">Fokus & Ruang Lingkup (Focus & Scope)</label>
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
                                '0' => 'Bebas Biaya (Diamond Open Access - Gratis)',
                                '1' => 'Berbayar (Gold Open Access - Penulis Bayar APC)',
                            ]"
                            onchange="toggleApcFields(this.value)"
                            container-class="w-full"
                            button-class="w-full px-3 py-2 text-xs border-gray-300 bg-white font-semibold"
                            panel-class="w-full"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="apc_fee" class="block font-semibold text-gray-700 mb-1">Keterangan Biaya Pemrosesan Naskah (APC)</label>
                        <input type="text" name="apc_fee" id="apc_fee" value="{{ $policies['apc_fee'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-medium text-emerald-800">
                    </div>
                    <div id="apc_amount_container" class="{{ ! $policies['apc_enabled'] ? 'opacity-50' : '' }}">
                        <label for="apc_amount" class="block font-semibold text-gray-700 mb-1">Nominal Tarif APC (Rp)</label>
                        <input type="number" name="apc_amount" id="apc_amount" value="{{ preg_replace('/[^0-9]/', '', $policies['apc_amount']) }}" placeholder="500000" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-semibold">
                    </div>
                </div>

                <!-- Bagian Konfigurasi Rekening Pembayaran APC Jurnal -->
                <div id="apc_bank_settings" class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-3.5 {{ ! $policies['apc_enabled'] ? 'hidden' : '' }}">
                    <div class="flex items-center gap-2 text-amber-900 border-b border-amber-200/60 pb-2">
                        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-xs font-bold">Rekening Bank Tujuan Pembayaran APC</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="payment_bank_name" class="block font-semibold text-gray-700 mb-1">Nama Bank</label>
                            <input type="text" name="payment_bank_name" id="payment_bank_name" value="{{ $policies['payment_bank_name'] }}" placeholder="Contoh: Bank Sulselbar" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <label for="payment_account_number" class="block font-semibold text-gray-700 mb-1">Nomor Rekening</label>
                            <input type="text" name="payment_account_number" id="payment_account_number" value="{{ $policies['payment_account_number'] }}" placeholder="Contoh: 130-001-009876-5" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg bg-white font-mono font-semibold">
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

                <script>
                    function toggleApcFields(val) {
                        const bankSettings = document.getElementById('apc_bank_settings');
                        const amountContainer = document.getElementById('apc_amount_container');
                        if (val === '1') {
                            if (bankSettings) bankSettings.classList.remove('hidden');
                            if (amountContainer) amountContainer.classList.remove('opacity-50');
                        } else {
                            if (bankSettings) bankSettings.classList.add('hidden');
                            if (amountContainer) amountContainer.classList.add('opacity-50');
                        }
                    }
                </script>

                <div>
                    <label for="open_access_policy" class="block font-semibold text-gray-700 mb-1">Kebijakan Akses Terbuka (Open Access Policy)</label>
                    <textarea name="open_access_policy" id="open_access_policy" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed">{{ $policies['open_access_policy'] }}</textarea>
                </div>

                <div>
                    <label for="peer_review_policy" class="block font-semibold text-gray-700 mb-1">Kebijakan Penelaahan Sejawat (Peer Review Policy)</label>
                    <textarea name="peer_review_policy" id="peer_review_policy" rows="2" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon leading-relaxed">{{ $policies['peer_review_policy'] }}</textarea>
                </div>

                <div>
                    <label for="license" class="block font-semibold text-gray-700 mb-1">Lisensi Publikasi</label>
                    <input type="text" name="license" id="license" value="{{ $policies['license'] }}" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-maroon/20 focus:border-brand-maroon font-mono text-gray-800">
                </div>
            </div>
        </div>

        <!-- Bagian 3: Pengumuman Publik & Warta Berita -->
        <div class="bg-white rounded-xl border border-gray-200/90 shadow-2xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-gray-200/90 bg-gray-50/50 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-brand-maroon/10 text-brand-maroon font-bold text-xs flex items-center justify-center">
                    3
                </div>
                <h2 class="text-sm sm:text-base font-bold text-gray-900">
                    Warta Pengumuman Publik Terkini (Announcements)
                </h2>
            </div>

            <div class="p-4 sm:p-5 space-y-3">
                @foreach($announcements as $ann)
                    <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50/50 space-y-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-gray-900">{{ $ann['title'] }}</h3>
                            <span class="text-[10px] text-gray-400">Tayang: {{ $ann['published_at'] }}</span>
                        </div>
                        <p class="text-[11px] text-gray-600 leading-relaxed">{{ $ann['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button 
                type="submit" 
                class="px-5 py-2.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 rounded-xl shadow-2xs transition"
            >
                Simpan Perubahan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection

