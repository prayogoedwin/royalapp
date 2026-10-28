<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_terkini', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->dateTime('waktu_hp');
            $table->timestamps();

            $table->index(['employee_id', 'waktu_hp']);
            $table->index(['user_id', 'waktu_hp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_terkini');
    }
};
