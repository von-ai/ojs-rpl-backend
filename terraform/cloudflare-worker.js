/**
 * Cloudflare Worker Edge Proxy — SIRIDA (BRIDA Kota Makassar)
 *
 * Fungsi:
 * Menjembatani domain publik (sirida.web.id) ke backend Azure Container Apps (ACA)
 * dengan melakukan penulisan ulang Host Header (Host Header Rewrite) secara otomatis
 * pada Cloudflare Edge Network.
 *
 * Mengapa Worker ini diperlukan?
 * 1. Azure Container Apps Express Environment melarang custom domain langsung di Ingress ACA.
 * 2. Cloudflare Origin Rules (Host Header Rewrite) memerlukan lisensi Enterprise Plan.
 * 3. Cloudflare Worker sepenuhnya GRATIS (100.000 req/hari) di semua tingkatan akun Cloudflare Free.
 */

export default {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);

    // Target FQDN Azure Container Apps Ingress (dinamis per deployment)
    const acaTargetFqdn = "ca-sirida-web-poc.calmglacier-a907fb8b.indonesiacentral.azurecontainerapps.io";

    // Ubah hostname target ke ACA
    url.hostname = acaTargetFqdn;

    // Duplikasi request asli dengan URL target baru (Host Header otomatis disesuaikan oleh fetch)
    const newRequest = new Request(url.toString(), {
      method: request.method,
      headers: request.headers,
      body: request.body,
      redirect: "manual"
    });

    return fetch(newRequest);
  }
};
