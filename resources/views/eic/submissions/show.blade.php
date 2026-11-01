@extends('layouts.eic')

@section('title', 'Ruang Kerja Naskah ' . $submission->submission_number . ' — Portal Pemimpin Redaksi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Naskah & Tahapan Alur Kerja -->
    @include('eic.submissions.partials.header-workflow')

    <!-- Layout 2 Kolom (2/3 Informasi Manuskrip, Telaah & Audit; 1/3 Panel Tindakan Redaksi) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Kolom Kiri (2/3): Metadata, Berkas, Ringkasan Telaah & Riwayat Audit -->
        <div class="lg:col-span-2 min-w-0 space-y-6">
            @include('eic.submissions.partials.manuscript-metadata')

            @include('eic.submissions.partials.review-rounds-summary')

            @include('eic.submissions.partials.audit-history')
        </div>

        <!-- Kolom Kanan (1/3): Panel Tindakan Pemimpin Redaksi -->
        @include('eic.submissions.partials.action-sidebar')

    </div>

</div>
@endsection
