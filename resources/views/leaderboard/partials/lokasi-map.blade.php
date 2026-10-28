@php
    $points = $points ?? [];
@endphp

<div class="mt-5">
    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('Peta lokasi hari ini') }}</h3>
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">
        {{ __('Titik terakhir tiap driver berwarna merah, titik sebelumnya hitam. Klik titik untuk lihat jam dan buka Google Maps.') }}
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
        <span class="inline-flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-red-600"></span> {{ __('Lokasi terakhir') }}</span>
        <span class="inline-flex items-center gap-1.5"><span class="inline-block w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-gray-100"></span> {{ __('Lokasi sebelumnya') }}</span>
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

        var grouped = {};
        var markers = [];

        points.forEach(function (point) {
            var color = point.is_latest ? '#dc2626' : '#111827';
            var marker = L.circleMarker([point.latitude, point.longitude], {
                radius: point.is_latest ? 9 : 7,
                color: '#ffffff',
                weight: 2,
                fillColor: color,
                fillOpacity: 1
            }).addTo(map);

            marker.bindTooltip(point.waktu_label || '', {
                permanent: true,
                direction: 'top',
                offset: [0, -8],
                className: 'lokasi-time-label'
            });

            var mapsLink = point.maps_url
                ? '<a href="' + escapeHtml(point.maps_url) + '" target="_blank" rel="noopener">Lihat di Google Maps</a>'
                : '';
            marker.bindPopup(
                '<div class="text-sm"><strong>' + escapeHtml(point.name || 'Driver') + '</strong><br>' +
                escapeHtml(point.tanggal_label || '') + '<br>' + mapsLink + '</div>'
            );

            markers.push(marker);
            if (!grouped[point.user_id]) {
                grouped[point.user_id] = [];
            }
            grouped[point.user_id].push([point.latitude, point.longitude]);
        });

        Object.keys(grouped).forEach(function (userId) {
            if (grouped[userId].length > 1) {
                L.polyline(grouped[userId], { color: '#6b7280', weight: 2, opacity: 0.7 }).addTo(map);
            }
        });

        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.2));
        if (points.length === 1) {
            map.setZoom(14);
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
</style>
