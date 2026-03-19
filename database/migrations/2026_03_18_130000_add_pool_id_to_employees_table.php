<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'pool_id')) {
                $table->foreignId('pool_id')->nullable()->constrained('pools')->nullOnDelete()->after('division_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'pool_id')) {
                $table->dropForeign(['pool_id']);
                $table->dropColumn('pool_id');
            }
        });
    }
};

