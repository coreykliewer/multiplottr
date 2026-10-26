# Multiplottr baseline review

Review date: 2026-10-25. Scope: the available application snapshot, local environment, local modifications, and existing test artifacts. New feature development, including the AI bulk-location API, is paused.

This is a local baseline, not a certification of the deployed website. No production site or database was contacted for this review. No application code, activation switches, isolation controls, or services were changed. The review used static inspection and existing results; it did not rerun tests that create records or change configuration. Private configuration, credentials, imported customer records, and personal data are excluded.

## 1. Existing capabilities and verification

Status meanings:

- **PASS:** the stated workflow was exercised with its actual dependencies, within the stated scope.
- **PARTIAL:** a subset was tested, or implementation and test coverage do not establish the whole workflow.
- **UNTESTED:** no adequate execution evidence; this includes features deliberately unavailable under local isolation.
- **FAIL:** an observed unresolved failure in the stated workflow.

“Real Maps” means the browser used the actual Google Maps API. “Synthetic data” means test users/maps/locations, and does not imply a mocked Maps API. Loader contract tests do use API doubles and are identified separately. A local membership PASS does not verify WordPress membership.

| Capability | Status | Implemented behavior and evidence limits |
| --- | --- | --- |
| Homepage, About panel, local menus | PASS | Actual offline browser checks verify local dependencies, About open/close, login panel, and Legend toggle without JavaScript/CSP failures. This does not cover every legacy menu item. |
| Map rendering | PASS | Real Maps browser run rendered raster tiles. Later membership tests also used the real renderer with synthetic map data. Loader readiness alone is not counted as rendering proof. |
| Address entry and Plot | PASS | Real geocoding plotted two city addresses and verified two visible markers. Multiline address parsing exists; larger batches and mixed-result batches are not established by this small test. |
| Invalid address handling | PASS | Actual geocoder returned ZERO_RESULTS; a visible message appeared, input remained available, and existing markers were retained. Ambiguous matches, quota errors, and network interruption are untested. |
| Place Marker | PASS | Real map click, description entry, and commit produced one additional marker without a duplicate preview marker. |
| Basic marker customization | PARTIAL | Fixed-palette color, coordinates, descriptions, and group values survive local save/reopen/edit. The full palette-selection UI, marker dragging, and all popup editing/deletion paths were not independently exercised. |
| Numbered, small, custom-image markers and labels | UNTESTED | Legacy UI/rendering branches and plugins exist. Current local persistence validates a fixed color palette; it does not establish round-trip support for every legacy marker style or arbitrary image URL. |
| Rich descriptions | PARTIAL | Rich-text entry was exercised in the live placement workflow. Local persistence strips HTML to plain text; text containing delimiters, quotes, and Unicode was round-trip tested. Lossless formatted HTML, images, and links are not verified capabilities. |
| Groups | PARTIAL | Multiple group names persist through save/reload/edit. Legacy grouping/filtering code exists, but group visibility/filter controls and every grouping UI interaction lack end-to-end evidence. |
| Map Settings | PASS | Real tests apply center/zoom lock and hide the zoom control without losing markers. Membership tests verify persisted center, zoom limits, control visibility, and legend visibility. Scope is the local road-map renderer. |
| Alternate map types and advanced controls | UNTESTED | Local rendering forces road map. Satellite/Street View, overview options, external tiles, and other legacy control variants are not demonstrated. Browser geolocation is deliberately disabled. |
| New Map | PARTIAL | Local reset logic and earlier UI evidence exist. The full current authenticated save → New Map → independent draft workflow has not been separately demonstrated in the final membership results. |
| Save Map, My Maps, reopen | PASS | Actual local PHP/MariaDB and real Maps browser tests create a multi-marker map, list it, reload/sign in, and reopen it with notes, groups, and settings intact. Authentication and records are synthetic and local only. |
| Edit and resave | PASS | Real local membership tests change metadata, marker content, groups, and settings, add a marker, then reload and verify persistence. Concurrent edits and recovery from interrupted saves are untested. |
| Private-map ownership and deletion | PASS | A second synthetic user cannot read/update/delete another user's map via UI/direct requests; anonymous/forged-cookie, CSRF, Origin, and legacy-route checks reject access. Delete removes the listing and subsequent reads return not found. Scope is the new local endpoints. |
| Production WordPress accounts, entitlements, limits | UNTESTED | Original integration code exists, but local membership bypasses it. Registration, subscription tiers, quotas, password recovery, and cross-site sessions are not verified. |
| Sharing | UNTESTED | Legacy links/social/email UI exists; some handlers are incomplete or commented. Local maps are owner-only, outgoing navigation/email are blocked, and no public-sharing authorization model is established. |
| Embedding | UNTESTED | Legacy iframe generation and embed PHP entry points exist. Local routing rejects those entry points. Availability, privacy semantics, and rendering parity require a separate approved integration test. |
| Pasted address import | PARTIAL | Multiline Plot is implemented and a two-address live input succeeded. This is not a validated general file-import workflow. |
| CSV/KML/GeoJSON or other file import | UNTESTED | No complete active upload/parser workflow was established from the inspected entry points. Legacy file/data-URL paths exist but are quarantined locally. File assets or old code alone are not evidence of supported import. |
| KML export | UNTESTED | Download UI, a hidden KML field, and a PHP download endpoint exist. The endpoint sends submitted KML; that alone does not validate KML generation. Local routing blocks it, and the replacement renderer's export integration is unverified. |
| Legend | PASS | Offline toggle works; visibility persists in local membership tests. Live print evidence includes a legend. Large legends, sorting, and every legacy legend option remain untested. |
| Print Map: demonstrated desktop fixture | PASS | Headed Edge native preview was visually inspected: one landscape page contained the real map, three markers, and legend. CSS print layout also passed. The native preview DOM probe was inconclusive; this PASS rests on visual evidence, not DOM automation. No print job was sent. |
| Print: broader workflows | PARTIAL | Printing the latest edited private-map fixture, long descriptions, large marker sets, multiple pages, alternate paper sizes, and other browsers was not established. Offline printing is intentionally blocked when a map is unavailable. |
| Lines, polygons, circles, other legacy geometry | UNTESTED | Legacy classes contain geometry types. Their presence does not establish parity in the active local client renderer or persistence schema. |
| Mobile, accessibility, cross-browser support | UNTESTED | Legacy responsive assets exist; recorded desktop tests do not establish touch, keyboard/screen-reader, or mobile behavior. |
| Structured bulk-location/machine API or MCP server | UNTESTED | No completed authenticated, idempotent structured bulk API was identified. This remains absent from the verified baseline and paused; multiline Plot is a different capability. |

No unresolved FAIL was established within the narrow passing workflows. This does not convert the untested features into passes or imply the original/deployed application has the local fixes.

## 2. Evidence and its limits

Primary recorded artifacts, relative to this report:

| Evidence | What it supports |
| --- | --- |
| [maps-live-results.json](local-test/maps-live-results.json) | Six final real-Maps checks covering rendering, plotting, invalid address, placement, print CSS, and settings. Recorded JavaScript errors, CSP violations, failed requests, and unexpected destinations were empty. |
| [maps-native-print-results.json](local-test/maps-native-print-results.json) | Native print preview visual PASS, with an inconclusive DOM probe and preview automation limitations. |
| [membership-results.json](local-test/membership-results.json) | Six browser workflows with real local PHP/MariaDB and Google Maps: create, save/list, reopen, edit, ownership isolation, delete. Synthetic users/data; no recorded JavaScript errors, CSP violations, or unexpected destinations. |
| [membership-offline-results.json](local-test/membership-offline-results.json) | Six equivalent persistence/security workflows offline. These verify editor/API state, not Google rendering or geocoding. |
| [browser-results.json](local-test/browser-results.json) | Latest ten offline guest/UI/isolation checks. |
| [maps-contract-results.json](local-test/maps-contract-results.json) | Six loader/failure tests using API doubles: disabled/invalid configuration, ready, API failure, plugin failure, and authorization failure. Not real Google compatibility tests. |
| [maps-verification.json](local-test/maps-verification.json) | Recorded configuration/CSP/vendor checks, isolation results, cleanup, and summary of test modes. |
| [DIAGNOSIS.md](local-test/DIAGNOSIS.md), [MAPS-LIVE-RESULTS.md](local-test/MAPS-LIVE-RESULTS.md), [MEMBERSHIP-RESULTS.md](local-test/MEMBERSHIP-RESULTS.md) | Diagnosis, implementation history, and explanatory results. Earlier blocked findings can be superseded by later final artifacts. |

The standalone Maps/print results precede some membership changes. The later real membership run exercises the resulting renderer and persistence, but does not repeat every earlier geocoding/print action. Tests are evidence for the recorded configuration, not a fresh complete-suite run at review time. Existing test scripts are not themselves proof of execution.

The recorded real Maps run observed only expected Google Maps/font browser destinations; a deprecated `google.maps.Marker` warning remained. Google Maps' own diagnostics must be distinguished from site analytics. Native print modal monitoring was limited. There is no claim that every possible Maps request or interaction was observed.

Existing records report temporary fixtures cleaned up and local membership tables empty after verification. Cleanup was not re-executed during this read-only review.

## 3. Local changes

### Candidates for production review and porting

These are candidate fixes, not a deployment package or authorization to change production:

- Serve core dependencies locally and establish deterministic script/plugin order. Guard Maps-dependent construction so failure does not disable independent controls.
- Use one coordinated asynchronous Maps initialization path; correct stale readiness checks and conflicting initialization/renderer paths.
- Display invalid-address feedback while preserving input and existing markers; remove duplicate preview placement markers.
- Apply settings without malformed submission URLs or loss of marker state.
- Correct print sizing/camera fitting and restore the camera afterward; render legend descriptions safely as text.
- Encode marker fields to prevent delimiter/newline corruption and avoid duplicate description insertion. Evaluate migration compatibility before adopting the encoding for existing maps.
- Supply the missing `stylebutton.css` fallback. It is a replacement, not proof of the original visual design.

Main implementation areas are `local-test/app/scripts/main.js`, `local-map-engine.js`, `local-maps-loader.js`, local CSS, and the local PHP renderer snapshot. Original root files and copied local files must be compared deliberately when preparing a future change set.

### Local configuration and isolation

- Docker PHP 7.4/MariaDB 10.11 environment, internal web/database network, loopback-only gateway, and no published database port.
- Local snapshot preparation rewrites application URLs/configuration; the PHP development server/router does not reproduce all Apache behavior.
- Private Maps configuration, two explicit activation switches, fail-closed defaults, and Maps-only browser CSP destinations. Configuration is private and excluded from version control patterns; its contents were not included in this review.
- Browser Maps mode preserves blocked server internet, mail, payments, ads, analytics, production navigation, and real geolocation. No controls were loosened for this review.
- Router quarantine of noncanonical PHP routes, legacy file/debug paths, and data files. This intentionally limits legacy export/embed/share workflows.
- Locally vendored dependencies, including the pinned Apache-licensed MarkerManager 1.0.29 UMD build and its provenance. The older packed MarkerManager file is not the active dependency.

These controls are a local harness; do not copy its router restrictions or development server into production without defining production requirements.

### Synthetic membership and test infrastructure

`local-membership.php`, `local-membership.js`, router endpoints, separate local membership/map tables, synthetic account flows, and browser/contract test scripts form a local test system. It uses password hashes, server sessions, CSRF/Origin validation, prepared queries, and session-derived ownership. Stored maps use a structured payload separate from imported application records.

This implementation **does not replace production WordPress membership**. It does not establish production roles, subscription entitlements, payment reconciliation, account migration, public-map grants, or machine-client authentication. Its persistence model also deliberately narrows some legacy behavior, including HTML descriptions and marker styles.

## 4. Remaining issues

### Confirmed findings

- The original copied code contains cookie-derived identity/legacy map access patterns without the ownership guarantees of the local API. Local routes replace or quarantine those paths; this is not evidence that production was repaired or tested.
- Dependency coupling, missing local CSS, geocoding feedback, duplicate placement, settings loss, and print/legend issues were observed historically and fixed in the local snapshot. Only the scopes listed above have passing evidence.
- The local map schema and renderer do not establish full legacy feature parity. Plain-text description persistence, fixed marker colors, road-map-only rendering, and blocked legacy endpoints are concrete constraints.
- Google emits a deprecated Marker warning. Existing jQuery 1.8.3, jQuery UI 1.10.3, legacy TinyMCE, InfoBox, and MarkerWithLabel are old dependencies; loading successfully is not comprehensive compatibility verification.
- At the time of the baseline review, this workspace was not a Git repository and existing test artifacts had no commit identity. The subsequent [preservation milestone](BASELINE-PRESERVATION.md) initializes local source control and records source/evidence hashes; it does not retroactively establish which source revision each old test executed. Multiple dated/legacy variants still require release identification.

### Suspected risks and unanswered integration questions

- PHP 8 compatibility and replacement of legacy database APIs need assessment; the local PHP 7.4 runtime does not prove a runtime upgrade will work. Plan dependency updates together, including legacy jQuery event APIs and Maps plugins.
- KML generation may need reconnecting to the replacement renderer; current evidence proves only the presence of the download code. Valid export/import round trips are unverified.
- Existing saved-map formats, custom icons, formatted notes, groups, geometry, and public/embed URLs may not migrate losslessly into the local model. No customer records were inspected to settle this.
- The relationship between the deployed PHP entry points, WordPress identity, subscription limits, map ownership, and public/private visibility is not established from local tests. Review the deployed inventory only under a separately authorized task.
- Duplicate/ambiguous locations, mixed-success batches, rate limits, retries, concurrent saves, large maps, and interrupted connections lack adequate coverage. No idempotent machine API contract is claimed.
- Local CSP still accommodates legacy inline/eval behavior. Production policy, content sanitization, and third-party requirements require a separate security review; the scoped local Maps allowlist is not a complete production policy.
- Apache rewrites, HTTPS/cookie behavior, mail/payment integration, and production performance remain outside this environment's proof.

## 5. Recommended priorities before redesign or new features

1. **Freeze a reproducible baseline and identify the intended production source.** Establish source control, dependency inventory, and versioned test evidence. This prevents porting the wrong legacy variant and makes regressions attributable.
2. **Resolve production identity and authorization first.** Define WordPress membership/entitlements and private/public/embed permissions, then review legacy entry points against that model. Users must not lose access to their maps or expose private data during an upgrade.
3. **Agree on the map-data and feature-parity contract.** Decide which icons, descriptions, groups, geometry, settings, links, and import/export formats must survive. Design migration and round-trip checks before replacing persistence or UI so saved work remains usable.
4. **Plan a supported runtime/dependency path and consolidate initialization.** Assess PHP/database APIs, jQuery/TinyMCE, Maps Marker replacement, and plugins together, porting reviewed local fixes in small validated changes. This reduces breakage and makes later visual work predictable.
5. **Close the acceptance-test gaps, then approve the upgrade scope.** Prioritize save/reopen through real membership, sharing/embed privacy, KML/import round trips, advanced markers/groups, mixed geocoding failures, mobile/accessibility, and realistic printing. Use those results to choose what to retain, repair, or retire before authorizing redesign, bulk API, or MCP work.

The next milestone is a baseline/scope review with the owner. No new feature implementation, external service activation, or deployment is authorized by this report.
