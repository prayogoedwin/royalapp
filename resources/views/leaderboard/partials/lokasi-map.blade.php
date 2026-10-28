@php
    $points = $points ?? [];
@endphp

<div class="mt-5">
    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('Peta lokasi hari ini') }}</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">
        {{ __('Default hanya titik terakhir, warna mengikuti daftar driver. Klik titik untuk nama, jam, Google Maps, dan jejak sebelumnya.') }}
    </p>
    <div class="relative">
        <div id="lokasi-map" class="h-[28rem] w-full rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900"></div>
        @if(count($points) === 0)
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <p class="text-sm text-gray-500 dark:text-gray-400 bg-white/80 dark:bg-gray-800/80 px-3 py-2 rounded">
                    {{ __('Belum ada lokasi terkini hari ini.') }}
                </p>
            </div>
        @endif
    </div>
    <div class="mt-2 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-gray-100"></span> {{ __('Titik sebelumnya (setelah diklik)') }}</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    (function () {
        var points = @json($points);
        var mapEl = document.getElementById('lokasi-map');
        if (!mapEl || typeof L === 'undefined') {
            return;
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function (ch) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
            });
        }

        var map = L.map(mapEl, { scrollWheelZoom: false }).setView([-6.2088, 106.8456], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        if (!points.length) {
            return;
        }

        var latestPoints = points.filter(function (point) { return point.is_latest; });
        var previousByUser = {};
        var trailVisible = {};
        var trailLayers = {};

        points.forEach(function (point) {
            if (point.is_latest) {
                return;
            }
            if (!previousByUser[point.user_id]) {
                previousByUser[point.user_id] = [];
            }
            previousByUser[point.user_id].push(point);
        });

        function popupHtml(point, withTrailButton) {
            var mapsLink = point.maps_url
                ? '<a href="' + escapeHtml(point.maps_url) + '" target="_blank" rel="noopener">Lihat di Google Maps</a>'
                : '';
            var trailButton = '';
            if (withTrailButton && point.has_previous) {
                var opened = !!trailVisible[point.user_id];
                trailButton = '<br><button type="button" class="lokasi-trail-btn mt-2 inline-block rounded bg-gray-800 text-white px-2 py-1 text-xs" data-trail-user="' + escapeHtml(point.user_id) + '">' +
                    (opened ? 'Sembunyikan titik sebelumnya' : 'Lihat titik sebelumnya') +
                    '</button>';
            }

            return '<div class="text-sm">' +
                '<strong>' + escapeHtml(point.name || 'Driver') + '</strong><br>' +
                escapeHtml(point.tanggal_label || '') + '<br>' +
                mapsLink + trailButton +
                '</div>';
        }

        function addCircle(point, color, radius) {
            var marker = L.circleMarker([point.latitude, point.longitude], {
                radius: radius,
                color: '#ffffff',
                weight: 2,
                fillColor: color,
                fillOpacity: 1
            });

            marker.bindTooltip(point.waktu_label || '', {
                permanent: true,
                direction: 'top',
                offset: [0, -8],
                className: 'lokasi-time-label'
            });
            marker.bindPopup(popupHtml(point, !!point.is_latest));

            return marker;
        }

        var latestMarkers = [];
        latestPoints.forEach(function (point) {
            var marker = addCircle(point, point.color || '#2563eb', 9).addTo(map);
            latestMarkers.push(marker);
        });

        function showTrail(userId) {
            if (trailLayers[userId]) {
                trailLayers[userId].addTo(map);
                trailVisible[userId] = true;
                return;
            }

            var layer = L.layerGroup();
            var coords = [];
            (previousByUser[userId] || []).forEach(function (point) {
                coords.push([point.latitude, point.longitude]);
                addCircle(point, '#111827', 7).addTo(layer);
            });
            var latest = latestPoints.find(function (point) {
                return String(point.user_id) === String(userId);
            });
            if (latest) {
                coords.push([latest.latitude, latest.longitude]);
            }
            if (coords.length > 1) {
                L.polyline(coords, { color: '#6b7280', weight: 2, opacity: 0.7 }).addTo(layer);
            }
            layer.addTo(map);
            trailLayers[userId] = layer;
            trailVisible[userId] = true;

            var bounds = L.latLngBounds(coords);
            if (bounds.isValid()) {
                map.fitBounds(bounds.pad(0.25));
            }
        }

        function hideTrail(userId) {
            if (trailLayers[userId]) {
                map.removeLayer(trailLayers[userId]);
            }
            trailVisible[userId] = false;
        }

        map.on('popupopen', function (event) {
            var root = event.popup.getElement();
            var button = root ? root.querySelector('[data-trail-user]') : null;
            if (!button) {
                return;
            }
            button.addEventListener('click', function (clickEvent) {
                clickEvent.preventDefault();
                var userId = button.getAttribute('data-trail-user');
                if (trailVisible[userId]) {
                    hideTrail(userId);
                    button.textContent = 'Lihat titik sebelumnya';
                } else {
                    showTrail(userId);
                    button.textContent = 'Sembunyikan titik sebelumnya';
                }
            });
        });

        if (latestMarkers.length) {
            map.fitBounds(L.featureGroup(latestMarkers).getBounds().pad(0.2));
            if (latestMarkers.length === 1) {
                map.setZoom(14);
            }
        }
    })();
</script>
<style>
    .lokasi-time-label {
        background: #fff;
        border: 0;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
        font-size: 11px;
        font-weight: 600;
        padding: 2px 5px;
        color: #111827;
    }
    .lokasi-time-label::before { display: none; }
    .lokasi-trail-btn { cursor: pointer; }
</style>
