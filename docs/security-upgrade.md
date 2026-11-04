# Upgrade keamanan dan operasi SIRIDA

Perubahan ini mencakup migrasi data. Terapkan dalam jendela pemeliharaan setelah backup database dan storage teruji. PR tidak otomatis menerapkan perubahan ke deployment atau database pengguna.

## Dampak migrasi

- File lama berlabel clean diubah menjadi pending. Unduh, undangan review, persetujuan dan produksi yang memerlukan file tersebut menunggu pemindaian sukses. File hilang atau terinfeksi tetap tidak dapat diunduh.
- Remember token lama dibatalkan. Sesi tanpa registry baru harus login ulang.
- Putaran review lama tanpa file anonim terpisah, checksum dan rubrik yang memadai ditutup; undangan aktifnya dibatalkan dan tindakan sistem diaudit. SE perlu menyiapkan putaran anonim baru. Riwayat lama tetap tersimpan.
- Grant global legacy reviewer/produksi diturunkan menjadi grant jurnal berdasarkan penugasan yang sudah tersimpan. Pengguna tanpa bukti penugasan memerlukan penetapan peran jurnal eksplisit melalui administrator.
- Unique/FK baru memerlukan data yang konsisten. Selesaikan duplikasi nomor, slot aktif atau relasi section/jurnal pada salinan staging sebelum migrasi produksi. Jangan menghapus riwayat audit untuk memaksa migrasi.
- Rollback tidak otomatis mengembalikan file menjadi clean atau memperluas kembali grant. Gunakan backup teruji jika perlu memulihkan seluruh versi aplikasi dan skemanya.

## Konfigurasi

Tetapkan APP_ENV=production, APP_DEBUG=false, APP_URL HTTPS yang benar, APP_KEY unik, SESSION_SECURE_COOKIE=true, SESSION_HTTP_ONLY=true, SESSION_SAME_SITE=lax, SESSION_LIFETIME maksimum 120. Sesi dienkripsi oleh aplikasi. Jangan memakai key test dari phpunit.xml.

Tetapkan TRUSTED_PROXIES ke IP/CIDR reverse proxy yang benar; hindari wildcard. TRUSTED_HOSTS hanya untuk hostname tambahan yang memang dipakai, termasuk probe internal bila diperlukan. Host utama mengikuti APP_URL.

Gunakan disk privat yang dapat dibaca web dan worker. PRIVATE_UPLOAD_DISK=local menyimpan pada storage privat Laravel; untuk beberapa replika mount volume privat yang sama atau pasang adapter object storage privat. Jangan mengekspos direktori privat melalui symlink public/storage atau ACL bucket publik. Simpan APP_KEY/SMTP/database/Redis credentials pada secret store deployment.

Untuk ClamAV daemon gunakan FILE_SCAN_DRIVER=clamd, CLAMD_HOST dan CLAMD_PORT. Alternatif FILE_SCAN_DRIVER=clamav membutuhkan binary CLAMAV_BINARY beserta signature yang diperbarui. Driver disabled sengaja menahan seluruh file dalam karantina. Socket clamd tidak memiliki autentikasi; hanya izinkan jaringan privat aplikasi/worker. [Dokumentasi INSTREAM](https://docs.clamav.net/manual/Usage/ClamdProtocol.html) dan [Docker resmi](https://docs.clamav.net/manual/Installing/Docker.html) menjadi rujukan protokol dan kebutuhan memori.

Compose menyediakan clamav, worker, scan-worker dan scheduler. ClamAV memakai image digest terpin, platform amd64 dan batas memori 4 GB; startup/signature update membutuhkan waktu dan koneksi keluar. Jangan mengubah stack aplikasi yang sedang digunakan hanya untuk menjalankan test.

Untuk deployment di luar Compose, jalankan proses berikut secara terawasi dengan storage/database dan konfigurasi yang sama:

```sh
php artisan queue:work database --queue=identity,notifications,imports,default --tries=3 --timeout=120
php artisan queue:work database --queue=file-security --tries=3 --timeout=180
php artisan schedule:work
```

Retry-after database/identity default 240 detik, harus lebih panjang dari timeout worker. Perubahan Terraform memperbarui daftar queue, tetapi scanner privat dan storage bersama tetap harus disediakan pada environment cloud sebelum digunakan; PR ini tidak memprovisikan deployment berbayar.

## Urutan penerapan

1. Hentikan perubahan data selama upgrade, backup database serta storage, lalu uji migrasi pada salinan staging.
2. Pasang dependensi dari lockfile dan build aset (`composer install`, `npm ci --ignore-scripts`, `npm run build`). Jangan jalankan demo seeder pada produksi; seeder pengguna ditolak pada APP_ENV=production.
3. Jalankan `php artisan migrate --force`, periksa status migrasi dan log. Bangun cache konfigurasi/rute/view setelah seluruh environment benar.
4. Pastikan scanner healthy, kemudian jalankan `php artisan files:scan-pending` untuk mengantrekan pemeriksaan file lama. Jalankan/restart worker dan scheduler dengan versi kode baru.
5. Pantau queue file-security/imports/identity/notifications dan failed_jobs. Hasil error perlu diperiksa dan diantrekan ulang setelah akar masalah selesai; jangan mengubah status database menjadi clean secara manual.
6. Login ulang dan lakukan smoke test file privat, putaran anonim baru, persetujuan author, proofreading, persetujuan EIC dan publikasi staging. Verifikasi file publik cocok dengan checksum persetujuan.
7. Buka trafik setelah hasil pemeriksaan dan konfigurasi benar. Jangan mengirim ulang semua notifikasi atau mengaktifkan kembali sesi lama secara massal.

## Menjalankan verifikasi

```sh
composer validate --strict
vendor/bin/pint --test
composer test
npm run build
composer audit
npm audit
```

Test default selalu SQLite in-memory. Pengujian MySQL menggunakan schema tetap `sirida_latest_test` pada host 127.0.0.1, port default 13319; override port/password melalui RPL_TEST_DB_PORT dan RPL_TEST_DB_PASSWORD. Gunakan server pengujian khusus, jangan arahkan port tersebut ke database produksi. RPL_TEST_MYSQL=1 mengaktifkan mode ini. RPL_TEST_CLAMD_HOST dan RPL_TEST_CLAMD_PORT (default 13320) mengaktifkan uji scanner asli. CI menjalankan SQLite dan MySQL secara berurutan agar fixture storage tidak saling dihapus.
