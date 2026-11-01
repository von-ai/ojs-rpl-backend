# -----------------------------------------------------------------------------
# Cloudflare Edge: DNS CNAME Mapping
# Mengarahkan domain publik ke Azure Container Apps Ingress FQDN
# -----------------------------------------------------------------------------

# DNS CNAME Record: Mengarahkan domain/subdomain ke ACA Ingress FQDN
resource "cloudflare_record" "cname_web" {
  count   = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id = var.cloudflare_zone_id
  name    = var.subdomain
  content = azurerm_container_app.web.ingress[0].fqdn
  type    = "CNAME"
  proxied = true # Mengaktifkan Cloudflare CDN, TLS, & Proteksi DDoS/WAF
  ttl     = 1    # Automatic jika proxied
  comment = "SIRIDA Web Ingress ACA diarahkan ke Cloudflare Edge"
}

# -----------------------------------------------------------------------------
# Cloudflare Origin Rule: Otomasi Host Header Rewrite ke ACA FQDN Dinamis
# Menyelesaikan batasan ACA Express agar tidak membutuhkan pendaftaran custom domain di Azure
# -----------------------------------------------------------------------------
resource "cloudflare_ruleset" "origin_rules" {
  count       = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id     = var.cloudflare_zone_id
  name        = "SIRIDA Dynamic Host Header Rewrite"
  description = "Otomatis mengarahkan Host Header ke ACA Ingress FQDN yang dinamis"
  kind        = "zone"
  phase       = "http_request_origin"

  rules {
    ref         = "route_to_aca"
    description = "Rewrite Host Header to dynamic ACA Ingress FQDN"
    expression  = "(http.host eq \"${var.subdomain == "@" ? var.domain_name : "${var.subdomain}.${var.domain_name}"}\")"
    enabled     = true
    action      = "route"

    action_parameters {
      host_header = azurerm_container_app.web.ingress[0].fqdn
    }
  }
}
