# Catatan Perubahan & Arsitektur (Changelog)

## Refaktor Arsitektur: Peralihan ke Pure Modular Monolith — 22 Oktober 2026

* **Peniadaan API Service:** Mengeliminasi seluruh spesifikasi OpenAPI 1.0.0 (196 operasi) dan dependensi ekosistem kontrak (`@redocly/cli`, `ajv`, `yaml`).
* **Penyederhanaan Autentikasi:** Menghapus implementasi JWT RS256 (`TokenService`), tabel `refresh_tokens`, dan model `AuthSession`. Seluruh autentikasi dipusatkan pada sistem sesi stateful bawaan Laravel (`auth:web`) dengan perlindungan CSRF otomatis dan penegakan lockout 15 menit.
* **Perampingan Kode:** Menghapus rute `routes/api.php`, controller `ApiAuthController`, exception `ApiException`, middleware `IdentityApi` & `ApiRequestId`, serta transformer `RegistrationUserResource`.
* **Perampingan Skema Basis Data (ERD):** Mengeliminasi 12 tabel usang yang tidak lagi relevan pasca-penghapusan API service (`oauth_states`, `file_scopes`, `upload_sessions`, `upload_parts`, `operations`, `integration_profiles`, `integration_candidates`, `integration_configurations`, `integration_tests`, `system_settings_versions`, `idempotency_records`), merampingkan skema dari 70 menjadi 58 tabel domain InnoDB dengan 114 Foreign Key.
* **CI/CD Cepat:** Pipeline GitHub Actions disederhanakan murni menjalankan `composer test` (PHPUnit) dan `pint` tanpa overhead kompilasi Node/OpenAPI check.

## Penyesuaian Schema Identity — 8 Oktober 2026

* Mengadopsi ULID char(26) sebagai primary key `users.id` dan foreign key relasi pengguna.
* Menghapus pemisahan `public_id`.

## Implementasi Fitur Registrasi & Autentikasi — 8 Oktober 2026

* Alur pendaftaran akun penulis, verifikasi email satu kali pakai, dan kirim ulang tautan verifikasi.
* Sesi login web (`/login`) dengan penegakan pembatasan tingkat percobaan (*rate limiting*) dan pencatatan audit log keamanan.
