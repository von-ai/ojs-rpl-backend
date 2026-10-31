# -----------------------------------------------------------------------------
# Azure Monitor: Application Insights & Availability Web Test
# Memantau SLA ketersediaan >= 99,5% & MTTR <= 2 jam (NF-05)
# -----------------------------------------------------------------------------
resource "azurerm_application_insights" "appinsights" {
  name                = "appi-${var.project_name}-${var.environment}-${random_string.suffix.result}"
  location            = azurerm_resource_group.rg.location
  resource_group_name = azurerm_resource_group.rg.name
  workspace_id        = azurerm_log_analytics_workspace.logs.id
  application_type    = "web"
  tags                = var.tags
}

# -----------------------------------------------------------------------------
# Global Availability Web Test (5 Probe Global, Ping Test Tiap 5 Menit)
# -----------------------------------------------------------------------------
resource "azurerm_application_insights_standard_web_test" "availability" {
  name                    = "webtest-${var.project_name}-uptime"
  resource_group_name     = azurerm_resource_group.rg.name
  location                = azurerm_resource_group.rg.location
  application_insights_id = azurerm_application_insights.appinsights.id
  geo_locations           = ["apac-hk-hkn-azr", "apac-jp-kaw-edge", "apac-sg-sin-azr", "emea-gb-db3-azr", "us-va-ash-azr"]
  frequency               = 300
  timeout                 = 30
  enabled                 = true

  request {
    url = var.app_url != "" ? "${var.app_url}/up" : "https://${azurerm_container_app.web.ingress[0].fqdn}/up"
  }

  tags = var.tags
}

# -----------------------------------------------------------------------------
# Action Group Notifikasi Alert (Surel DevOps Administrator)
# -----------------------------------------------------------------------------
resource "azurerm_monitor_action_group" "action_group" {
  name                = "ag-${var.project_name}-alerts"
  resource_group_name = azurerm_resource_group.rg.name
  short_name          = "siridaalert"

  email_receiver {
    name          = "devops-admin"
    email_address = var.alert_email
  }

  tags = var.tags
}

# -----------------------------------------------------------------------------
# Metric Alert: Trigger jika Uptime Availability < 99.5%
# -----------------------------------------------------------------------------
resource "azurerm_monitor_metric_alert" "sla_alert" {
  name                = "alert-${var.project_name}-availability-sla"
  resource_group_name = azurerm_resource_group.rg.name
  scopes              = [azurerm_application_insights.appinsights.id, azurerm_application_insights_standard_web_test.availability.id]
  description         = "Peringatan otomatis saat ketersediaan layanan SLA turun di bawah 99.5% (NF-05)"
  severity            = 1
  frequency           = "PT5M"
  window_size         = "PT15M"

  criteria {
    metric_namespace = "microsoft.insights/components"
    metric_name      = "availabilityResults/availabilityPercentage"
    aggregation      = "Average"
    operator         = "LessThan"
    threshold        = 99.5
  }

  action {
    action_group_id = azurerm_monitor_action_group.action_group.id
  }

  tags = var.tags
}
