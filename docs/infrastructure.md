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
| 3 | **Komputasi Scheduler** | ACA Scheduled Job (Cron), Azure Function, Dedicated ACA Daemon | **ACA Scheduler Daemon** | Host Cron / Artisan | Menjalankan `php artisan schedule:work` secara kontinu (min 1, max 1 replika). Dipilih karena pola ACA Scheduled Job 1-menit (`* * * * *`) di Azure Container Apps memiliki keterbatasan latensi cold-start (15–30 dtk) dan potensi overlap, sedangkan daemon loop internal PHP menjamin eksekusi tepat waktu di detik ke-0. |
| 4 | **RDBMS** | Azure Database for MySQL, TiDB Serverless, AWS RDS | **TiDB Cloud Serverless** | Container MySQL 8.0 | 100% MySQL 8.0 wire protocol, *scale-to-zero*, bebas biaya sewa disk tetap, kuota gratis 5 GB storage & 50M Request Units/bln. |
| 5 | **In-Memory Cache & Broker** | Azure Managed Redis, Upstash, DB Store | **Azure Managed Redis (AMR Balanced_B0)** | Container Redis | Layanan Redis Enterprise terkelola native Azure dengan mesin multi-threading performa tinggi, dedicated in-memory store untuk session, atomic locking naskah, dan cache layer (~Rp 210 rb/bln saat aktif atau Rp 0 saat di-destroy). |
| 6 | **Object Storage** | Azure Blob Storage, AWS S3, Cloudflare R2 | **Azure Blob Storage** | Azurite Container (Port 10000) | Penyimpanan objek terkelola native Azure dengan integrasi langsung Microsoft Defender for Storage (Malware Scanning). Enkripsi data at-rest otomatis (AES-256), integrasi SAS (Shared Access Signature) token, dan manajemen lifecycle versi berkas 30 hari. |
| 7 | **Container Registry** | Azure Container Registry (ACR), GHCR, DockerHub | **GitHub Container Registry (GHCR)** | Local Docker Engine | Terintegrasi langsung dengan GitHub Actions CI/CD, gratis private container image (kuota 500 MB & 1 GB transfer). |
| 8 | **DNS** | Azure DNS, Cloudflare DNS | **Cloudflare DNS** | `/etc/hosts` lokal | Resolusi DNS Anycast tercepat di dunia, pembaruan propagasi instan, 100% gratis. |
| 9 | **Edge CDN** | Azure Front Door, Cloudflare CDN | **Cloudflare CDN** | Tanpa CDN | Edge caching global untuk aset statis frontend dan file PDF galai publik, menghemat biaya komputasi origin. |
| 10 | **Firewall & Keamanan**| Azure WAF, Cloudflare WAF | **Cloudflare WAF** | Tanpa WAF | Mitigasi DDoS otomatis, proteksi serangan OWASP Top 10 (SQLi, XSS), inspeksi Layer 7 gratis. |
| 11 | **Email Transaksional**| Azure Comm Services (ACS), Brevo, AWS SES | **Azure Communication Services (ACS)** | Mailpit Container (Port 1025/8025) | Layanan email transaksional resmi di ekosistem Azure melalui protokol SMTP terkelola. Biaya ~Rp 4-5/email, terintegrasi langsung dengan tenant Azure, dan di dev lokal menggunakan Mailpit (port 8025). |
| 12 | **Secret Manager** | Azure Key Vault, ACA Secrets, .env file | **ACA Built-in Secrets** | File `.env` lokal | Enkripsi bawaan Azure pada control plane, di-inject langsung ke environment variable container tanpa biaya per transaksi. |
| 13 | **Logging & Audit** | Azure Log Analytics, ELK Stack, Grafana Loki | **Azure Log Analytics Workspace** | Log file lokal (`storage/logs`) | Streaming otomatis stdout/stderr dari ACA container, retensi terpusat, kuota gratis data ingestion 5 GB/bln. |
| 14 | **Uptime Monitoring** | Azure Monitor (App Insights), Cloudflare Health, UptimeRobot | **Azure Monitor (Application Insights)** | - | Memantau SLA ketersediaan 99,5% secara native lewat Availability Standard Web Test (5 probe global) dengan alert otomatis jika target MTTR < 2 jam terancam. |
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
            Download/Upload SAS URL File                  │ Forward Request HTTP Web
                          │                               ▼ (Port 443 / HTTPS)
   ┌──────────────────────▼──────┐       ┌───────────────────────────────────────┐
   │  AZURE BLOB STORAGE ACCOUNT │       │     AZURE CONTAINER APPS (ACA)        │
   │ • `sirida-manuscripts`      │       │ • Managed Ingress (Envoy Proxy)       │
   │ • SAS Token Direct Upload   │       │ • Virtual Network Environment         │
   │ • Defender Malware Scanning │       │                                       │
   │ • Blob Versioning (30 Hr)   │       │ ┌───────────────────────────────────┐ │
   └─────────────────────────────┘       │ │ Web Container (Stateless)         │ │
                                         │ │ Laravel 13 + PHP 8.4 (PHP-FPM)    │ │
                                         │ │ (Autoscale 0 - 5 Replika)         │ │
                                         │ └───────────────────────────────────┘ │
                                         │ ┌───────────────────────────────────┐ │
                                         │ │ Worker Container (Background)     │ │
                                         │ │ php artisan queue:work (Worker)   │ │
                                         │ └───────────────────────────────────┘ │
                                         │ ┌───────────────────────────────────┐ │
                                         │ │ Scheduler Container (Daemon)      │ │
                                         │ │ php artisan schedule:work (Min 1) │ │
                                         │ └───────────────────────────────────┘ │
                                         │ • ACA Encrypted Secrets               │
                                         │ • Azure Log Analytics (stdout/stderr) │
                                         └───────┬──────────────┬───────┬────────┘
                                                 │              │       │
                                  TLS Port 4000  │ TLS Port 6380│       │ TLS Port 587
                                  MySQL Protocol │ Redis Native │       │ SMTP Protocol
                                                 ▼              ▼       ▼
                          ┌──────────────────────────────┐ ┌─────────┐ ┌─────────┐
                          │    TiDB CLOUD SERVERLESS     │ │ AZURE   │ │ AZURE   │
                          │ • Dialek MySQL 8.0           │ │ REDIS   │ │ COMM    │
                          │ • Auto-scaling & Zero Idle   │ │ (C0)    │ │ SERVICES│
                          │ • Automated Snapshot Backup  │ │ Sub-1ms │ │ (ACS)   │
                          └──────────────────────────────┘ └─────────┘ └─────────┘
```

---

## 4. Aliran Data Jaringan (*Network & Traffic Flow*)

### 4.1 Aliran Akses Aplikasi Web
1. Browser klien melakukan resolusi DNS ke domain publik melalui **Cloudflare DNS**.
2. Request dikirimkan melalui protokol **HTTPS (TLS 1.3)** menuju Cloudflare Edge.
3. **Cloudflare WAF** memeriksa request; lalu lintas mencurigakan (SQLi/XSS/bot) langsung diblokir.
4. Jika request meminta aset statis yang ter-cache, Cloudflare CDN langsung merespons dari *edge cache*.
5. Request dinamis aplikasi web diproses oleh **Cloudflare Worker Edge Proxy** (`cloudflare-worker.js`) untuk melakukan *Host Header Rewrite* secara transparan dari domain kustom publik (`sirida.web.id`) ke FQDN dinamis internal Azure Container Apps (`ca-sirida-web-poc...`). Pola ini menyelesaikan batasan Express Environment pada Azure Container Apps dan batasan lisensi paket Cloudflare Free tanpa biaya tambahan.
6. Envoy Proxy di ACA menerima request dengan Host Header yang sesuai, lalu meneruskannya ke replika **Web App Container (Laravel)** yang sedang aktif.

### 4.2 Aliran Penyimpanan & Pengunduhan Berkas (*Direct Azure Blob SAS URL*)
Untuk memenuhi syarat performa proposal (NF-01: upload/download file hingga 20 MB berlangsung cepat tanpa membebani memori web server):
1. **Unggah Berkas (Upload)**:
   * Penulis meminta sesi unggah ke backend.
   * Backend menghasilkan *Shared Access Signature (SAS) PUT URL* bertanda tangan sementara dari Azure Blob Storage.
   * Browser mengunggah file PDF/Word langsung ke container Azure Blob Storage tanpa melewati server aplikasi Laravel.
   * **Microsoft Defender for Storage (Malware Scanning)** secara otomatis mendeteksi objek baru dan memindai malware secara *near real-time*.
   * Setelah selesai, backend menerima konfirmasi, memeriksa hasil pemindaian Defender (Blob Index Tag / webhook), dan menandai status berkas menjadi `clean`.
2. **Unduh Berkas (Download)**:
   * Pengguna terverifikasi meminta tautan berkas.
   * Backend memvalidasi hak akses (otorisasi double-blind) lalu menghasilkan *SAS GET URL* berdurasi terbatas.
   * File diunduh langsung dari Azure Blob Storage.

---

## 5. Spesifikasi Teknis & Dimensi Resource (*Sizing*)

| Komponen | Spesifikasi vCPU | Alokasi Memori (RAM) | Kebijakan Penskalaan (*Scaling Policy*) | Catatan Operasional |
| :--- | :---: | :---: | :--- | :--- |
| **ACA Web Container** | 0.5 vCPU | 1.0 GiB | Min 0 replika, Max 5 replika (KEDA HTTP Concurrent Request > 30) | *Scale-to-zero* saat tidak ada traffic untuk hemat biaya. |
| **ACA Queue Worker** | 0.5 vCPU | 0.5 GiB | Min 1 replika (saat aktif) atau KEDA Queue Rule | Eksekusi pekerjaan antrean asinkron (outbox processing & engine PDF). |
| **ACA Scheduler Daemon** | 0.25 vCPU | 0.5 GiB | Dedicated Daemon (min 1, max 1 replika) | Menjalankan `php artisan schedule:work` secara kontinu di memori tanpa penalti cold-start 1-menit. |
| **Redis Container (ACA)**| 0.25 vCPU | 0.5 GiB | Min 1 replika (Internal TCP Ingress Port 6379) | Collocated di dalam ACA Environment (latensi < 1 ms), $0 gratis masuk kuota ACA. |
| **TiDB Serverless** | Dynamic (RU) | Dynamic | Otomatis menyesuaikan beban query | Mendukung hingga 50 juta Request Units (RU) gratis/bulan. |
| **Azure Blob Storage** | - | Pay-as-you-go | Unlimited kapasitas | Hot Tier dengan enkripsi AES-256 bawaan; kuota gratis 100 GB egress/bulan dari Azure. |
| **Defender for Storage** | - | Cloud-native Security | On-demand scan | Pemindaian malware near real-time ($0.15/GB dipindai), menandai Blob Index Tags otomatis. |
| **Log Analytics** | - | 5 GB / bulan | Pay-as-you-go | Data ingestion hingga 5 GB/bulan gratis dari Azure. |
| **Application Insights** | - | Kuota terintegrasi | Availability Ping Test (5 menit) | 5 probe global menguji ketersediaan SLA 99,5% dan alert otomatis via Action Group. |

---

## 6. Strategi Keamanan & Kepatuhan (*Security & Compliance*)

1. **Enkripsi Data**:
   * **In-Transit**: Seluruh komunikasi eksternal wajib melalui HTTPS dengan TLS 1.3. Koneksi database ke TiDB Serverless wajib menggunakan enkripsi TLS/SSL (Port 4000 standar TiDB Serverless / Port 3306 pada MySQL lokal).
   * **At-Rest**: Berkas naskah di Azure Blob Storage dienkripsi otomatis menggunakan algoritma **AES-256** (Azure Storage Service Encryption). Data di TiDB dienkripsi di tingkat storage volume.
2. **Isolasi Rahasia (*Secret Management*)**:
   * Nilai sensitif (`APP_KEY`, password database, kredensial SMTP, token API Crossref/ORCID) disimpan di dalam fitur *Secrets* terkelola milik Azure Container Apps.
   * Tidak ada kredensial atau kunci privat yang dimasukkan ke dalam repositori Git atau Docker image.
3. **Audit Trail Sesuai UU PDP**:
   * Seluruh aksi krusial pengguna (perubahan hak akses, revisi naskah, keputusan dewan redaksi) dicatat ke dalam tabel audit yang bersifat *append-only* (tidak dapat diubah) dan diteruskan ke Azure Log Analytics Workspace.
4. **Perlindungan Berkas (Malware Sanitization & Security Scanning)**:
   * Manuskrip yang diunggah dikarantina (`pending_scan`) sampai sistem selesai melakukan validasi MIME magic bytes, verifikasi checksum SHA-256, dan pemindaian malware otomatis menggunakan **Microsoft Defender for Storage (Malware Scanning)**. Berkas berstatus `quarantined` jika terdeteksi ancaman.

---

## 7. Strategi Pencadangan & Pemulihan (*Backup & Disaster Recovery*)

Sesuai spesifikasi kebutuhan non-fungsional proposal (**NF-08: Backup setiap 24 jam dengan retensi 30 hari, RPO ≤ 24 jam, RTO ≤ 4 jam**):

1. **Pencadangan Berkas (Object Storage)**:
   * Fitur **Blob Versioning** dan **Soft Delete (retensi 30 hari)** diaktifkan pada Azure Blob Storage account.
   * *Lifecycle Management Rules* dikonfigurasi untuk memindahkan atau membersihkan versi lama (*previous versions*) setelah 30 hari.
2. **Pencadangan Basis Data (RDBMS)**:
   * TiDB Cloud Serverless mengelola snapshot cluster dan *Continuous Point-in-Time Recovery (PITR)* secara internal.
   * Untuk redundansi jangka panjang, disiapkan skrip otomatis yang mengekspor dump SQL terkompresi (`mysqldump`) terjadwal setiap malam ke dalam container `sirida-backups` di Azure Blob Storage.

---

## 8. Otomasi Infrastruktur: Infrastructure as Code (IaC) & DevOps

### 8.1 Struktur Direktori Modul Terraform
Seluruh infrastruktur cloud dikelola secara deklaratif melalui Terraform. Struktur direktori Terraform direncanakan sebagai berikut:

```text
terraform/
├── providers.tf       # Definisi provider azurerm (~> 4.0) dan cloudflare (~> 4.0)
├── variables.tf       # Variabel konfigurasi lingkungan (region, domain, credentials)
├── main.tf            # Azure Resource Group & Log Analytics Workspace
├── aca.tf             # ACA Environment, Web App, Worker, dan Scheduled Job
├── storage.tf         # Azure Storage Account, Blob Container, dan Defender for Storage
├── redis.tf           # Azure Managed Redis (AMR Balanced_B0, Redis Enterprise Engine)
├── monitoring.tf      # Azure Monitor Application Insights & Availability Web Test
├── cloudflare.tf      # DNS records, Cloudflare WAF ruleset, TLS configuration
├── secrets.tf         # Pemetaan ACA built-in secrets
└── outputs.tf         # Output FQDN domain, endpoint Azure Blob Storage, dan status deployment
```

### 8.2 Pola Deployment Ephemeral PoC (*The Ephemeral Cloud Pattern*)
Karena proyek ini berstatus *Proof of Concept* untuk pengujian dan expo berdurasi singkat (1–2 jam):
1. **Fase Peluncuran (*Spin-Up*)**:
   Sepuluh menit sebelum sesi presentasi atau evaluasi dimulai, jalankan perintah dari terminal:
   ```bash
   terraform apply -auto-approve
   ```
   Dalam waktu 3 s.d. 5 menit, seluruh ekosistem (ACA, Azure Blob Storage, Defender for Storage, DNS, dan WAF) telah berdiri dan dapat diakses langsung secara global melalui URL resmi `https://jurnal.makassar.go.id`.
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
* Pengujian unit dan fitur (`composer test`).
* Otomasi *build* dan *push* Docker Image produksi bertag rilis (misal `v1.0.0-poc`) ke **GitHub Container Registry (GHCR)**.

---

## 9. Matriks Pemenuhan Kebutuhan Non-Fungsional (SRS Bab 2.4)

| Kode SRS | Parameter Kebutuhan | Solusi Implementasi Infrastruktur | Status |
| :--- | :--- | :--- | :---: |
| **NF-01** | Respons < 3 dtk (500 user), upload 20 MB < 10 dtk | Direct SAS Upload langsung ke Azure Blob Storage, Edge CDN Caching | **Terpenuhi** |
| **NF-02** | Keamanan enkripsi AES-256, TLS 1.2+, isolasi rahasia | Cloudflare TLS 1.3, ACA Encrypted Secrets, Enkripsi storage at-rest | **Terpenuhi** |
| **NF-03** | Skalabilitas 1.000 user (degradasi < 20%) | KEDA Horizontal Pod Autoscaler di ACA + TiDB Serverless Autoscale | **Terpenuhi** |
| **NF-05** | Uptime minimal 99,5%, MTTR maksimal 2 jam | Azure Monitor (Application Insights Availability Tests) + Action Group Alert | **Terpenuhi** |
| **NF-08** | Backup otomatis tiap 24 jam dengan retensi 30 hari | Blob Versioning & Soft Delete 30 hari + Scheduled Database Dump ke Azure Blob | **Terpenuhi** |
| **NF-10** | Pencatatan audit trail & kepatuhan privasi (UU PDP) | Centralized stdout streaming ke Azure Log Analytics Workspace | **Terpenuhi** |
