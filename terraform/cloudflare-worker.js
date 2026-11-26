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

    const isDynamicPortal = /^\/(sysadmin|journalmanager|eic|section-editor|reviewer|copyeditor|layouteditor|proofreader|author|dashboard|auth|login|register|password|email)(\/|$)/.test(url.pathname);

    // Duplikasi request asli dengan URL target baru (Host Header otomatis disesuaikan oleh fetch)
    const newRequest = new Request(url.toString(), {
      method: request.method,
      headers: request.headers,
      body: request.body,
      redirect: "manual"
    });

    const response = await fetch(newRequest, isDynamicPortal ? { cf: { cacheTtl: 0, cacheEverything: false } } : undefined);
    if (!isDynamicPortal) {
      return response;
    }

    const responseHeaders = new Headers(response.headers);
    responseHeaders.set("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0, private");
    responseHeaders.set("Pragma", "no-cache");
    responseHeaders.set("Expires", "0");
    responseHeaders.set("CDN-Cache-Control", "no-store");

    return new Response(response.body, {
      status: response.status,
      statusText: response.statusText,
      headers: responseHeaders
    });
  }
};
