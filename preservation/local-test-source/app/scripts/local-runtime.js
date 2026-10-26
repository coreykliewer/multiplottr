(function (window, document) {
    'use strict';
    var mapsMessage = 'Google Maps is disabled in local testing. A separate test key and restricted browser access are required.';
    function notice(message) {
        var status = document.getElementById('localEnvironmentStatus');
        if (!status) {
            status = document.createElement('div');
            status.id = 'localEnvironmentStatus';
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');
            document.body.appendChild(status);
        }
        status.textContent = message;
    }
    function mapsAvailable() { return false; } // The async loader supplies the live readiness check.
    window.MultiplottrLocal = {
        mapsAvailable: mapsAvailable,
        requireMaps: function () { if (window.MultiplottrLocal.mapsAvailable()) return true; notice(mapsMessage); return false; },
        notice: notice
    };
    document.addEventListener('DOMContentLoaded', function () {
        notice('Local testing: synthetic membership and menus are available. Email and payments are disabled; Maps uses the development configuration.');
        // Show a useful placeholder without constructing a fake Google Maps API.
        var canvas = document.getElementById('map_canvas');
        if (canvas && !window.MultiplottrLocal.mapsAvailable()) {
            var placeholder = document.createElement('p');
            placeholder.className = 'local-map-placeholder';
            placeholder.textContent = 'Google Maps is disabled in this isolated environment.';
            canvas.appendChild(placeholder);
        }
        // Legacy output includes payment forms and external links. Keep navigation local too.
        document.querySelectorAll('form').forEach(function (form) {
            if (new URL(form.action || location.href, location.href).origin !== location.origin) {
                form.addEventListener('submit', function (event) { event.preventDefault(); notice('Payments and external forms are disabled in local testing.'); });
            }
        });
    });
    document.addEventListener('click', function (event) {
        if (!window.MultiplottrLocal.mapsAvailable() && event.target.closest && event.target.closest('#map_canvas')) {
            notice(mapsMessage);
            return;
        }
        var target = event.target.closest && event.target.closest('a, button, input, header');
        if (!target) return;
        if (target.matches('#showLocation, #plotLocation')) {
            event.preventDefault(); event.stopImmediatePropagation();
            notice('Real geolocation is disabled in development mode. Use synthetic locations.');
            return;
        }
        if (target.matches('#quickSearch a, #batchGeocode, #zoomView, #showLocation, #downloadKML, #submitMarker, #newMapSubmitMarker, #updateMarker, #geoCodeAddress, #addAddress')) {
            if (!window.MultiplottrLocal.mapsAvailable()) { event.preventDefault(); event.stopImmediatePropagation(); notice(mapsMessage); return; }
        }
        if (target.matches('#myMaps, #loadAnOldMap, #saveMap, #miniSaveMap, #saveMapButton, #loginRegister, #mobilLogin, #subscribeNow')) {
            if (window.LocalMembership) return;
            event.preventDefault(); event.stopImmediatePropagation();
            notice('Membership is not configured locally. Save Map and My Maps require a separate local test account; no live login is used.');
            return;
        }
        if (target.tagName === 'A' && target.getAttribute('href') && !/^(javascript:|#)/i.test(target.getAttribute('href'))) {
            var url = new URL(target.href, location.href);
            if (url.origin !== location.origin) {
                event.preventDefault(); event.stopImmediatePropagation();
                notice('External navigation is disabled in local testing.');
            }
        }
    }, true);
    document.addEventListener('submit', function (event) {
        if (new URL(event.target.action || location.href, location.href).origin !== location.origin) {
            event.preventDefault(); event.stopImmediatePropagation();
            notice('External forms are disabled in local testing.');
        }
    }, true);
}(window, document));
