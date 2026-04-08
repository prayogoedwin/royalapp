<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_pics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_maintenance_id')->constrained('vehicle_maintenances')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees');
            $table->enum('type', ['identification', 'repair']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_pics');
    }
};
