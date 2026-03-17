<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $ambulance = Division::where('nama', 'Royal Ambulance')->first();
        $towing = Division::where('nama', 'Royal Towing')->first();

        if ($ambulance) {
            $ambulanceUnits = [
                'RA01', 'RA02', 'RA03', 'RA04', 'RA05', 'RA06', 'RA07'
            ];

            foreach ($ambulanceUnits as $code) {
                Unit::firstOrCreate([
                    'division_id' => $ambulance->id,
                    'code' => $code,
                ]);
            }
        }

        if ($towing) {
            $towingUnits = [
                'RT01', 'RT02', 'RT03', 'RT04', 'RT05', 'RT06', 'RT07', 'RT08'
            ];

            foreach ($towingUnits as $code) {
                Unit::firstOrCreate([
                    'division_id' => $towing->id,
                    'code' => $code,
                ]);
            }
        }
    }
}
