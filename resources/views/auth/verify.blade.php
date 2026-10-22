<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="referrer" content="no-referrer"><title>Konfirmasi email</title></head><body>
<h1>Konfirmasi alamat email</h1>
@if (is_string($token) && strlen($token) >= 20 && strlen($token) <= 512)
<form method="POST" action="{{ route('verification.verify') }}">
@csrf
<input type="hidden" name="token" value="{{ $token }}">
<button type="submit">Verifikasi email saya</button>
</form>
@else
<p>Tautan tidak valid.</p>
@endif
<p><a href="{{ route('verification.notice') }}">Kirim ulang tautan verifikasi</a></p>
</body></html>
