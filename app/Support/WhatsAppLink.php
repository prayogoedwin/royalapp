<?php

namespace App\Support;

use Illuminate\Support\Str;

class WhatsAppLink
{
    public static function url(?string $phone): ?string
    {
        $digits = self::internationalDigits($phone);

        if ($digits === null) {
            return null;
        }

        return 'https://web.whatsapp.com/send?phone='.$digits;
    }

    public static function internationalDigits(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $phone)) ?? '';

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
