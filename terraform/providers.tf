terraform {
  required_version = ">= 1.5.0"

  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~> 4.0"
    }
    cloudflare = {
      source  = "cloudflare/cloudflare"
      version = "~> 4.0"
    }
    random = {
      source  = "hashicorp/random"
      version = "~> 3.5"
    }
  }

  # Konfigurasi remote backend Azure Blob Storage opsional jika ingin menyimpan state terpusat
  # backend "azurerm" {}
}

provider "azurerm" {
  resource_provider_registrations = "core"
  resource_providers_to_register = [
    "Microsoft.App",
    "Microsoft.Cache"
  ]
  features {
    resource_group {
      prevent_deletion_if_contains_resources = false
    }
  }
}

provider "cloudflare" {
  api_token = var.cloudflare_api_token
}
