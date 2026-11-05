# -----------------------------------------------------------------------------
# Migrasi State Otomatis (Backward Compatibility)
# Memindahkan address resource tunggal ke indexed [0] tanpa re-create
# -----------------------------------------------------------------------------
moved {
  from = azurerm_storage_account.storage
  to   = azurerm_storage_account.storage[0]
}

moved {
  from = random_string.storage_suffix
  to   = random_string.storage_suffix[0]
}

moved {
  from = azurerm_storage_container.manuscripts
  to   = azurerm_storage_container.manuscripts[0]
}

moved {
  from = azurerm_storage_container.backups
  to   = azurerm_storage_container.backups[0]
}

moved {
  from = azurerm_storage_management_policy.lifecycle
  to   = azurerm_storage_management_policy.lifecycle[0]
}

# -----------------------------------------------------------------------------
# Dedicated Resource Group untuk Stateful Azure Blob Storage
# Terisolasi dari Resource Group aplikasi (stateless) agar siklus hidup
# dan data persisten tidak ikut terhapus saat teardown aplikasi (NF-08)
# -----------------------------------------------------------------------------
resource "azurerm_resource_group" "storage_rg" {
  count    = var.create_storage_account && var.separate_storage_resource_group ? 1 : 0
  name     = var.storage_resource_group_name != "" ? var.storage_resource_group_name : "rg-${var.project_name}-storage-${var.environment}"
  location = var.storage_location
  tags     = var.tags
}

# -----------------------------------------------------------------------------
# Random Suffix Khusus Storage Account (Menghindari Name Collision Global Azure)
# -----------------------------------------------------------------------------
resource "random_string" "storage_suffix" {
  count   = var.create_storage_account ? 1 : 0
  length  = 6
  special = false
  upper   = false
}

# -----------------------------------------------------------------------------
# Azure Blob Storage Account
# Enkripsi AES-256 bawaan, TLS 1.2+, versioning & soft delete (NF-02, NF-08)
# -----------------------------------------------------------------------------
resource "azurerm_storage_account" "storage" {
  count                    = var.create_storage_account ? 1 : 0
  name                     = "st${var.project_name}${var.environment}${random_string.storage_suffix[0].result}"
  resource_group_name      = var.separate_storage_resource_group ? azurerm_resource_group.storage_rg[0].name : azurerm_resource_group.rg.name
  location                 = var.storage_location
  account_tier             = "Standard"
  account_replication_type = "LRS"
  account_kind             = "StorageV2"
  min_tls_version          = "TLS1_2"

  allow_nested_items_to_be_public = false

  blob_properties {
    versioning_enabled = true

    delete_retention_policy {
      days = var.storage_retention_days
    }

    container_delete_retention_policy {
      days = var.storage_retention_days
    }

    cors_rule {
      allowed_headers    = ["*"]
      allowed_methods    = ["GET", "HEAD", "PUT", "OPTIONS"]
      allowed_origins    = [var.subdomain == "@" ? "https://${var.domain_name}" : "https://${var.subdomain}.${var.domain_name}", "http://localhost", "http://localhost:8000", "http://127.0.0.1:8000"]
      exposed_headers    = ["*"]
      max_age_in_seconds = 3600
    }
  }

  tags = var.tags
}

# -----------------------------------------------------------------------------
# Blob Container untuk Naskah Manuskrip & Berkas Lampiran
# -----------------------------------------------------------------------------
resource "azurerm_storage_container" "manuscripts" {
  count                 = var.create_storage_account ? 1 : 0
  name                  = var.storage_container_name
  storage_account_id    = azurerm_storage_account.storage[0].id
  container_access_type = "private"

  depends_on = [azurerm_storage_account.storage]
}

# -----------------------------------------------------------------------------
# Blob Container untuk Cadangan Terjadwal Basis Data (Database Dump Backups)
# -----------------------------------------------------------------------------
resource "azurerm_storage_container" "backups" {
  count                 = var.create_storage_account ? 1 : 0
  name                  = "sirida-backups"
  storage_account_id    = azurerm_storage_account.storage[0].id
  container_access_type = "private"

  depends_on = [azurerm_storage_account.storage]
}

# -----------------------------------------------------------------------------
# Aturan Manajemen Siklus Hidup Objek (Lifecycle Management: 30 Hari)
# -----------------------------------------------------------------------------
resource "azurerm_storage_management_policy" "lifecycle" {
  count              = var.create_storage_account ? 1 : 0
  storage_account_id = azurerm_storage_account.storage[0].id

  rule {
    name    = "cleanup-old-blob-versions-30days"
    enabled = true
    filters {
      prefix_match = [var.storage_container_name, "sirida-backups"]
      blob_types   = ["blockBlob"]
    }
    actions {
      version {
        delete_after_days_since_creation = var.storage_retention_days
      }
    }
  }

  rule {
    name    = "cleanup-staged-manuscripts"
    enabled = true
    filters {
      prefix_match = ["${var.storage_container_name}/staging/manuscripts/"]
      blob_types   = ["blockBlob"]
    }
    actions {
      base_blob {
        delete_after_days_since_modification_greater_than = 2
      }
    }
  }

  depends_on = [azurerm_storage_account.storage]
}

# -----------------------------------------------------------------------------
# Microsoft Defender for Storage (Malware Scanning)
# Berjalan aktif di Singapore (southeastasia) untuk pemindaian malware naskah otomatis
# -----------------------------------------------------------------------------
resource "azurerm_security_center_storage_defender" "defender" {
  count                                       = var.create_storage_account && var.enable_storage_defender ? 1 : 0
  storage_account_id                          = azurerm_storage_account.storage[0].id
  malware_scanning_on_upload_enabled          = true
  malware_scanning_on_upload_cap_gb_per_month = 50
  sensitive_data_discovery_enabled            = false

  depends_on = [azurerm_storage_account.storage]
}

# -----------------------------------------------------------------------------
# Azure Management Lock (Pencegahan Penghapusan Data Tidak Sengaja)
# Mencegah eksekusi penghapusan pada Storage Account saat lock aktif (NF-08)
# -----------------------------------------------------------------------------
resource "azurerm_management_lock" "storage_lock" {
  count      = var.create_storage_account && var.enable_storage_delete_lock ? 1 : 0
  name       = "lock-${var.project_name}-storage-delete-protection"
  scope      = azurerm_storage_account.storage[0].id
  lock_level = "CanNotDelete"
  notes      = "Perlindungan data persisten Azure Blob Storage SIRIDA dari penghapusan tidak sengaja agar sinkron dengan metadata TiDB."

  depends_on = [azurerm_storage_account.storage]
}

# -----------------------------------------------------------------------------
# Data Source untuk Existing Storage Account (Jika create_storage_account = false)
# Digunakan saat arsitektur sepenuhnya dipisahkan atau menghubungkan ke storage persisten
# -----------------------------------------------------------------------------
data "azurerm_storage_account" "existing" {
  count               = var.create_storage_account ? 0 : 1
  name                = var.existing_storage_account_name
  resource_group_name = var.existing_storage_resource_group_name != "" ? var.existing_storage_resource_group_name : (var.storage_resource_group_name != "" ? var.storage_resource_group_name : "rg-${var.project_name}-storage-${var.environment}")
}
