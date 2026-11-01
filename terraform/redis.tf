# -----------------------------------------------------------------------------
# Dedicated Redis 7 In-Memory Cache & Broker Container di Azure Container Apps
# Berjalan sebagai internal microservice di dalam ACA Environment ($0 / Free Tier)
# -----------------------------------------------------------------------------
resource "random_password" "redis_password" {
  length  = 32
  special = false
}

resource "azurerm_container_app" "redis" {
  name                         = "ca-${var.project_name}-redis-${var.environment}"
  container_app_environment_id = azurerm_container_app_environment.env.id
  resource_group_name          = azurerm_resource_group.rg.name
  revision_mode                = "Single"
  tags                         = var.tags

  secret {
    name  = "redis-password"
    value = random_password.redis_password.result
  }

  ingress {
    external_enabled = false
    target_port      = 6379
    transport        = "tcp"
    exposed_port     = 6379

    traffic_weight {
      percentage      = 100
      latest_revision = true
    }
  }

  template {
    min_replicas = 1
    max_replicas = 1

    container {
      name    = "redis"
      image   = "redis:7-alpine"
      cpu     = 0.25
      memory  = "0.5Gi"
      command = ["sh", "-c", "exec redis-server --requirepass \"$REDIS_PASSWORD\""]

      env {
        name        = "REDIS_PASSWORD"
        secret_name = "redis-password"
      }
    }
  }
}


