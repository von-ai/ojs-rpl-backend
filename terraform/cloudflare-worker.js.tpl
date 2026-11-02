addEventListener("fetch", event => {
  event.respondWith(handleRequest(event.request));
});

async function handleRequest(request) {
  const url = new URL(request.url);
  const originalHost = url.hostname;
  const targetHost = "${origin_fqdn}";

  url.hostname = targetHost;

  const headers = new Headers(request.headers);
  headers.set("X-Forwarded-Host", originalHost);
  headers.set("X-Forwarded-Proto", "https");

  const newRequest = new Request(url.toString(), {
    method: request.method,
    headers: headers,
    body: request.body,
    redirect: "manual"
  });

  const response = await fetch(newRequest);

  // Sesuaikan header Location pada response redirect (301, 302, 307, 308)
  const responseHeaders = new Headers(response.headers);
  const location = responseHeaders.get("Location");
  if (location && location.includes(targetHost)) {
    responseHeaders.set("Location", location.replace(targetHost, originalHost));
  }

  // Jika response berformat text/html, tulis ulang seluruh kemunculan FQDN ACA menjadi domain kustom
  const contentType = responseHeaders.get("content-type") || "";
  if (contentType.includes("text/html")) {
    let bodyText = await response.text();
    bodyText = bodyText.replaceAll(targetHost, originalHost);
    return new Response(bodyText, {
      status: response.status,
      statusText: response.statusText,
      headers: responseHeaders
    });
  }

  return new Response(response.body, {
    status: response.status,
    statusText: response.statusText,
    headers: responseHeaders
  });
}
