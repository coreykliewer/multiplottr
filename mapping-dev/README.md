# Local mapping consistency slice

Implemented 2026-10-26 on branch `mapping-consistency-phase1`. This is a separate local workspace at **http://localhost:8080/mapping/**. The prior environment remains at `/`. No production integration, customer migration, deployment, AI bulk API or MCP service is included.

## Try it

1. Open `http://localhost:8080/mapping/` while the existing local Docker environment is running. Current development-key configuration is reused without displaying it.
2. Expand **Local test account**. Use an existing synthetic account, or enter a unique username such as `local_demo_corey` and your own password of at least 12 characters, then click **Create synthetic account**. These accounts are local only; do not enter production credentials.
3. Click **Load synthetic sample** to see numbered/colored/small pins, a local custom image, labels, rich descriptions, groups, and line/polygon/circle geometry. Two locations intentionally share coordinates. Use the location list to select overlapping markers.
4. Select a marker in the list or its popup, edit its name/description/style/group, and click **Apply marker changes**. The HTML field currently accepts a limited safe subset: headings, paragraphs, bold/emphasis, lists, local links, and approved local image references. A local star image is available at `/mapping/assets/star.svg`. External media and unsupported markup are retained as read-only content, not fetched or stripped.
5. Toggle groups, add a group, and open **Map Settings**. Set authored center/zoom/limits/control/legend choices; click **Apply settings**. **Use current view** captures a panned/zoomed view into the document. Transient panning and popup auto-pan do not silently change authored settings.
6. Click **Save Map**, then **New Map**, **My Maps**, and your saved title. Reload the page; the same document and renderer reopen. Edit and save again. Saved workspace URLs use `/mapping/?map=<id>`; these are separate from legacy/customer map IDs.
7. Try **Read-only sample**. The warning explains unsupported media/geometry, and Save/marker/group editing are disabled. **Download preserved document** retains the full raw document for recovery. It never includes the Maps key or runtime objects.
8. To plot addresses manually, start **New Map**, enter one address per line, and click **Plot**. This uses browser Google geocoding under the existing development-key restrictions. Alternatively use **Place Marker** and click the map. Browser automation used synthetic geocoder results; live geocoding of arbitrary addresses is not certified by these tests.

New Map confirms discarding unsaved changes and starts a new identity. Save failures/conflicts keep the current draft; they do not auto-reload or silently overwrite a newer revision. Download the preserved document before intentionally discarding a draft. Drafts are held in memory, not a durable browser crash-recovery store. Automatic conflict merging and importing recovery JSON are not implemented.

## What is editable

- One SVG-pin-based client renderer for draft, saved, reopened and edited documents. It retains the preferred pin silhouette and incorporates popup editing, dragging, keyboard-accessible location buttons, group visibility and settings.
- Stable map/location IDs, original address, names, HTML/text description representations, marker style/label/size/local icon, group metadata, authored settings, and known geometry.
- Line/polygon/circle geometry is rendered and preserved during marker/settings edits; geometry drawing/edit controls are not part of this slice.
- Unknown fields/extensions are retained. Server update checks reject dropping or modifying unknown/original compatibility metadata, instead of silently normalizing it away. Ordinary supported fields remain editable.
- Separate local JSON store, optimistic revision checks, owner checks, CSRF/Origin checks, and idempotent creation retries. This uses synthetic membership identity only, not its old plain-text validator.

## Read-only or deferred

Remote custom icons/media, video/iframe/script content, unknown geometry/tokens, unsupported document versions/appearances and non-roadmap types are recoverable but read-only. Only the bundled local star image is demonstrated. Arbitrary media policies, satellite/Street View, general import/KML, legacy URL/embed resolution, production permissions/WordPress, full mobile/accessibility certification, and native print acceptance remain deferred. Print CSS is checked; a new native print preview was not certified. The marker backend is still the existing deprecated Google Marker behind the shared renderer; replacing it requires the planned compatibility spike.

The legacy adapter is a **synthetic, read-only compatibility investigation tool**, not a customer migration writer. It retains the original text and flags unsupported tokens. It intentionally does not guess ambiguous pipe records or silently re-geocode saved coordinates.

## Source, runtime, recovery

Canonical implementation/tests are in `mapping-dev/`. `install.ps1` copies only the new workspace files into ignored `local-test/app/mapping-dev/`, which the existing read-only application mount already serves. It does not overwrite the previous app's scripts or data.

Only two existing local files have small hooks: `local-test/router.php` adds scoped workspace/assets/document routes; `local-test/local-membership.php` exposes the existing synthetic database connection to the new service. [integration.patch](integration.patch) records those exact changes without copying credential-bearing configuration into Git. It is already applied in this workspace. On a separately restored private local environment, check/apply it only if the hooks are absent, then run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File mapping-dev/install.ps1
```

`git apply --reverse --check mapping-dev/integration.patch` checks whether the installed hooks match. To roll back, first stop workspace use; reverse those hooks after checking them, leaving the workspace source and synthetic tables recoverable. The previous `/` environment remains available throughout. Do not run the baseline preparation script over the existing app. No customer tables need restoration because they were not modified.

The new `local_document_maps` table contains only synthetic workspace documents and references synthetic users. Test cleanup removes only accounts created by the test, cascading their document rows; it does not clear other test users or documents. Preserved source snapshots remain unchanged.

## Run checks

From the repository root, with the existing local services running:

```powershell
node mapping-dev/tests.cjs
& ./local-test/browser-tools/node_modules/node/bin/node.exe mapping-dev/browser-tests.cjs
& ./local-test/browser-tools/node_modules/node/bin/node.exe mapping-dev/offline-browser.cjs
node mapping-dev/regressions.cjs
powershell -NoProfile -ExecutionPolicy Bypass -File local-test/Test-Membership.ps1
```

Use the bundled Node runtime for Playwright (system Node 18 is too old). `regressions.cjs` temporarily turns the two Maps switches off to run the existing offline suite, then restores the private file byte-for-byte in `finally`; it never prints the key. Do not interrupt that wrapper mid-process. The standalone offline workspace check uses a browser configuration double and blocks every external request without altering the actual config.

Generated reports/screenshots remain ignored under `local-test/`. See [RESULTS.md](RESULTS.md) for scope and verification.
