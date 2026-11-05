@extends('layouts.author')

@section('title', 'Kirim Naskah Baru — Portal Penulis BRIDA Kota Makassar')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12">

    <!-- Header, Breadcrumbs, Alerts & Stepper Progress -->
    @include('author.submissions.partials.header')

    <!-- Form Utama Multi-Step Wizard -->
    <form id="submission-wizard-form" action="{{ route('author.submissions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" autocomplete="off" novalidate>
        @csrf
        <input type="hidden" name="action" id="form-action" value="submit">
        <input type="hidden" name="draft_id" id="draft_id" value="{{ old('draft_id', $draft->id ?? '') }}">
        <input type="hidden" name="current_step" id="current_step" value="1">
        <input type="hidden" name="keywords" id="keywords-hidden-input" value="{{ old('keywords', json_encode($draft->keywords ?? [])) }}">
        <input type="hidden" name="contributors" id="contributors-hidden-input" value="{{ old('contributors', json_encode(($draft && $draft->contributors && count($draft->contributors) > 0) ? $draft->contributors->map(fn($c) => ['given_name' => $c->given_name, 'family_name' => $c->family_name, 'email' => $c->email, 'affiliation' => $c->affiliation, 'orcid_id' => $c->orcid, 'corresponding' => $c->is_corresponding, 'sequence' => $c->position]) : [$viewModel->defaultContributor()])) }}">

        <!-- Langkah 1: Panduan, Ketentuan, & Pemilihan Seksi Jurnal -->
        @include('author.submissions.partials.step-1-section')

        <!-- Langkah 2: Unggah Berkas Naskah Utama -->
        @include('author.submissions.partials.step-2-files')

        <!-- Langkah 3: Metadata Naskah Ilmiah -->
        @include('author.submissions.partials.step-3-metadata')

        <!-- Langkah 4: Kontributor & Penulis Bersama -->
        @include('author.submissions.partials.step-4-contributors')

        <!-- Langkah 5: Konfirmasi & Pernyataan Integritas Ilmiah -->
        @include('author.submissions.partials.step-5-confirm')

        <!-- Navigasi Bawah Wizard -->
        @include('author.submissions.partials.wizard-navigation')
    </form>

    <!-- Modal Dialog: Tambah Penulis Bersama -->
    @include('author.submissions.partials.modal-author')

</div>
@endsection

@push('scripts')
    @include('author.submissions.partials.scripts')
@endpush
