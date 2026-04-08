<?php

namespace App\Support;

class OrderOptions
{
    public static function paymentMethods(): array
    {
        return [
            'CASH' => 'CASH',
            'BANK TRANSFER' => 'BANK TRANSFER',
            'LAINNYA' => 'LAINNYA',
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            'PAID' => 'PAID',
            'UNPAID' => 'UNPAID',
            'FREE' => 'FREE',
        ];
    }
}
