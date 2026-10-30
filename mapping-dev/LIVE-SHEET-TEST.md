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
- The live bad-data case was duplicate identities in syntactically valid CSV. Malformed quoting, ragged rows, invalid UTF-8 and HTTP-200 HTML are now covered by the controlled local follow-up below. Those fixture results are separate from live Google observations.
- Network failures were explicitly injected using browser offline mode or request abort, rather than a naturally observed Google network outage. Successful import and reconciliation responses were never replaced.
- The live source has no explicit appearance columns. The controlled follow-up below now verifies source-provided color, style, size, icon, label and group changes. It does not reclassify them as live Google source edits.
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

## Controlled local follow-up: malformed responses and source appearance

Completed 2026-10-29 (America/New_York). These new cases use explicitly controlled synthetic CSV responses at the existing loopback helper boundary. They do not fetch or modify a Google Sheet, and are not added to the earlier 12/12 live count. Google Maps rendering and Docker-backed local persistence are real. Browser route fulfillment is declared as fixture response mocking in the new evidence. No application-code fix was required.

Files added or extended:

- `sheet-gap-fixtures.cjs`: a shared 11-column fixture, three valid appearance versions and five malformed response cases.
- `sheet-gap-browser-tests.cjs`: the real-browser/local-persistence coverage and guarded fixture cleanup.
- `public-sheet-tests.cjs`: extended reconciliation unit coverage using those fixtures, including source metadata preservation.

### Malformed response rejection and recovery

| Controlled response | Observed UI error | Preservation and recovery |
| --- | --- | --- |
| Unclosed quoted field after a valid changed row | Unclosed quoted cell starting on row 3. | Complete map/connection unchanged; valid CSV recovered |
| Text after a closing quote | Unexpected text after a quoted cell on row 3. | Complete map/connection unchanged; valid CSV recovered |
| Ragged data row | Row 3: Expected 11 columns; found 4. | Complete map/connection unchanged; valid CSV recovered |
| Invalid UTF-8 bytes | TextDecoder reported that the encoded data was not valid; last loaded map kept | Complete map/connection unchanged; valid CSV recovered |
| HTTP 200 with HTML content type/body | Page rather than CSV; publish the selected tab as CSV; last loaded map kept | Complete map/connection unchanged; valid CSV recovered |

Each syntax/shape fixture starts with a valid row that would change a marker and create a new group if applied prematurely. Assertions verify that no partial update or group creation happens. Every rejected response preserves the complete document, connection identity/configuration/lastSuccess, groups, marker IDs and instances, cluster manager, camera, dirty state, save revision and stored database document. Before/after document SHA-256 hashes match for all five cases. Recovery clears the error, retains locations and connection configuration, and saves successfully; only the accepted refresh timestamp advances.

A separate disallowed external icon case is also rejected atomically, followed by valid recovery. The existing icon/URL rules are exercised without relaxing them or fetching the external icon.

### Source-provided appearance changes

Two successive valid fixture versions reorder the source rows and update all supported appearance fields. The tests verify the document values, actual Google Marker icon/label options and side-label overlay text, rather than checking only parsed CSV.

| Field | Controlled transitions verified |
| --- | --- |
| Style | Source A: pin to numbered to local image; source B: numbered to local image to pin |
| Color | Named colors and a custom hex color, including hex normalization |
| Size | Normal and small, including actual symbol scale and image dimensions |
| Icon | Approved local star image applied, then removed when switching back to a pin |
| Label | Quoted/comma-containing text, replacement and explicit clearing with no stale overlay |
| Group | Source group changes, with stable group references on repeated refresh |
| Source row order | Reordered rows continue to update the same internal marker IDs |

Every source location retains its existing marker ID and Google Marker instance during refresh. The unrelated manual marker retains its exact custom image, color, size, label, name, description, address, coordinates, group and metadata. The unit extension also verifies preservation of an existing linked marker's root extension metadata. Both appearance versions survive real save/reopen and are idempotent on repeated refresh; no duplicate locations or groups appear. Clustering stays active with the expected membership counts and no cluster-manager reinitialization.

### Final follow-up results

| Suite | Result |
| --- | --- |
| New controlled browser suite | 16/16 pass |
| Extended reconciliation unit tests | Pass, including all five malformed cases/recovery and appearance transitions |
| Complete mapping lifecycle | 19/19 pass |
| Complete clustering browser suite | 13/13 pass |
| Defaults persistence browser suite | 11/11 pass |
| Existing spreadsheet browser suite | 30/30 pass |
| Existing sheet browser suite | 9/9 pass |
| Fetch/helper policy tests and sheet persistence contract | Pass |
| Spreadsheet parser | 16/16 pass |
| CSV round trip, document/legacy contract, clustering contract | Pass |

The new browser run recorded no page exceptions, CSP violations or unexpected destinations. Hashes verify that application rendering, clustering, document/schema handling, source reconciliation, fetch/URL policy, local router/CSP-related source and private Maps configuration were unchanged. The synthetic account was deleted through guarded cleanup; the removed map returned HTTP 404. No saved browser session or long-lived fixture was created by this suite.

Reproduce from the workspace:

```powershell
$nodeExe = './local-test/browser-tools/node_modules/node/bin/node.exe'
& $nodeExe mapping-dev/public-sheet-tests.cjs
& $nodeExe mapping-dev/sheet-gap-browser-tests.cjs
& $nodeExe mapping-dev/public-sheet-fetch-tests.cjs
& $nodeExe mapping-dev/sheet-contract-tests.cjs
& $nodeExe mapping-dev/browser-tests.cjs
& $nodeExe mapping-dev/clustering-browser-tests.cjs
& $nodeExe mapping-dev/new-marker-defaults-browser-tests.cjs
```

New ignored evidence is `local-test/sheet-gap-summary.json`, `sheet-gap-browser-results.json` and `sheet-gap-browser.png`. The browser report includes the injected response types, hashes and byte counts, exact rejection errors, matching document hashes, save revisions, rendered appearance snapshots and cleanup flags. The prior live HTTP reports and `live-sheet-summary.json` are preserved separately and unchanged.

### Remaining limitations

- Malformed responses and explicit source appearance edits are now covered under controlled conditions; Google itself was not made to emit those malformed responses or appearance columns. The prior 12/12 live workflow remains the live evidence.
- The new gap suite runs on desktop Chromium. Mobile coverage remains the separate passing clustering suite, not a new mobile malformed-response run.
- These tests exercise the existing local helper and synthetic local persistence. They do not establish that the localhost-only helper or development account service is suitable for production hosting.

## Proposed deployment and rollback steps (not executed)

This follow-up changes tests and documentation only. It has no runtime artifact, database/schema change or production configuration to deploy.

For this coverage change:

1. Review a scoped patch containing the new fixture/test, the added reconciliation assertions and this report. Preserve unrelated pre-existing workspace changes.
2. Run the commands above in the existing isolated local or approved staging/CI environment, using Node 22 and the existing Maps/CSV policies. Require passing tests and cleanup flags.
3. Promote the reviewed test/documentation changes through normal source review. Keep generated local evidence, private configuration and synthetic account/session state out of deployable assets. The existing installer does not package these test files.

For any later engine deployment, prepare a separate reviewed release: identify the approved production connector and hosting path under the unchanged security policy, record the current application artifact/version and backup point, and first verify saved-map/connection/appearance behavior in staging. The local-only installer and helper are not a production rollout plan. A production promotion requires its own explicit authorization and compatibility review; none was performed here.

Rollback for this follow-up is limited to reverting the new test/fixture files and added test/report content, leaving application code and existing user work intact. No database restore is needed because there is no migration and all test records were cleaned up.

For a separately authorized future runtime release, retain the previous known-good application/connector artifacts. If a release fails its smoke checks, restore those artifacts while preserving saved documents and the existing URL/CSP policy, then verify saved map IDs, source identities, manual customization and refresh behavior. Data-backup restoration would require a separate determination that stored data was changed and a reviewed recovery procedure; it is not part of this test-only rollback.

The public synthetic publication identifier is redacted from this shareable report. Exact provenance remains in ignored local evidence.

## Scoped live tooling safety

The live results above are historical evidence, not replayed during commit preparation. The scoped harness requires an explicit synthetic source URL and optional tab ID for baseline mode; the unavailable-source probe also requires an explicit URL. Neither command silently reads a previous source URL from local evidence.

```powershell
& $nodeExe mapping-dev/live-sheet-integration.cjs baseline 'PUBLISHED_SYNTHETIC_SHEET_URL' 'TAB_ID'
```

The remaining refresh/invalid/restore/cleanup modes use only their deliberately retained synthetic test session. The live-tooling contract test verifies that missing input exits before any request, account creation or evidence rewrite.
