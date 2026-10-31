# Local marker clustering

Implemented 2026-10-29 for the separate development workspace at http://localhost:8080/mapping/. No production files, production data, deployment, or customer migration were changed.

## Use

Open **Map Details > Map Settings**, select **Cluster nearby markers**, then **Apply settings**. Save Map persists the setting. New maps default to On. Existing documents without `settings.clusterMarkers` continue to display individual markers; loading them does not add or change fields. The local legacy adapter defaults to Off.

Blue/cyan cluster icons show the number of visible locations. Click or tap to zoom in. If coordinates are identical, or the configured maximum zoom has been reached, a scrollable location chooser opens. Each choice opens the ordinary location popup and existing Edit marker flow. Coincident locations also have a chooser when clustering is Off. Location coordinates are never spread or rewritten.

## Architecture and compatibility

- The official `@googlemaps/markerclusterer` 2.6.2 browser bundle is pinned under `assets/markerclusterer/`, including its Apache-2.0 license and package metadata. The npm archive's SHA-512 was verified before extraction. No runtime CDN request or CSP expansion is required.
- `clustering.js` creates one cluster manager per map. Its renderer uses the existing Google Maps Marker API and Multiplottr blue/cyan colors, with a white count and accessible title.
- `workspace.js` retains location marker instances by stable location ID. Changes update existing instances; removal clears only the removed markers and their listeners. Every data source enters this shared renderer, including plotted addresses, saved documents, imported CSV/TSV/legacy rows, and published Sheets connections.
- Only visible-group markers enter the cluster index. Side labels follow their individual marker's attachment to the map, so labels hide in clusters and return when unclustered.
- Google SuperCluster normally caches marker identities. The adapter invalidates its spatial index when visible membership, order, or coordinates change, so moving an existing marker updates clusters without replacing the marker or cluster manager.
- Cluster objects, counts, and chooser state are transient. They are never written into locations, IDs, groups, geometry, or extensions. The only added document setting is the optional boolean `settings.clusterMarkers`, validated in both JS and PHP. Unknown-field preservation still applies.
- Disabling clustering detaches its overlays and restores visible individual markers. Re-enabling reuses the same manager and marker instances. Zoom/pan does not change authored document settings.

## Verification

Run with the bundled Node 22 runtime, not system Node 18:

```powershell
$nodeExe = './local-test/browser-tools/node_modules/node/bin/node.exe'
& $nodeExe mapping-dev/clustering-contract-tests.cjs
& $nodeExe mapping-dev/clustering-browser-tests.cjs
```

`install.ps1` includes the cluster controller and pinned library. The changed assets were also copied into `local-test/app/mapping-dev/` for the running local site.

| Check | Result |
| --- | --- |
| New clustering browser scenarios | 13/13 pass |
| Library checksum, new/legacy defaults, optional setting types in JS/PHP, compatibility-preserving updates | Pass |
| Existing document contract, including 34 legacy marker-token fixtures | Pass |
| Spreadsheet parser and historical legacy parser | 16/16 and 14/14 pass |
| Existing CSV round-trip, public-sheet reconciliation, JS/PHP sheet contract and restricted-fetch checks | Pass |
| Existing spreadsheet browser scenarios | 30/30 pass |
| Existing published-sheet browser scenarios | 9/9 pass |
| Existing queue, camera, visibility, sidebar and offline workspace checks | Pass; queue 11/11 |
| Existing broader mapping lifecycle | 19/19 pass after the defaults persistence follow-up below |
| Existing isolated regression runner | Pass: 15 config/CSP checks, pinned legacy vendor check, 6 loader contracts, 10 offline guest scenarios, 6 membership scenarios |

The 13 clustering scenarios cover five nearby markers; 125 markers across five cities; native desktop cluster clicks; native mobile cluster taps; zoom separation; identical coordinates at zoom 22; each individual popup; user-group filters; custom colors, numbered and image icons and side labels; edits, dragging, additions and deletion; new plotted addresses; real local saves and reloads; published Sheets CSV connection/refresh; CSV import; optional legacy settings; repeated On/Off; zero visible markers; read-only filters; keyboard choice selection; and mobile touch targets.

The clustering browser suite uses real Google Maps and real local persistence. Addresses/geocoding are synthetic. The published CSV response is mocked at the existing loopback fetch helper; it does not fetch a real user's Google Sheet. Synthetic accounts created by the suites are cleaned up. No browser exceptions, CSP violations or unexpected destinations were recorded by the clustering suite. The isolated regression runner restored private configuration byte-for-byte.

Existing browser harnesses were updated for the current registration controls, nested collapsed sections, status text, shared/private map rules and session-only queue fixture. These changes do not alter application behavior.

## Limitations

The initially recorded new-marker defaults persistence failure was fixed in a separate local follow-up. The complete mapping lifecycle suite now passes 19/19, the clustering suite still passes 13/13, and the new defaults suite passes 11/11. The clustering implementation was unchanged. See [DEFAULTS-PERSISTENCE.md](DEFAULTS-PERSISTENCE.md) for the root cause, compatibility details and verification.

Mobile verification used Edge/Chromium touch emulation at 390px width, not a physical iOS/Android device. The new engine retains its existing 500-location document limit and legacy Google Maps Marker implementation; no API migration was introduced.

Generated evidence is ignored under `local-test/`: `clustering-results.json`, `clustering-mobile.png`, `clustering-desktop.png`, and the existing regression report names.
