<?php

namespace Database\Seeders;

use App\Models\EmployeeType;
use Illuminate\Database\Seeder;

class EmployeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $employeeTypes = [
            'Permanent',
            'Contract',
            'Freelance',
            'Intern',
        ];

        foreach ($employeeTypes as $typeName) {
            EmployeeType::firstOrCreate(['nama' => $typeName]);
        }
    }
}
