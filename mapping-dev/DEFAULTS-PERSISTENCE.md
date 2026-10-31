# New-marker defaults persistence

Scoped-history note: this report records the uncommitted integrated-development regression and fix. The committed f4d3979 baseline already persisted the original defaults. The scoped enrichment commit preserves that contract while adding custom-color/group controls, metadata preservation and matching preview/marker dimensions; it does not introduce an intermediate cache regression.

Fixed locally on 2026-10-29. No production changes, deployment, schema migration, or clustering implementation changes.

## Root cause and previous data flow

The new-marker controls changed only `state.markerDefaults`. Creating a group also changed this transient cache. `save()` correctly serialized `state.document`, but the cache was not part of that document, so a defaults-only change did not mark the map dirty and the new values never reached storage.

On load, `selectDocument()` cleared the cache. `plotDefaults()` then reconstructed a red, normal pin in the first visible group, rather than reading `document.settings.plotDefaults`. It consulted only the remembered custom color from the saved setting, and even that could be overridden by browser localStorage. This caused the observed default group reset from `b` to `a` and also affected style, color, size, icon and label defaults.

The existing JavaScript/PHP schema, save service, and known-field preservation rules already supported `settings.plotDefaults`. They did not require changes.

## Corrected data flow

1. `plotDefaults()` reads the map's saved `settings.plotDefaults` and returns an independent copy, retaining unknown metadata. Saved values take precedence over built-in and browser fallback values. It does not infer defaults from existing location properties on load.
2. Control changes call `setPlotDefaults()` and the existing document `edit()`/`commit()` path. Defaults changes mark the map dirty and update only its settings. Existing marker instances, location appearance and camera state remain unchanged.
3. New group creation writes the group and its default selection in the same document transaction. The pending `__new_group__` UI choice is never saved as a group ID.
4. `save()` snapshots the defaults along with the rest of the document. Existing snapshot/revision handling retains defaults edits made during an in-flight save until a later save persists them.
5. `openSaved()`/`selectDocument()` restore the stored document. `plotDefaults()` supplies its defaults to the controls, the plotting queue and placed-marker creation. New locations receive copies of the effective appearance and the selected group.

The transient defaults cache was removed. The existing convenience behavior of explicitly choosing a group and previewing its current style is retained; merely loading a map or editing a location does not derive or replace saved defaults from those locations.

## Supported defaults and compatibility

| Field | Persistence and application |
| --- | --- |
| `groupId` | Stored group identity, including a group currently hidden |
| `appearance.kind` | Pin, numbered or approved local image |
| `appearance.color` | Named color or supported custom hex |
| `appearance.size` | Normal or small |
| `appearance.icon` | Approved local image path or null; existing supported document values retained on load |
| `appearance.label` | Saved label retained and copied to new locations, independently of existing marker labels |
| `lastCustomColor` | Remembered per-map color; browser preference used only when the map has no remembered value |

Unknown fields at the settings, defaults and defaults-appearance levels survive updates and real save/reload cycles. Existing server protection still rejects dropping unknown metadata. Unsupported appearances remain read-only and are not silently rewritten.

Older documents without `plotDefaults` still use the red/normal/pin and first-visible-group fallback. Reading, loading or saving them does not automatically add a defaults setting. Explicitly configuring defaults adds the already-supported optional setting. Switching maps does not leak the previous map's defaults. No document version change or data migration was needed.

## Verification

Run from the workspace with the bundled Node 22 runtime:

```powershell
$nodeExe = './local-test/browser-tools/node_modules/node/bin/node.exe'
& $nodeExe mapping-dev/new-marker-defaults-browser-tests.cjs
& $nodeExe mapping-dev/browser-tests.cjs
& $nodeExe mapping-dev/clustering-browser-tests.cjs
```

| Suite | Final result |
| --- | --- |
| Complete mapping lifecycle | 19/19 pass, including the original `b` to `a` regression |
| Complete clustering browser suite | 13/13 pass |
| New defaults persistence browser suite | 11/11 pass |
| Camera preservation | Pass |
| Existing spreadsheet browser suite | 30/30 pass |
| Existing public-sheet browser suite | 9/9 pass |
| Offline workspace save/reopen | Pass |
| Document contract / 34 legacy token fixtures | Pass |
| Clustering package integrity and compatibility contract | Pass |
| Spreadsheet parser / historical legacy parser | 16/16 and 14/14 pass |
| CSV round trip, public-sheet reconciliation, JS/PHP sheet contract | Pass |

The new 11-scenario suite covers every supported default, real local save/reload/edit/resave, plotting and placing all three marker styles, nonempty saved labels and approved image icons, per-map remembered colors despite a conflicting browser preference, existing-marker independence, pending/new group selection, save failures and retry, concurrent edits during save, map switching, hidden groups, old documents without defaults, unknown metadata preservation/rejection of metadata removal, and read-only/unsupported documents.

The main lifecycle, clustering and defaults browser suites use real Google Maps and real local persistence with synthetic accounts/data; geocoding uses synthetic callbacks. The default suite recorded no browser errors, CSP violations or unexpected network destinations. Its synthetic accounts and maps were removed by the existing fixture cleanup. Mobile verification in the clustering suite uses Chromium touch emulation, and public-sheet browser responses are mocked.

Only `mapping-dev/workspace.js` required an application-code change. Its local runtime copy at `local-test/app/mapping-dev/workspace.js` was updated. Schema, clustering controller, vendor library and production files remain unchanged by this fix. Generated evidence is ignored under `local-test/new-marker-defaults-results.json` and the existing lifecycle/clustering report names.
