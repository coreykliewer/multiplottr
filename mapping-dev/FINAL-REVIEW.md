# Final PR #1 review — 2026-10-30

Reviewed the complete change range from `f4d3979`, including runtime fixes `7ce317d` and portable checks `6a0bc4e`. Review covered import/export and HTML handling, source URL/redirect/Host/CORS restrictions, schema and unknown-field compatibility, owner/CSRF/revision checks, asynchronous connection/queue cancellation, marker and clustering lifecycle, defaults/groups, installers, vendored dependency provenance, test setup and workflow permissions. No further actionable runtime issue was found. The follow-up changes add regression coverage and this evidence; runtime code is unchanged from `6a0bc4e`.

## Fix-to-regression coverage

| Fix | Meaningful regression assertion |
| --- | --- |
| Refresh drops unknown nested fields | `review-regression-tests.cjs` updates a source marker, checks all four nested objects, stable ID and untouched input, then invokes the actual PHP preservation gate. `review-browser-tests.cjs` additionally refreshes real Google marker instances and saves/reopens/resaves through the real Docker API, checking manual customization and metadata. |
| Malformed documents throw PHP/client exceptions | Portable checks exercise 17 malformed documents in JS/PHP, with PHP warnings promoted to failures. The scalar-heading case specifically exercises guarded iteration. Local integration submits seven malformed documents through both POST and PUT: all 14 return 422, retain the full saved map/revision and browser state, and permit a subsequent valid edit/save. |
| Malformed request target terminates CSV helper | `public-sheet-fetch-tests.cjs` sends an actual malformed HTTP request to the PR helper on ephemeral loopback, asserts HTTP 400, then asserts a successful health response. The pre-fix implementation terminated the process. No Google fetch is involved in this check. |
| Ignored source / Docker / private browser dependencies | Fresh-checkout CI runs PHP schema checks without a container or database, uses the isolated checked-in legacy parser oracle, and spawns both live tools without arguments to assert opt-in exit status 2 before private dependencies/configuration are loaded. |
| Windows checkout breaks vendor integrity | Explicit LF attributes preserve the unmodified vendor bytes. A clean Windows clone and Ubuntu hosted jobs run the exact SHA-256/license/version assertions. |

The earlier review reproduced nested-field loss, the original PHP fatal failure, and helper process termination before fixing them. The final browser/API checks now exercise the resulting behavior through the approved local integration boundaries, beyond the portable unit assertions.

## Current local browser / Docker rerun

All **14 commands passed** using the approved existing harness and the PR runtime, with only synthetic fixtures:

| Check | Result |
| --- | --- |
| New `review-browser-tests.cjs` | 5/5; real Maps and Docker persistence, controlled CSV |
| Mapping lifecycle `browser-tests.cjs` | 19/19 |
| Controlled `sheet-gap-browser-tests.cjs` | 16/16; malformed CSV/recovery and source appearance |
| `public-sheet-browser-tests.cjs` | 9/9; browser network/storage doubles |
| `spreadsheet-browser-tests.cjs` | 30/30; no external requests/server writes |
| New-marker defaults | 11/11 |
| Clustering browser checks | 13/13, including 125 locations, coincident markers and mobile access |
| Sharing API contracts | Pass: real owner/private/read-only/toggle validation |
| Plot queue | 11/11 |
| Default camera, plot visibility, sidebar, offline workspace | All four commands pass |
| Synthetic local membership | 6/6 |

These are current local integration results, **not a live published Google Sheets rerun**. Real Maps rendering used the existing approved development configuration; CSV helper responses in browser integration were controlled synthetic fixtures. The helper regression separately used real loopback HTTP with a synthetic fetch dependency.

The original runtime was snapshotted before testing and restored byte-for-byte afterward. Hash checks confirmed the original source, private Maps configuration, local router and historical live JSON evidence were unchanged. Existing generated reports/screenshots were restored; fresh evidence is held only under ignored `local-test/final-pr1-review-20261030/`. Synthetic account cleanup completed, including explicit 404 verification for the new review map and controlled-gap map. No private configuration, sessions or raw generated artifacts are publication candidates.

## Evidence boundaries and remaining limitations

The historical **12/12 live Google CSV checks** in [LIVE-SHEET-TEST.md](LIVE-SHEET-TEST.md) remain historical and unchanged. The historical **16/16 controlled checks** are separate; the 16/16 controlled suite was also rerun during this final review. These counts are not combined with the new 5/5 regression suite or the credential-free gate.

Credential-free checks remain reproducible with the [documented fresh-checkout setup](TESTING.md). Full-stack reproduction elsewhere still requires the approved local harness, browser/Docker dependencies and development Maps configuration; those intentionally remain private. This is a portability limitation, not an unexecuted local check in this review. Live Google publishing behavior was not recertified and still requires an explicitly provided synthetic URL and coordinated human sheet edits. No new release-blocking issue was identified within this local development scope; production rollout still needs separate approval and its own integration/rollback plan.

Hosted CI must be checked against the final PR head after the follow-up test/documentation commit, rather than relying on the earlier green `6a0bc4e` run. The PR description records that final head and hosted result before readiness is changed. No merge, deployment, production configuration, CSP or source URL-policy change is part of this review.
