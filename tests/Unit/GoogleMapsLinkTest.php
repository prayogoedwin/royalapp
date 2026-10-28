<?php

namespace Tests\Unit;

use App\Support\GoogleMapsLink;
use PHPUnit\Framework\TestCase;

class GoogleMapsLinkTest extends TestCase
{
    public function test_builds_google_maps_search_url(): void
    {
        $this->assertSame(
            'https://www.google.com/maps/search/?api=1&query=-6.2088%2C106.8456',
            GoogleMapsLink::url(-6.2088, 106.8456)
        );
    }

    public function test_returns_null_url_when_coordinates_missing(): void
    {
        $this->assertNull(GoogleMapsLink::url(null, 106.8));
        $this->assertNull(GoogleMapsLink::url(-6.2, null));
    }

    public function test_parses_at_coordinates_from_maps_url(): void
    {
        $this->assertSame(
            [-6.2088, 106.8456],
            GoogleMapsLink::parse('https://www.google.com/maps/place/Monas/@-6.2088,106.8456,17z')
        );
    }

    public function test_parses_query_coordinates(): void
    {
        $this->assertSame(
            [-6.2, 106.8],
            GoogleMapsLink::parse('https://www.google.com/maps?q=-6.2,106.8')
        );
    }

    public function test_parses_pasted_lat_lng(): void
    {
        $this->assertSame([-6.175392, 106.827153], GoogleMapsLink::parse('-6.175392, 106.827153'));
    }

    public function test_rejects_invalid_values(): void
    {
        $this->assertNull(GoogleMapsLink::parse(''));
        $this->assertNull(GoogleMapsLink::parse('https://maps.app.goo.gl/abc'));
        $this->assertNull(GoogleMapsLink::parse('99.0,10.0'));
    }
}
