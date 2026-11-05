@extends('layouts.author')

@section('title', 'Kuitansi Pembayaran APC')

@section('content')
    <div class="max-w-2xl mx-auto space-y-4">
        <section class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <p class="text-sm text-gray-600">{{ $submission->journal?->name }}</p>
                <h1 class="text-xl font-bold text-gray-900">Kuitansi Pembayaran APC</h1>
            </div>
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-600">Nomor tagihan</dt>
                <dd class="font-semibold text-gray-900">{{ $payment->invoice_number }}</dd>
                <dt class="text-gray-600">Nomor naskah</dt>
                <dd>{{ $submission->submission_number }}</dd>
                <dt class="text-gray-600">Jumlah diterima</dt>
                <dd class="font-semibold text-gray-900">Rp {{ number_format((float) $payment->amount_paid, 2, ',', '.') }}</dd>
                <dt class="text-gray-600">Diverifikasi</dt>
                <dd>{{ $payment->verified_at->copy()->setTimezone('Asia/Makassar')->format('d M Y, H:i') }} WITA</dd>
                <dt class="text-gray-600">Status</dt>
                <dd class="font-semibold text-emerald-700">Lunas</dd>
            </dl>
        </section>

        <div class="flex items-center gap-3 print:hidden">
            <x-button type="button" onclick="window.print()">Cetak kuitansi</x-button>
            <a href="{{ route('author.submissions.show', $submission->id) }}" class="text-sm font-semibold text-brand-maroon">
                Kembali ke naskah
            </a>
        </div>
    </div>
@endsection
