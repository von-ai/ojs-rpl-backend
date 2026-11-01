# -----------------------------------------------------------------------------
# Cloudflare Edge: DNS CNAME Mapping
# Mengarahkan domain publik ke Azure Container Apps Ingress FQDN
# (Hanya aktif jika Worker dinonaktifkan, karena Worker Custom Domain mengelola
# DNS routing secara otomatis di edge Cloudflare dan melarang record CNAME ganda)
# -----------------------------------------------------------------------------

# DNS CNAME Record: Mengarahkan domain/subdomain ke ACA Ingress FQDN
resource "cloudflare_record" "cname_web" {
  count   = var.enable_cloudflare && !var.enable_cloudflare_worker && var.cloudflare_zone_id != "" ? 1 : 0
  zone_id = var.cloudflare_zone_id
  name    = var.subdomain == "@" ? var.domain_name : var.subdomain
  content = azurerm_container_app.web.ingress[0].fqdn
  type    = "CNAME"
  proxied = true # Mengaktifkan Cloudflare CDN, TLS, & Proteksi DDoS/WAF
  ttl     = 1    # Automatic jika proxied
  comment = "SIRIDA Web Ingress ACA diarahkan ke Cloudflare Edge"
}

# -----------------------------------------------------------------------------
# Cloudflare Worker Edge Proxy
# Solusi Otomatis Gratis untuk Dynamic Host Header Rewrite ke ACA Ingress FQDN
# Menghindari batasan Enterprise Plan pada Cloudflare Origin Rules
# -----------------------------------------------------------------------------
resource "cloudflare_workers_script" "proxy" {
  count      = var.enable_cloudflare && var.enable_cloudflare_worker && var.cloudflare_account_id != "" ? 1 : 0
  account_id = var.cloudflare_account_id
  name       = "sirida-proxy"
  content    = templatefile("${path.module}/cloudflare-worker.js.tpl", {
    origin_fqdn = azurerm_container_app.web.ingress[0].fqdn
  })
}

resource "cloudflare_workers_domain" "custom_domain" {
  count      = var.enable_cloudflare && var.enable_cloudflare_worker && var.cloudflare_account_id != "" && var.cloudflare_zone_id != "" ? 1 : 0
  account_id = var.cloudflare_account_id
  zone_id    = var.cloudflare_zone_id
  hostname   = var.subdomain == "@" ? var.domain_name : "${var.subdomain}.${var.domain_name}"
  service    = cloudflare_workers_script.proxy[0].name
}

# -----------------------------------------------------------------------------
# Cloudflare Origin Rule: Otomasi Host Header Rewrite ke ACA FQDN Dinamis
# (Hanya untuk paket Cloudflare Enterprise)
# -----------------------------------------------------------------------------
resource "cloudflare_ruleset" "origin_rules" {
  count       = var.enable_cloudflare && var.enable_cloudflare_origin_rule && var.cloudflare_zone_id != "" ? 1 : 0
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
