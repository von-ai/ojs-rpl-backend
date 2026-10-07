# SIRIDA — baseline arsitektur dan teknologi 1.0.0

Tanggal: 7 Oktober 2026. Status: **kontrak final untuk implementasi**, diajukan
melalui PR untuk review tim. Bukan pernyataan bahwa mitra sudah menyetujui setiap
keputusan, server tersedia, atau endpoint telah selesai dibangun.

Sumber kebutuhan: [proposal/SRS](https://docs.google.com/document/d/1bWldGLdB9Ek2bgmJtyupGi5yA7C1E409w6Gs65p__OM/)
(tab Proposal, UC-01–18, NF-01–10) dan
[catatan mitra](https://docs.google.com/document/d/10xW2fS-EfoSJnsHYmCIoMZm_T48a1GOLHD2JnpNoTBo/).
Keputusan berikut menyelesaikan pilihan implementasi; perbedaan dari proposal
harus tercatat saat review, bukan dianggap kutipan persetujuan mitra.

## Stack yang dipilih

| Lapisan | Baseline | Bukti/status di repository |
| --- | --- | --- |
| Aplikasi | Modular monolith Laravel 13, PHP 8.4.1+ | Laravel `^13.17`, PHP `^8.4.1` pada Composer; domain belum dibangun. Ikuti lockfile, jangan upgrade otomatis saat deploy. |
| UI | Blade, Livewire 4, Tailwind CSS 4 | Blade/Tailwind tersedia; Livewire dipilih, belum menjadi dependency. Tim frontend tetap memiliki implementasi halaman. |
| Aset dan kontrak | Vite 8; Node 22.12+; Redocly CLI, AJV 2020, YAML | Dependency build/CI tersedia. Produksi hanya menerima hasil build, tanpa proses Node. |
| Database | PostgreSQL 18, Eloquent, migrations | PostgreSQL 18 tersedia sebagai konfigurasi Compose lokal; skema domain belum ada. |
| Web server | Linux milik/dikelola Kominfo, Nginx + PHP-FPM, HTTPS | Target deployment, belum terverifikasi. Document root hanya `public/`. |
| Queue dan jadwal | Laravel database queue pada PostgreSQL; supervised worker; cron scheduler setiap menit; transactional outbox | Dipilih untuk baseline tanpa layanan antrean tambahan. Belum ada worker domain. |
| Session/cache/locks | Shared database store | Semua instance memakai store bersama. Redis merupakan optimasi setelah pengukuran, bukan prasyarat kontrak. |
| Berkas | Laravel Filesystem/Flysystem + storage privat S3-compatible di infrastruktur yang disetujui Kominfo | Adapter/vendor harus diuji; tidak mengasumsikan AWS atau bucket publik. Single PUT wajib; multipart diiklankan hanya jika lulus uji. |
| Pemeriksaan berkas | Worker antivirus ClamAV, inspeksi MIME/checksum, sanitizer HTML/XML | Dipilih, belum terpasang; scanner tidak tersedia berarti berkas tetap karantina. |
| Identitas API | JWT RS256 melalui library `lcobucci/jwt`; refresh opaque acak, hash disimpan server | Pilihan implementasi, belum dependency. Library bukan pengganti guard, revocation dan policy aplikasi. |
| Pencarian | PostgreSQL full-text search + indeks GIN; konfigurasi `simple` sebagai awal untuk teks Indonesia | Dipilih; relevansi dan query plan harus diuji dengan korpus jurnal. Tidak perlu Elasticsearch pada baseline. |
| Integrasi | SMTP, ORCID authorization code, Crossref deposit, OAI-PMH 2.0/Dublin Core | Kontrak tersedia, kredensial/adapter/sandbox belum diuji. Similarity provider opsional dan default nonaktif. |
| Notifikasi UI | REST polling; SSE opsional bila gateway/worker mendukung | Notifikasi persisten sebagai sumber utama. SSE tidak boleh menjadi satu-satunya cara membaca notifikasi. |
| Pengujian | PHPUnit 12, Pint, Redocly/AJV, GitHub Actions | Pemeriksaan scaffold/kontrak tersedia. Integration test menggunakan PostgreSQL wajib ditambahkan saat domain dibangun. |

Referensi teknis: [deployment Laravel](https://laravel.com/docs/13.x/deployment),
[queue](https://laravel.com/docs/13.x/queues),
[scheduler](https://laravel.com/docs/13.x/scheduling),
[filesystem](https://laravel.com/docs/13.x/filesystem),
[Livewire](https://livewire.laravel.com/docs/4.x/installation),
[JWT library](https://lcobucci-jwt.readthedocs.io/en/stable/),
[PostgreSQL FTS](https://www.postgresql.org/docs/18/textsearch.html).
Versi PHP minimum repository mengikuti dependency terkunci, bukan hanya minimum
framework. Tidak ada instalasi dependency atau deployment yang dilakukan oleh
perubahan kontrak ini.

## Batas modul dan transaksi

Modul: Identity & Membership, Journal Configuration, Submission & Files, Peer Review,
Editorial Decision, Production, Publication, Notifications & Audit, Integrations.
Controller REST dan aksi Livewire memanggil application service dan policy yang sama;
jangan menggandakan aturan workflow dalam UI. Gunakan Form Requests/DTO untuk input,
Policy untuk journal/ownership/assignment, API Resources untuk proyeksi private,
author, reviewer dan public yang berbeda.

PostgreSQL menyimpan sumber kebenaran. Setiap transisi mengunci aggregate terkait,
memeriksa ETag, akses, state dan waktu, lalu menulis state, audit dan outbox dalam
satu transaksi. Worker membaca outbox setelah commit. Konsumen harus idempotent:
delivery boleh diulang, efek bisnis tidak. `afterCommit` saja tidak menggantikan
outbox persisten ketika proses mati setelah commit tetapi sebelum enqueue.

Gunakan foreign key, uniqueness journal-scoped, indeks daftar/deadline, unique DOI,
dan public ID ULID yang immutable. Bigint internal boleh dipertahankan; jangan
mengeksposnya sebagai kontrak baru atau melakukan migrasi destruktif tanpa backfill.
Semua perbandingan deadline memakai jam database UTC setelah lock, bukan jam browser.

## Browser dan autentikasi

UI Blade/Livewire menggunakan session HttpOnly, Secure, SameSite=Lax dan CSRF.
`/api/v1` tetap bearer-only; cookie tidak otomatis memberikan akses API. Aksi UI
memanggil service yang sama dengan identitas terverifikasi, atau BFF meneruskan
bearer server-side bila perlu. Tidak perlu HTTP loopback dari monolith ke dirinya.
Token API tidak disimpan di localStorage dan tidak dimasukkan ke URL/SSE.

Baseline konfigurasi: access token 15 menit, absolute session 24 jam, password
minimum 12 karakter; batas maksimum mengikuti schema. Rotasi refresh sekali pakai
mengunci token family; reuse mencabut family. Client menyerialkan refresh, tidak
melakukan retry paralel terhadap refresh lama. Logout/reset/deaktivasi mencabut
session terkait. JWT memvalidasi signature, allowlist RS256, issuer, audience,
subject, jti, expiry dan not-before; signing key/kid dikelola operator dan tidak
berasal dari URL dalam token. Role JWT tidak menggantikan pemeriksaan membership terkini.
Session UI juga mengikuti batas absolut dan pencabutan, bukan memperpanjang tanpa batas.

## Deployment yang dapat diterima

Kominfo harus menyediakan PHP yang sesuai lockfile + `pdo_pgsql`, PostgreSQL,
worker terawasi, scheduler, storage privat, scanner, SMTP, DNS/TLS dan outbound
allowlist ke provider. Shared hosting yang hanya melayani request PHP tanpa worker,
cron atau penyimpanan privat **tidak memenuhi baseline**; minta VM/layanan terkelola
pada infrastruktur yang disetujui. Docker Compose lokal bukan bukti produksi siap.

Simpan secret di konfigurasi operator/secret store yang tidak berada di document
root atau Git. Reference integrasi dipetakan server-side; jangan menerima arbitrary
URL/credential dari Journal Manager. Pisahkan kredensial staging dan produksi.
Logging hanya correlation ID dan metadata aman; redact token, URL signed, password,
komentar rahasia dan data pribadi. Monitoring mencakup queue/outbox lag, scheduler
heartbeat, scan failures, provider failures dan waktu respons.

Backup database dan file setiap hari, retensi 30 hari, target RPO ≤24 jam/RTO ≤4 jam,
dengan latihan restore triwulanan. Deployment memakai migration kompatibel mundur,
restart worker setelah rilis, dan rollback aplikasi tanpa menghapus data domain.
Release gagal jika readiness kritis gagal; provider opsional boleh degraded dengan
status yang terlihat. `/up` scaffold tidak membuktikan readiness domain.

## Keputusan bisnis baseline dan perubahan proposal

| Keputusan final desain v1 | Asal / implikasi |
| --- | --- |
| Registrasi tanpa pilihan role; reviewer melalui undangan editor per naskah/putaran | Kesepakatan 7 Oktober 2026: hapus pengajuan reviewer mandiri. Direktori jurnal dikelola melalui membership/role, akses manuskrip memerlukan assignment yang diterima. Username tetap wajib. |
| Double-blind; originality/metodologi wajib, skala integer 1–5 | Alur blind dan aspek penilaian berasal dari UC-09/11; skala 1–5 keputusan desain. Guideline versioned, round lama tetap memakai snapshot. |
| Review terlambat read-only sampai extension disetujui | Pengetatan eksplisit UC-09 yang menyebut draft sebelum deadline; detail race/extension merupakan keputusan desain. |
| PDF wajib saat publikasi; HTML/XML sebagai pendamping | Keputusan baseline kompatibilitas pembaca; proposal menyebut PDF/HTML/XML. Perbedaan ini perlu ditinjau saat PR. |
| Maksimum 20 MiB = 20.971.520 byte | Normalisasi unit desain; proposal menulis 20 MB. Client mengikuti `max_upload_bytes`, bukan menebak unit. |
| Supplementary/evidence awal hanya PDF/DOC/DOCX | Allowlist desain; format dataset lain memerlukan revisi kebijakan/schema sebelum diterima. |
| Auto-withdraw revisi nonaktif; similarity nonaktif | Menghindari tindakan otomatis tanpa kebijakan jurnal. Manager boleh mengaktifkan setelah mengisi grace/provider valid. |
| Deadline undangan/review eksplisit, tidak ada durasi tersembunyi | Setting hari hanya membantu pengisian; tanggal absolut per assignment menjadi acuan. |
| Laravel/Blade/Livewire di Kominfo | Menyelaraskan catatan mitra; menggantikan asumsi Next.js/AWS dalam arsitektur proposal. S3-compatible tidak berarti AWS. |
| Polling baseline; SSE kemampuan tambahan | Infrastruktur sederhana tetap berfungsi, real-time push bukan syarat deployment awal. |

Tidak menambah pembayaran/APC, aplikasi native, AI layout atau mesin plagiarisme
internal. Enum model akses jurnal hanya metadata kebijakan; tidak mengimplementasikan
paywall/billing. Pilihan di atas merupakan keputusan engineering untuk baseline ini,
bukan bukti bahwa proposal atau notulen sumber telah diubah.

## Penerimaan non-fungsional

NF-01: ukur p95 operasi umum <3 detik pada 500 pengguna bersamaan dengan dataset,
jenis request, mesin dan error rate tercatat. Untuk 20 MB di 1 Mbps, payload saja
memerlukan sekitar 160 detik (20 MiB sekitar 168 detik). Target <10 detik harus
menggunakan bandwidth memadai dan batas ukur jelas; catat koreksi ini di SRS.
NF-03: uji 1.000 pengguna, degradasi <20% terhadap baseline yang sama; belum tercapai
hanya karena memakai PostgreSQL/Laravel. NF-04/06: uji browser, perangkat kecil,
WCAG 2.1 AA dan proses submission <10 menit dengan pengguna representatif.
NF-05: monitoring 99,5% availability; NF-07: ≥70% core-code coverage setelah core ada;
NF-08: bukti restore; NF-09: sandbox dan XML/XSD; NF-10: audit append-only dan redaksi.
Semua ini acceptance gate implementasi, belum hasil pengukuran repository.
