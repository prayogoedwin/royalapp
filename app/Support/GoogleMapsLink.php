<?php

namespace App\Support;

class GoogleMapsLink
{
    /**
     * @return array{0: float, 1: float}|null
     */
    public static function parse(?string $value): ?array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $patterns = [
            '/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/',
            '/[?&](?:q|query|ll|destination|center|daddr)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/i',
            '/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/',
            '/^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches) !== 1) {
                continue;
            }

            $coords = self::normalize($matches[1], $matches[2]);
            if ($coords !== null) {
                return $coords;
            }
        }

        return null;
    }

    public static function url(?float $latitude, ?float $longitude): ?string
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(
            self::format($latitude).','.self::format($longitude)
        );
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private static function normalize(string $latitude, string $longitude): ?array
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [$lat, $lng];
    }

    private static function format(float $value): string
    {
        return rtrim(rtrim(sprintf('%.7f', $value), '0'), '.');
    }
}
