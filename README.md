# OJS RPL Backend

## Implementation status and API contract

This repository currently contains a Laravel scaffold and a **planned**
[OpenAPI contract](docs/openapi.yaml). The documented domain endpoints are not yet
registered or implemented. Do not use the contract as evidence that authentication,
authorization, submission or publishing functionality is production-ready.

The final implementation baseline **1.0.0** defines 196 planned operations.
Start with the [selected technology stack](docs/architecture.md),
[UC-01–18 acceptance scenarios](docs/use-cases.md),
[client integration guide](docs/integration-guide.md) and
[compatibility notes](docs/changelog.md). Final contract status does not mean
stakeholder approval or a deployed implementation. See the
[contract decisions and gap closure](docs/contract-decisions.md) for workflow,
privacy, upload, integration and configuration rules, and the
[historical SIRIDA proposal review](docs/review-srs.md) for original findings.
Changes from the drafts require regenerating client mocks and SDKs; upload-session
routes now use `/api/v1/upload-sessions` and several response/request schemas changed.

Validate the contract with Node.js 22.12+ (development/CI tooling only):

```sh
npm ci --ignore-scripts
npm run check:openapi
```

Validate the Laravel scaffold with the PHP version required by `composer.lock`
(currently PHP 8.4.1+):

```sh
composer install --no-interaction --prefer-dist
composer validate --strict
composer test
vendor/bin/pint --test
```

Tests use an in-memory SQLite database and a test-only encryption key, so they do
not require a personal `.env` or a running PostgreSQL service. Normal application
setup still requires copying `.env.example`, generating a unique application key
with `php artisan key:generate` and configuring/migrating the database. Never use
the test key in a deployed environment.

GitHub Actions runs contract checks and scaffold tests on pull requests. These
checks do not yet measure coverage or validate unimplemented domain endpoints.

## Menjalankan Proyek (Running the Project Locally)

Terdapat dua cara untuk menjalankan aplikasi di lingkungan lokal:
1. **Opsi 1 (Direkomendasikan): Full Docker Compose** — Menjalankan seluruh stack (Web Nginx + PHP 8.4-FPM, MySQL, Redis, Mailpit, Azurite) di dalam kontainer tanpa perlu menginstal PHP atau driver database di mesin lokal Anda.
2. **Opsi 2: Hybrid Development** — Menjalankan layanan database dan storage di Docker, sedangkan aplikasi Laravel dan Vite dijalankan langsung di mesin lokal.

---

### Opsi 1: Menggunakan Docker Compose (Rekomendasi)

#### 1. Salin Konfigurasi Environment
Pastikan Anda berada di direktori `ojs-rpl-backend`:
```sh
cp .env.example .env
```

#### 2. Bangun (*Build*) dan Jalankan Seluruh Kontainer
Jalankan seluruh service (`app`, `mysql`, `redis`, `mailpit`, dan `azurite`) di latar belakang:
```sh
docker compose up -d --build
```

#### 3. Inisialisasi Kunci Aplikasi & Migrasi Database
Jalankan perintah ini di dalam kontainer `app`:
```sh
# Generate application key (jika belum ada)
docker compose exec app php artisan key:generate

# Jalankan migrasi tabel database
docker compose exec app php artisan migrate
```

#### 4. Akses Layanan Aplikasi
Setelah seluruh kontainer berstatus *healthy* dan aktif:
- **Web App / REST API**: [`http://localhost:8000`](http://localhost:8000)
- **Halaman Masuk (Login)**: [`http://localhost:8000/login`](http://localhost:8000/login)
- **Mailpit Webmail UI**: [`http://localhost:8025`](http://localhost:8025) (SMTP pada port `1025`)
- **MySQL 8.0**: `127.0.0.1:3306` (Database: `ojs_rpl`, User: `ojs_rpl`, Password: `ojs_rpl_dev`)
- **Redis 7**: `127.0.0.1:6379`
- **Azurite (Azure Blob Storage Emulator)**: `127.0.0.1:10000`

#### Perintah Tambahan Docker:
```sh
# Memantau log aplikasi secara real-time
docker compose logs -f app

# Masuk ke terminal shell di dalam kontainer aplikasi
docker compose exec -it app sh

# Menjalankan perintah artisan lainnya
docker compose exec app php artisan <command>

# Membuka konsol MySQL langsung
docker compose exec mysql sh -c 'mysql -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'

# Menghentikan seluruh layanan
docker compose down

# Menghentikan layanan dan menghapus volume data (reset total)
docker compose down -v
```

---

### Opsi 2: Menjalankan Secara Hybrid (Host PHP + Docker Services)

Jika Anda lebih memilih menjalankan server Laravel langsung di terminal mesin lokal Anda:

#### 1. Salin Konfigurasi Environment & Nyalakan Layanan Pendukung
```sh
cp .env.example .env
docker compose up -d --wait mysql redis mailpit azurite
```

#### 2. Pasang Dependensi & Bangun Aset Frontend
Pastikan mesin lokal Anda sudah terpasang PHP 8.4+ (beserta ekstensi `php-mysqlnd` / `pdo_mysql`) dan Node.js 22+:
```sh
# Pasang dependensi PHP
composer install --no-interaction --prefer-dist

# Pasang dependensi Node dan kompilasi aset Vite
npm install
npm run build
```

> **Catatan Frontend:**  
> Jika sedang aktif mendesain antarmuka/Blade, jalankan `npm run dev` di terminal terpisah agar fitur *Hot Module Replacement* (HMR) aktif. Jika tidak menjalankan `npm run dev`, pastikan sudah menjalankan `npm run build` agar tidak terjadi error `ViteManifestNotFoundException`.

#### 3. Inisialisasi Aplikasi & Migrasi Database
```sh
php artisan key:generate
php artisan migrate
```

#### 4. Jalankan Server Pengembangan Laravel
```sh
php artisan serve
```
Aplikasi dapat diakses di [`http://127.0.0.1:8000`](http://127.0.0.1:8000).


## Laravel

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
