<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'Director',
            'Head Of Production',
            'Operation Supervisor',
            'Customer Relation',
            'Finance',
            'Field Coordinator',
            'Nurse',
            'Driver',
        ];

        foreach ($positions as $positionName) {
            Position::firstOrCreate(['nama' => $positionName]);
        }
    }
}
