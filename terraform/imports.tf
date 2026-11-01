# -----------------------------------------------------------------------------
# Declarative Resource Imports (Terraform 1.5+)
# Mengimpor resource yang sudah berhasil dibuat di Azure ke dalam state
# -----------------------------------------------------------------------------
import {
  to = azurerm_application_insights.appinsights
  id = "/subscriptions/30c6bbda-71f5-4c2e-b66c-9efd42b52fbc/resourceGroups/rg-sirida-poc/providers/Microsoft.Insights/components/appi-sirida-poc-gw72uc"
}

import {
  to = azurerm_storage_account.storage
  id = "/subscriptions/30c6bbda-71f5-4c2e-b66c-9efd42b52fbc/resourceGroups/rg-sirida-poc/providers/Microsoft.Storage/storageAccounts/stsiridapocgw72uc"
}
