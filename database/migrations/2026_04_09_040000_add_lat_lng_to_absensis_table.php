<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            if (!Schema::hasColumn('absensis', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable()->after('pulang_awal_menit');
            }
            if (!Schema::hasColumn('absensis', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable()->after('lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            if (Schema::hasColumn('absensis', 'lng')) {
                $table->dropColumn('lng');
            }
            if (Schema::hasColumn('absensis', 'lat')) {
                $table->dropColumn('lat');
            }
        });
    }
};
