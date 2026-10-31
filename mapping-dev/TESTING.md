# Reproducible mapping checks

Run these checks from a new clone of this branch. Install Git, Node.js **22 or 24** (including npm), and **PHP CLI 8.2+**. No PHP extensions, database, Docker, browser, Google account/key, `.env`, local configuration, npm packages from another checkout, or private registry are needed. PHP must be on PATH; alternatively set `PHP_BINARY` to its executable path. On Ubuntu: `sudo apt-get update` then `sudo apt-get install -y --no-install-recommends php-cli`.

```sh
git clone https://github.com/coreykliewer/multiplottr.git
cd multiplottr
git checkout mapping-scoped-engine-20261029
cd mapping-dev
npm ci --ignore-scripts --no-audit --no-fund
npm run verify
```

The same npm commands work in PowerShell. `npm run check` checks all workspace JavaScript and the PHP document service; `npm test` runs the 11 suites below, failing on the first failing suite. The checked-in lockfile currently has **zero third-party test dependencies**. Node supplies assertions, VM, HTTP, fetch and crypto; PHP checks load only the document class definition, never instantiate its database store. npm setup does not install browser packages or execute lifecycle scripts.

| Suite | Scope |
| --- | --- |
| `tests.cjs` | 34 legacy marker tokens, document round trips, JS/PHP capabilities |
| `spreadsheet-tests.cjs` | 16 parser/validation checks |
| `legacy-paste-tests.cjs` | 14 historical parser/safety checks; checked-in isolated parser oracle |
| `csv-roundtrip-tests.cjs` | Import/export, appearance, Unicode, IDs and formula protection |
| `public-sheet-tests.cjs` | Reconciliation, failures/recovery, stable IDs, source appearance and manual isolation |
| `public-sheet-fetch-tests.cjs` | Synthetic HTTP responses; redirect/size/HTML policy, loopback Host/CORS restrictions, malformed request recovery |
| `sheet-contract-tests.cjs` | Saved connection schema and server unknown-field preservation gate |
| `clustering-contract-tests.cjs` | Official vendored 2.6.2 checksum/license, settings and JS/PHP parity |
| `clustering-unit-tests.cjs` | Controller doubles: 5/105 markers, movement/removal/hiding/toggle, duplicate/max-zoom chooser and count icon |
| `review-regression-tests.cjs` | Nested unknown-field preservation and 17 malformed document cases |
| `live-tooling-contract-tests.cjs` | No-argument live tools reject before reading local setup or loading Playwright |

Only the fetch-server suite opens a temporary HTTP server, bound to loopback on an ephemeral port. Successful Google responses are synthetic/injected in this gate. The cluster controller suite uses doubles, not the real Google Maps renderer or spatial algorithm. Vendor bytes are protected by `.gitattributes` so Windows CRLF conversion cannot change their SHA-256. The legacy oracle contains only the two historical parser functions, with synthetic inputs and stubbed dependencies; the preserved review application is never bootstrapped. Reports are not generated unless `WRITE_LOCAL_TEST_EVIDENCE=1` is explicitly set for `tests.cjs`.

## GitHub Actions

[mapping-checks.yml](../.github/workflows/mapping-checks.yml) runs `npm ci` and `npm run verify` on Ubuntu 24.04 with Node 22 and 24. PHP CLI is installed explicitly. It runs on pull requests (including drafts) and pushes to main/the PR branch. It uses pinned official [checkout](https://github.com/actions/checkout) and [setup-node](https://github.com/actions/setup-node) actions, read-only repository permission, no persisted checkout credentials, no caches, services, secret inputs or deployment steps. A hosted runner needs ordinary network access to obtain the checkout, official actions, Node and Ubuntu packages; the tests do not fetch Google data. The workflow uses `pull_request`, not `pull_request_target`, and does not execute private local scripts.

## Checks requiring the local integration environment

These are **not included** in the credential-free gate:

| Scripts | Additional setup |
| --- | --- |
| `browser-tests.cjs`, `clustering-browser-tests.cjs`, `new-marker-defaults-browser-tests.cjs`, `default-camera-tests.cjs`, `plot-visibility-tests.cjs`, `plot-queue-tests.cjs`, `sidebar-tests.cjs` | Existing local app/membership/document API on localhost:8080, Docker synthetic database, Playwright Core 1.64.1 at `local-test/browser-tools/node_modules/playwright-core`, compatible Node, installed Edge at the Windows path in each script, approved development Maps configuration/key and Google network access |
| `sheet-gap-browser-tests.cjs` | Same local browser/Docker/Maps setup; routes controlled CSV fixtures, checks real Maps rendering and save/reload, cleans up its synthetic account/map |
| `review-browser-tests.cjs` | Same approved local browser/Docker/Maps setup; five checks for nested metadata, rendered marker identity, real save/reopen/resave, fourteen rejected POST/PUT requests, unchanged data/revision and valid recovery; controlled CSV, no live Google Sheet |
| `sharing-contract-tests.cjs` | Playwright request client and local Docker membership/document API; no Google rendering |
| `spreadsheet-browser-tests.cjs`, `public-sheet-browser-tests.cjs`, `offline-browser.cjs` | Playwright/Edge and locally served workspace; network/session/configuration doubles, Google disabled. These scripts still depend on the local harness paths and are not fresh-clone CI tests |
| `regressions.cjs`, `local-test/Test-Local.ps1`, `local-test/Test-Membership.ps1` | Existing private local harness/Docker and PowerShell. The wrapper temporarily changes and restores the ignored Maps config; do not run it in hosted CI |
| `live-sheet-integration.cjs`, `live-sheet-unavailable-tests.cjs` | Full approved local browser/Docker/helper environment, explicit synthetic published URL, and a human able to edit that public sheet |

The runtime harness, Maps key and installed browser dependencies are intentionally not in Git. Sanitized `preservation/` files are review copies, **not a working app bootstrap**. A fresh clone cannot honestly reproduce those full-stack checks until a separate approved synthetic local harness is supplied. Do not copy private config or production data into a clone to make CI pass. The local helper, CSP installer and app installer are never executed by `npm run verify`; source URL policy and production CSP remain unchanged.

The historical **12/12 live Google CSV checks** and **16/16 controlled browser checks** in [LIVE-SHEET-TEST.md](LIVE-SHEET-TEST.md) remain separate evidence. Neither set is rerun or certified by this CI gate. Explicit opt-in guards can be checked safely without a URL; actually running a live mode requires its documented local setup and coordinated synthetic sheet edits.

## Rollback

Keep the PR in draft until hosted checks and the separate local integration review are satisfactory. There is no deployment step. Revert the review fix commit to undo the runtime fixes; revert the following CI/setup commit to remove this gate. Existing saved documents are unchanged, so no data migration or database restoration is needed for these commits. Any eventual engine rollout needs its own approved deployment/rollback plan and the local integration checks above.
