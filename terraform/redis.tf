# -----------------------------------------------------------------------------
# Azure Cache for Redis (Basic C0 ~250MB, dedicated low-latency broker/cache)
# Digunakan untuk Laravel Session, Cache, Atomic Locks, & Queue
# -----------------------------------------------------------------------------
resource "azurerm_redis_cache" "redis" {
  name                = "redis-${var.project_name}-${var.environment}-${random_string.suffix.result}"
  location            = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
  capacity            = var.redis_capacity
  family              = var.redis_family
  sku_name             = var.redis_sku_name
  non_ssl_port_enabled = false
  minimum_tls_version  = "1.2"

  redis_configuration {
    maxmemory_reserved = 10
    maxmemory_delta    = 10
    maxmemory_policy   = "volatile-lru"
  }

  tags = var.tags
}
