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

## 5. Penghancuran Infrastruktur (*Ephemeral Teardown*)

Setelah sesi presentasi, pengujian, atau evaluasi expo selesai, jalankan script teardown untuk menghapus seluruh komponen cloud secara tuntas:

```bash
./scripts/destroy.sh
```
Atau tanpa prompt interaktif (untuk otomasi CI/CD):
```bash
./scripts/destroy.sh --auto-approve
```
Semua tagihan sewa infrastruktur cloud akan langsung berhenti.
