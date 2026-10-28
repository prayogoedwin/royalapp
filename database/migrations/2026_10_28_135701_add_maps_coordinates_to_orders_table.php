<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('pickup_latitude', 10, 7)->nullable()->after('pickup_address');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->after('pickup_latitude');
            $table->text('pickup_maps_url')->nullable()->after('pickup_longitude');
            $table->decimal('destination_latitude', 10, 7)->nullable()->after('destination_address');
            $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            $table->text('destination_maps_url')->nullable()->after('destination_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_latitude',
                'pickup_longitude',
                'pickup_maps_url',
                'destination_latitude',
                'destination_longitude',
                'destination_maps_url',
            ]);
        });
    }
};
