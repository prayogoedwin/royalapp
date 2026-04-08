<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (! Schema::hasColumn('units', 'tgl_kir_terakhir')) {
                $table->date('tgl_kir_terakhir')->nullable()->after('tgl_ganti_plat_berikutnya');
            }
            if (! Schema::hasColumn('units', 'tgl_kir_berikutnya')) {
                $table->date('tgl_kir_berikutnya')->nullable()->after('tgl_kir_terakhir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'tgl_kir_berikutnya')) {
                $table->dropColumn('tgl_kir_berikutnya');
            }
            if (Schema::hasColumn('units', 'tgl_kir_terakhir')) {
                $table->dropColumn('tgl_kir_terakhir');
            }
        });
    }
};
