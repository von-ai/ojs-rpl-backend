# OJS RPL Backend

## PostgreSQL untuk pengembangan lokal

Service menggunakan PostgreSQL 18. Pastikan Docker dan Docker Compose
tersedia. Salin konfigurasi contoh:

```sh
cp .env.example .env
```

Jalankan PostgreSQL:

```sh
docker compose up -d --wait postgres
```

Database tersedia di `127.0.0.1:5432`, dengan nama database dan pengguna
`ojs_rpl`, serta kata sandi pengembangan `ojs_rpl_dev`. Pengaturan dapat
diubah melalui variabel `DB_*` di `.env`. Aplikasi yang berjalan di host
dapat menggunakan konfigurasi tersebut untuk terhubung ke PostgreSQL.

Periksa status atau buka konsol database:

```sh
docker compose ps
docker compose exec postgres sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

Hentikan service:

```sh
docker compose down
```

Data tersimpan di volume `postgres_data` dan tetap ada setelah service
dihentikan. `docker compose down -v` menghapus volume beserta seluruh data.
Variabel nama database, pengguna, dan kata sandi hanya diterapkan saat
inisialisasi volume pertama kali; mengubah `.env` tidak mengubah database
yang sudah tersimpan.

Jika volume sudah berisi data PostgreSQL 16, migrasikan data ke PostgreSQL
18 melalui dump/restore atau `pg_upgrade` sebelum menggunakan konfigurasi
ini. Mengganti versi image tidak otomatis memigrasikan data yang ada.
