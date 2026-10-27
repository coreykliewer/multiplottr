# Phase 0 / Phase 1 local results

Verified 2026-10-26 on development branch `mapping-consistency-phase1`. Try the workspace at **http://localhost:8080/mapping/** using the [steps in README.md](README.md).

## Acceptance results

| Workflow | Result | Evidence and scope |
| --- | --- | --- |
| Phase 0 compatibility corpus | PASS | 34 legacy marker-token cases retain original text and either adapt supported fields or become explicitly read-only. Four structured/legacy document fixtures complete ten JSON clone cycles each; client/PHP capability results agree for those fixtures. This is synthetic compatibility evidence, not customer migration validation. |
| Real map rendering | PASS | Headless Edge loaded actual Google raster tiles and real marker/geometry objects under the existing restricted development-key configuration. |
| Create and Plot | PASS — synthetic geocoding | Browser UI plots two successful synthetic results with intentional duplicate coordinates and one ZERO_RESULTS failure. Names/original inputs and distinct IDs are retained. This run uses injected Geocoder callbacks on the real map; it does not certify new live geocoding requests. |
| Customize, popup editing and groups | PASS | Browser uses formatted notes, marker name/number/label/custom local icon, group creation/assignment/hide, and authored camera/control settings. Hidden-group state and actual marker visibility match after reopen. |
| Save → My Maps → reopen → edit → resave | PASS | UI opens the saved title from My Maps, reloads, verifies complete document equality and stable rendered icon/label/position/visibility/settings properties, edits again and saves. Google runtime internals are not treated as serialized map data. |
| Repeated cycles and duplicates | PASS | Repeated server saves preserve content and stable IDs. Identical coordinates remain separate locations. Fixture runs preserve rich descriptions, unknown extension metadata, appearance, group data, three geometry types and settings. |
| New Map identity | PASS | New Map starts a new draft identity; saving it leaves the earlier stored map unchanged. |
| Failed/stale saves and retries | PASS | Synthetic 503 failure and actual stale-revision conflict retain the unsaved browser document and dirty state. Repeating a create request returns the same map. No silent last-writer overwrite. |
| Unsupported original content | PASS | Synthetic remote media/icons and unknown geometry survive exact storage/load. UI clearly marks read-only; direct overwrite returns 422. Dropping protected unknown metadata on supported data also returns 422. |
| Unsupported newly entered media | PASS | Raw video content is retained in the draft, Save/group/marker editing become disabled, and a preserved-document download is available. No external media is fetched. |
| Authorization and validation | PASS | Anonymous requests rejected; another synthetic user receives 404 for read/update/delete. Invalid coordinates, bad CSRF/Origin and stale delete are rejected. Authorized revision-checked delete removes the document. |
| Placement and keyboard/mobile checks | PASS, bounded | Real map click creates a marker. Keyboard location selection opens editing. Small-screen map is visible. These are smoke tests, not comprehensive accessibility/mobile certification. |
| Print | PARTIAL | Browser print CSS hides editing controls and retains the map. No new native print-preview/printer acceptance was performed for this workspace. |
| Offline workspace | PASS | With a browser-only disabled configuration double, full document save/reopen works; unsupported controls are disabled; zero external requests and no Google runtime. This does not simulate map rendering. |
| JS/CSP/external monitoring | PASS | Final workspace browser run recorded no page JavaScript errors, CSP violations or unexpected destinations. Only loopback and the existing narrowly allowed Google Maps/font hosts were permitted. The deprecated Marker backend remains a known compatibility warning/debt. |

The final real-browser suite has **14 passing groups**. Initial runs exposed test timing issues (tile/list/popup readiness and comparison of provider object internals); checks now wait for the relevant UI and compare only stable appearance properties. The final results supersede those initial runs. No passing result is inferred solely from loader readiness.

## Existing regression suites

- Existing offline suite: **15 configuration/CSP checks, pinned vendor verification, six synthetic loader scenarios, ten guest browser checks and six offline membership workflows passed**.
- Existing real-Maps membership suite: **six browser lifecycle/authorization/delete workflows passed** with synthetic accounts.
- PHP syntax checks passed for the document service/router. The document contract suite exercises actual isolated PHP without invoking database constructors for validation-only cases.
- Private Maps switches were temporarily tightened to off only for the offline suite, then the private file was restored byte-for-byte. The key was not printed; CSP/network rules were not broadened.

Generated evidence remains ignored in `local-test/`: `document-contract-results.json`, `document-browser-results.json`, `document-offline-results.json`, and `document-workspace.png`, plus the existing regression artifact names. Browser suites remove only their own synthetic accounts; foreign-key cascades remove their documents. Final cleanup checks found zero synthetic map rows in both local map tables. User-created synthetic accounts/maps are not globally purged by the tests.

## Preservation and isolation

- All **1,103 preserved source-copy hashes** still match the baseline manifest. All original-root source hashes remain unchanged. Only `local-test/router.php` and `local-test/local-membership.php` differ among the previously inventoried working sources, for the documented local integration hooks.
- All seven previously inventoried final evidence artifacts still match their baseline hashes after regression reruns. The preserved snapshots, fixture sources and prior reports remain recoverable.
- New runtime assets live only under `local-test/app/mapping-dev/`; canonical source, fixtures and tests are in `mapping-dev/`. Imported map tables/customer records are not queried or migrated by the new document service.
- Docker web/database stay on the internal network; web remains attached only to that isolated network. The existing loopback gateway, private key setup, blocked mail, production connections, real payments, ads/analytics and geolocation controls remain in place. Isolation verification used metadata/function availability, not outbound probes.

## Files and boundaries

| Area | Files |
| --- | --- |
| Document/fixtures | `mapping-dev/document.js`, `fixtures.js` |
| Renderer/editor/loader | `mapping-dev/workspace.js`, `workspace.css`, `index.html`, `assets/star.svg` |
| Separate synthetic persistence | `mapping-dev/document-service.php` |
| Installation/reversal | `mapping-dev/install.ps1`, `integration.patch`; two existing local hook files noted above |
| Tests | `mapping-dev/tests.cjs`, `browser-tests.cjs`, `offline-browser.cjs`, `regressions.cjs` |
| Review/source control | `mapping-dev/README.md`, this report, root `.gitignore` allowlist and the existing mapping plan |

Still read-only/deferred: remote media and arbitrary icon assets, video/unsafe HTML, unknown legacy geometry/types, non-roadmap settings, alternate document versions, drawing geometry, customer-format migration, legacy URL/embed parity, production WordPress/sharing integration, native print and general export/import. Preserved JSON download is a recovery mechanism, not a new legacy export format or general importer.

The implementation is a local vertical slice and compatibility foundation, not a production-ready replacement. Stop here before production integration, customer migration, deployment, visual redesign expansion, or AI/MCP work.

Follow-up verification: the current browser suite has 16 passing groups, including synchronized popup/editor closing and new-marker defaults. Style/color/size/group defaults are stored per map, apply to new plotted and placed markers only, and survive saves/reopening. A separate synthetic visibility regression verifies that an unconfigured new plot prefers a visible group while preserving hidden existing groups. Real Google rendering was used; geocoding callbacks remained synthetic. No existing user maps were inspected or altered during these checks.
