# Registrasi MVP

Implementasi 8 Oktober 2026: register, verify email, resend email melalui API dan
Blade tanpa CSS/JavaScript. Login/JWT, enrollment serta policy Reader/Author
per jurnal merupakan pekerjaan berikutnya; registrasi tidak membuat role reviewer
atau editorial. Akun terverifikasi menjadi `active`, tetapi status tersebut sendiri
bukan grant akses ke naskah atau jurnal.

## Menjalankan lokal

Pastikan PHP sesuai Composer, `pdo_pgsql`, dependency Composer dan APP_KEY unik
tersedia. Jangan mengganti APP_KEY yang sudah digunakan karena payload antrean
email terenkripsi dengan key tersebut.

```sh
docker compose up -d --wait postgres
php artisan migrate
php artisan serve
```

Buka `http://localhost:8000/register`. Atur `APP_URL` ke origin aplikasi yang benar
untuk tautan email. Di terminal lain jalankan worker khusus:

```sh
php artisan queue:work identity --queue=identity --tries=5
```

Antrean `identity` memakai tabel `jobs` pada koneksi database aplikasi yang sama,
terlepas dari QUEUE_CONNECTION/DB_QUEUE_CONNECTION. Insert job dilakukan sebelum
commit dalam transaksi pembuatan identity/challenge/audit sehingga crash setelah
commit tidak menghilangkan email. Job memakai `ShouldBeEncrypted`; token tidak
tersimpan plaintext pada payload database. SMTP gagal diretry dengan backoff;
setelah lima kegagalan job masuk `failed_jobs`, dapat dicoba kembali dengan
`php artisan queue:retry <uuid>`. Token yang expired/consumed tidak dikirim kembali.
Delivery SMTP dapat terjadi lebih dari sekali bila worker mati setelah provider
menerima email; aktivasi akun tetap sekali pakai.

Default `MAIL_MAILER=log` menyimpan email untuk development di
`storage/logs/laravel.log`; ambil tautannya di sana untuk mencoba verifikasi.
Gunakan SMTP/SES yang dikonfigurasi untuk email sungguhan, bukan log mailer pada
produksi. Log mailer development memuat tautan rahasia; jangan membagikan log.

## API

- `POST /api/v1/auth/register`: `full_name`, `email`, `username`, `password`, `affiliation` → 201 UserResponse.
- `POST /api/v1/auth/email/verify`: `token` → 200 UserResponse; unknown/expired/consumed → 410.
- `POST /api/v1/auth/email/resend`: `email` → 202 body kosong untuk semua alamat valid.

Semua request API memakai `Content-Type: application/json`. Field tambahan ditolak,
termasuk `requested_role`, `roles`, `journal_id` dan `status`. Form Blade juga
memerlukan `password_confirmation` dan CSRF. Password tidak disimpan dalam old input.
Email/username dinormalisasi ke huruf kecil; email maksimum 255 karakter sesuai
storage. Username 3–50 karakter ASCII `[A-Za-z0-9_.-]`; password 12–128 karakter
menggunakan Argon2id agar password panjang tidak terpotong oleh batas bcrypt.
Nama lengkap dan afiliasi mengikuti kontrak. Duplikasi email/username → 409.

Challenge disimpan sebagai SHA-256, berlaku 24 jam, dikonsumsi sekali dengan lock
user lalu challenge. PostgreSQL `clock_timestamp()` UTC diambil setelah lock;
`now >= expires_at` ditolak. Kirim ulang memiliki cooldown 60 detik dan membatalkan
challenge lama. Email worker melewati challenge yang sudah tidak valid. GET tautan
email hanya membuka form konfirmasi, sehingga email-link scanner tidak mengaktifkan
akun; perubahan state terjadi melalui POST dengan CSRF.

Rate limit shared register/verify/resend: 10 request/menit dan 100/jam per IP,
plus cooldown resend per akun. Error API mengikuti Problem, dengan correlation ID,
Cache-Control no-store dan Retry-After untuk 429. Browser dan API memakai service
yang sama; registrasi/verifikasi tidak membuat sesi login atau token API.

## Data dan batas implementasi

`users.id` adalah primary key ULID char(26) sesuai SIRIDA / ERD v1.0.0.
Tidak ada `public_id` terpisah. Eloquent HasUlids menghasilkan ID secara otomatis,
dan API memakai primary key tersebut. Foreign key user_profiles.user_id,
identity_challenges.user_id, audit_logs.actor_id dan sessions.user_id memakai ULID.
Username wajib dan unik. Nama/password memakai kolom scaffold `name`/`password`;
API memproyeksikan `full_name` tanpa mengekspos password.

Migrasi awal telah disesuaikan untuk database development berisi dummy. Untuk
checkout yang sudah menjalankan migrasi versi bigint, jalankan
`php artisan migrate:fresh` untuk membuat ulang schema; perintah ini menghapus
seluruh tabel/data pada schema terpilih. Jangan gunakan untuk data produksi.

Tabel baru: user_profiles, identity_challenges, audit_logs. Audit identity bersifat
append-only di application service, mencatat resource public ID, actor/action/time,
tanpa password/token. Schema audit menyiapkan journal scope nullable; FK ke journals
perlu ditambahkan saat tabel jurnal dibangun. Grant/membership dan audit workflow
lain belum diimplementasikan. Belum ada halaman login; MVP menampilkan keberhasilan
verifikasi dengan jelas.

## Validasi

```sh
php artisan test
vendor/bin/pint --test
npm run check:openapi
```

Tes feature mencakup web/API, token single-use/expiry, normalisasi/duplikasi,
privilege injection, password panjang Unicode, encrypted queue delivery,
rollback bila enqueue gagal dan rate limiting. SQLite suite tidak membuktikan race
lintas koneksi PostgreSQL; uji transaksi kompetitif tetap diperlukan sebelum rilis.
Untuk isolasi integration test PostgreSQL gunakan database/schema khusus melalui
DB_CONNECTION/DB_DATABASE/DB_SCHEMA. Jangan menjalankan RefreshDatabase pada schema
yang berisi data pengguna.
