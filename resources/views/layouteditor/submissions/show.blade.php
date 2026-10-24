@extends('layouts.layouteditor')

@section('title', 'Ruang Kerja Galai Naskah ' . $submission['number'] . ' — Portal Layout Editor')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- 1. Header Naskah & Berkas Final Copyedit -->
    @include('layouteditor.submissions.partials.header-card')

    <!-- 2. Tata Letak 2 Kolom: Alur Kerja 3 Langkah & Info Pendukung -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Alur Produksi 3 Langkah (2 Kolom Lebar) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Langkah 1: Berkas Galai Publikasi (PDF / HTML) -->
            @include('layouteditor.submissions.partials.step-1-galleys')

            <!-- Langkah 2: Putaran Proofreading & Catatan Koreksi -->
            @include('layouteditor.submissions.partials.step-2-proofreading')

            <!-- Langkah 3: Persetujuan Akhir Galai Publikasi -->
            @include('layouteditor.submissions.partials.step-3-final-approval')
        </div>

        <!-- Kolom Kanan: Info Pendukung, Eskalasi, Panduan, & Jejak Aktivitas (1 Kolom) -->
        <div class="space-y-6">
            @include('layouteditor.submissions.partials.sidebar-info')
        </div>

    </div>

    <!-- 3. Modal Dialog Interaktif -->
    @include('layouteditor.submissions.partials.modal-upload-galley')
    @include('layouteditor.submissions.partials.modal-resolve-note')
    @include('layouteditor.submissions.partials.modal-escalate-galley')

</div>
@endsection
