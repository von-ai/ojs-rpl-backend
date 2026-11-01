# -----------------------------------------------------------------------------
# Azure Managed Redis (AMR) - Balanced_B0 (~500MB, Redis Enterprise Engine)
# Dedicated low-latency broker/cache untuk Laravel Session, Cache, & Atomic Locks
# -----------------------------------------------------------------------------
resource "azurerm_managed_redis" "redis" {
  name                = "amr-${var.project_name}-${var.environment}-${random_string.suffix.result}"
  location            = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
  sku_name            = var.redis_sku_name

  default_database {
    client_protocol                    = "Encrypted"
    clustering_policy                  = "EnterpriseCluster"
    eviction_policy                    = "VolatileLRU"
    access_keys_authentication_enabled = true
  }

  tags = var.tags
}
