<?php

namespace App\Support;

use Carbon\Carbon;

class UploadPath
{
    public static function dir(string $category, ?Carbon $at = null): string
    {
        $date = $at ?? now();
        $safeCategory = trim($category, '/');

        return 'uploads/' . $date->format('Y') . '/' . $date->format('m') . '/' . $safeCategory;
    }
}

