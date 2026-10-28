@php
    $required = $required ?? true;
    $addressValue = old($prefix.'_address', $address ?? '');
    $latitudeValue = old($prefix.'_latitude', $latitude ?? '');
    $longitudeValue = old($prefix.'_longitude', $longitude ?? '');
    $mapsUrlValue = old($prefix.'_maps_url', $mapsUrl ?? '');
@endphp

<div
    class="space-y-3"
    x-data="placeSearchField({
        searchUrl: @js(route('orders.places.search')),
        detailsUrl: @js(route('orders.places.details')),
        address: @js((string) $addressValue),
        latitude: @js($latitudeValue !== '' && $latitudeValue !== null ? (string) $latitudeValue : ''),
        longitude: @js($longitudeValue !== '' && $longitudeValue !== null ? (string) $longitudeValue : ''),
        mapsUrl: @js((string) $mapsUrlValue),
    })"
>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
        <textarea
            name="{{ $prefix }}_address"
            x-model="address"
            @if($required) required @endif
            rows="3"
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
        ></textarea>
        @error($prefix.'_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Cari alamat (seperti Google)') }}</label>
        <input
            type="search"
            x-model="query"
            @input.debounce.400ms="search()"
            @focus="open = results.length > 0"
            @keydown.escape.window="open = false"
            autocomplete="off"
            placeholder="Ketik nama tempat atau alamat..."
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
        >
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Pilih hasil pencarian untuk mengisi alamat dan koordinat. Tidak wajib.') }}</p>
        <div
            x-cloak
            x-show="open"
            class="absolute z-20 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg max-h-56 overflow-y-auto"
        >
            <template x-if="loading">
                <div class="px-3 py-2 text-sm text-gray-500">{{ __('Mencari...') }}</div>
            </template>
            <template x-for="item in results" :key="item.id">
                <button
                    type="button"
                    class="block w-full text-left px-3 py-2 text-sm text-gray-800 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-700"
                    @click="choose(item)"
                    x-text="item.label"
                ></button>
            </template>
            <div x-show="!loading && results.length === 0" class="px-3 py-2 text-sm text-gray-500">{{ __('Tidak ada hasil.') }}</div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Link Google Maps (opsional)') }}</label>
        <input
            type="text"
            name="{{ $prefix }}_maps_url"
            x-model="mapsUrl"
            @change="parsePastedLink()"
            placeholder="https://maps.google.com/..."
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
        >
        @error($prefix.'_maps_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <a
            x-cloak
            x-show="mapsUrl"
            :href="mapsUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-block mt-1 text-sm text-blue-600 dark:text-blue-400 hover:underline"
        >{{ __('Buka di Google Maps') }}</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Latitude') }}</label>
            <input
                type="text"
                inputmode="decimal"
                name="{{ $prefix }}_latitude"
                x-model="latitude"
                placeholder="-6.2000000"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
            >
            @error($prefix.'_latitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Longitude') }}</label>
            <input
                type="text"
                inputmode="decimal"
                name="{{ $prefix }}_longitude"
                x-model="longitude"
                placeholder="106.8166667"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm"
            >
            @error($prefix.'_longitude')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('placeSearchField', (config) => ({
                searchUrl: config.searchUrl,
                detailsUrl: config.detailsUrl,
                query: '',
                address: config.address || '',
                latitude: config.latitude || '',
                longitude: config.longitude || '',
                mapsUrl: config.mapsUrl || '',
                results: [],
                open: false,
                loading: false,

                async search() {
                    const q = (this.query || '').trim();
                    if (q.length < 3) {
                        this.results = [];
                        this.open = false;
                        return;
                    }
                    this.loading = true;
                    this.open = true;
                    try {
                        const response = await fetch(this.searchUrl + '?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const json = await response.json();
                        this.results = Array.isArray(json.data) ? json.data : [];
                    } catch (e) {
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },

                async choose(item) {
                    this.open = false;
                    this.query = item.label || '';
                    let place = item;
                    if (item.needs_details && item.id) {
                        const response = await fetch(this.detailsUrl + '?place_id=' + encodeURIComponent(item.id), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const json = await response.json();
                        if (json.status && json.data) {
                            place = json.data;
                        }
                    }
                    this.applyPlace(place);
                },

                applyPlace(place) {
                    if (place.address) {
                        this.address = place.address;
                    } else if (place.label) {
                        this.address = place.label;
                    }
                    this.latitude = place.latitude != null ? String(place.latitude) : this.latitude;
                    this.longitude = place.longitude != null ? String(place.longitude) : this.longitude;
                    this.mapsUrl = place.maps_url || this.buildMapsUrl(this.latitude, this.longitude);
                },

                parsePastedLink() {
                    const value = (this.mapsUrl || '').trim();
                    const match = value.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/)
                        || value.match(/[?&](?:q|query|ll|destination)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i)
                        || value.match(/^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/);
                    if (match) {
                        this.latitude = match[1];
                        this.longitude = match[2];
                    }
                    if (this.latitude && this.longitude && !this.mapsUrl) {
                        this.mapsUrl = this.buildMapsUrl(this.latitude, this.longitude);
                    }
                },

                buildMapsUrl(lat, lng) {
                    if (!lat || !lng) {
                        return '';
                    }
                    return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(lat + ',' + lng);
                },
            }));
        });
    </script>
@endonce
