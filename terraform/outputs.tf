# -----------------------------------------------------------------------------
# Output Nilai Infrastruktur SIRIDA
# -----------------------------------------------------------------------------

output "resource_group_name" {
  description = "Nama Azure Resource Group"
  value       = azurerm_resource_group.rg.name
}

output "web_app_direct_fqdn" {
  description = "FQDN langsung dari Azure Container Apps Envoy Ingress"
  value       = azurerm_container_app.web.ingress[0].fqdn
}

output "public_app_url" {
  description = "URL publik aplikasi web jurnal"
  value       = var.enable_cloudflare ? (var.subdomain == "@" ? "https://${var.domain_name}" : "https://${var.subdomain}.${var.domain_name}") : "https://${azurerm_container_app.web.ingress[0].fqdn}"
}

output "web_app_name" {
  description = "Nama Container App untuk Web"
  value       = azurerm_container_app.web.name
}

output "worker_app_name" {
  description = "Nama Container App untuk Worker"
  value       = azurerm_container_app.worker.name
}

output "scheduler_app_name" {
  description = "Nama Container App untuk Scheduler Daemon"
  value       = azurerm_container_app.scheduler.name
}

output "storage_account_name" {
  description = "Nama Azure Storage Account"
  value       = azurerm_storage_account.storage.name
}

output "storage_primary_blob_endpoint" {
  description = "Endpoint utama Azure Blob Storage"
  value       = azurerm_storage_account.storage.primary_blob_endpoint
}

output "storage_container_manuscripts" {
  description = "Nama container blob manuskrip naskah"
  value       = azurerm_storage_container.manuscripts.name
}

output "storage_container_backups" {
  description = "Nama container blob pencadangan database"
  value       = azurerm_storage_container.backups.name
}

output "storage_file_share_name" {
  description = "Nama Azure File Share untuk volume persistensi data container"
  value       = azurerm_storage_share.app_share.name
}

output "redis_hostname" {
  description = "Hostname Azure Managed Redis"
  value       = azurerm_managed_redis.redis.hostname
}

output "redis_port" {
  description = "Port Azure Managed Redis default database"
  value       = azurerm_managed_redis.redis.default_database[0].port
}



output "log_analytics_workspace_name" {
  description = "Nama Log Analytics Workspace"
  value       = azurerm_log_analytics_workspace.logs.name
}

output "application_insights_name" {
  description = "Nama Azure Application Insights"
  value       = azurerm_application_insights.appinsights.name
}

output "custom_domain_verification_id" {
  description = "Token verifikasi kepemilikan custom domain ACA"
  value       = azurerm_container_app.web.custom_domain_verification_id
  sensitive   = true
}

output "cli_stream_web_logs" {
  description = "Perintah Azure CLI untuk live-stream log Web Container"
  value       = "az containerapp logs show --name ${azurerm_container_app.web.name} --resource-group ${azurerm_resource_group.rg.name} --type console --follow"
}

output "cli_stream_worker_logs" {
  description = "Perintah Azure CLI untuk live-stream log Worker Container"
  value       = "az containerapp logs show --name ${azurerm_container_app.worker.name} --resource-group ${azurerm_resource_group.rg.name} --type console --follow"
}

output "cli_exec_bash_web" {
  description = "Perintah Azure CLI untuk membuka terminal interaktif di Web Container"
  value       = "az containerapp exec --name ${azurerm_container_app.web.name} --resource-group ${azurerm_resource_group.rg.name} --container web"
}

output "cloudflare_origin_host_header" {
  description = "FQDN Ingress ACA yang harus di-rewrite sebagai Host Header di Cloudflare Origin Rule"
  value       = azurerm_container_app.web.ingress[0].fqdn
}
