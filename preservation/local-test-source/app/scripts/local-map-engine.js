(function (window, document) {
    'use strict';
    var manager, managerReady = false, disposed = false, started = false, originalRender;
    function refreshLegend() {
        var lines = document.getElementById('markerLocationField').value.trim().split('\n');
        ['legendDiv', 'printLegend'].forEach(function (id) {
            var container = document.getElementById(id);
            if (!container) return;
            container.textContent = '';
            lines.filter(Boolean).forEach(function (line) {
                var fields = line.split('|');
                var item = document.createElement('li');
                var label = document.createElement('textarea');
                label.innerHTML = (window.LocalMembership ? LocalMembership.decodeField(fields[1]) : (fields[1] || fields[0])).replace(/<[^>]*>/g, '');
                item.textContent = label.value;
                container.appendChild(item);
            });
        });
        if (managerReady && manager) {
            manager.clearMarkers();
            manager.addMarkers(window.addressPlottedMarkers || [], 0, 22);
            manager.refresh();
        }
    }
    function dispose() {
        disposed = true;
        managerReady = false;
        if (window.localMapClickListener) {
            google.maps.event.removeListener(window.localMapClickListener);
            window.localMapClickListener = null;
        }
        if (window.jQuery) jQuery('#zoomView').off('.localDevelopmentMaps');
        if (manager) { manager.clearMarkers(); manager.hide(); }
        if (window.mapClickMarker) window.mapClickMarker.setMap(null);
        if (typeof window.removeAllMarkers === 'function') window.removeAllMarkers();
    }
    async function initialize() {
        if (started) return;
        started = true;
        if (!window.MultiplottrLocal.mapsAvailable()) throw new Error('Maps API and plugins are not ready');
        if (!window.jQuery || !jQuery.ui || typeof window.clientMapLoad !== 'function') throw new Error('Local controls are not ready');
        document.querySelectorAll('.local-map-placeholder').forEach(function (e) { e.remove(); });
        // Reuse the client's existing marker renderer. Never run the competing PHP-generated engine.
        originalRender = window.clientMapLoad;
        window.clientMapLoad = function () {
            if (disposed || !window.MultiplottrLocal.mapsAvailable()) return false;
            var result = originalRender();
            refreshLegend();
            return result;
        };
        window.clientMapLoad();
        if (!window.map || !window.geocoder) throw new Error('Map construction failed');
        manager = new window.MarkerManager(window.map, {maxZoom: 22, show: true});
        google.maps.event.addListenerOnce(manager, 'loaded', function () { if (!disposed) { managerReady = true; refreshLegend(); } });
        window.geocodeThis = function (address) {
            if (!window.MultiplottrLocal.requireMaps()) return;
            window.geocoder.geocode({address: address}, function (results, status) {
                if (disposed) return;
                if (status !== 'OK' || !results[0]) return window.MultiplottrLocal.notice('No location found for the supplied synthetic address.');
                google.maps.event.trigger(window.map, 'click', {latLng: results[0].geometry.location});
            });
        };
        jQuery('#zoomView').on('click.localDevelopmentMaps', function () {
            if (!window.MultiplottrLocal.requireMaps()) return;
            var center = window.map.getCenter();
            jQuery('#urlParam').val(window.location.origin + '/?mapZoom=' + window.map.getZoom() + '&location=' + center.lat() + ',' + center.lng());
            window.MultiplottrLocal.notice('Map center and zoom prepared in the local settings URL.');
        });
        // Apply supported settings in place. The legacy form built malformed URLs and reloaded away the markers.
        function settingsURL() {
            var url = new URL('/', window.location.origin);
            var center = window.map.getCenter();
            url.searchParams.set('mapZoom', window.map.getZoom());
            url.searchParams.set('location', center.lat() + ',' + center.lng());
            url.searchParams.set('mapControl', jQuery('#mapControl').prop('checked') ? 'true' : 'false');
            url.searchParams.set('mapType', jQuery('#hideMapType').prop('checked') ? 'true' : 'false');
            var min = Number(jQuery('#minZoom').val());
            var max = Number(jQuery('#maxZoom').val());
            if (Number.isInteger(min) && Number.isInteger(max)) {
                url.searchParams.set('minZoom', min); url.searchParams.set('maxZoom', max);
            }
            jQuery('#urlParam').val(url.href);
            return {url: url, min: min, max: max};
        }
        function applySettings(event) {
            if (event) { event.preventDefault(); event.stopImmediatePropagation(); }
            if (!window.MultiplottrLocal.requireMaps()) return false;
            var settings = settingsURL();
            if (!Number.isInteger(settings.min) || !Number.isInteger(settings.max) || settings.min < 0 || settings.max > 22 || settings.min > settings.max) {
                window.MultiplottrLocal.notice('Choose zoom limits between 0 and 22, with minimum no greater than maximum.');
                return false;
            }
            window.map.setOptions({
                zoomControl: !jQuery('#mapControl').prop('checked'),
                mapTypeControl: !jQuery('#hideMapType').prop('checked'),
                mapTypeControlOptions: {mapTypeIds: [google.maps.MapTypeId.ROADMAP]},
                minZoom: settings.min, maxZoom: settings.max,
                streetViewControl: false
            });
            window.MultiplottrLocal.notice('Local map settings applied. Plotted markers were preserved.');
            return false;
        }
        jQuery('#hideMapType').prop('checked', true);
        jQuery('#showMapOverview, #mapControlSize, #customMapURL, #mapDatabaseURL')
            .prop('disabled', true).attr('title', 'Unsupported or external sources are disabled in local development.');
        jQuery('#mapControl, #hideMapType, #minZoom, #maxZoom').off('click change').on('change.localDevelopmentMaps', settingsURL);
        jQuery('#updateURL').off('click').on('click.localDevelopmentMaps', applySettings);
        jQuery('#urlMapSettings').on('submit.localDevelopmentMaps', applySettings);
        // A first idle event is a runtime readiness signal, not proof of compatibility or successful billing/authentication.
        await new Promise(function (resolve, reject) {
            var timer = setTimeout(function () { reject(new Error('Map did not become idle')); }, 20000);
            google.maps.event.addListenerOnce(window.map, 'idle', function () { clearTimeout(timer); disposed ? reject(new Error('Map disposed')) : resolve(); });
        });
    }
    async function printMap() {
        if (!window.MultiplottrLocal.requireMaps()) return;
        var oldCenter = window.map.getCenter(), oldZoom = window.map.getZoom();
        document.body.classList.add('local-print-layout');
        try {
            // Lay out the map at the same dimensions as print preview before fitting the markers.
            await new Promise(function (resolve) { requestAnimationFrame(function () { requestAnimationFrame(resolve); }); });
            var bounds = new google.maps.LatLngBounds();
            (window.addressPlottedMarkers || []).forEach(function (marker) { bounds.extend(marker.getPosition()); });
            await new Promise(function (resolve) {
                var timer = setTimeout(resolve, 3000);
                google.maps.event.addListenerOnce(window.map, 'idle', function () { clearTimeout(timer); resolve(); });
                google.maps.event.trigger(window.map, 'resize');
                if ((window.addressPlottedMarkers || []).length) window.map.fitBounds(bounds, 40);
            });
            await new Promise(function (resolve) { setTimeout(resolve, 500); });
            window.print();
        } finally {
            document.body.classList.remove('local-print-layout');
            google.maps.event.trigger(window.map, 'resize');
            window.map.setCenter(oldCenter); window.map.setZoom(oldZoom);
        }
    }
    window.MultiplottrDevelopmentMap = {initialize: initialize, dispose: dispose, print: printMap};
}(window, document));
