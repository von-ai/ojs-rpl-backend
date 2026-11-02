@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', 'Kontak Redaksi — ' . ($journal->name ?? 'Jurnal Riset & Inovasi BRIDA Makassar'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.about') }}" class="hover:text-brand-maroon">Tentang Jurnal</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Kontak Redaksi</span>
    </nav>

    <!-- Header Halaman -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">
            SEKRETARIAT & NARAHUBUNG
        </span>
        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">
            Kontak dan Sekretariat Redaksi
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-2xl leading-relaxed">
            Hubungi pengelola jurnal dan tim sekretariat redaksi {{ $journal->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA)' }} untuk pertanyaan seputar proses submisi naskah, kerja sama penerbitan, atau kendala teknis sistem.
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Konten Utama (2 Kolom Kiri) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 sm:p-8 space-y-8">
            
            <x-journal.about-subnav />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                
                <!-- Kontak Utama (Principal Contact) -->
                <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/50 space-y-3">
                    <div class="flex items-center gap-2 text-brand-maroon font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Narahubung Utama (Principal Contact)</span>
                    </div>

                    <div class="space-y-1.5 text-gray-700">
                        <strong class="text-gray-900 text-sm block">Sekretariat Redaksi {{ $journal->initials ?? 'SIRIDA' }}</strong>
                        <p class="text-gray-600">{{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar' }}</p>
                        
                        <div class="pt-2 space-y-1 border-t border-gray-200/80">
                            <p>Surel Resmi: <a href="mailto:jurnal.brida@makassarkota.go.id" class="text-brand-maroon font-semibold hover:underline">jurnal.brida@makassarkota.go.id</a></p>
                            @if(isset($journal->editorial_email) && $journal->editorial_email !== 'jurnal.brida@makassarkota.go.id')
                                <p>Surel Redaksi: <a href="mailto:{{ $journal->editorial_email }}" class="text-brand-maroon font-semibold hover:underline">{{ $journal->editorial_email }}</a></p>
                            @endif
                            <p>Telepon: <span class="font-semibold text-gray-900">{{ $journal->contact_phone ?? '(0411) 3622455' }}</span></p>
                            <p>WhatsApp Helpdesk: <span class="font-semibold text-gray-900">+62 811-444-2026</span></p>
                        </div>
                    </div>
                </div>

                <!-- Kontak Bantuan Teknis (Technical Support) -->
                <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/50 space-y-3">
                    <div class="flex items-center gap-2 text-brand-maroon font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Dukungan Teknis Portal OJS</span>
                    </div>

                    <div class="space-y-1.5 text-gray-700">
                        <strong class="text-gray-900 text-sm block">Tim IT & Pengelola Sistem Jurnal</strong>
                        <p class="text-gray-600">Layanan bantuan akun, kendala unggah naskah, dan verifikasi email.</p>
                        
                        <div class="pt-2 space-y-1 border-t border-gray-200/80">
                            <p>Surel Bantuan: <a href="mailto:support.jurnal@makassarkota.go.id" class="text-brand-maroon font-semibold hover:underline">support.jurnal@makassarkota.go.id</a></p>
                            <p>Waktu Respon: <span class="font-semibold text-gray-900">Maksimal 1 x 24 Jam Kerja</span></p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Alamat Sekretariat & Peta Kantor -->
            <section class="space-y-3">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Alamat Fisik Kantor Sekretariat</span>
                </h2>

                <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 text-xs text-gray-700 space-y-1 leading-relaxed">
                    <strong class="text-gray-900 font-bold block text-sm">{{ $journal->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar' }}</strong>
                    <p>Gedung Balai Kota Makassar, Lantai 3</p>
                    <p>Jl. Ahmad Yani No. 2, Kelurahan Baru, Kecamatan Ujung Pandang</p>
                    <p>Kota Makassar, Provinsi Sulawesi Selatan, Kode Pos 90111</p>
                    <p class="text-[11px] text-gray-500 pt-1">Jam Pelayanan Redaksi: Senin – Jumat, 08.00 – 16.00 WITA (Kecuali Hari Libur Nasional)</p>
                </div>
            </section>

        </div>

        <!-- Kolom Samping (1 Kolom Kanan) -->
        <aside class="lg:col-span-1 space-y-6">
            <x-journal.sidebar
                :publisherInfo="$viewModel->publisherInfo()"
                :indexingData="$viewModel->indexingData()"
                :impactMetrics="$viewModel->impactMetrics()"
                :announcements="$viewModel->editorialAnnouncements()"
            />
        </aside>

    </div>

</div>
@endsection
