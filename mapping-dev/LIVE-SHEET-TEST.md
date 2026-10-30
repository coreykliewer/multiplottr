# Live published Google Sheets CSV integration — completed

Completed locally on 2026-10-29 (America/New_York), using the existing Docker application, approved loopback CSV-fetch helper and automated Edge/Chromium browser infrastructure. CSV and Google Maps responses were live. No response body was mocked, fulfilled, or substituted. Only synthetic locations, synthetic local accounts and synthetic local maps were used. No production changes or deployment occurred.

## Publication and actual HTTP results

The supplied publication is:

https://docs.google.com/spreadsheets/d/e/PUBLISHED_SYNTHETIC_SHEET_ID/pub?output=csv

The public tab metadata identifies **Synthetic Locations**, tab ID **1591404261**. The successful connection used the existing Tab ID control and existing URL normalizer:

https://docs.google.com/spreadsheets/d/e/PUBLISHED_SYNTHETIC_SHEET_ID/pub?gid=1591404261&single=true&output=csv

| Observation | Actual result |
| --- | --- |
| Approved local helper health | HTTP 200, expected helper service identity |
| Google request for the correct published tab | HTTP 307 from docs.google.com to the permitted doc-10-7g-sheets.googleusercontent.com/pub/ host |
| Final Google CSV response | HTTP 200, text/csv; charset=utf-8 |
| Browser requests through the approved /source helper during the completed workflow | 12 recorded HTTP 200 responses, with response bytes and SHA-256 hashes in the evidence |
| Earlier/default gid=0 requests | Google HTTP 400; helper HTTP 502 with a useful error |
| Initial exact-URL diagnostic before retry | Google HTTP 400; the exact URL later became accessible on retry |

The working CSV was 1,088 bytes at the first correct-tab fetch and 1,038 bytes after the requested source edits. The final valid CSV SHA-256 was `5e1293196b00df45948e972fe9eab34c0fd0b7d91f00236051aae72512d1db2a`. The temporarily invalid duplicate-ID CSV had a different observed hash. These are actual downloaded versions, not fixture responses.

No published-URL policy, security setting, CSP, router, fetch implementation or private Maps configuration was changed. Hash comparisons across the workflow confirm this. The only additions are test harnesses, documentation and ignored local evidence.

## Parsing, plotting and clustering

All 12 headers and six rows were parsed. Existing mappings correctly recognized:

| Published header | Engine field |
| --- | --- |
| location_id | Stable source_id |
| location_name | Name |
| latitude / longitude | Coordinates |
| notes | Description |

The remaining address fragments, city/state/postal/country and is_synthetic columns were retained in the connection headers but left unmapped by the existing import UI. The test independently checked that each source row had is_synthetic TRUE. Coordinates were used for plotting; invented address fragments were not concatenated or geocoded. No geocoder doubles were installed.

The six linked locations were rendered through the existing shared marker system. A separate customized manual image marker served as a preservation control. The nearby source locations formed a blue/cyan numbered cluster of six; clicking the actual rendered cluster zoomed the map and did not alter the location data. Marker map size, cluster membership totals and stable unique marker IDs were checked.

Saving and reopening retained the connection identity, URL, headers, mapping, connection defaults and existing source-to-marker IDs. Reopening made a real on-open CSV request. Repeated refresh/save/reopen operations did not create duplicates.

## Live reconciliation after user-coordinated sheet edits

| Source change | Verified behavior |
| --- | --- |
| SYN-001 renamed to Synthetic Test Alpha Live Updated and moved to 35.1244, -100.5688 | Existing marker updated; internal marker ID retained |
| SYN-007 added as Synthetic Test Golf at 35.1834, -100.6278 | Exactly one new linked marker added |
| SYN-002 removed from the sheet | Existing linked marker marked missing and retained for review |
| Remove missing rows confirmed through the normal UI | Only the missing linked marker was deleted; unrelated manual marker was untouched |

Repeated real refreshes and another save/reopen preserved the updated data and connection. The final six source identities were SYN-001, SYN-003, SYN-004, SYN-005, SYN-006 and SYN-007. Every final mapped name, coordinate and description was compared with the actual final live CSV. The final local map contained six linked markers plus the one manual control marker before cleanup.

## Customization and ownership rules

The manual control marker retained its custom icon, label, size, color, description, group and metadata through all refreshes, source updates, reviewed deletion and reopening. Its label was deliberately edited before the source-change phase, and that edit was preserved.

Because this published CSV has no appearance columns, its linked markers inherited the connection's captured defaults: small numbered markers with custom hex color #2E81B7 and label Live CSV. Changing the map's later new-marker color to GREEN did not overwrite that captured connection default or recolor the linked markers. The UI correctly refused manual editing of source-managed fields on a linked marker.

This verifies the existing rules: unrelated manual markers are untouched; linked markers' known source-managed fields follow source rows and the connection's defaults. It does not promise manual overrides of source-owned fields, which the existing UI intentionally blocks.

## Failure handling and recovery

| Check | Outcome |
| --- | --- |
| Actual unavailable/default-tab Google response | Helper HTTP 502 reported underlying Google HTTP 400; existing saved synthetic document, marker customization, identities, instances and revision preserved |
| Invalid URL outside the published-sheet policy | Rejected before fetching with a useful UI error; existing data preserved |
| User temporarily changed SYN-007's source ID to SYN-001 | Actual HTTP 200 CSV contained duplicate source IDs; refresh showed Duplicate source_id: SYN-001 and rejected the dataset atomically |
| Duplicate-ID rejection | Immediate valid map snapshot, marker instances and stored document/revision unchanged |
| User restored SYN-007 | Normal live refresh recovered; no duplication or customization loss |
| Browser offline failure probe | Genuine failed request, useful error and unchanged synthetic map; no substituted response |
| Targeted failed helper request on the connected map | Browser aborted the request with connectionfailed; no HTTP response body was supplied; map remained unchanged |
| Next unmodified real helper request | HTTP 200; error cleared, live refresh and save succeeded |

Google briefly served the previous valid CSV before exposing the duplicate-ID version. The evidence records both real responses. The rejection check compares against the immediate pre-refresh snapshot, allowing an earlier successful on-open refresh to advance lastSuccess without mistaking that legitimate metadata change for corruption.

## Outcomes and limitations

**12/12 completed live workflow checks passed:** six baseline checks, three reconciliation checks, one live invalid-data rejection check and two recovery checks. Three additional initial failure-path checks passed. Final field-by-field comparison with the real CSV also passed. The completed workflow recorded no browser exceptions, CSP violations or unexpected network destinations.

Important scope limits:

- The correct tab ID is required. This URL omits gid, while the existing connection defaults to gid=0; select 1591404261 in the Tab ID control. No auto-discovery behavior was added to the application.
- The live invalid-data case covered duplicate identities in valid CSV; malformed syntax remains a separate controlled-test topic.
- Network failures were explicitly injected using browser offline mode or request abort, rather than a naturally observed Google network outage. Successful import and reconciliation responses were never replaced.
- The live source has no appearance columns; explicit appearance changes remain a separate controlled-test topic.
- Source edits were performed by the user. Published changes can be delayed, as observed. Testing used desktop Chromium; the earlier clustering suite's mobile coverage is separate.

## Cleanup and reproduction

The synthetic local account and map were removed using the existing guarded fixture cleanup. Anonymous access to the removed test map returned HTTP 404. Retained synthetic browser-session and state files were deleted. The published sheet was restored to valid unique IDs; the user's intentional add/update/remove changes remain in the synthetic source.

`mapping-dev/live-sheet-integration.cjs` records the staged live workflow. It has baseline, refresh, invalid, restore and cleanup modes. The original run used the correct-tab URL stored in the ignored live-sheet-working-http.json evidence file; the update phases require deliberate source edits and the retained synthetic baseline. `live-sheet-unavailable-tests.cjs` records only the unavailable-publication/failure paths and is not a substitute for a successful CSV test.

Primary ignored evidence under local-test/:

- live-sheet-summary.json — final aggregate outcomes and cleanup status.
- live-sheet-working-http.json and live-sheet-working-upstream.json — real successful helper response and Google redirect chain.
- live-sheet-baseline-results.json, live-sheet-refresh-results.json, live-sheet-invalid-results.json, live-sheet-restore-results.json — actual HTTP bodies, status, byte counts, hashes and phase assertions.
- live-sheet-reconciliation.json, live-sheet-invalid-data.json, live-sheet-final-state.json — exact reconciliation, rejection and final valid state.
- live-sheet-unavailable-results.json and earlier HTTP diagnostics — real failure-path evidence.
- live-sheet-baseline.png, live-sheet-refresh.png, live-sheet-invalid.png and live-sheet-restore.png — browser screenshots.


The public synthetic publication identifier is redacted from this shareable report. Exact provenance remains in ignored local evidence.

## Scoped live tooling safety

The live results above are historical evidence, not replayed during commit preparation. The scoped harness requires an explicit synthetic source URL and optional tab ID for baseline mode; the unavailable-source probe also requires an explicit URL. Neither command silently reads a previous source URL from local evidence.

```powershell
& $nodeExe mapping-dev/live-sheet-integration.cjs baseline 'PUBLISHED_SYNTHETIC_SHEET_URL' 'TAB_ID'
```

The remaining refresh/invalid/restore/cleanup modes use only their deliberately retained synthetic test session. The live-tooling contract test verifies that missing input exits before any request, account creation or evidence rewrite.
