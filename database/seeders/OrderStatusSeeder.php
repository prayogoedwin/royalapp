<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Pending', 'color' => 'yellow'],
            ['name' => 'Waiting', 'color' => 'gray'],
            ['name' => 'Ongoing', 'color' => 'blue'],
            ['name' => 'Done', 'color' => 'green'],
            ['name' => 'Cancelled', 'color' => 'red'],
        ];

        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(
                ['name' => $status['name']],
                ['color' => $status['color']]
            );
        }
    }
}
