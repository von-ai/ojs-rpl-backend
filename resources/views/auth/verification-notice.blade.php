<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Verifikasi email SIRIDA</title></head><body>
<h1>Verifikasi email</h1>
@if (session('verified'))
<p role="status">Email terverifikasi. Akun Anda aktif. Fitur login belum tersedia pada MVP ini.</p>
@else
@if (isset($verificationError))<p role="alert">{{ $verificationError }}</p>@endif
@if (session('registered'))<p role="status">Akun berhasil dibuat. Silakan periksa email Anda.</p>@endif
@if (session('resent'))<p role="status">Jika akun memenuhi syarat, email verifikasi akan dikirim. Tunggu setidaknya satu menit sebelum mencoba lagi.</p>@endif
<p>Buka tautan verifikasi di email, lalu konfirmasikan. Tautan berlaku 24 jam.</p>
@if ($errors->any())<ul role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
<form method="POST" action="{{ route('verification.resend') }}">
@csrf
<label>Email <input type="email" name="email" autocomplete="email" maxlength="255" required></label>
<button type="submit">Kirim ulang verifikasi</button>
</form>
@endif
<p><a href="{{ route('register') }}">Kembali ke registrasi</a></p>
</body></html>
