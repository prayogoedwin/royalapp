<?php

namespace App\Support;

use Illuminate\Support\Str;

class WhatsAppLink
{
    public static function localDisplay(?string $phone): string
    {
        return preg_replace('/\D+/', '', trim((string) $phone)) ?? '';
    }

    public static function url(?string $phone, ?string $text = null): ?string
    {
        $digits = self::internationalDigits($phone);

        if ($digits === null) {
            return null;
        }

        $url = 'https://web.whatsapp.com/send?phone='.$digits;
        if ($text !== null && trim($text) !== '') {
            $url .= '&text='.rawurlencode($text);
        }

        return $url;
    }

    public static function appUrl(?string $phone, ?string $text = null): ?string
    {
        $digits = self::internationalDigits($phone);

        if ($digits === null) {
            return null;
        }

        $url = 'https://wa.me/'.$digits;
        if ($text !== null && trim($text) !== '') {
            $url .= '?text='.rawurlencode($text);
        }

        return $url;
    }

    public static function nativeUrl(?string $phone, ?string $text = null): ?string
    {
        $digits = self::internationalDigits($phone);

        if ($digits === null) {
            return null;
        }

        $url = 'whatsapp://send?phone='.$digits;
        if ($text !== null && trim($text) !== '') {
            $url .= '&text='.rawurlencode($text);
        }

        return $url;
    }

    public static function internationalDigits(?string $phone): ?string
    {
        $digits = self::localDisplay($phone);

        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (Str::startsWith($digits, '8')) {
            $digits = '62'.$digits;
        } elseif (Str::startsWith($digits, '620')) {
            $digits = '62'.substr($digits, 3);
        }

        if (preg_match('/^62[1-9]\d{7,12}$/', $digits) !== 1) {
            return null;
        }

        return $digits;
    }
}
