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
| Aplikasi | Modular monolith Laravel 13, PHP 8.4.1+ | Laravel `^13.17`, PHP `^8.4.1` pada Composer. Menggunakan arsitektur monolitik murni tanpa layer API service terpisah. |
| UI | Blade, Livewire 4, Tailwind CSS 4 | Blade/Tailwind tersedia; Livewire dipilih untuk reaktivitas UI server-side terkelola. |
| Aset dan build | Vite 8; Node 22+; Tailwind CSS 4 | Dependency build/asset bundling tersedia. Produksi hanya menerima hasil build aset, tanpa proses Node di runtime. |
| Database | MySQL 8.0 / TiDB Cloud Serverless, Eloquent, migrations | TiDB Serverless untuk produksi/PoC; MySQL 8.0 Compose untuk pengembangan lokal. |
| Web server | Linux milik/dikelola Kominfo, Nginx + PHP-FPM, HTTPS | Target deployment. Document root hanya `public/`. |
| Queue dan jadwal | Laravel queue pada Redis / Database; supervised worker (ACA); cron scheduler setiap menit; transactional outbox | Didukung Azure Managed Redis (AMR Balanced_B0) / Database pada produksi untuk antrean dan locking. |
| Session/cache/locks | Azure Managed Redis (AMR Balanced_B0) pada produksi; Redis/Database store pada lokal | Memastikan latensi < 1 ms dan atomic locks untuk konkurensi review (Redis Enterprise Engine). |
| Berkas | Azure Blob Storage melalui Laravel Filesystem/Flysystem | Adapter Azure Blob teruji, container privat, direct upload melalui SAS (Shared Access Signature) URL, enkripsi server-side bawaan. Azurite untuk dev lokal. |
| Pemeriksaan berkas | Microsoft Defender for Storage (Malware Scanning), inspeksi MIME/checksum, sanitizer HTML/XML | Pemindaian malware otomatis near real-time oleh Microsoft Defender for Storage di cloud; mock scanner pada dev lokal; berkas berstatus karantina jika terdeteksi ancaman. |
| Autentikasi & Sesi | Stateful Session Cookie (`auth:web`), CSRF token, HttpOnly, SameSite=Lax | Menggunakan sistem session bawaan Laravel dengan proteksi lockout 15 menit dan audit log. Disederhanakan tanpa JWT/Bearer token. |
| Pencarian | MySQL Full-Text Search (indeks `FULLTEXT`); konfigurasi boolean mode untuk teks Indonesia | Menggunakan query MATCH(...) AGAINST(...). Tidak perlu Elasticsearch pada baseline. |
| Integrasi | SMTP (Azure Communication Services untuk produksi, Mailpit untuk pengembangan lokal), ORCID authorization code callback, Crossref deposit (outbox worker), OAI-PMH 2.0/Dublin Core XML (`/oai`) | Layanan terintegrasi via worker backend dan rute web khusus OAI-PMH / OAuth callback. Mailpit digunakan untuk inspeksi visual email lokal. |
| Notifikasi UI | Polling terkelola / Livewire refresh | Notifikasi persisten dari database sebagai sumber utama. |
| Pengujian | PHPUnit 12, Pint, GitHub Actions | Feature test web, integration test, dan automated style checking tanpa overhead pengujian skema kontrak JSON API. |

Referensi teknis: [deployment Laravel](https://laravel.com/docs/13.x/deployment),
[queue](https://laravel.com/docs/13.x/queues),
[scheduler](https://laravel.com/docs/13.x/scheduling),
[filesystem](https://laravel.com/docs/13.x/filesystem),
[Livewire](https://livewire.laravel.com/docs/4.x/installation),
[MySQL FTS](https://dev.mysql.com/doc/refman/8.0/en/fulltext-search.html).
Versi PHP minimum repository mengikuti dependency terkunci, bukan hanya minimum
framework.

## Batas modul dan transaksi

Modul: Identity & Membership, Journal Configuration, Submission & Files, Peer Review,
Editorial Decision, Production, Publication, Notifications & Audit, Integrations.
Controller Web dan aksi Livewire memanggil application service dan policy yang sama;
jangan menggandakan aturan workflow dalam UI. Gunakan Form Requests untuk input dan
Policy untuk pemeriksaan hak akses journal/ownership/assignment.

MySQL / TiDB menyimpan sumber kebenaran. Setiap transisi mengunci aggregate terkait,
memeriksa hak akses, state dan waktu, lalu menulis state, audit dan outbox dalam
satu transaksi. Worker membaca outbox setelah commit. Konsumen harus idempotent:
delivery boleh diulang, efek bisnis tidak. `afterCommit` saja tidak menggantikan
outbox persisten ketika proses mati setelah commit tetapi sebelum enqueue.

Gunakan foreign key, uniqueness journal-scoped, indeks daftar/deadline, unique DOI,
dan public ID ULID yang immutable. Bigint internal boleh dipertahankan; jangan
mengeksposnya sebagai kontrak baru atau melakukan migrasi destruktif tanpa backfill.
Semua perbandingan deadline memakai jam database UTC setelah lock, bukan jam browser.

## Browser dan autentikasi

UI Blade/Livewire menggunakan session HttpOnly, Secure, SameSite=Lax dan CSRF token.
Aplikasi menggunakan sistem autentikasi sesi stateful Laravel (`auth:web`).
Tidak ada perantara REST API atau Bearer JWT; seluruh pertukaran data antarmuka terjadi
langsung antara browser dan komponen Livewire/Blade server-side.

Baseline konfigurasi: absolute session 24 jam, password minimum 12 karakter,
proteksi lockout otomatis 15 menit setelah 5 kali kegagalan login berturut-turut
(menerbitkan outbox email peringatan keamanan). Logout membatalkan sesi dan
meregenerasi CSRF token secara otomatis.

## Deployment yang dapat diterima

Target infrastruktur (lihat spesifikasi detail pada [docs/infrastructure.md](infrastructure.md)) mengusung multi-cloud serverless (Azure Container Apps, TiDB Serverless MySQL, Azure Blob Storage, Cloudflare WAF/DNS, Redis Container di ACA). Lingkungan ini menyediakan runtime PHP yang sesuai lockfile + `pdo_mysql`, MySQL/TiDB, worker terawasi (ACA), scheduler (ACA Scheduled Job), storage privat (Azure Blob Storage), scanner (Microsoft Defender for Storage), SMTP (Azure Communication Services untuk produksi, Mailpit untuk lokal dev), DNS/TLS (Cloudflare) dan outbound allowlist ke provider. Shared hosting yang hanya melayani request PHP tanpa worker, cron atau penyimpanan privat **tidak memenuhi baseline**; Docker Compose lokal bukan bukti produksi siap.

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
| Multi-Cloud Ephemeral PoC (Azure + Cloudflare + TiDB) | Detail pada docs/infrastructure.md; menggantikan asumsi AWS monolitik dan Kominfo on-premise untuk efisiensi demo PoC 1–2 jam. |
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
hanya karena memakai MySQL/Laravel. NF-04/06: uji browser, perangkat kecil,
WCAG 2.1 AA dan proses submission <10 menit dengan pengguna representatif.
NF-05: monitoring 99,5% availability via Azure Monitor Application Insights Web Test; NF-07: ≥70% core-code coverage setelah core ada;
NF-08: bukti restore; NF-09: sandbox dan XML/XSD; NF-10: audit append-only dan redaksi.
Semua ini acceptance gate implementasi, belum hasil pengukuran repository.
