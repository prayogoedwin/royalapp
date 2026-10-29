<?php

return [
    'company_name' => env('ROYAL_COMPANY_NAME', 'PT. ROYAL MEDICAL SERVICES'),
    'company_phone' => env('ROYAL_COMPANY_PHONE', '0811 7000 9000 (24 hours)'),
    'company_email' => env('ROYAL_COMPANY_EMAIL', 'Royal.emsindonesia@gmail.com'),
    'brand_blue' => env('ROYAL_BRAND_BLUE', '#4CA7DC'),
    'invoice_brands' => [
        'ambulance' => [
            'name' => 'Royal Ambulance',
            'logo' => 'images/invoice/royal-ambulance.png',
            'address' => 'Jl. Sunter Karya Selatan V Blok HA No. 3A',
            'city' => 'Jakarta Utara',
            'phone' => env('ROYAL_AMBULANCE_PHONE', '+62811131000'),
            'email' => env('ROYAL_AMBULANCE_EMAIL', 'royal.emsindonesia@gmail.com'),
            'banks' => [
                [
                    'bank' => 'BCA',
                    'number' => '534-525-826-1',
                    'name' => 'Royal Medical Services PT',
                    'line' => 'BCA 534-525-826-1 an Royal Medical Services PT',
                ],
                [
                    'bank' => 'Mandiri',
                    'number' => '1210019942899',
                    'name' => 'PT Royal Medical Services',
                    'line' => 'Mandiri 1210019942899 an PT Royal Medical Services',
                ],
            ],
        ],
        'towing' => [
            'name' => 'Royal Towing',
            'logo' => 'images/invoice/royal-towing.png',
            'address' => 'Jl. Sunter Karya Selatan V Blok HA No. 3A',
            'city' => 'Jakarta Utara',
            'phone' => env('ROYAL_TOWING_PHONE', '+6281170009000'),
            'email' => env('ROYAL_TOWING_EMAIL', 'royaltowing.id@gmail.com'),
            'banks' => [
                [
                    'bank' => 'BCA',
                    'number' => '5345271144',
                    'name' => 'Royal Medical Services PT',
                    'line' => 'BCA 5345271144 Royal Medical Services PT',
                ],
            ],
        ],
    ],
];
