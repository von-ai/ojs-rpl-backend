# SIRIDA — baseline arsitektur dan teknologi 1.0.0

**Update implementasi 8 Oktober 2026:** register, verifikasi email dan kirim ulang
sudah tersedia sebagai MVP. Lihat [panduan registrasi](registration.md). Pernyataan
planned/scaffold dalam baseline historis di bawah tetap berlaku untuk fitur lain;
login, membership dan workflow editorial belum dibangun.

Tanggal: 7 Oktober 2026. Status: **kontrak final untuk implementasi**, diajukan
melalui PR untuk review tim. Bukan pernyataan bahwa mitra sudah menyetujui setiap
keputusan, server tersedia, atau endpoint telah selesai dibangun.

Sumber kebutuhan: [proposal/SRS](https://docs.google.com/document/d/1bWldGLdB9Ek2bgmJtyupGi5yA7C1E409w6Gs65p__OM/)
(tab Proposal, UC-01–18, NF-01–10) dan
[catatan mitra](https://docs.google.com/document/d/10xW2fS-EfoSJnsHYmCIoMZm_T48a1GOLHD2JnpNoTBo/).
Target deployment **AWS dan Docker** mengikuti proposal bagian 2.1.1–2.1.2,
ditegaskan kembali oleh pengguna pada 7 Oktober 2026. Catatan mitra tetap menjadi
sumber kebutuhan bisnis; asumsi hosting Kominfo pada baseline sebelumnya
digantikan oleh keputusan ini. Pemilihan layanan
AWS di bawah merupakan baseline engineering, bukan bukti resource telah dibuat.
Perbedaan dari proposal harus tercatat saat review, bukan dianggap kutipan
persetujuan mitra.

## Stack yang dipilih

| Lapisan | Baseline | Bukti/status di repository |
| --- | --- | --- |
| Aplikasi | Modular monolith Laravel 13, PHP 8.4.1+ | Laravel `^13.17`, PHP `^8.4.1` pada Composer; domain belum dibangun. Ikuti lockfile, jangan upgrade otomatis saat deploy. |
| UI | Blade, Livewire 4, Tailwind CSS 4 | Blade/Tailwind tersedia; Livewire dipilih, belum menjadi dependency. Tim frontend tetap memiliki implementasi halaman. |
| Aset dan kontrak | Vite 8; Node 22.12+; Redocly CLI, AJV 2020, YAML | Dependency build/CI tersedia. Produksi hanya menerima hasil build, tanpa proses Node. |
| Database | Amazon RDS for PostgreSQL 18, Eloquent, migrations | Compose PostgreSQL untuk pengembangan lokal; RDS dan skema domain belum dibuat. Minor version serta ketersediaan region diverifikasi sebelum provisioning. |
| Web server | Amazon EC2 Linux dengan Docker Compose untuk Nginx + PHP-FPM, Application Load Balancer (ALB), AWS Certificate Manager (ACM) | Baseline deployment AWS; image produksi dan instance/ALB belum dibuat. Document root hanya `public/`; TLS diterminasi ALB, trusted proxy dikonfigurasi. |
| Queue dan jadwal | Laravel database queue pada RDS; container worker dan scheduler pada EC2; scheduler setiap menit; transactional outbox | Docker mengelola restart container; baseline tetap menggunakan database queue. SQS dapat dievaluasi saat scaling; belum ada worker domain atau integrasi SQS. |
| Session/cache/locks | Shared database store pada RDS | Semua instance memakai store bersama. ElastiCache dapat dievaluasi setelah pengukuran; bukan dependency baseline. |
| Berkas | Amazon S3 privat melalui Laravel Filesystem/Flysystem dan AWS SDK | Presigned PUT/GET; AWS multipart API untuk upload parts. Bucket, IAM, CORS dan adapter belum dikonfigurasi. Single PUT wajib; multipart diiklankan setelah uji integrasi lulus. |
| Pemeriksaan berkas | Worker EC2 dengan ClamAV, inspeksi MIME/checksum, sanitizer HTML/XML | Worker membaca objek S3 privat; belum terpasang. Scanner tidak tersedia berarti berkas tetap karantina. |
| Identitas API | JWT RS256 melalui library `lcobucci/jwt`; refresh opaque acak, hash disimpan server | Pilihan implementasi, belum dependency. Library bukan pengganti guard, revocation dan policy aplikasi. |
| Pencarian | PostgreSQL full-text search + indeks GIN; konfigurasi `simple` sebagai awal untuk teks Indonesia | Dipilih; relevansi dan query plan harus diuji dengan korpus jurnal. Tidak perlu Elasticsearch pada baseline. |
| Integrasi | Amazon SES melalui SMTP, ORCID authorization code, Crossref deposit, OAI-PMH 2.0/Dublin Core | SES domain/production access serta kredensial/adapter/sandbox belum dikonfigurasi atau diuji. Similarity provider opsional dan default nonaktif. |
| Notifikasi UI | REST polling; SSE opsional bila gateway/worker mendukung | Notifikasi persisten sebagai sumber utama. SSE tidak boleh menjadi satu-satunya cara membaca notifikasi. |
| Pengujian | PHPUnit 12, Pint, Redocly/AJV, GitHub Actions | Pemeriksaan scaffold/kontrak tersedia. Integration test menggunakan PostgreSQL wajib ditambahkan saat domain dibangun. |
| Secret dan akses AWS | AWS Secrets Manager, IAM instance role, KMS | Secret dan izin operator dipisahkan dari role pengguna jurnal; belum diprovisioning. |
| Observabilitas | Amazon CloudWatch Logs/metrics/alarms | Agent, metrik domain dan alarm belum dikonfigurasi. |

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

## Deployment AWS

EC2 dipilih sebagai baseline compute; Docker Compose menjalankan container
Nginx/PHP-FPM, worker Laravel, scanner dan scheduler pada setiap host.
Docker Compose mengelola satu host; scaling lintas EC2 memerlukan pengaturan
deployment dan Auto Scaling tambahan atau orkestrasi ECS. ECS/Fargate merupakan
opsi evolusi orkestrasi, bukan prasyarat rilis ini. Region, instance size, jumlah instance dan
anggaran harus dicatat pada konfigurasi deployment setelah pengujian kapasitas;
dokumen ini tidak menetapkan akun AWS, region atau resource yang sudah aktif.

| Layanan AWS | Tanggung jawab |
| --- | --- |
| EC2 + Docker | Menjalankan container web Laravel, queue worker, scanner dan scheduler dengan PHP sesuai lockfile serta `pdo_pgsql`. Docker memakai restart policy; health checks dan alarm memantau proses. |
| ALB + ACM | Endpoint HTTPS publik dan health checks ke web EC2. Aplikasi harus mengenali HTTPS dari trusted proxy untuk cookie dan redirect OAuth. |
| VPC + security groups | ALB pada subnet publik; EC2 dan RDS pada subnet privat. EC2 hanya menerima traffic aplikasi dari ALB; RDS hanya dari instance aplikasi/worker yang diizinkan. |
| RDS PostgreSQL | Data domain, sesi, locks, queue dan transactional outbox. Database tidak dibuka langsung ke internet. |
| S3 | Objek berkas privat, upload langsung browser dan download melalui presigned URL. Aktifkan Block Public Access, enkripsi dan versioning. |
| SES | Email verifikasi, reset, undangan dan notifikasi melalui transport SMTP Laravel. Verifikasi domain, konfigurasi DKIM dan tangani bounce/complaint. |
| Secrets Manager + IAM | Menyimpan secret database, SMTP, ORCID, Crossref dan signing key JWT; instance role memberi izin AWS minimum tanpa access key permanen dalam kode. |
| CloudWatch | Log teredaksi, metrik aplikasi/worker serta alarm operasional. |

Alur request: browser → ALB HTTPS → container Nginx/PHP-FPM di EC2 → RDS.
Alur file: browser meminta sesi ke aplikasi → aplikasi memberikan presigned URL →
browser PUT langsung ke S3 → aplikasi memverifikasi objek dan mengantrekan scan →
worker memindai → file `clean` dapat dipakai dalam workflow. Nginx bukan jalur
transfer utama berkas. Browser tidak menerima kredensial AWS atau meneruskan
bearer SIRIDA ke S3.

### S3 dan konsistensi file

Simpan relasi upload ID aplikasi dengan multipart upload ID S3, bucket dan object
key di server. `GET /upload-sessions/{upload_id}` merekonsiliasi parts menggunakan
S3 `ListParts`. `/part-urls` menandatangani operasi `UploadPart`; `/complete`
memverifikasi parts dan menyelesaikan multipart; `/abort` membatalkan multipart.
Single PUT tetap didukung. Part 5 MiB dan batas total 20 MiB mengikuti kontrak.
CORS S3 hanya mengizinkan origin frontend yang dikonfigurasi dan header upload yang
diperlukan; expose ETag/checksum yang diperlukan untuk completion.

Presigned URL dapat digunakan ulang selama masih valid. Karena itu upload memakai
object key staging unik; jangan mengizinkan URL upload menimpa objek final yang
sudah dipakai workflow. Completion mengikat objek/version S3 yang tepat. Scan dan
promosi ke objek final harus memakai bytes/version yang sama; upload ulang ke
staging tidak boleh mengubah isi yang sudah disetujui. File ID baru digunakan
untuk penggantian isi. ETag S3 tidak dianggap sebagai SHA-256 keseluruhan; worker
memverifikasi checksum isi penuh. Lifecycle membersihkan multipart tidak selesai
serta staging kedaluwarsa tanpa menghapus berkas yang masih direferensikan.

### Operasional dan penerimaan deployment

Semua instance menggunakan session/cache/lock database bersama. Saat web/worker
dipisah atau direplikasi, seluruhnya tetap mengakses RDS dan S3 yang sama. Cron
memanggil scheduler setiap menit; single-leader/shared lock mencegah job yang sama
berjalan serentak. Job outbox, scan, email dan timeout harus idempotent.
Pipeline membangun image aplikasi sesuai lockfile, menyimpannya di Amazon ECR,
dan EC2 mengambil image dengan tag/digest rilis yang tetap. Web, worker dan
scheduler memakai versi image aplikasi yang sama; rollback memakai image rilis
sebelumnya. Dockerfile produksi, konfigurasi Compose dan pipeline ini belum dibuat.

Sediakan outbound HTTPS ke AWS, ORCID, Crossref dan pembaruan signature ClamAV,
serta koneksi SMTP TLS ke SES. Subnet privat memerlukan jalur egress yang sesuai
(misalnya NAT dan endpoint VPC untuk layanan yang didukung). Outbound provider
mengikuti allowlist. DNS domain, callback ORCID HTTPS, CORS, IAM serta SES
production access harus diverifikasi sebelum menerima pengguna umum.
[SES sandbox](https://docs.aws.amazon.com/ses/latest/dg/request-production-access.html)
membatasi penerima; konfigurasi SMTP saja tidak membuktikan email produksi siap.

Pisahkan staging dan produksi pada database, bucket/prefix, IAM dan secret.
Secret tidak berada di document root, Git atau hasil build. Referensi integrasi
dipetakan server-side; Journal Manager tidak memasukkan arbitrary URL/credential.
Log ke CloudWatch menggunakan correlation ID dan metadata aman; redact token,
URL signed, password, komentar rahasia serta data pribadi. Alarm mencakup
queue/outbox lag, scheduler heartbeat, kegagalan scan/provider, error rate,
latency, kapasitas EC2 dan kapasitas/koneksi RDS.

Konfigurasikan backup otomatis RDS dengan retensi 30 hari dan point-in-time recovery.
S3 versioning disertai kebijakan backup/retensi terpisah minimal 30 hari; versioning
saja bukan backup terisolasi. Target RPO ≤24 jam/RTO ≤4 jam diverifikasi melalui
latihan restore triwulanan, termasuk konsistensi relasi database dan objek S3.
Deployment memakai migration kompatibel mundur, restart worker setelah rilis,
dan rollback aplikasi tanpa menghapus data domain. Produksi memakai redundansi
web lintas Availability Zone dan RDS Multi-AZ sesuai target availability; lingkungan
demo satu instance tidak menjadi bukti NF-05 tercapai.

Release gagal jika readiness kritis gagal; provider opsional boleh degraded dengan
status terlihat. `/up` scaffold dan Compose lokal tidak membuktikan readiness
AWS/domain. Belum ada provisioning, infrastructure-as-code atau deployment AWS
yang dilakukan melalui perubahan dokumentasi ini.

Referensi AWS: [RDS PostgreSQL versions](https://docs.aws.amazon.com/AmazonRDS/latest/PostgreSQLReleaseNotes/postgresql-release-calendar.html),
[S3 presigned URLs](https://docs.aws.amazon.com/AmazonS3/latest/userguide/using-presigned-url.html),
[S3 multipart/ListParts](https://docs.aws.amazon.com/AmazonS3/latest/API/API_ListParts.html),
[RDS backup retention](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_WorkingWithAutomatedBackups.BackupRetention.html)
dan [RDS recovery](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_WorkingWithAutomatedBackups.html).

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
| Laravel/Blade/Livewire pada AWS dengan Docker | AWS/Docker mengikuti proposal dan arahan pengguna 7 Oktober 2026, menggantikan asumsi hosting Kominfo. Compute EC2 dengan container, database RDS, file S3 dan email SES menjadi baseline deployment; stack aplikasi tetap Laravel. |
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
