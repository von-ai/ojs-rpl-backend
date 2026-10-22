<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Daftar SIRIDA</title></head>
<body>
<h1>Daftar akun SIRIDA</h1>
<p>Isi data berikut, lalu verifikasi email Anda.</p>
@if ($errors->any())
<ul role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
@endif
<form method="POST" action="{{ route('register.store') }}">
    @csrf
    <p><label>Nama lengkap <input name="full_name" value="{{ old('full_name') }}" minlength="2" maxlength="200" autocomplete="name" required></label></p>
    <p><label>Email <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required></label></p>
    <p><label>Username <input name="username" value="{{ old('username') }}" pattern="[A-Za-z0-9_.-]{3,50}" minlength="3" maxlength="50" autocomplete="username" required></label></p>
    <p><label>Afiliasi institusi <input name="affiliation" value="{{ old('affiliation') }}" minlength="2" maxlength="255" autocomplete="organization" required></label></p>
    <p><label>Password <input type="password" name="password" minlength="12" maxlength="128" autocomplete="new-password" required></label></p>
    <p><label>Konfirmasi password <input type="password" name="password_confirmation" minlength="12" maxlength="128" autocomplete="new-password" required></label></p>
    <p>Password minimal 12 karakter. Username hanya huruf, angka, titik, garis bawah, atau tanda hubung.</p>
    <button type="submit">Daftar</button>
</form>
<p><a href="{{ route('verification.notice') }}">Kirim ulang email verifikasi</a></p>
</body></html>
