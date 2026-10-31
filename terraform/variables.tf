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
  description = "Wilayah/region Azure untuk menempatkan resource (direkomendasikan Southeast Asia / Singapore)"
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

variable "cloudflare_api_token" {
  description = "API Token Cloudflare dengan izin Zone.DNS dan Zone.WAF"
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
# Storage & Defender
# -----------------------------------------------------------------------------
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
  description = "Driver penyimpanan default Laravel (local atau azure)"
  type        = string
  default     = "local"
}

variable "enable_storage_defender" {
  description = "Aktifkan Microsoft Defender for Storage (Malware Scanning)"
  type        = bool
  default     = true
}

# -----------------------------------------------------------------------------
# Azure Cache for Redis
# -----------------------------------------------------------------------------
variable "redis_capacity" {
  description = "Kapasitas cache Redis (0 untuk Basic C0 ~250MB)"
  type        = number
  default     = 0
}

variable "redis_family" {
  description = "Family SKU Redis (C untuk Basic/Standard)"
  type        = string
  default     = "C"
}

variable "redis_sku_name" {
  description = "Nama SKU Redis (Basic / Standard / Premium)"
  type        = string
  default     = "Basic"
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
  description = "Alokasi vCPU untuk Web Container (default 0.5 vCPU)"
  type        = number
  default     = 0.5
}

variable "aca_web_memory" {
  description = "Alokasi RAM untuk Web Container (default 1.0Gi)"
  type        = string
  default     = "1.0Gi"
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
  description = "Alokasi vCPU untuk Queue Worker Container (default 0.5 vCPU)"
  type        = number
  default     = 0.5
}

variable "aca_worker_memory" {
  description = "Alokasi RAM untuk Queue Worker Container (default 0.5Gi)"
  type        = string
  default     = "0.5Gi"
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
