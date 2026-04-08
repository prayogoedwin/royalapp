<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_cost_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_maintenance_id')->constrained('vehicle_maintenances')->cascadeOnDelete();
            $table->enum('type', ['JASA', 'BARANG', 'LAINNYA']);
            $table->text('description')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_cost_details');
    }
};
