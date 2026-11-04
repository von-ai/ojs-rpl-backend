@extends('layouts.auth')
@section('title', 'Pemeliharaan | SIRIDA')
@section('content')
<section class="w-full max-w-lg mx-auto rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 space-y-4">
    <h1 class="text-xl font-bold text-gray-900">Sedang dalam pemeliharaan</h1>
    <p class="text-sm text-gray-600">{{ $message }}</p>
</section>
@endsection
