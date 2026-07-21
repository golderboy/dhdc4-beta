const { chromium } = require("playwright");

function readArg(name, fallback) {
  const prefix = `--${name}=`;
  const argument = process.argv.find((value) => value.startsWith(prefix));
  return argument ? argument.slice(prefix.length) : fallback;
}

const baseUrl = new URL(readArg("base", "https://localhost:18443"));

const auditHtml = `<!doctype html>
<html><head><meta charset="utf-8"><title>Map runtime audit</title>
<link rel="stylesheet" href="/lib/map/vendor/bootstrap-3.4.1/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="/lib/map/vendor/mapbox-3.1.1/mapbox.css">
<link rel="stylesheet" href="/lib/map/vendor/leaflet-draw-0.4.10/dist/leaflet.draw.css">
<link rel="stylesheet" href="/lib/map/vendor/leaflet-markercluster-1.0.0/dist/MarkerCluster.css">
<link rel="stylesheet" href="/lib/map/vendor/leaflet-markercluster-1.0.0/dist/MarkerCluster.Default.css">
<link rel="stylesheet" href="/lib/map/vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.mapbox.min.css">
<link rel="stylesheet" href="/lib/map/vendor/font-awesome-4.7.0/css/font-awesome.min.css">
</head><body><div id="map" style="height:320px"></div><pre id="result">running</pre>
<script src="/lib/map/vendor/jquery-3.7.1/jquery.min.js"></script>
<script src="/lib/map/vendor/bootstrap-3.4.1/js/transition.js"></script>
<script src="/lib/map/vendor/bootstrap-3.4.1/js/modal.js"></script>
<script src="/lib/map/vendor/mapbox-3.1.1/mapbox.js"></script>
<script src="/lib/map/vendor/leaflet-draw-0.4.10/dist/leaflet.draw.js"></script>
<script src="/lib/map/vendor/leaflet-markercluster-1.0.0/dist/leaflet.markercluster.js"></script>
<script src="/lib/map/vendor/leaflet-locatecontrol-0.43.0/dist/L.Control.Locate.min.js"></script>
<script src="/lib/map/vendor/leaflet-hash-0.2.1/leaflet-hash.js"></script>
<script src="/lib/map/vendor/turf-compat-7.3.5/turf-compat.min.js"></script>
<script>
try {
  if (typeof window.jQuery !== "function" || typeof window.jQuery.fn.modal !== "function") throw new Error("jQuery/Bootstrap unavailable");
  if (!window.L || !L.mapbox || !L.Control.Draw || !L.MarkerClusterGroup || !L.control.locate || !L.Hash) throw new Error("Leaflet runtime incomplete");
  if (!window.turf || typeof turf.within !== "function" || typeof turf.circle !== "function" || typeof turf.area !== "function") throw new Error("Turf API unavailable");
  var map = L.map("map", {maxZoom:20}).setView([13,100], 7);
  map.addLayer(new L.MarkerClusterGroup());
  map.addControl(new L.Control.Draw({draw:false}));
  map.addControl(L.control.locate());
  new L.Hash(map);
  var points = turf.featureCollection([turf.point([100,13])]);
  var polygon = turf.polygon([[[99,12],[101,12],[101,14],[99,14],[99,12]]]);
  if (turf.within(points, turf.featureCollection([polygon])).features.length !== 1) throw new Error("turf.within returned the wrong result");
  if (turf.circle([100,13], 1, 100, "kilometers", {}).geometry.type !== "Polygon") throw new Error("turf.circle failed");
  if (!(turf.area(polygon) > 0)) throw new Error("turf.area failed");
  document.body.dataset.audit = "pass";
  document.getElementById("result").textContent = "MAP_RUNTIME_AUDIT_PASS";
} catch (error) {
  document.body.dataset.audit = "fail";
  document.getElementById("result").textContent = "MAP_RUNTIME_AUDIT_FAIL: " + error.message;
  throw error;
}
</script></body></html>`;

(async () => {
  const browser = await chromium.launch({ headless: true });
  try {
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();
    const pageErrors = [];
    const failedRequests = [];
    const externalRequests = [];

    page.on("pageerror", (error) => pageErrors.push(error.stack || error.message));
    page.on("requestfailed", (request) => {
      failedRequests.push(`${request.url()} ${request.failure()?.errorText || "failed"}`);
    });
    page.on("request", (request) => {
      const requestUrl = new URL(request.url());
      if (requestUrl.hostname !== baseUrl.hostname) externalRequests.push(request.url());
    });

    const bootstrapResponse = await page.goto(new URL("/robots.txt", baseUrl).toString(), { waitUntil: "networkidle" });
    if (!bootstrapResponse || bootstrapResponse.status() !== 200) {
      throw new Error(`Unable to establish the localhost test origin: HTTP ${bootstrapResponse?.status() || 0}`);
    }

    await page.setContent(auditHtml, { waitUntil: "networkidle" });
    const audit = await page.locator("body").getAttribute("data-audit");
    const result = await page.locator("#result").textContent();

    if (audit !== "pass" || pageErrors.length || failedRequests.length || externalRequests.length) {
      throw new Error([
        result,
        ...pageErrors,
        ...failedRequests,
        ...externalRequests.map((url) => `Unexpected external request: ${url}`),
      ].join("\n"));
    }

    console.log(result);
    console.log("Map runtime loaded entirely from localhost with no failed or external requests.");
  } finally {
    await browser.close();
  }
})().catch((error) => {
  console.error(error.stack || error.message);
  process.exit(1);
});
