# Dokumentasi Spesifikasi Teknis & Integrasi API Halaman Login
## Portal Penulis — BRIDA Kota Makassar (`ojs-rpl-backend`)

Dokumen ini berisi spesifikasi teknis, arsitektur backend, serta panduan integrasi API untuk fitur autentikasi (login) pada aplikasi `ojs-rpl-backend`.

---

## 1. Arsitektur Data & Alur Autentikasi (Authentication Flow)

Aplikasi `ojs-rpl-backend` mendukung dua mode transportasi autentikasi sesuai standar OpenAPI 3.1.1 dan skenario deployment:

### A. Web Session / BFF Mode (Server-Rendered Client)
1. Pengguna membuka `/login` (GET) → `LoginController::showLoginForm()`.
2. Pengguna mengirimkan kredensial ke `/login` (POST) → `LoginController::login()`.
3. Validasi & Rate Limiting ditangani oleh `LoginRequest::authenticate()`.
4. Jika berhasil: Sesi diregenerasi (`$request->session()->regenerate()`) dan pengguna dialihkan ke `route('dashboard')`.
5. Jika gagal: `RateLimiter::hit()` mencatat kegagalan per kombinasi IP + identifier. Jika mencapai 5 kali, `Lockout` event dipicu dan exception `ValidationException` (HTTP 422 / 429) dikembalikan.

### B. REST API Bearer Mode (`POST /api/v1/auth/login`)
- **Headers Required**: `Content-Type: application/json`, `Accept: application/json`
- **Request Body Payload (`LoginRequest`)**:
  ```json
  {
    "login": "penulis@makassarkota.go.id",
    "password": "PasswordSangatAman123!"
  }
  ```
- **Response Success (HTTP 200 OK — `AuthTokenResponse`)**:
  ```json
  {
    "data": {
      "tokens": {
        "token_type": "Bearer",
        "access_token": "eyJhbGciOi...",
        "access_token_expires_at": "2026-10-09T09:30:00Z",
        "refresh_token": "def50200...",
        "refresh_token_expires_at": "2026-10-09T21:30:00Z"
      },
      "user": {
        "id": "01995e1c-f2b0-7a9c-8c9d-b065ea3dc8f1",
        "type": "user",
        "attributes": {
          "full_name": "Dr. Ahmad Ridwan",
          "email": "penulis@makassarkota.go.id",
          "username": "ahmad_ridwan",
          "affiliation": "Universitas Hasanuddin",
          "status": "active",
          "email_verified": true,
          "version": 1
        }
      },
      "memberships": [],
      "system_roles": []
    },
    "meta": {
      "request_id": "01K5REQUEST00000000000001"
    }
  }
  ```

- **Response Error Rate Limited (HTTP 429 Too Many Requests — `Problem` RFC 7807)**:
  ```json
  {
    "type": "https://api.jurnal-brida.example/problems/rate-limited",
    "title": "Too Many Requests",
    "status": 429,
    "code": "RATE_LIMITED",
    "detail": "Terlalu banyak percobaan login. Silakan coba lagi dalam 900 detik.",
    "instance": "/api/v1/auth/login",
    "request_id": "01K5REQUEST00000000000001",
    "errors": []
  }
  ```

---

## 2. Komponen Backend & Struktur Berkas

1. **Form Request (`App\Http\Requests\Auth\LoginRequest`)**:
   - Berkas: [`app/Http/Requests/Auth/LoginRequest.php`]
   - Bertanggung jawab memvalidasi input, mendeteksi tipe masukan (email vs username), mengelola rate-limiting 15 menit, dan memicu event `Lockout`.

2. **Controller (`App\Http\Controllers\Auth\LoginController`)**:
   - Berkas: [`app/Http/Controllers/Auth/LoginController.php`]
   - Bertindak sebagai pengendali *thin controller* yang memanggil `$request->authenticate()`, meregenerasi sesi, dan menangani logout.

3. **Perutean Aplikasi (`routes/web.php`)**:
   - Berkas: [`routes/web.php`]
   - Rute terdaftar:
     - `GET /login` (`name: login`) — Tampilan halaman login (Middleware: `guest`).
     - `POST /login` — Memproses autentikasi.
     - `POST /logout` (`name: logout`) — Memproses pembatalan sesi (Middleware: `auth`).
     - `GET /dashboard` (`name: dashboard`) — Halaman tujuan utama setelah login berhasil.

---

## 3. Aksesibilitas (A11y) & Keamanan Form Tampilan

Halaman tampilan Blade di [`resources/views/auth/login.blade.php`] memenuhi standar aksesibilitas WCAG 2.1 AA:
- **Form Association & Labels**: Setiap elemen input terhubung dengan `<x-label for="...">`.
- **Screen Reader Error Feedback**: Menggunakan atribut `aria-invalid="true|false"` dan `aria-describedby="[field]-error"` untuk menyampaikan status kesalahan validasi ke perangkat pembaca layar.
- **Keyboard Focus Ring**: Menggunakan ring fokus bergaris jelas yang dapat di-tab melalui keyboard.

---

## 4. Pengujian Otomatis & Penjaminan Kualitas (QA)

Seluruh fungsi autentikasi telah diuji secara otomatis menggunakan suite pengujian Feature Test pada berkas [`tests/Feature/Auth/LoginPageTest.php`].

### Skenario Uji Tercover:
1. `test_halaman_login_dapat_diakses()` — Memastikan HTTP 200 OK dan elemen teks utama muncul.
2. `test_halaman_utama_meredirect_ke_login()` — Memastikan rute `/` meredirect ke `/login`.
3. `test_pengguna_dapat_login_dengan_kredensial_valid()` — Memastikan user terautentikasi dan dialihkan ke `/dashboard`.
4. `test_pengguna_gagal_login_dengan_kata_sandi_salah()` — Memastikan user tetap *guest* dan menerima error validasi.
5. `test_login_terkunci_jika_terlalu_banyak_percobaan_gagal()` — Memastikan Rate Limiter mengunci percobaan setelah 5x gagal.
6. `test_pengguna_dapat_logout()` — Memastikan sesi dihapus dan dialihkan kembali ke `/login`.

### Perintah Menjalankan Pengujian:
```bash
php artisan test --filter=LoginPageTest
```
