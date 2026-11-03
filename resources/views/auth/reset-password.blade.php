@extends('layouts.auth')
@section('title', 'Buat kata sandi baru | BRIDA Makassar')
@section('content')
<section class="w-full max-w-md mx-auto bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 space-y-6">
    <h1 class="text-xl font-bold text-gray-900">Buat kata sandi baru</h1>
    @if(session('status'))
        <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <ul role="alert" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <label for="email" class="block text-sm font-semibold text-gray-700">Alamat email</label>
            <x-input id="email" name="email" type="email" maxlength="254" autocomplete="email" :value="old('email', $email ?? '')" required />
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="password" class="block text-sm font-semibold text-gray-700">Kata sandi baru</label>
            <x-input id="password" name="password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required />
            <p class="text-xs text-gray-500">Gunakan 12–128 karakter.</p>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Konfirmasi kata sandi</label>
            <x-input id="password_confirmation" name="password_confirmation" type="password" minlength="12" maxlength="128" autocomplete="new-password" required />
        <x-button>Simpan kata sandi baru</x-button>
    </form>
    <a href="{{ route('login') }}" class="block text-sm font-semibold text-brand-maroon hover:underline">Kembali ke halaman masuk</a>
</section>
@endsection
