# Baseline preservation

Date: 2026-10-25. This milestone changes repository/documentation files only. Application behavior, data, services, and isolation remain unchanged. No production access or remote push occurred.

## Source boundaries

| Source set | Location | Preservation |
| --- | --- | --- |
| Imported application working tree | Root application files and original subdirectories | Left in place, unchanged and ignored by default. This is the available imported snapshot, not confirmed deployed source. |
| Current local test working tree | `local-test/` including its modified `app/` | Left in place and unchanged. Ignored to prevent private config, fixtures, outputs, and copied data entering Git. |
| Original source review snapshot | `preservation/original-source/` | Sanitized copies of core entry-point variants, renderer classes, scripts, CSS, includes, and database classes. |
| Local source review snapshot | `preservation/local-test-source/` | Sanitized copies of the corresponding app source plus router, Maps loader/configuration reader, membership harness, Docker/service definitions, test scripts, package manifest, and Maps example configuration. |

The preserved source sets are **review copies, not runnable backups**. Secret-bearing configuration is excluded or replaced with placeholders. Sanitization also removes email addresses, nonlocal IPv4 addresses, account identifiers, and credential-like literals. Such edits can affect parsing or example behavior; never execute these review copies as the working application. License/attribution files are retained where present in selected source folders.

`preservation/MANIFEST.json` records each included source path, its original-byte SHA-256, preserved-byte SHA-256, and whether sanitization changed it. It also records hashes of seven existing final test artifacts without copying their generated contents into Git. Hashes establish identity, not a claim that tests were rerun.

`preservation/preserve-source.cjs` documents the selection and redaction policy and refuses to overwrite an existing snapshot. It is not a general secret-detection guarantee or a full-site backup tool.

## Excluded material and recovery limits

Before staging, the root ignore policy defaults to excluding the imported trees and permits only the reviewed documentation and preservation folder. Additional recursive exclusions cover private Maps configuration, environment/credential files, known database configuration files, database dumps, archives, customer/data/upload folders, logs, generated results/screenshots, and dependency installation folders. No real configuration or credentials are copied from the private Maps file.

Other sites/WordPress trees, customer-specific folders, raw imported records, binary assets, and dependency installations are not part of the committed source review snapshot. Existing files remain on disk; this task neither relocates nor deletes them. Selected vendored runtime JavaScript is included as source because it is already part of the application, whereas `node_modules` is excluded. The package manifest records installation requirements without committing installed packages.

This repository is not an off-device backup, and the source snapshots alone cannot restore a running system. Preserve existing private backups separately under the owner's storage policy. Recovery of excluded runtime assets/configuration/data requires those private originals or backups. No new database export or data migration was performed.

## Likely original entry points and variants

| Local evidence | Interpretation | Confidence / unresolved point |
| --- | --- | --- |
| Root `index.php` includes `db/db.class.v7.4.php`, `db/plotter.class.v7.4.php`, `checklogin.v7.4.php`, `reglogin.v7.4.php`, `localsession.v7.4.php`, and `mpcMain.v7.4.php`. | Strongest candidate for the imported main application entry point and PHP 7.4 source family. | Confirmed dependency references in the local file. Whether this exact file/version serves production is unconfirmed. |
| `index.v7.4.php`, `index.v7.4.org.php`, `index.v5.4.php`, cleaned/test and date-stamped `index*` files; matching `mpcMain*` and database-class variants. | Parallel PHP generations, experiments, and historical variants. `index.v7.4.php` still references some unsuffixed login files. | Filenames/dates are clues, not deployment order or compatibility proof. Do not select the newest filename automatically. |
| `index_embed.php` references PHP 7.4 classes; `index_embed_v5.php`, `index_embed_v7.4.php`, and `embed.php` coexist. | Likely embedded-map entry-point family. | References/source presence confirmed; production rewrite targets, privacy, and active variant unconfirmed. |
| `download_kml.php` and `download_kml.v7.4.php`. | KML download handlers returning submitted form content. | Handler presence confirmed; valid generation and production route selection unverified. |
| `mpcLoad.php`, `MULTIPLOTTR*`, `multipMain.php`, plus older renderer variants. | Other loader/custom/legacy map paths; `mpcLoad.php` references unsuffixed `mpcMain.php`. | No claim that these are active or safe to retire. |
| Root `.htaccess` and neighboring WordPress/subsite directories. | Apache redirects and separate site/membership context may influence routing. | Local development server does not reproduce deployed virtual-host/DirectoryIndex/rewrite settings. Those settings and WordPress linkage cannot be confirmed here. |
| `local-test/compose.yaml` → `router.php` → copied `app/index.php`; local loader/engine and membership endpoints. | Actual isolated test entry-point family, deliberately distinct from imported source. | Local harness structure confirmed. Its synthetic membership and router quarantine are not production integration. |

## Repository policy and validation

No repository existed at inspection. A local `baseline` branch was initialized; no remote was added. The existing global initial-branch preference was not changed. Git's ownership check under the sandbox account is handled only with a command-scoped `safe.directory` value for this workspace, not a global trust change.

The preservation commit uses a repository-local, non-personal identity. No signing or remote authentication is required. Validation checks the manifest hashes against untouched originals and sanitized copies, confirms excluded path samples with Git ignore rules, scans staging candidates for credential-shaped content, and checks that only documentation and preserved source enter the commit. No application or browser tests are needed for this source/documentation-only milestone.

See [UPGRADE-DECISIONS.md](UPGRADE-DECISIONS.md) for the owner's pending scope choices. New feature development remains paused.

### Preservation checks completed

- Preserved 533 original-source files and 570 local-test-source files; 225 copies required sanitization.
- Verified all 1,103 original source hashes against the untouched working trees and all 1,103 preserved hashes against the review copies.
- Verified seven recorded evidence hashes; no test outputs were regenerated.
- Credential-pattern scanning found an older Google key format in two historical copies; both were redacted before staging. The final targeted scan found no remaining matches. Pattern scanning is not a general security audit.
- Git ignore probes confirmed exclusion of the private Maps configuration, backups/dumps, customer data paths, installed packages, generated results, and known database config paths.
- The staged allowlist contains 1,109 files: the source copies, manifest, preservation script, ignore policy, and three review documents. No unexpected paths were staged. No remote is configured.

The preservation snapshots intentionally exclude historical binary assets and private data; the hash manifest inventories included source, not every file in the imported account. Subsequent development must use reviewed working sources and a deliberate change set, rather than treating sanitized copies as a deployable branch.
