# Optional Google Maps development mode

Prepared October 25, 2026. Current status: ENABLED locally at the user's request with their development key. The scoped real tests are documented in [MAPS-LIVE-RESULTS.md](MAPS-LIVE-RESULTS.md). The code default remains offline when the private file is absent or either switch is false.

## Configuration and activation gates

The PHP reader is maps-config.php, mounted outside /app at /config/maps-config.php.
It reads config/maps.local.json from a read-only directory mount. Only the example
config/maps.local.example.json exists initially. The real file is ignored by Git.

```json
{
  "enabled": false,
  "allowBrowserNetwork": false,
  "apiKey": "[REDACTED_CONFIG_VALUE]"
}
```

All of these conditions are required to activate mode: both switches must be literal
JSON true; the key must match a Google browser-key format; it must not match the
fingerprints of known keys embedded in the original source; and the request host
must be localhost:8080 or 127.0.0.1:8080. Missing/invalid configuration or a missing
fingerprint list fails closed. The fingerprint check cannot establish project
ownership: you must create the separate development key described below.

The private key file now exists and both switches are enabled, as explicitly authorized. Restoring either switch to false and closing/reloading existing Maps tabs returns to offline mode; it cannot unload code in an old tab. Keep the key private and never commit it.

The localhost-only /__local_maps_config endpoint exposes a key only in active mode.
Responses use Cache-Control: no-store. Browser API keys are necessarily visible to
the browser; referrer and API restrictions are still essential. Never paste a key
into chat or a command that prints it.

## What to configure in Google Cloud

1. Create a separate project, for example Multiplottr Development. Do not modify the
   production project/key. Link a billing account to the development project.
2. Enable Maps JavaScript API and Geocoding API for this project. No Places,
   Street View, payment, or server-side API access is needed for the prepared mode.
3. Create a new API key. Set Application restrictions to Websites (HTTP referrers):
   - http://localhost:8080/*
   - http://127.0.0.1:8080/*
4. Set API restrictions to Restrict key and select only Maps JavaScript API and
   Geocoding API. Set appropriate low development quotas and budget alerts; alerts
   are notifications, not guaranteed spending caps.
5. If config/maps.local.json does not exist, copy config/maps.local.example.json
   to that name. Enter the NEW key in apiKey using your editor. Leave enabled and
   allowBrowserNetwork false. Do not use the keys in the original source.
6. Once ready for separately authorized live testing, enable the two switches and
   hard-refresh the local page. There is no need to open server internet access,
   change the Docker network, alter production DNS, or expose a database port.

Google's [key restriction guidance](https://developers.google.com/maps/api-security-best-practices)
explains website/API restrictions. [Maps billing requirements](https://developers.google.com/maps/documentation/javascript/usage-and-billing)
require billing and a key; the [JavaScript geocoder guide](https://developers.google.com/maps/documentation/javascript/geocoding)
covers the browser service. These setup steps have not been performed by this task.

## Loader and map ownership

1. Local jQuery 1.8.3, UI, editor, and main.js initialize the ordinary menus first.
2. local-maps-loader.js waits for DOM readiness and retrieves the same-origin
   configuration. Disabled mode returns without adding any external script.
3. Active mode creates one asynchronous script for
   https://maps.googleapis.com/maps/api/js, using quarterly, loading=async,
   libraries=marker,geocoding, and callback=__multiplottrGoogleReady. The script URL
   cannot be supplied by the configuration.
4. After the API callback and required core-class checks, local scripts load
   sequentially: InfoBox -> MarkerWithLabel -> geolocation helper -> pinned
   MarkerManager -> jQuery UI Map -> local-map-engine.js. Each export is checked.
5. The local engine invokes the existing clientMapLoad renderer once, after its
   dependencies are ready. It uses a 2D raster road map with satellite selection,
   Street View, and clickable POIs disabled. MarkerManager is constructed for the
   client markers; the local legend is refreshed after marker rendering.
6. The competing PHP-generated JavaScript engine is not emitted in either mode.
   PHP server geocoding/URL fetching is disabled in this local class. Production
   plot telemetry is omitted from main.js. Real geolocation remains blocked.

Duplicate boot calls reuse the same promise. API/plugin timeouts, resource failures,
missing exports, and gm_authFailure put Maps into a failed state, clean up local
map listeners/markers, and show a generic status without logging key URLs. Ordinary
menus remain available. There is no fake Google API in the application; API doubles
exist only in automated test response interception.

The first idle event is used as a runtime startup signal. It does NOT establish
successful authentication, billing, tile delivery, geocoding, or plugin compatibility.
Those require the real-key browser tests below.

## MarkerManager resolution

The initial report called MarkerManager absent. Inspection found an old packed
scripts/markermanager.js that was never loaded. It is not selected by the new loader.

A pinned @googlemaps/markermanager 1.0.29 UMD bundle is now served locally from
scripts/vendor/markermanager-1.0.29/markermanager.umd.js, with its Apache-2.0 license,
source metadata, npm integrity, and SHA-256 in that directory's README.md. The bundle
exports google.maps.plugins.markermanager.MarkerManager; the loader aliases it to
window.MarkerManager after it loads. No CDN is used at runtime.

vendor-tests.cjs verifies the real bundle checksum, export, required methods, and
license. The selected real-browser tests now exercise this bundle; see MAPS-LIVE-RESULTS.md for verified scopes. Large-marker performance remains untested. See the [upstream project](https://googlemaps.github.io/js-markermanager/).
InfoBox's default close icon is local; MarkerWithLabel's optional static Google
assets use HTTPS. The legacy plugin versions were exercised in the selected live tests; other behaviors remain unverified.

## Prepared Maps-only CSP

The offline CSP has no external source and remains unchanged. local_maps_csp(true)
adds only the sources below, and only after all activation gates pass. Existing
same-origin form/frame/base restrictions remain in force. There is no wildcard
host, general https: source, external iframe permission, or CDN allowance.

| Directive | Exact HTTPS hosts added in Maps mode |
| --- | --- |
| script-src | maps.googleapis.com; maps.gstatic.com |
| connect-src | maps.googleapis.com; mapsresources-pa.googleapis.com; maps.gstatic.com |
| img-src | maps.googleapis.com; mapsresources-pa.googleapis.com; maps.gstatic.com |
| style-src | fonts.googleapis.com |
| font-src | fonts.gstatic.com |

These are prepared for 2D road maps and geocoding, not a claim of live-tested
completeness. New Maps releases or unprepared modes may require different assets;
CSP will block them. Diagnose exact destinations before adding any rule.
The scoped list follows Google's [service domain list](https://developers.google.com/maps/domains)
and [CSP guidance](https://developers.google.com/maps/documentation/javascript/content-security-policy).
The listed scoped CSP exceptions are now active locally at the user's request. The existing legacy inline/eval script
permissions have not been expanded; nonce hardening is a separate page-wide change.

PHP and the database stay on Docker's internal network. Only the nginx gateway
publishes 127.0.0.1:8080. Email, payments, ads, analytics, external navigation, and
production connections remain blocked. Permissions-Policy also disables real
geolocation and browser payment APIs.

## Automated checks and limits

Run the integrated offline suite with Docker Desktop and the containers running:

```powershell
Set-Location 'E:\Cloud\Google\Websites\Multiplottr'
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\local-test\Test-Local.ps1
```

The suite refuses to run against an active Maps configuration. It uses only newly
created local probe accounts, synthetic coordinates/addresses, and local Google API
response doubles. Every non-local real browser request is rejected. No real key,
customer account, or live Google response is used. Synthetic database records are
removed in finally, and fixture metadata is ignored by Git.

- 15 configuration/CSP checks: missing/invalid files, strict Boolean gates, key
  redaction, local hosts, embedded-key exclusion, missing fingerprint list, and
  exact-host policy boundaries.
- MarkerManager bundle checksum/export/method/license checks.
- Six synthetic loader scenarios: disabled, invalid key, success with doubles,
  API failure, plugin failure, and authentication failure. These test ordering,
  idempotence, single map construction, synthetic marker/geocoder plumbing, cleanup,
  and recovery of the ordinary menus. They do NOT test Maps rendering.
- 14 offline browser/HTTP checks, including all previously tested controls and the
  local synthetic database create/read/list/update/delete flow.

Results: maps-contract-results.json (realMapsTested=false) and browser-results.json.
The synthetic contract suite changes CSP only in intercepted browser test responses;
the actual localhost server remains in offline mode throughout.

## Real-key testing status

The requested small real-browser test has now been performed. See
[MAPS-LIVE-RESULTS.md](MAPS-LIVE-RESULTS.md) for precise passing scopes, fixes,
remaining limitations, native preview evidence, and temporary-record cleanup.
No customer map IDs or production accounts were used. Membership remains unconfigured.
The prior synthetic test results are not evidence of real Maps compatibility beyond
what was explicitly checked in that separate real test.

## Implementation files in this change

- maps-config.php and router.php: private configuration validation, key exclusion,
  localhost config endpoint, and conditional CSP. compose.yaml mounts the reader
  and config directory read-only; Docker networks and published ports are unchanged.
- config/maps.local.example.json and blocked-key-fingerprints.json: disabled example
  and SHA-256 exclusions for known embedded source keys. .gitignore excludes the
  actual config/maps.local.json key file.
- app/scripts/local-maps-loader.js and local-map-engine.js: asynchronous lifecycle,
  dependency order, one client map initializer, failure recovery, and local legend.
- app/index.php, scripts/local-runtime.js, scripts/main.js, mpcMain.v7.4.php: loader
  wiring, preserved offline controls, guarded resources, local-only server behavior,
  removal of the competing generated engine and plot telemetry, and listener cleanup.
- app/scripts/vendor/markermanager-1.0.29/: pinned UMD build, license and provenance.
  Existing infobox.js and markerwithlabel.js have local/HTTPS asset defaults.
- maps-config-tests.php, vendor-tests.cjs, maps-contract-tests.cjs and Test-Local.ps1:
  integrated offline-only checks. Results and test artifacts contain no real keys.
- DIAGNOSIS.md, README.md and this guide: configuration, limits and next steps.

## Membership update

Local-only synthetic membership is now enabled, independent of WordPress/email/payments. Current private Save/My Maps results are in MEMBERSHIP-RESULTS.md; earlier membership-blocked notes in this setup reference are historical. New regression commands use server-side local sessions and separate tables. No imported users/maps are used for this membership.
