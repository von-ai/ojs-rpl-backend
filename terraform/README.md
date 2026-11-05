# SIRIDA — Otomasi Infrastruktur & Deployment Terraform (IaC)

Direktori ini berisi seluruh modul deklaratif **Terraform (Infrastructure as Code)** untuk provisioning, konfigurasi, dan otomatisasi deployment sistem **SIRIDA (BRIDA Kota Makassar)** pada multi-cloud serverless environment (Microsoft Azure + Cloudflare Edge + TiDB Cloud).

Arsitektur ini menerapkan pola **The Ephemeral Cloud Pattern**:
- Diluncurkan sesaat sebelum presentasi/pengujian (`terraform apply -auto-approve`) dalam tempo 3–5 menit.
- Dihancurkan segera setelah sesi evaluasi selesai (`terraform destroy -auto-approve`) untuk menghentikan seluruh tagihan sewa cloud (biaya sewa riil berkisar **Rp 0 s.d. < Rp 1.500 per sesi demo**).

---

## 1. Struktur Modul Terraform

```text
terraform/
├── providers.tf            # Definisi provider azurerm (~> 4.0), cloudflare (~> 4.0), random
├── variables.tf            # Deklarasi variabel konfigurasi (region, domain, sizing, credentials)
├── main.tf                 # Azure Resource Group & Log Analytics Workspace
├── storage.tf              # Azure Storage Account, Blob Container, Lifecycle, Defender
├── redis.tf                # Azure Managed Redis (AMR) - Balanced_B0 (~500MB, Redis Enterprise Engine)
├── secrets.tf              # Pemetaan ACA built-in secrets & variabel environment container
├── aca.tf                  # ACA Environment, Web Container, Queue Worker, Scheduled Job
├── monitoring.tf           # Azure Application Insights, Web Test (5 Probe), Alert SLA 99.5%
├── cloudflare.tf           # Cloudflare DNS CNAME, TXT verification, TLS 1.3, Cloudflare WAF
├── outputs.tf              # Output nilai endpoint, FQDN, dan pintasan Azure CLI
├── terraform.tfvars.example # Template pengisian konfigurasi rahasia/kredensial
└── README.md               # Dokumentasi panduan operasional deployment
```

---

## 2. Prasyarat (*Prerequisites*)

Pastikan tool berikut telah terpasang di sistem pengembang / CI runner:
1. **Terraform CLI** (versi `>= 1.5.0`) atau **OpenTofu** (`>= 1.6.0`).
2. **Azure CLI (`az`)** terautentikasi ke akun Azure:
   ```bash
   az login
   az account set --subscription "<SUBSCRIPTION_ID>"
   ```
3. Akses ke **GitHub Container Registry (GHCR)**: Personal Access Token (PAT) dengan scope `read:packages`.
4. Kluster database **TiDB Cloud Serverless** yang telah dibuat dan siap menerima koneksi TLS.

---

## 3. Langkah Cepat Deployment (*Quick Start*)

### Langkah 1: Siapkan Konfigurasi `terraform.tfvars`
Salin template `terraform.tfvars.example`:
```bash
cd terraform
cp terraform.tfvars.example terraform.tfvars
```
Buka file `terraform.tfvars` dan lengkapi nilai variabel:
- `app_key`: Kunci aplikasi Laravel (`php artisan key:generate --show`).
- `db_host`, `db_password`: Endpoint dan kata sandi TiDB Serverless.
- `container_registry_username`, `container_registry_password`: Akses ke GHCR jika image privat.
- `enable_cloudflare`: Set `true` dan isi `cloudflare_api_token` + `cloudflare_zone_id` jika DNS publik siap dihubungkan.

### Langkah 2: Jalankan Script Deployment Otomatis
Dari root direktori repositori:
```bash
./scripts/deploy.sh
```
Atau jika ingin menyetujui perubahan secara otomatis tanpa konfirmasi interaktif:
```bash
./scripts/deploy.sh --auto-approve
```
Jika ingin langsung mengeksekusi migrasi database Laravel setelah provisioning selesai:
```bash
./scripts/deploy.sh --auto-approve --migrate
```

---

## 4. Perintah Operasional Pasca Deployment

Setelah deployment sukses, terminal akan menampilkan output FQDN Web App, Resource Group, dan Storage Endpoint. Anda dapat memantau kontainer dengan perintah:

1. **Streaming Live Log Web Container**:
   ```bash
   az containerapp logs show --name ca-sirida-web-poc --resource-group rg-sirida-poc --type console --follow
   ```

2. **Streaming Live Log Background Worker**:
   ```bash
   az containerapp logs show --name ca-sirida-worker-poc --resource-group rg-sirida-poc --type console --follow
   ```

3. **Membuka Terminal Bash Interaktif di Kontainer**:
   ```bash
   az containerapp exec --name ca-sirida-web-poc --resource-group rg-sirida-poc --container web
   ```

4. **Eksekusi Manual Migrasi Database**:
   ```bash
   ./scripts/run-migration.sh --seed
   ```

---

## 5. Integrasi Custom Domain Publik (`sirida.web.id`) via Cloudflare Worker

### Mengapa Cloudflare Worker?
1. **Kebijakan Azure Container Apps Express Environment**: Azure menolak pendaftaran custom domain secara langsung pada tingkat ingress Container App (`ExpressCustomDomainsNotSupported`).
2. **Batasan Lisensi Cloudflare Free Plan**: Fitur penulisan ulang Host Header (*HostHeader override*) pada Cloudflare Origin Rules dikunci khusus untuk pelanggan paket Enterprise.
3. **Solusi Standar Industri**: Menggunakan **Cloudflare Worker** (gratis hingga 100.000 req/hari) yang bertindak sebagai Edge Reverse Proxy untuk menerima trafik `https://sirida.web.id` dan menulis ulang Host Header ke FQDN ACA secara otomatis.

### Otomasi Penuh via Terraform (Rekomendasi)
Integrasi ini sekarang **100% terotomasi** di dalam Terraform ([`cloudflare.tf`](cloudflare.tf)):
1. Pastikan variabel berikut terisi di `terraform.tfvars`:
   ```hcl
   enable_cloudflare        = true
   enable_cloudflare_worker = true
   cloudflare_account_id    = "<CLOUDFLARE_ACCOUNT_ID>"
   cloudflare_api_token     = "<CLOUDFLARE_API_TOKEN>"
   cloudflare_zone_id       = "<CLOUDFLARE_ZONE_ID>"
   domain_name              = "sirida.web.id"
   subdomain                = "@"
   ```
2. Saat `terraform apply` dijalankan:
   - Terraform membaca template [`terraform/cloudflare-worker.js.tpl`](cloudflare-worker.js.tpl) dan menginjeksi FQDN dinamis ACA Ingress (`azurerm_container_app.web.ingress[0].fqdn`).
   - Terraform membuat atau memperbarui resource Worker Script (`cloudflare_workers_script.proxy`).
   - Terraform mengaitkan custom domain publik (`cloudflare_workers_domain.custom_domain`) ke Worker secara otomatis.
   - Tidak diperlukan pembuatan CNAME manual ataupun klik pada dashboard Cloudflare.

### Konfigurasi Manual Dashboard (Opsional / Fallback)
Jika tidak menggunakan Terraform untuk mengelola Cloudflare:
1. Buka [dash.cloudflare.com](https://dash.cloudflare.com) ➔ pilih **Workers & Pages**.
2. Klik **Create Application** ➔ tab **Workers** ➔ **Create Worker** (beri nama: `sirida-proxy`) ➔ klik **Deploy**.
3. Klik **Edit Code** dan masukkan kode dari [`terraform/cloudflare-worker.js.tpl`](cloudflare-worker.js.tpl) dengan mengganti `${origin_fqdn}` dengan nilai output Terraform `web_app_direct_fqdn`.
4. Buka tab **Settings** ➔ **Domains & Routes** ➔ klik **Add** ➔ pilih **Custom Domain**.
5. Masukkan: **`sirida.web.id`** ➔ klik **Add Custom Domain**.

---

## 6. Pemisahan Siklus Hidup Stateful (Storage) vs Stateless (App)

Arsitektur cloud SIRIDA memisahkan komponen **Stateful** (Azure Blob Storage berisi naskah manuskrip dan bukti bayar) dari komponen **Stateless** (ACA, Redis, Cloudflare, Logs):

1. **Resource Group Terpisah**:
   - `rg-sirida-poc`: Resource Group Stateless di Jakarta (`indonesiacentral`) untuk ACA Web/Worker, Redis, dan Logs.
   - `rg-sirida-storage-poc`: Resource Group Stateful di Singapura (`southeastasia`) khusus untuk Storage Account dan Microsoft Defender.
2. **Perlindungan Terhadap Dangling Metadata**:
   - Menghapus komponen aplikasi tidak akan menghapus data berkas di Azure Blob Storage, sehingga metadata naskah di database TiDB tetap konsisten dan utuh.
3. **Kunci Proteksi (Azure Management Lock)**:
   - Variabel `enable_storage_delete_lock = true` dapat diaktifkan di staging/production untuk mencegah penghapusan Storage Account secara tidak sengaja melalui Terraform maupun Azure Portal.

---

## 7. Opsi Inisiasi & Deployment Terpisah

Anda dapat menginisiasi seluruh stack atau memilih layer tertentu:

1. **Deploy Seluruh Stack (Default)**:
   ```bash
   ./scripts/deploy.sh
   ```
2. **Inisiasi Azure Blob Storage Saja (Stateful)**:
   ```bash
   ./scripts/deploy.sh --storage-only
   ```
   Hanya membuat Resource Group Storage, Storage Account, Containers, Lifecycle Policy, Defender, dan Lock.
3. **Inisiasi Stateless App Saja (Menghubungkan ke Storage yang Ada)**:
   ```bash
   ./scripts/deploy.sh --app-only
   ```
   Hanya membuat ACA, Redis, Log Analytics, dan Cloudflare.

---

## 8. Opsi Penghancuran (*Teardown*) Terpisah

Saat sesi evaluasi/pengujian selesai, Anda dapat memilih cakupan penghancuran agar biaya sewa berhenti tanpa mengorbankan integritas data:

```bash
./scripts/destroy.sh
```
Skrip akan menampilkan menu interaktif:
- **Pilihan 1 [Rekomendasi]: Hancurkan Stateless App Saja** (`--app-only`)
  - Menghapus ACA, Redis, Log Analytics, dan Cloudflare.
  - Azure Blob Storage & berkas naskah **tetap aman**. Biaya komputasi berhenti dan metadata TiDB tidak menjadi dangling.
- **Pilihan 2: Hancurkan Azure Blob Storage Saja** (`--storage-only`)
  - Hanya menghapus Storage Account dan seluruh berkas naskah di dalamnya.
- **Pilihan 3: Hancurkan SEMUA (Full Teardown)** (`--all`)
  - Menghapus seluruh resource Azure dan Cloudflare tanpa terkecuali.

**Perintah Non-Interaktif (Otomasi CI/CD):**
```bash
# Hancurkan hanya aplikasi (Storage aman):
./scripts/destroy.sh --app-only --auto-approve

# Hancurkan hanya storage:
./scripts/destroy.sh --storage-only --auto-approve

# Hancurkan seluruh infrastruktur:
./scripts/destroy.sh --all --auto-approve
```
