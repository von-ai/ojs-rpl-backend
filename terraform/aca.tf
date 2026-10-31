# -----------------------------------------------------------------------------
# Azure Container Apps (ACA) Environment
# Hosting serverless stateless container dengan Envoy ingress & KEDA autoscaler
# -----------------------------------------------------------------------------
resource "azurerm_container_app_environment" "env" {
  name                       = "cae-${var.project_name}-${var.environment}"
  location                   = azurerm_resource_group.rg.location
  resource_group_name        = azurerm_resource_group.rg.name
  log_analytics_workspace_id = azurerm_log_analytics_workspace.logs.id
  tags                       = var.tags
}

# -----------------------------------------------------------------------------
# ACA Web Application Container (Laravel 13 + PHP 8.4-FPM + Nginx)
# Scale-to-zero saat idle (0 - 5 replika, KEDA concurrent HTTP requests > 30)
# -----------------------------------------------------------------------------
resource "azurerm_container_app" "web" {
  name                         = "ca-${var.project_name}-web-${var.environment}"
  container_app_environment_id = azurerm_container_app_environment.env.id
  resource_group_name          = azurerm_resource_group.rg.name
  revision_mode                = "Single"
  tags                         = var.tags

  dynamic "secret" {
    for_each = local.common_secrets
    content {
      name  = secret.value.name
      value = secret.value.value
    }
  }

  dynamic "registry" {
    for_each = var.container_registry_password != "" ? [1] : []
    content {
      server               = var.container_registry_server
      username             = var.container_registry_username
      password_secret_name = "ghcr-password"
    }
  }

  ingress {
    external_enabled = true
    target_port      = 80
    transport        = "auto"

    traffic_weight {
      latest_revision = true
      percentage      = 100
    }
  }

  template {
    min_replicas = var.aca_web_min_replicas
    max_replicas = var.aca_web_max_replicas

    http_scale_rule {
      name                = "http-concurrency-rule"
      concurrent_requests = 30
    }

    container {
      name   = "web"
      image  = var.container_image
      cpu    = var.aca_web_cpu
      memory = var.aca_web_memory

      dynamic "env" {
        for_each = local.common_env_vars
        content {
          name        = env.value.name
          value       = try(env.value.value, null)
          secret_name = try(env.value.secret_name, null)
        }
      }

      readiness_probe {
        transport               = "HTTP"
        port                    = 80
        path                    = "/up"
        interval_seconds        = 10
        failure_count_threshold = 3
      }

      liveness_probe {
        transport               = "HTTP"
        port                    = 80
        path                    = "/up"
        interval_seconds        = 30
        failure_count_threshold = 3
      }
    }
  }
}

# -----------------------------------------------------------------------------
# ACA Dedicated Background Queue Worker Container
# Menjalankan pemrosesan antrean asynchronous (PDF rendering, outbox, notifikasi)
# -----------------------------------------------------------------------------
resource "azurerm_container_app" "worker" {
  name                         = "ca-${var.project_name}-worker-${var.environment}"
  container_app_environment_id = azurerm_container_app_environment.env.id
  resource_group_name          = azurerm_resource_group.rg.name
  revision_mode                = "Single"
  tags                         = var.tags

  dynamic "secret" {
    for_each = local.common_secrets
    content {
      name  = secret.value.name
      value = secret.value.value
    }
  }

  dynamic "registry" {
    for_each = var.container_registry_password != "" ? [1] : []
    content {
      server               = var.container_registry_server
      username             = var.container_registry_username
      password_secret_name = "ghcr-password"
    }
  }

  template {
    min_replicas = var.aca_worker_min_replicas
    max_replicas = var.aca_worker_max_replicas

    container {
      name    = "worker"
      image   = var.container_image
      cpu     = var.aca_worker_cpu
      memory  = var.aca_worker_memory
      command = ["php", "artisan", "queue:work", "--verbose", "--tries=3", "--timeout=90"]

      dynamic "env" {
        for_each = local.common_env_vars
        content {
          name        = env.value.name
          value       = try(env.value.value, null)
          secret_name = try(env.value.secret_name, null)
        }
      }
    }
  }
}

# -----------------------------------------------------------------------------
# ACA Scheduled Job (Cron Pemicu: * * * * *)
# Hidup hanya beberapa detik per menit untuk `php artisan schedule:run`
# -----------------------------------------------------------------------------
resource "azurerm_container_app_job" "scheduler" {
  name                         = "caj-${var.project_name}-scheduler-${var.environment}"
  location                     = azurerm_resource_group.rg.location
  resource_group_name          = azurerm_resource_group.rg.name
  container_app_environment_id = azurerm_container_app_environment.env.id
  replica_timeout_in_seconds   = 60
  replica_retry_limit          = 1
  tags                         = var.tags

  dynamic "secret" {
    for_each = local.common_secrets
    content {
      name  = secret.value.name
      value = secret.value.value
    }
  }

  dynamic "registry" {
    for_each = var.container_registry_password != "" ? [1] : []
    content {
      server               = var.container_registry_server
      username             = var.container_registry_username
      password_secret_name = "ghcr-password"
    }
  }

  schedule_trigger_config {
    cron_expression = "* * * * *"
  }

  template {
    container {
      name    = "scheduler"
      image   = var.container_image
      cpu     = var.aca_job_cpu
      memory  = var.aca_job_memory
      command = ["php", "artisan", "schedule:run"]

      dynamic "env" {
        for_each = local.common_env_vars
        content {
          name        = env.value.name
          value       = try(env.value.value, null)
          secret_name = try(env.value.secret_name, null)
        }
      }
    }
  }
}
