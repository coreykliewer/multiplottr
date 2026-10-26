(function (window, document) {
    'use strict';
    var api = window.MultiplottrLocal;
    var bootPromise;
    var phase = 'disabled';
    var failure = null;
    var pluginList = [
        {src: '/scripts/infobox.js', check: function () { return typeof window.InfoBox === 'function'; }},
        {src: '/scripts/markerwithlabel.js', check: function () { return typeof window.MarkerWithLabel === 'function'; }},
        {src: '/scripts/geolocation-marker.js', check: function () { return typeof window.GeolocationMarker === 'function'; }},
        {src: '/scripts/vendor/markermanager-1.0.29/markermanager.umd.js', check: function () {
            var exported = window.google && google.maps.plugins && google.maps.plugins.markermanager;
            if (exported && typeof exported.MarkerManager === 'function') window.MarkerManager = exported.MarkerManager;
            return typeof window.MarkerManager === 'function';
        }},
        {src: '/scripts/jquery.ui.map.js', check: function () { return !!(window.jQuery && jQuery.fn.gmap); }},
        {src: '/scripts/local-map-engine.js', check: function () { return !!window.MultiplottrDevelopmentMap; }}
    ];
    function pluginsReady() {
        return !!(window.google && google.maps && typeof google.maps.Map === 'function' &&
            window.InfoBox && window.MarkerWithLabel && window.GeolocationMarker && window.MarkerManager);
    }
    api.mapsAvailable = function () { return (phase === 'initializing' || phase === 'ready') && pluginsReady(); };
    api.mapsState = function () { return phase; };
    api.requireMaps = function () {
        if (api.mapsAvailable()) return true;
        api.notice(phase === 'disabled' ?
            'Google Maps is disabled in local testing. A separate test key and restricted browser access are required.' :
            phase === 'failed' ? 'Google Maps could not start. Local menus remain available; check the development configuration.' :
            'Google Maps development mode is loading. Please wait; your input has been preserved.');
        return false;
    };
    function fail() {
        phase = 'failed';
        failure = 'Maps startup failed; verify the development key, allowed referrers, APIs, and narrow CSP.';
        api.notice(failure + ' Local menus remain available.');
        if (window.MultiplottrDevelopmentMap) window.MultiplottrDevelopmentMap.dispose();
    }
    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            var timer = setTimeout(function () { script.remove(); reject(new Error('Local plugin timeout')); }, 12000);
            script.src = src;
            script.async = true;
            script.onload = function () { clearTimeout(timer); resolve(); };
            script.onerror = function () { clearTimeout(timer); script.remove(); reject(new Error('Local plugin failed')); };
            document.head.appendChild(script);
        });
    }
    function loadGoogle(key) {
        return new Promise(function (resolve, reject) {
            var script = document.createElement('script');
            var timer;
            function finish(error) {
                clearTimeout(timer);
                script.onerror = null;
                if (error) window.__multiplottrGoogleReady = function () {};
                else delete window.__multiplottrGoogleReady;
                if (error) { script.remove(); reject(error); } else resolve();
            }
            window.__multiplottrGoogleReady = function () {
                if (!window.google || !google.maps || ['Map', 'Marker', 'OverlayView', 'Geocoder', 'LatLngBounds', 'Point', 'Size'].some(function (name) { return typeof google.maps[name] !== 'function'; })) return finish(new Error('Maps API unavailable'));
                finish();
            };
            // Fixed Google endpoint; the private configuration cannot provide an arbitrary script URL.
            var url = new URL('https://maps.googleapis.com/maps/api/js');
            url.searchParams.set('key', key);
            url.searchParams.set('loading', 'async');
            url.searchParams.set('callback', '__multiplottrGoogleReady');
            url.searchParams.set('v', 'quarterly');
            url.searchParams.set('libraries', 'marker,geocoding');
            url.searchParams.set('language', 'en');
            script.src = url.href;
            script.async = true;
            script.onerror = function () { finish(new Error('Maps API load failed')); };
            timer = setTimeout(function () { finish(new Error('Maps API timeout')); }, 20000);
            document.head.appendChild(script);
        });
    }
    api.startDevelopmentMaps = function () {
        if (bootPromise) return bootPromise;
        bootPromise = (async function () {
            var controller = new AbortController();
            var timer = setTimeout(function () { controller.abort(); }, 8000);
            var config;
            try {
                var response = await fetch('/__local_maps_config', {cache: 'no-store', credentials: 'same-origin', signal: controller.signal});
                if (!response.ok) throw new Error('Configuration unavailable');
                config = await response.json();
            } finally { clearTimeout(timer); }
            if (!config.enabled) {
                if (config.reason !== 'disabled') api.notice('Google Maps remains disabled: ' + config.reason.replace(/_/g, ' ') + '.');
                return {state: 'disabled'};
            }
            if (!/^AIza[A-Za-z0-9_-]{35}$/.test(config.apiKey || '')) throw new Error('Development key required');
            phase = 'loading';
            api.notice('Loading Google Maps development mode. Email, payments, ads, and analytics remain disabled.');
            window.gm_authFailure = fail;
            await loadGoogle(config.apiKey);
            config.apiKey = "[REDACTED_CONFIG_VALUE]";
            for (var i = 0; i < pluginList.length; i++) {
                if (phase === 'failed') throw new Error('Maps authentication failed');
                await loadScript(pluginList[i].src);
                if (!pluginList[i].check()) throw new Error('Plugin export unavailable');
            }
            if (!window.jQuery || !jQuery.ui || typeof window.clientMapLoad !== 'function') throw new Error('Local UI not ready');
            phase = 'initializing';
            await window.MultiplottrDevelopmentMap.initialize();
            if (phase === 'failed') throw new Error('Maps authentication failed');
            phase = 'ready';
            window.dispatchEvent(new Event('local-maps-ready'));
            api.notice('Google Maps development mode loaded. Local test membership is available. Email, payments, ads, analytics, and real geolocation remain disabled.');
            return {state: phase};
        }()).catch(function () { fail(); return {state: phase, reason: failure}; });
        return bootPromise;
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', api.startDevelopmentMaps, {once: true});
    else api.startDevelopmentMaps();
}(window, document));
