<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE order_vehicle_issues MODIFY issue_category ENUM('mechanical', 'body', 'interior', 'safety', 'medical_equipment', 'other') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE order_vehicle_issues MODIFY issue_category ENUM('mechanical', 'body', 'interior', 'safety', 'medical_equipment') NOT NULL");
    }
};
