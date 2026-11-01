# -----------------------------------------------------------------------------
# Cloudflare Edge: DNS, CDN Caching, TLS 1.3, & WAF (NF-01, NF-02)
# -----------------------------------------------------------------------------

# DNS CNAME Record: Mengarahkan subdomain jurnal.makassar.go.id ke ACA Ingress FQDN
resource "cloudflare_record" "cname_web" {
  count   = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id = var.cloudflare_zone_id
  name    = var.subdomain
  value   = azurerm_container_app.web.ingress[0].fqdn
  type    = "CNAME"
  proxied = true # Mengaktifkan Cloudflare CDN & WAF
  ttl     = 1    # Automatic jika proxied
  comment = "SIRIDA Web Ingress ACA diarahkan ke Cloudflare Edge"
}

# DNS TXT Record: Verifikasi Kepemilikan Domain untuk Azure Container Apps
resource "cloudflare_record" "asuid_txt" {
  count   = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id = var.cloudflare_zone_id
  name    = var.subdomain == "@" ? "asuid" : "asuid.${var.subdomain}"
  value   = azurerm_container_app.web.custom_domain_verification_id
  type    = "TXT"
  proxied = false
  ttl     = 300
  comment = "Azure Container Apps Custom Domain Verification Token"
}

# -----------------------------------------------------------------------------
# Binding Custom Domain ke Azure Container Apps
# -----------------------------------------------------------------------------
resource "azurerm_container_app_custom_domain" "web" {
  count            = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  name             = var.subdomain == "@" ? var.domain_name : "${var.subdomain}.${var.domain_name}"
  container_app_id = azurerm_container_app.web.id

  certificate_binding_type = "Disabled"

  depends_on = [
    cloudflare_record.asuid_txt,
    cloudflare_record.cname_web
  ]
}

# -----------------------------------------------------------------------------
# Konfigurasi Keamanan TLS & Optimasi HTTP/3 di Cloudflare Edge
# -----------------------------------------------------------------------------
resource "cloudflare_zone_settings_override" "zone_settings" {
  count   = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id = var.cloudflare_zone_id

  settings {
    ssl              = "full"
    tls_1_3          = "on"
    min_tls_version  = "1.2"
    always_use_https = "on"
    http3            = "on"
    security_level   = "medium"
    browser_check    = "on"
  }
}

# -----------------------------------------------------------------------------
# Cloudflare WAF Ruleset: Mitigasi Serangan OWASP Top 10 & High Threat Score
# -----------------------------------------------------------------------------
resource "cloudflare_ruleset" "waf" {
  count       = var.enable_cloudflare && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id     = var.cloudflare_zone_id
  name        = "SIRIDA WAF Security Ruleset"
  description = "Proteksi DDoS, mitigasi bot jahat, dan inspeksi Layer 7"
  kind        = "zone"
  phase       = "http_request_firewall_custom"

  rules {
    action      = "block"
    expression  = "(cf.threat_score gt 40) or (http.request.uri.path contains \"/etc/passwd\")"
    description = "Blokir ancaman dengan skor reputasi buruk dan percobaan LFI"
    enabled     = true
  }
}
