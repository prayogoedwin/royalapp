<?php

namespace App\Services;

use App\Support\GoogleMapsLink;
use Illuminate\Support\Facades\Http;
use Throwable;

class PlaceSearchService
{
    /**
     * @return list<array{id: string, label: string, address: ?string, latitude: ?float, longitude: ?float, maps_url: ?string, needs_details: bool}>
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 3) {
            return [];
        }

        $key = config('services.google.maps_key');
        if (filled($key)) {
            try {
                $google = $this->searchGoogle($query, (string) $key);
                if ($google !== null) {
                    return $google;
                }
            } catch (Throwable) {
                // Fall through to Nominatim when Google is unavailable.
            }
        }

        return $this->searchNominatim($query);
    }

    /**
     * @return array{id: string, label: string, address: ?string, latitude: ?float, longitude: ?float, maps_url: ?string, needs_details: bool}|null
     */
    public function details(string $placeId): ?array
    {
        $placeId = trim($placeId);
        if ($placeId === '' || str_starts_with($placeId, 'osm:')) {
            return null;
        }

        $key = config('services.google.maps_key');
        if (! filled($key)) {
            return null;
        }

        $response = Http::timeout(8)
            ->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'formatted_address,geometry,name,url',
                'language' => 'id',
                'key' => $key,
            ]);

        if (! $response->ok() || $response->json('status') !== 'OK') {
            return null;
        }

        $result = $response->json('result', []);
        $lat = data_get($result, 'geometry.location.lat');
        $lng = data_get($result, 'geometry.location.lng');
        $address = $result['formatted_address'] ?? $result['name'] ?? null;
        $mapsUrl = $result['url'] ?? GoogleMapsLink::url(
            is_numeric($lat) ? (float) $lat : null,
            is_numeric($lng) ? (float) $lng : null,
        );

        return [
            'id' => $placeId,
            'label' => (string) ($result['name'] ?? $address ?? $placeId),
            'address' => $address,
            'latitude' => is_numeric($lat) ? (float) $lat : null,
            'longitude' => is_numeric($lng) ? (float) $lng : null,
            'maps_url' => $mapsUrl,
            'needs_details' => false,
        ];
    }

    /**
     * @return list<array{id: string, label: string, address: ?string, latitude: ?float, longitude: ?float, maps_url: ?string, needs_details: bool}>|null
     */
    private function searchGoogle(string $query, string $key): ?array
    {
        $response = Http::timeout(8)
            ->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                'input' => $query,
                'language' => 'id',
                'components' => 'country:id',
                'key' => $key,
            ]);

        if (! $response->ok()) {
            return null;
        }

        $status = (string) $response->json('status');
        if (in_array($status, ['REQUEST_DENIED', 'OVER_QUERY_LIMIT', 'UNKNOWN_ERROR'], true)) {
            return null;
        }

        if ($status === 'ZERO_RESULTS') {
            return [];
        }

        if ($status !== 'OK') {
            return null;
        }

        return collect($response->json('predictions', []))
            ->map(function (array $prediction): array {
                $label = (string) ($prediction['description'] ?? '');

                return [
                    'id' => (string) ($prediction['place_id'] ?? ''),
                    'label' => $label,
                    'address' => $label,
                    'latitude' => null,
                    'longitude' => null,
                    'maps_url' => null,
                    'needs_details' => true,
                ];
            })
            ->filter(fn (array $item): bool => $item['id'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, label: string, address: ?string, latitude: ?float, longitude: ?float, maps_url: ?string, needs_details: bool}>
     */
    private function searchNominatim(string $query): array
    {
        $response = Http::timeout(8)
            ->withHeaders([
                'User-Agent' => 'RoyalApp/1.0 (https://royalambulance.my.id)',
                'Accept-Language' => 'id',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $query,
                'format' => 'jsonv2',
                'addressdetails' => 0,
                'limit' => 6,
                'countrycodes' => 'id',
            ]);

        if (! $response->ok()) {
            return [];
        }

        return collect($response->json())
            ->map(function (array $hit): ?array {
                $lat = $hit['lat'] ?? null;
                $lng = $hit['lon'] ?? null;
                if (! is_numeric($lat) || ! is_numeric($lng)) {
                    return null;
                }

                $label = (string) ($hit['display_name'] ?? '');

                return [
                    'id' => 'osm:'.($hit['place_id'] ?? $lat.','.$lng),
                    'label' => $label,
                    'address' => $label,
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lng,
                    'maps_url' => GoogleMapsLink::url((float) $lat, (float) $lng),
                    'needs_details' => false,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
