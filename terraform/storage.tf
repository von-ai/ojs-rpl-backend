# -----------------------------------------------------------------------------
# Azure Blob Storage Account
# Enkripsi AES-256 bawaan, TLS 1.2+, versioning & soft delete (NF-02, NF-08)
# -----------------------------------------------------------------------------
resource "azurerm_storage_account" "storage" {
  name                     = "st${var.project_name}${var.environment}${random_string.suffix.result}"
  resource_group_name      = azurerm_resource_group.rg.name
  location                 = azurerm_resource_group.rg.location
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
  }

  tags = var.tags
}

# -----------------------------------------------------------------------------
# Blob Container untuk Naskah Manuskrip & Berkas Lampiran
# -----------------------------------------------------------------------------
resource "azurerm_storage_container" "manuscripts" {
  name                  = var.storage_container_name
  storage_account_name  = azurerm_storage_account.storage.name
  container_access_type = "private"
}

# -----------------------------------------------------------------------------
# Blob Container untuk Cadangan Terjadwal Basis Data (Database Dump Backups)
# -----------------------------------------------------------------------------
resource "azurerm_storage_container" "backups" {
  name                  = "sirida-backups"
  storage_account_name  = azurerm_storage_account.storage.name
  container_access_type = "private"
}

# -----------------------------------------------------------------------------
# Azure Storage File Share untuk Persistensi Berkas Kontainer Stateless (NF-01, NF-08)
# Memastikan berkas di /var/www/html/storage/app tersimpan permanen & aman
# -----------------------------------------------------------------------------
resource "azurerm_storage_share" "app_share" {
  name                 = "sirida-app-data"
  storage_account_name = azurerm_storage_account.storage.name
  quota                = 5 # 5 GB
}

# -----------------------------------------------------------------------------
# Aturan Manajemen Siklus Hidup Objek (Lifecycle Management: 30 Hari)
# -----------------------------------------------------------------------------
resource "azurerm_storage_management_policy" "lifecycle" {
  storage_account_id = azurerm_storage_account.storage.id

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
}

# -----------------------------------------------------------------------------
# Advanced Threat Protection & Microsoft Defender for Storage (Malware Scanning)
# -----------------------------------------------------------------------------
resource "azurerm_advanced_threat_protection" "atp" {
  target_resource_id = azurerm_storage_account.storage.id
  enabled            = true
}

resource "azurerm_security_center_storage_defender" "defender" {
  count                                       = var.enable_storage_defender ? 1 : 0
  storage_account_id                          = azurerm_storage_account.storage.id
  malware_scanning_on_upload_enabled          = true
  malware_scanning_on_upload_cap_gb_per_month = 50
  sensitive_data_discovery_enabled            = false
}
