# Local Multiplottr testing

Open http://localhost:8080 while Docker Desktop is running.
Double-click Start.cmd to start the environment and Stop.cmd to stop it.
Stopping preserves the database. Do not delete the Docker volume unless you intend to discard the local database.

The PHP application and database use an internal Docker network with no internet route.
A separate nginx gateway publishes only 127.0.0.1:8080. Browser CSP blocks external
scripts, forms, frames, images and requests, including Google Maps. PHP email and
shell execution are disabled. Original workspace files stay intact; edit app/ for local changes.

PHP 7.4.33 matches the production PHP series. MariaDB 10.11 matches the backup
header (production 10.11.19). The PHP development server is for local testing only;
Apache rewrite behavior is not reproduced. The production .htaccess is not copied.
WordPress membership and payment sites are not included. Maps need a separate
local testing API key and a carefully restricted CSP update before enabling them.

The imported database contains production data; keep it private and use synthetic
accounts for tests. The backup and local app are excluded from Git.

prepare.ps1 creates the separate application copy. Do not run it again after making
edits in app/ without preserving those edits. Database credentials in compose.yaml
are disposable local credentials, not production credentials.

See DIAGNOSIS.md for the local repairs, feature results, Maps prerequisites, and repeatable Test-Local.ps1 command. Preparation now refuses to overwrite an existing app.

Optional Google Maps development mode is now enabled locally by explicit user request. See MAPS-DEVELOPMENT.md for exact configuration and key restrictions. Both activation flags are currently on; Test-Local.ps1 includes offline/configuration/synthetic-loader checks and never tests real Maps.

Current local state: Maps development mode enabled by explicit user request. See MAPS-LIVE-RESULTS.md for real browser results and scoped fixes. Test-Maps-Live.ps1 uses actual Google requests; Test-Local.ps1 requires both switches off. Server isolation and other service blocks remain in place.

Local test membership is now configured. Login/Register creates/signs in synthetic local_ users; Save Map and My Maps use only separate owner-scoped local tables. No WordPress/email/payments are involved. See MEMBERSHIP-RESULTS.md and Test-Membership.ps1. All temporary test users/maps have been removed; create your own synthetic account for manual testing. Earlier membership-blocked notes above are historical.
