<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('nopol')->nullable()->after('description');
            $table->year('tahun_pembelian')->nullable()->after('nopol');
            $table->date('tgl_perpanjangan_pajak')->nullable()->after('tahun_pembelian');
            $table->date('tgl_perpanjangan_pajak_berikutnya')->nullable()->after('tgl_perpanjangan_pajak');
            $table->date('tgl_ganti_plat')->nullable()->after('tgl_perpanjangan_pajak_berikutnya');
            $table->date('tgl_ganti_plat_berikutnya')->nullable()->after('tgl_ganti_plat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn([
                'nopol',
                'tahun_pembelian',
                'tgl_perpanjangan_pajak',
                'tgl_perpanjangan_pajak_berikutnya',
                'tgl_ganti_plat',
                'tgl_ganti_plat_berikutnya',
            ]);
        });
    }
};
