# SIRIDA — Dokumen Perancangan Infrastruktur Cloud & DevOps

**Versi:** 1.0.0  
**Tanggal:** 18 Oktober 2026  
**Status:** Dokumen Resmi Arsitektur Infrastruktur & Panduan Implementasi PoC  
**Proyek:** Sistem Informasi Pengelolaan Publikasi Jurnal Ilmiah Berbasis Web dengan Integrasi Alur Peer-Review (SIRIDA) — BRIDA Kota Makassar  
**Arsitek Infrastruktur:** Benediktus Gianto Jarod (NIM: D121231012) — Konsentrasi Cloud Computing  

---

## 1. Pendahuluan & Latar Belakang

Dokumen ini merupakan panduan arsitektur dan operasional infrastruktur cloud untuk sistem **SIRIDA**. Dokumen ini secara khusus mendefinisikan seluruh komponen fisik, virtual, jaringan, keamanan, dan otomatisasi deployment yang menjadi fondasi tempat sistem aplikasi web dan worker backend dijalankan.

Untuk mendukung fase pengujian, demonstrasi expo, dan penilaian akademik (*Proof of Concept / PoC*), infrastruktur dirancang menggunakan prinsip **Multi-Cloud Serverless & Ephemeral Infrastructure** (kombinasi Microsoft Azure, Cloudflare, dan TiDB Cloud) yang dikelola secara deklaratif menggunakan **Terraform (Infrastructure as Code)**. Pendekatan ini memberikan performa tingkat produksi dengan skalabilitas tinggi, keamanan berlapis, dan efisiensi biaya operasional maksimal (**mendekati Rp 0**).

---

## 2. Matriks Pemilihan Layanan Cloud (Resource Selection Matrix)

Berdasarkan analisis kebutuhan proposal SRS dan evaluasi berbagai opsi layanan cloud, berikut adalah matriks keputusan teknologi yang dipilih:

| No | Kategori Kebutuhan | Opsi Layanan Dipertimbangkan | Opsi Terpilih (Prod/PoC) | Lingkungan Dev Lokal | Alasan Pemilihan & Keunggulan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | **Komputasi Web** | Azure VM, App Service, Azure Container Apps (ACA) | **Azure Container Apps (ACA)** | Docker Desktop / Compose | Serverless container, *scale-to-zero* saat idle, *cold start* cepat (5–15 detik), Envoy Ingress terkelola, kuota gratis 180.000 vCPU-s/bln. |
| 2 | **Komputasi Worker** | Azure VM, Celery/Supervisor, Dedicated ACA | **ACA Dedicated Worker** | Docker Container | Menjalankan `php artisan queue:work` secara asinkron (pemrosesan PDF dan pemindaian berkas), isolasi proses dari traffic web. |
| 3 | **Komputasi Scheduler** | Cron VM, Azure Function, ACA Scheduled Job | **ACA Scheduled Job** | Host Cron / Artisan | Menjalankan `php artisan schedule:run` setiap 1 menit untuk deadline review & outbox retry tanpa server standby 24/7. |
| 4 | **RDBMS** | Azure Database for MySQL, TiDB Serverless, AWS RDS | **TiDB Cloud Serverless** | Container MySQL 8.0 | 100% MySQL 8.0 wire protocol, *scale-to-zero*, bebas biaya sewa disk tetap, kuota gratis 5 GB storage & 50M Request Units/bln. |
| 5 | **In-Memory Cache & Broker** | Azure Cache for Redis, Upstash, DB Store | **Azure Cache for Redis (Basic C0)** | Container Redis | Latensi sub-milidetik (< 1 ms, collocated di region Azure yang sama dengan ACA), tanpa batasan kuota command (menangani query masif, rate limiting, atomic lock, queue broker), dan biaya on-demand per jam sangat murah (~$0.022/jam atau ~Rp 700 untuk demo 2 jam). |
| 6 | **Object Storage** | Azure Blob Storage, AWS S3, Cloudflare R2 | **Cloudflare R2** | MinIO Container | S3-Compatible API (Laravel Flysystem native), **Zero Egress Fee** (unduh PDF gratis bandwidth), kuota gratis 10 GB/bln. |
| 7 | **Container Registry** | Azure Container Registry (ACR), GHCR, DockerHub | **GitHub Container Registry (GHCR)** | Local Docker Engine | Terintegrasi langsung dengan GitHub Actions CI/CD, gratis private container image (kuota 500 MB & 1 GB transfer). |
| 8 | **DNS** | Azure DNS, Cloudflare DNS | **Cloudflare DNS** | `/etc/hosts` lokal | Resolusi DNS Anycast tercepat di dunia, pembaruan propagasi instan, 100% gratis. |
| 9 | **Edge CDN** | Azure Front Door, Cloudflare CDN | **Cloudflare CDN** | Tanpa CDN | Edge caching global untuk aset statis frontend dan file PDF galai publik, menghemat biaya komputasi origin. |
| 10 | **Firewall & Keamanan**| Azure WAF, Cloudflare WAF | **Cloudflare WAF** | Tanpa WAF | Mitigasi DDoS otomatis, proteksi serangan OWASP Top 10 (SQLi, XSS), inspeksi Layer 7 gratis. |
| 11 | **Email Transaksional**| Azure Comm Services (ACS), Brevo, AWS SES | **Azure Comm Services / Brevo** | Log Driver / Mailpit | Pengiriman email verifikasi & review melalui protokol SMTP standar, biaya sangat murah atau ada tier gratis 300 email/hari. |
| 12 | **Secret Manager** | Azure Key Vault, ACA Secrets, .env file | **ACA Built-in Secrets** | File `.env` lokal | Enkripsi bawaan Azure pada control plane, di-inject langsung ke environment variable container tanpa biaya per transaksi. |
| 13 | **Logging & Audit** | Azure Log Analytics, ELK Stack, Grafana Loki | **Azure Log Analytics Workspace** | Log file lokal (`storage/logs`) | Streaming otomatis stdout/stderr dari ACA container, retensi terpusat, kuota gratis data ingestion 5 GB/bln. |
| 14 | **Uptime Monitoring** | Azure Monitor Alert, Cloudflare Health, UptimeRobot | **Cloudflare Health / UptimeRobot** | - | Memantau SLA ketersediaan 99,5% dan alert otomatis jika target MTTR < 2 jam terancam. |
| 15 | **IaC Tool** | Terraform, Pulumi, Bicep | **Terraform** | CLI Terraform lokal | Standar industri multi-cloud (*provider* `azurerm` dan `cloudflare`), mendukung provisioning dan penghancuran dalam hitungan menit. |

---

## 3. Topologi Arsitektur Multi-Cloud

Berikut adalah visualisasi aliran lalu lintas jaringan (*traffic flow*) dan batasan keamanan antar penyedia cloud:

```
                                [ Pengguna / Browser ]
                                          │
                                          ▼ HTTPS / TLS 1.3
   ┌─────────────────────────────────────────────────────────────────────────────┐
   │                       CLOUDFLARE EDGE PLATFORM                              │
   │  • Anycast DNS (jurnal.makassar.go.id)                                      │
   │  • CDN Edge Caching (Aset Statis & Terbitan PDF Publik)                     │
   │  • Web Application Firewall (WAF) & DDoS Shield                             │
   └──────────────────────┬───────────────────────────────┬──────────────────────┘
                          │                               │
            Download/Upload Presigned File                │ Forward Request Web/API
                          │                               ▼ (Port 443 / HTTPS)
   ┌──────────────────────▼──────┐       ┌───────────────────────────────────────┐
   │    CLOUDFLARE R2 STORAGE    │       │     AZURE CONTAINER APPS (ACA)        │
   │ • Bucket: `sirida-manuscript│       │ • Managed Ingress (Envoy Proxy)       │
   │ • S3 API Endpoint           │       │ • Virtual Network Environment         │
   │ • Zero Egress Bandwidth     │       │                                       │
   │ • Object Versioning (30 Hr) │       │ ┌───────────────────────────────────┐ │
   └─────────────────────────────┘       │ │ Web Container (Stateless)         │ │
                                         │ │ Laravel 13 + PHP 8.4 (PHP-FPM)    │ │
                                         │ │ (Autoscale 0 - 5 Replika)         │ │
                                         │ └───────────────────────────────────┘ │
                                         │ ┌───────────────────────────────────┐ │
                                         │ │ Worker Container (Background)     │ │
                                         │ │ php artisan queue:work (ClamAV)   │ │
                                         │ └───────────────────────────────────┘ │
                                         │ ┌───────────────────────────────────┐ │
                                         │ │ Scheduled Job (Cron Pemicu)       │ │
                                         │ │ php artisan schedule:run (* * * *)│ │
                                         │ └───────────────────────────────────┘ │
                                         │ • ACA Encrypted Secrets               │
                                         │ • Azure Log Analytics (stdout/stderr) │
                                         └───────┬──────────────┬───────┬────────┘
                                                 │              │       │
                                  TLS Port 3306  │ TLS Port 6380│       │ TLS Port 587
                                  MySQL Protocol │ Redis Native │       │ SMTP Protocol
                                                 ▼              ▼       ▼
                          ┌──────────────────────────────┐ ┌─────────┐ ┌─────────┐
                          │    TiDB CLOUD SERVERLESS     │ │ AZURE   │ │ EMAIL   │
                          │ • Dialek MySQL 8.0           │ │ REDIS   │ │ SERVICE │
                          │ • Auto-scaling & Zero Idle   │ │ (C0)    │ │ ACS /   │
                          │ • Automated Snapshot Backup  │ │ Sub-1ms │ │ Brevo   │
                          └──────────────────────────────┘ └─────────┘ └─────────┘
```

---

## 4. Aliran Data Jaringan (*Network & Traffic Flow*)

### 4.1 Aliran Akses Aplikasi Web & API
1. Browser klien melakukan resolusi DNS ke domain publik melalui **Cloudflare DNS**.
2. Request dikirimkan melalui protokol **HTTPS (TLS 1.3)** menuju Cloudflare Edge.
3. **Cloudflare WAF** memeriksa request; lalu lintas mencurigakan (SQLi/XSS/bot) langsung diblokir.
4. Jika request meminta aset statis yang ter-cache, Cloudflare CDN langsung merespons dari *edge cache*.
5. Request dinamis (REST API) diteruskan (*proxied*) ke alamat Ingress publik **Azure Container Apps**.
6. Envoy Proxy di ACA meneruskan request ke replika **Web App Container (Laravel)** yang sedang aktif.

### 4.2 Aliran Penyimpanan & Pengunduhan Berkas (*Direct S3 Presigned URL*)
Untuk memenuhi syarat performa proposal (NF-01: upload/download file hingga 20 MB berlangsung cepat tanpa membebani memori web server):
1. **Unggah Berkas (Upload)**:
   * Penulis meminta sesi unggah ke backend.
   * Backend menghasilkan *Presigned S3 PUT URL* bertanda tangan sementara dari Cloudflare R2.
   * Browser mengunggah file PDF/Word langsung ke bucket Cloudflare R2 tanpa melewati server aplikasi Laravel.
   * Setelah selesai, backend menerima konfirmasi dan memasukkan pekerjaan ke tabel `outbox` untuk dipindai oleh worker.
2. **Unduh Berkas (Download)**:
   * Pengguna terverifikasi meminta tautan berkas.
   * Backend memvalidasi hak akses (otorisasi double-blind) lalu menghasilkan *Presigned GET URL* berdurasi terbatas.
   * File diunduh langsung dari Cloudflare R2 dengan **Zero Egress Fee**.

---

## 5. Spesifikasi Teknis & Dimensi Resource (*Sizing*)

| Komponen | Spesifikasi vCPU | Alokasi Memori (RAM) | Kebijakan Penskalaan (*Scaling Policy*) | Catatan Operasional |
| :--- | :---: | :---: | :--- | :--- |
| **ACA Web Container** | 0.5 vCPU | 1.0 GiB | Min 0 replika, Max 5 replika (KEDA HTTP Concurrent Request > 30) | *Scale-to-zero* saat tidak ada traffic untuk hemat biaya. |
| **ACA Queue Worker** | 0.5 vCPU | 1.0 GiB | Min 1 replika (saat aktif) atau KEDA Queue Rule | Dialokasikan 1.0 GiB agar cukup menjalankan scanner file & engine PDF. |
| **ACA Scheduled Job** | 0.25 vCPU | 0.5 GiB | Trigger cron `* * * * *` (Maks durasi eksekusi: 60 detik) | Hidup hanya beberapa detik tiap menit untuk `schedule:run`. |
| **Azure Cache for Redis**| Dedicated (Shared) | 250 MB (Basic C0) | On-Demand (Per jam: ~$0.022/jam) | Collocated di region Azure (latensi < 1 ms), tanpa batas QPS/kuota query. |
| **TiDB Serverless** | Dynamic (RU) | Dynamic | Otomatis menyesuaikan beban query | Mendukung hingga 50 juta Request Units (RU) gratis/bulan. |
| **Cloudflare R2** | - | 10 GB Storage | Unlimited kapasitas | 10 GB pertama gratis/bulan, Class A: 1M ops, Class B: 10M ops gratis. |
| **Log Analytics** | - | 5 GB / bulan | Pay-as-you-go | Data ingestion hingga 5 GB/bulan gratis dari Azure. |

---

## 6. Strategi Keamanan & Kepatuhan (*Security & Compliance*)

1. **Enkripsi Data**:
   * **In-Transit**: Seluruh komunikasi eksternal wajib melalui HTTPS dengan TLS 1.3. Koneksi database ke TiDB Serverless wajib menggunakan enkripsi TLS/SSL (Port 3306).
   * **At-Rest**: Berkas naskah di Cloudflare R2 dienkripsi otomatis menggunakan algoritma **AES-256**. Data di TiDB dienkripsi di tingkat storage volume.
2. **Isolasi Rahasia (*Secret Management*)**:
   * Nilai sensitif (`APP_KEY`, password database, kredensial SMTP, token API Crossref/ORCID) disimpan di dalam fitur *Secrets* terkelola milik Azure Container Apps.
   * Tidak ada kredensial atau kunci privat yang dimasukkan ke dalam repositori Git atau Docker image.
3. **Audit Trail Sesuai UU PDP**:
   * Seluruh aksi krusial pengguna (perubahan hak akses, revisi naskah, keputusan dewan redaksi) dicatat ke dalam tabel audit yang bersifat *append-only* (tidak dapat diubah) dan diteruskan ke Azure Log Analytics Workspace.
4. **Perlindungan Berkas (Malware Sanitization)**:
   * Manuskrip yang diunggah dikarantina sampai background worker berhasil melakukan inspeksi MIME type, validasi integritas checksum SHA-256, dan pemindaian malware menggunakan ClamAV.

---

## 7. Strategi Pencadangan & Pemulihan (*Backup & Disaster Recovery*)

Sesuai spesifikasi kebutuhan non-fungsional proposal (**NF-08: Backup setiap 24 jam dengan retensi 30 hari, RPO ≤ 24 jam, RTO ≤ 4 jam**):

1. **Pencadangan Berkas (Object Storage)**:
   * Fitur **Bucket Object Versioning** diaktifkan pada Cloudflare R2.
   * *Lifecycle Rules* dikonfigurasi untuk mempertahankan riwayat versi lama (*non-current versions*) selama 30 hari sebelum dihapus permanen.
2. **Pencadangan Basis Data (RDBMS)**:
   * TiDB Cloud Serverless mengelola snapshot cluster dan *Continuous Point-in-Time Recovery (PITR)* secara internal.
   * Untuk redundansi jangka panjang, disiapkan skrip otomatis yang mengekspor dump SQL terkompresi (`mysqldump`) terjadwal setiap malam ke dalam folder `/backups/database/` di Cloudflare R2.

---

## 8. Otomasi Infrastruktur: Infrastructure as Code (IaC) & DevOps

### 8.1 Struktur Direktori Modul Terraform
Seluruh infrastruktur cloud dikelola secara deklaratif melalui Terraform. Struktur direktori Terraform direncanakan sebagai berikut:

```text
terraform/
├── providers.tf       # Definisi provider azurerm (~> 3.0) dan cloudflare (~> 4.0)
├── variables.tf       # Variabel konfigurasi lingkungan (region, domain, credentials)
├── main.tf            # Azure Resource Group & Log Analytics Workspace
├── aca.tf             # ACA Environment, Web App, Worker, dan Scheduled Job
├── redis.tf           # Azure Cache for Redis (Basic C0)
├── cloudflare.tf      # DNS records, R2 Storage Bucket, WAF ruleset, TLS configuration
├── secrets.tf         # Pemetaan ACA built-in secrets
└── outputs.tf         # Output FQDN domain, endpoint R2, dan status deployment
```

### 8.2 Pola Deployment Ephemeral PoC (*The Ephemeral Cloud Pattern*)
Karena proyek ini berstatus *Proof of Concept* untuk pengujian dan expo berdurasi singkat (1–2 jam):
1. **Fase Peluncuran (*Spin-Up*)**:
   Sepuluh menit sebelum sesi presentasi atau evaluasi dimulai, jalankan perintah dari terminal:
   ```bash
   terraform apply -auto-approve
   ```
   Dalam waktu 3 s.d. 5 menit, seluruh ekosistem (ACA, DNS, WAF, dan R2) telah berdiri dan dapat diakses langsung secara global melalui URL resmi `https://jurnal.makassar.go.id`.
2. **Fase Evaluasi / Pengujian (Durasi 1–2 Jam)**:
   Aplikasi dan seluruh worker bekerja secara penuh untuk melayani demonstrasi alur peer-review naskah.
3. **Fase Pembersihan (*Tear-Down*)**:
   Segera setelah evaluasi selesai, seluruh infrastruktur cloud dihancurkan untuk menghentikan seluruh tagihan:
   ```bash
   terraform destroy -auto-approve
   ```
*Dampak Finansial:* Penerapan pola ini menghasilkan total pengeluaran biaya sewa cloud riil pada kisaran **Rp 0 s.d. < Rp 1.500 per sesi demo**.

### 8.3 Integrasi Berkelanjutan (CI via GitHub Actions)
Deployment cloud dipicu secara manual dan terkontrol (agar infra tidak menyala tanpa sengaja), sedangkan proses **CI** berjalan otomatis di GitHub Actions pada setiap *Pull Request*:
* Pemeriksaan linting dan standar PSR-12 (`vendor/bin/pint --test`).
* Validasi kontrak antarmuka API (`npm run check:openapi`).
* Pengujian unit dan fitur (`composer test`).
* Otomasi *build* dan *push* Docker Image produksi bertag rilis (misal `v1.0.0-poc`) ke **GitHub Container Registry (GHCR)**.

---

## 9. Matriks Pemenuhan Kebutuhan Non-Fungsional (SRS Bab 2.4)

| Kode SRS | Parameter Kebutuhan | Solusi Implementasi Infrastruktur | Status |
| :--- | :--- | :--- | :---: |
| **NF-01** | Respons < 3 dtk (500 user), upload 20 MB < 10 dtk | Presigned Upload langsung ke Cloudflare R2, Edge CDN Caching | **Terpenuhi** |
| **NF-02** | Keamanan enkripsi AES-256, TLS 1.2+, isolasi rahasia | Cloudflare TLS 1.3, ACA Encrypted Secrets, Enkripsi storage at-rest | **Terpenuhi** |
| **NF-03** | Skalabilitas 1.000 user (degradasi < 20%) | KEDA Horizontal Pod Autoscaler di ACA + TiDB Serverless Autoscale | **Terpenuhi** |
| **NF-05** | Uptime minimal 99,5%, MTTR maksimal 2 jam | Jaringan global Anycast Cloudflare + Uptime Health Alerting | **Terpenuhi** |
| **NF-08** | Backup otomatis tiap 24 jam dengan retensi 30 hari | R2 Object Versioning 30 hari + Scheduled Database Dump ke R2 | **Terpenuhi** |
| **NF-10** | Pencatatan audit trail & kepatuhan privasi (UU PDP) | Centralized stdout streaming ke Azure Log Analytics Workspace | **Terpenuhi** |
