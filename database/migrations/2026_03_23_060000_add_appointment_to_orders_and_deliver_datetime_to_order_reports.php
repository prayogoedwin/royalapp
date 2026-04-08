<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'appointment')) {
                $table->string('appointment')->nullable()->after('customer_phone');
            }
        });

        Schema::table('order_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('order_reports', 'deliver_datetime')) {
                $table->dateTime('deliver_datetime')->nullable()->after('km_akhir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'appointment')) {
                $table->dropColumn('appointment');
            }
        });

        Schema::table('order_reports', function (Blueprint $table) {
            if (Schema::hasColumn('order_reports', 'deliver_datetime')) {
                $table->dropColumn('deliver_datetime');
            }
        });
    }
};
