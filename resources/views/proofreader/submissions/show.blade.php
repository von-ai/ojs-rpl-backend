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

    <!-- Banner Status Eskalasi Aktif -->
    @if (!empty($submission['escalation']['has_active']))
        <div class="p-4 rounded-xl bg-purple-50/90 border border-purple-200 text-purple-900 flex items-start gap-3 shadow-2xs">
            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="text-xs space-y-1">
                <div class="font-bold flex items-center gap-2">
                    <span class="text-purple-950 font-semibold">Naskah Dalam Tiket Eskalasi Redaksi ({{ $submission['escalation']['ticket_id'] }})</span>
                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-200 text-purple-900 border border-purple-300">Sedang Ditinjau</span>
                </div>
                <p class="text-purple-800 leading-relaxed">
                    Proofreader telah mengajukan tiket eskalasi terkait ketidaksesuaian naskah. Persetujuan akhir (sign-off) ditangguhkan sementara hingga Section Editor memberikan arahan redaksional.
                </p>
            </div>
        </div>
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
