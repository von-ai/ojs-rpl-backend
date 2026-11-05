variable "project_name" {
  description = "Nama proyek untuk penamaan resource"
  type        = string
  default     = "sirida"
}

variable "environment" {
  description = "Target lingkungan deployment (misal: poc, staging, production)"
  type        = string
  default     = "poc"
}

variable "azure_location" {
  description = "Wilayah/region Azure untuk menempatkan resource (default: indonesiacentral / Jakarta)"
  type        = string
  default     = "indonesiacentral"
}

variable "storage_location" {
  description = "Wilayah/region Azure khusus untuk Storage Account (default: southeastasia / Singapore agar mendukung Microsoft Defender for Storage)"
  type        = string
  default     = "southeastasia"
}

variable "tags" {
  description = "Tag metadata resource Azure"
  type        = map(string)
  default = {
    Project     = "SIRIDA"
    ManagedBy   = "Terraform"
    Environment = "PoC"
    Institution = "BRIDA Kota Makassar"
  }
}

# -----------------------------------------------------------------------------
# Container Registry & Image
# -----------------------------------------------------------------------------
variable "container_image" {
  description = "URI lengkap container image di GHCR"
  type        = string
  default     = "ghcr.io/von-ai/ojs-rpl-backend:latest"
}

variable "container_registry_server" {
  description = "Alamat host container registry"
  type        = string
  default     = "ghcr.io"
}

variable "container_registry_username" {
  description = "Username akun GitHub untuk mengunduh image privat dari GHCR"
  type        = string
  default     = ""
}

variable "container_registry_password" {
  description = "Personal Access Token (PAT) GitHub dengan scope read:packages"
  type        = string
  sensitive   = true
  default     = ""
}

# -----------------------------------------------------------------------------
# Domain & Cloudflare Edge
# -----------------------------------------------------------------------------
variable "enable_cloudflare" {
  description = "Aktifkan integrasi DNS dan Cloudflare WAF"
  type        = bool
  default     = false
}

variable "enable_cloudflare_origin_rule" {
  description = "Aktifkan otomatisasi Cloudflare Origin Rule untuk rewrite Host Header ke ACA Ingress FQDN (memerlukan izin Zone.Rulesets:Edit pada Cloudflare API Token dan paket Enterprise)"
  type        = bool
  default     = false
}

variable "enable_cloudflare_worker" {
  description = "Gunakan Cloudflare Worker Edge Proxy untuk Host Header Rewrite otomatis (Solusi gratis tanpa perlu Enterprise plan)"
  type        = bool
  default     = true
}

variable "cloudflare_account_id" {
  description = "Account ID Cloudflare (dibutuhkan untuk mengelola Worker dan Worker Custom Domain)"
  type        = string
  default     = ""
}

variable "cloudflare_api_token" {
  description = "API Token Cloudflare dengan izin Zone.DNS, Zone.WAF, Workers Scripts:Edit, dan Workers Routes:Edit"
  type        = string
  sensitive   = true
  default     = ""
}

variable "cloudflare_zone_id" {
  description = "Zone ID domain di Cloudflare"
  type        = string
  default     = ""
}

variable "domain_name" {
  description = "Nama domain utama di Cloudflare (misal: makassar.go.id)"
  type        = string
  default     = "makassar.go.id"
}

variable "subdomain" {
  description = "Subdomain untuk portal jurnal SIRIDA (misal: jurnal)"
  type        = string
  default     = "jurnal"
}

# -----------------------------------------------------------------------------
# Laravel Application Configuration
# -----------------------------------------------------------------------------
variable "app_key" {
  description = "Kunci enkripsi aplikasi Laravel (APP_KEY, e.g. base64:...)"
  type        = string
  sensitive   = true
}

variable "app_debug" {
  description = "Mode debug Laravel (wajib false di production/demo publik)"
  type        = bool
  default     = false
}

variable "app_url" {
  description = "URL dasar aplikasi web SIRIDA (kosongkan jika deploy tanpa custom domain untuk menggunakan FQDN bawaan Azure)"
  type        = string
  default     = ""
}

# -----------------------------------------------------------------------------
# Database (TiDB Cloud Serverless / MySQL 8.0)
# -----------------------------------------------------------------------------
variable "db_host" {
  description = "Host endpoint basis data MySQL / TiDB Cloud Serverless"
  type        = string
}

variable "db_port" {
  description = "Port koneksi database (umumnya 4000 untuk TiDB atau 3306 untuk MySQL)"
  type        = number
  default     = 4000
}

variable "db_database" {
  description = "Nama basis data"
  type        = string
  default     = "ojs_rpl"
}

variable "db_username" {
  description = "Nama pengguna basis data"
  type        = string
  default     = "sirida_app"
}

variable "db_password" {
  description = "Kata sandi basis data"
  type        = string
  sensitive   = true
}

variable "db_ssl_mode" {
  description = "Mode SSL koneksi database (REQUIRED untuk TiDB Serverless)"
  type        = string
  default     = "REQUIRED"
}

# -----------------------------------------------------------------------------
# Storage & Defender (Pemisahan Siklus Hidup Stateful vs Stateless)
# -----------------------------------------------------------------------------
variable "create_storage_account" {
  description = "Tentukan apakah Terraform mengelola inisiasi/penghancuran Storage Account baru (true) atau menggunakan yang sudah ada (false)"
  type        = bool
  default     = true
}

variable "separate_storage_resource_group" {
  description = "Tempatkan Azure Blob Storage dalam Resource Group terpisah (Stateful RG) agar terisolasi dari siklus hidup aplikasi (Stateless RG)"
  type        = bool
  default     = true
}

variable "storage_resource_group_name" {
  description = "Nama Resource Group khusus untuk Storage (kosongkan untuk menggunakan penamaan default 'rg-{project_name}-storage-{environment}')"
  type        = string
  default     = ""
}

variable "existing_storage_account_name" {
  description = "Nama Storage Account yang sudah ada jika create_storage_account = false"
  type        = string
  default     = ""
}

variable "existing_storage_resource_group_name" {
  description = "Nama Resource Group tempat existing_storage_account_name berada jika create_storage_account = false"
  type        = string
  default     = ""
}

variable "enable_storage_delete_lock" {
  description = "Aktifkan Azure Resource Management Lock (CanNotDelete) untuk mencegah penghapusan data blob yang tidak sengaja"
  type        = bool
  default     = false
}

variable "storage_container_name" {
  description = "Nama blob container untuk berkas naskah manuskrip"
  type        = string
  default     = "sirida-manuscripts"
}

variable "storage_retention_days" {
  description = "Durasi retensi soft delete dan versioning berkas (hari, NF-08: 30 hari)"
  type        = number
  default     = 30
}

variable "filesystem_disk" {
  description = "Driver penyimpanan default Laravel (azure)"
  type        = string
  default     = "azure"
}

variable "enable_storage_defender" {
  description = "Aktifkan Microsoft Defender for Storage (Malware Scanning)"
  type        = bool
  default     = true
}

variable "file_scan_driver" {
  description = "Driver pemindai malware (defender untuk Microsoft Defender for Storage di cloud)"
  type        = string
  default     = "defender"
}

# -----------------------------------------------------------------------------
# Azure Cache for Redis
# -----------------------------------------------------------------------------
# Azure Managed Redis (AMR)
# -----------------------------------------------------------------------------
variable "redis_sku_name" {
  description = "SKU Azure Managed Redis (AMR): Balanced_B0 (~500MB untuk PoC/Dev), Balanced_B1 (~1GB), Balanced_B3 (~3GB)"
  type        = string
  default     = "Balanced_B0"
}

variable "redis_capacity" {
  description = "Kapasitas cache Redis (Deprecated di AMR, dipertahankan untuk kompatibilitas konfigurasi lama)"
  type        = number
  default     = 0
}

variable "redis_family" {
  description = "Family SKU Redis (Deprecated di AMR, dipertahankan untuk kompatibilitas konfigurasi lama)"
  type        = string
  default     = "C"
}


# -----------------------------------------------------------------------------
# Transactional Email (Azure Communication Services / SMTP)
# -----------------------------------------------------------------------------
variable "mail_host" {
  description = "Host SMTP untuk pengiriman surel"
  type        = string
  default     = "smtp.azurecomm.net"
}

variable "mail_port" {
  description = "Port SMTP (587 untuk TLS)"
  type        = number
  default     = 587
}

variable "mail_username" {
  description = "Username SMTP"
  type        = string
  default     = ""
}

variable "mail_password" {
  description = "Password SMTP"
  type        = string
  sensitive   = true
  default     = ""
}

variable "mail_from_address" {
  description = "Alamat email pengirim transaksi jurnal"
  type        = string
  default     = "no-reply@jurnal.makassar.go.id"
}

variable "mail_from_name" {
  description = "Nama pengirim email"
  type        = string
  default     = "SIRIDA - Jurnal Kota Makassar"
}

# -----------------------------------------------------------------------------
# ACA Sizing & Scaling Policy
# -----------------------------------------------------------------------------
variable "aca_web_cpu" {
  description = "Alokasi vCPU untuk Web Container (default 2.0 vCPU)"
  type        = number
  default     = 2.0
}

variable "aca_web_memory" {
  description = "Alokasi RAM untuk Web Container (default 4.0Gi)"
  type        = string
  default     = "4.0Gi"
}

variable "aca_web_min_replicas" {
  description = "Replika minimum Web Container (0 untuk scale-to-zero hemat biaya)"
  type        = number
  default     = 0
}

variable "aca_web_max_replicas" {
  description = "Replika maksimum Web Container"
  type        = number
  default     = 5
}

variable "aca_worker_cpu" {
  description = "Alokasi vCPU untuk Queue Worker Container (default 1.0 vCPU)"
  type        = number
  default     = 1.0
}

variable "aca_worker_memory" {
  description = "Alokasi RAM untuk Queue Worker Container (default 2.0Gi, rasio 1:2 vCPU:RAM di ACA Consumption Plan)"
  type        = string
  default     = "2.0Gi"
}

variable "aca_worker_min_replicas" {
  description = "Replika minimum Queue Worker Container"
  type        = number
  default     = 1
}

variable "aca_worker_max_replicas" {
  description = "Replika maksimum Queue Worker Container"
  type        = number
  default     = 3
}

variable "aca_job_cpu" {
  description = "Alokasi vCPU untuk Scheduled Job (default 0.25 vCPU)"
  type        = number
  default     = 0.25
}

variable "aca_job_memory" {
  description = "Alokasi RAM untuk Scheduled Job (default 0.5Gi)"
  type        = string
  default     = "0.5Gi"
}

variable "alert_email" {
  description = "Email penerima alert Azure Monitor jika ketersediaan SLA < 99,5%"
  type        = string
  default     = "admin@makassar.go.id"
}
