@extends('layouts.proofreader')

@section('title', 'Pemeriksaan Galai ' . $submission['number'] . ' — Portal Proofreader')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <x-alert type="success" title="Pemberitahuan">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- 1. Header Naskah & Penugasan -->
    @include('proofreader.submissions.partials.header-card')

    <!-- 2. Tata Letak 2 Kolom: Ruang Kerja Pemeriksaan Galai & Info Persetujuan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Berkas Galai & Catatan Koreksi (2 Kolom Lebar) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Berkas Galai Aktif & Naskah Acuan Copyedit -->
            @include('proofreader.submissions.partials.galley-files')

            <!-- Catatan Koreksi Proofreading & Formulir Cepat -->
            @include('proofreader.submissions.partials.notes-section')
        </div>

        <!-- Kolom Kanan: Persetujuan Akhir, Eskalasi, & Linimasa (1 Kolom) -->
        <div class="space-y-6">
            <!-- Persetujuan Akhir (Sign-off) & Guard Rails -->
            @include('proofreader.submissions.partials.signoff-card')

            <!-- Eskalasi Ketidaksesuaian Copyedit -->
            @include('proofreader.submissions.partials.escalation-card')

            <!-- Linimasa Kronologis Putaran -->
            @include('proofreader.submissions.partials.timeline-card')
        </div>

    </div>

</div>
@endsection
