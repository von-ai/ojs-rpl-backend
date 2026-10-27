@extends('layouts.copyeditor')

@section('title', 'Ruang Kerja Naskah ' . $submission['number'] . ' — Portal Copy Editor')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    @if (session('error'))
        <x-alert type="error" title="Kendala Alur Kerja">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- 1. Header Naskah & Tombol Navigasi -->
    @include('copyeditor.submissions.partials.header-card')

    <!-- 2. Tata Letak 2 Kolom: Alur Kerja Utama & Info Pendukung -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Alur Kerja Terpadu 3 Langkah (2 Kolom Lebar) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Langkah 1: Berkas Hasil Suntingan (Output Set) -->
            @include('copyeditor.submissions.partials.step-1-outputs')

            <!-- Langkah 2: Persetujuan Penulis & Komunikasi Mediasi -->
            @include('copyeditor.submissions.partials.step-2-approval')

            <!-- Langkah 3: Serah Terima ke Layout Editor (Galley Handoff) -->
            @include('copyeditor.submissions.partials.step-3-handoff')
        </div>

        <!-- Kolom Kanan: Info Pendukung, Panduan, & Riwayat (1 Kolom) -->
        <div class="space-y-6">
            @include('copyeditor.submissions.partials.sidebar-info')
        </div>

    </div>

    <!-- 3. Modal-Modal Dialog Interaktif -->
    @include('copyeditor.submissions.partials.modal-escalate')
    @include('copyeditor.submissions.partials.modal-handoff-confirm')

</div>
@endsection
