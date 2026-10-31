# Multiplottr preview scope agreement

## Goal

Put the merged Multiplottr version online at a separate preview address so we can use it and identify remaining issues. Keep the current **multiplottr.com unchanged**. This replaces the earlier VM-only staging direction; inspect existing hosting first.

## In scope

- Inspect hosting and choose the simplest separate preview setup.
- Configure isolated application files, database, sessions, and required services.
- Make only changes necessary to run the existing features online.
- Verify Sheets/CSV, clustering, saved defaults, and save/reopen using synthetic preview data.
- Prepare an independent way to disable or remove the preview.

## Out of scope

- PHP upgrades, redesigns, modernization, and additional features.
- Production cutover or changes to current customer accounts, maps, database, routing, or site files.
- Importing production data.
- New infrastructure unless inspection establishes that existing hosting cannot support the preview.

Necessary preview-specific configuration and compatibility changes belong in scope only when they directly enable an existing feature online. They do not authorize unrelated improvements or changes to the current site.

## Working rules

1. Read this agreement and applicable repository instructions before working.
2. Before each implementation step, state how it directly supports the goal.
3. Record unrelated findings in a backlog; do not implement them.
4. If a necessary change would cross these boundaries, explain the specific blocker and obtain approval before proceeding with that change.
5. Report completed work, remaining blockers, and the next concrete step. Preserve unrelated local work and keep credentials out of Git and chat.

## Completion

Finish when the separate preview works, Sheets/CSV, clustering, saved defaults, and save/reopen checks pass, the preview can be disabled independently, and the existing site is verified unaffected. Verify production through non-mutating checks; do not run synthetic write tests against production. Do not expand acceptance afterward.
