<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['nama' => 'Office'],
            ['nama' => 'Royal Ambulance'],
            ['nama' => 'Royal Towing'],
        ];

        foreach ($divisions as $division) {
            Division::firstOrCreate($division);
        }
    }
}
