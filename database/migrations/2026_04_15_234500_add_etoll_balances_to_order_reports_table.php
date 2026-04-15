<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('order_reports', 'saldo_etoll_before')) {
                $table->decimal('saldo_etoll_before', 15, 2)->nullable()->after('km_akhir');
            }
            if (!Schema::hasColumn('order_reports', 'saldo_etoll_after')) {
                $table->decimal('saldo_etoll_after', 15, 2)->nullable()->after('saldo_etoll_before');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_reports', function (Blueprint $table) {
            if (Schema::hasColumn('order_reports', 'saldo_etoll_after')) {
                $table->dropColumn('saldo_etoll_after');
            }
            if (Schema::hasColumn('order_reports', 'saldo_etoll_before')) {
                $table->dropColumn('saldo_etoll_before');
            }
        });
    }
};
