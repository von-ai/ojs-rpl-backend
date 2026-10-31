@extends('layouts.section-editor')

@section('title', 'Ruang Kerja Naskah ' . $viewModel->submissionNumber() . ' — Portal Editor Bagian')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    <!-- Header Naskah & Tahapan Alur Kerja -->
    @include('section-editor.submissions.partials.header-workflow')

    <!-- Layout 2 Kolom (2/3 Informasi Manuskrip, Putaran Telaah & Audit; 1/3 Panel Tindakan Editor Bagian) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Kolom Kiri (2/3): Metadata, Berkas, Manajemen Putaran Telaah & Riwayat Audit -->
        <div class="lg:col-span-2 min-w-0 space-y-6">
            @include('section-editor.submissions.partials.manuscript-metadata')

            @include('section-editor.submissions.partials.review-rounds-manager')

            @include('section-editor.submissions.partials.audit-history')
        </div>

        <!-- Kolom Kanan (1/3): Panel Tindakan Editor Bagian -->
        @include('section-editor.submissions.partials.action-sidebar')

    </div>

</div>
@endsection
