<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('pool_id')->nullable()->constrained('pools')->nullOnDelete();

            // Shift date (tanggal saat masuk, sehingga pulang yang melewati tengah malam tetap terkait shift ini)
            $table->date('tanggal');

            // Jadwal boleh null di awal (admin bisa isi belakangan)
            $table->time('jadwal_jam_masuk')->nullable();
            $table->time('jadwal_jam_pulang')->nullable();

            // Realisasi
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            $table->string('status')->nullable();
            $table->boolean('is_overnight')->default(false);

            $table->integer('telat_menit')->nullable();
            $table->integer('pulang_awal_menit')->nullable();

            // Lokasi masuk
            $table->decimal('lat_masuk', 10, 8)->nullable();
            $table->decimal('lng_masuk', 11, 8)->nullable();
            $table->decimal('jarak_lokasi_masuk', 10, 2)->nullable();

            // Lokasi pulang
            $table->decimal('lat_pulang', 10, 8)->nullable();
            $table->decimal('lng_pulang', 11, 8)->nullable();
            $table->decimal('jarak_lokasi_pulang', 10, 2)->nullable();

            // Foto bukti
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();

            $table->json('device_info')->nullable();
            $table->text('keterangan')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};

