# -----------------------------------------------------------------------------
# ACA Built-in Secrets & Shared Environment Variables
# Terisolasi secara aman di control plane ACA tanpa hardcoded credentials (NF-02)
# -----------------------------------------------------------------------------
locals {
  # Resolusi dinamis resource storage (baru vs existing)
  storage_account_name          = var.create_storage_account ? azurerm_storage_account.storage[0].name : (length(data.azurerm_storage_account.existing) > 0 ? data.azurerm_storage_account.existing[0].name : var.existing_storage_account_name)
  storage_account_key           = var.create_storage_account ? azurerm_storage_account.storage[0].primary_access_key : (length(data.azurerm_storage_account.existing) > 0 ? data.azurerm_storage_account.existing[0].primary_access_key : "")
  storage_primary_blob_endpoint = var.create_storage_account ? azurerm_storage_account.storage[0].primary_blob_endpoint : (length(data.azurerm_storage_account.existing) > 0 ? data.azurerm_storage_account.existing[0].primary_blob_endpoint : "https://${var.existing_storage_account_name}.blob.core.windows.net/")
  storage_container_manuscripts = var.storage_container_name
  storage_container_backups     = "sirida-backups"
  storage_rg_name               = var.create_storage_account ? (var.separate_storage_resource_group ? azurerm_resource_group.storage_rg[0].name : azurerm_resource_group.rg.name) : (var.existing_storage_resource_group_name != "" ? var.existing_storage_resource_group_name : (var.storage_resource_group_name != "" ? var.storage_resource_group_name : "rg-${var.project_name}-storage-${var.environment}"))

  common_secrets = concat(
    [
      {
        name  = "app-key"
        value = var.app_key
      },
      {
        name  = "db-password"
        value = var.db_password
      },
      {
        name  = "redis-password"
        value = azurerm_managed_redis.redis.default_database[0].primary_access_key
      },
      {
        name  = "azure-storage-key"
        value = local.storage_account_key
      },
      {
        name  = "mail-password"
        value = var.mail_password != "" ? var.mail_password : "placeholder-mail-password"
      }
    ],
    var.container_registry_password != "" ? [
      {
        name  = "ghcr-password"
        value = var.container_registry_password
      }
    ] : []
  )

  common_env_vars = [
    { name = "APP_NAME", value = "SIRIDA" },
    { name = "APP_ENV", value = var.environment },
    { name = "APP_DEBUG", value = tostring(var.app_debug) },
    { name = "APP_URL", value = var.enable_cloudflare ? (var.app_url != "" ? (startswith(var.app_url, "http") ? var.app_url : "https://${var.app_url}") : (var.subdomain == "@" ? "https://${var.domain_name}" : "https://${var.subdomain}.${var.domain_name}")) : (var.app_url != "" && startswith(var.app_url, "http") ? var.app_url : "https://ca-${var.project_name}-web-${var.environment}.${azurerm_container_app_environment.env.default_domain}") },
    { name = "TRUSTED_PROXIES", value = "*" },
    { name = "TRUSTED_HOSTS", value = var.subdomain == "@" ? "${var.domain_name},ca-${var.project_name}-web-${var.environment}.${azurerm_container_app_environment.env.default_domain}" : "${var.subdomain}.${var.domain_name},${var.domain_name},ca-${var.project_name}-web-${var.environment}.${azurerm_container_app_environment.env.default_domain}" },
    { name = "APP_KEY", secret_name = "app-key" },
    { name = "LOG_CHANNEL", value = "stderr" },
    { name = "DB_CONNECTION", value = "mysql" },
    { name = "DB_HOST", value = var.db_host },
    { name = "DB_PORT", value = tostring(var.db_port) },
    { name = "DB_DATABASE", value = var.db_database },
    { name = "DB_USERNAME", value = var.db_username },
    { name = "DB_PASSWORD", secret_name = "db-password" },
    { name = "DB_SSL_MODE", value = var.db_ssl_mode },
    { name = "MYSQL_ATTR_SSL_CA", value = "/etc/ssl/certs/ca-certificates.crt" },
    { name = "DB_PERSISTENT", value = "true" },
    { name = "SESSION_DRIVER", value = "redis" },
    { name = "CACHE_STORE", value = "redis" },
    { name = "QUEUE_CONNECTION", value = "database" },
    { name = "REDIS_CLIENT", value = "phpredis" },
    { name = "REDIS_CLUSTER", value = "false" },
    { name = "REDIS_HOST", value = azurerm_managed_redis.redis.hostname },
    { name = "REDIS_PORT", value = tostring(azurerm_managed_redis.redis.default_database[0].port) },
    { name = "REDIS_PASSWORD", secret_name = "redis-password" },
    { name = "REDIS_SCHEME", value = "tls" },
    { name = "REDIS_DB", value = "0" },
    { name = "REDIS_CACHE_DB", value = "0" },
    { name = "FILESYSTEM_DISK", value = var.filesystem_disk },
    { name = "PRIVATE_UPLOAD_DISK", value = var.filesystem_disk },
    { name = "FILE_SCAN_DRIVER", value = var.file_scan_driver },
    { name = "AZURE_STORAGE_NAME", value = local.storage_account_name },
    { name = "AZURE_STORAGE_KEY", secret_name = "azure-storage-key" },
    { name = "AZURE_STORAGE_CONTAINER", value = local.storage_container_manuscripts },
    { name = "AZURE_BACKUP_CONTAINER", value = local.storage_container_backups },
    { name = "AZURE_STORAGE_URL", value = local.storage_primary_blob_endpoint },
    { name = "MAIL_MAILER", value = "smtp" },
    { name = "MAIL_HOST", value = var.mail_host },
    { name = "MAIL_PORT", value = tostring(var.mail_port) },
    { name = "MAIL_USERNAME", value = var.mail_username },
    { name = "MAIL_PASSWORD", secret_name = "mail-password" },
    { name = "MAIL_ENCRYPTION", value = "tls" },
    { name = "MAIL_FROM_ADDRESS", value = var.mail_from_address },
    { name = "MAIL_FROM_NAME", value = var.mail_from_name }
  ]
}
