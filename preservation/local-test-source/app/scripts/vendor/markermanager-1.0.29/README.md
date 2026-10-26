# MarkerManager local dependency

Package: @googlemaps/markermanager 1.0.29
Source: https://www.npmjs.com/package/@googlemaps/markermanager/v/1.0.29
Upstream: https://github.com/googlemaps/js-markermanager
Documentation: https://googlemaps.github.io/js-markermanager/
License: Apache-2.0; included in LICENSE.

Copied artifact: dist/index.umd.js, unchanged, as markermanager.umd.js.
SHA-256: 136bcf1f1cb0f8a50f99d1d9d7a52d2cc883916f001cccd5178b3d298d97c78d
npm integrity: sha512-t3NjqxYeEUmMBiSwx76MlBLk9Wi+liulI9Ph2XbixfT/Fyg6WOFwTORmlb1qMYwl5teGri9448LV2Wte7VAk1Q==
The tooling package-lock.json pins the package and integrity.

Browser export: google.maps.plugins.markermanager.MarkerManager.
The development loader assigns that constructor to window.MarkerManager after the
Google API and earlier plugins have loaded. The anonymous packed scripts/markermanager.js
found in this workspace is not loaded. There is no runtime CDN dependency.

vendor-tests.cjs checks the checksum, UMD export, required method surface, and license
with a minimal API double. This is not a real Google Maps compatibility test.
