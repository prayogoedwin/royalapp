<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            PositionSeeder::class,
            DivisionSeeder::class,
            EmployeeTypeSeeder::class,
            OrderStatusSeeder::class,
            UnitSeeder::class,
        ]);
    }
}
