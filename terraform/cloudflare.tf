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
