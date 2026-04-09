<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeSampleSeeder extends Seeder
{
    public function run(): void
    {
        $driverPosition = Position::query()->whereRaw('LOWER(nama) = ?', ['driver'])->first();
        $nursePosition = Position::query()->whereRaw('LOWER(nama) = ?', ['nurse'])->first();
        $division = Division::query()->whereRaw('LOWER(nama) = ?', ['royal ambulance'])->first()
            ?? Division::query()->first();
        $employeeType = EmployeeType::query()->whereRaw('LOWER(nama) = ?', ['permanent'])->first()
            ?? EmployeeType::query()->first();

        if (!$driverPosition || !$nursePosition || !$division || !$employeeType) {
            $this->command?->warn('Master data belum lengkap. Jalankan PositionSeeder, DivisionSeeder, dan EmployeeTypeSeeder dulu.');
            return;
        }

        $faker = fake('id_ID');
        $today = now()->toDateString();

        $definitions = [
            ['role' => 'driver', 'count' => 5, 'position_id' => $driverPosition->id],
            ['role' => 'nurse', 'count' => 2, 'position_id' => $nursePosition->id],
        ];

        foreach ($definitions as $definition) {
            for ($i = 1; $i <= $definition['count']; $i++) {
                $label = strtoupper($definition['role']) . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $nik = 'SAMPLE-' . $label;
                $fullName = $faker->name();
                $safeEmail = Str::slug($fullName, '.') . '.' . strtolower($definition['role']) . $i . '@example.com';

                $user = User::updateOrCreate(
                    ['email' => $safeEmail],
                    [
                        'name' => $fullName,
                        'password' => Hash::make('password'),
                    ]
                );

                Employee::updateOrCreate(
                    ['nik' => $nik],
                    [
                        'user_id' => $user->id,
                        'position_id' => $definition['position_id'],
                        'division_id' => $division->id,
                        'employee_type_id' => $employeeType->id,
                        'full_name' => $fullName,
                        'phone' => $faker->phoneNumber(),
                        'address' => $faker->address(),
                        'birth_date' => $faker->date(),
                        'status' => 'active',
                        'join_date' => $today,
                    ]
                );
            }
        }

        $this->command?->info('Sample employee created: 5 Driver + 2 Nurse.');
    }
}
