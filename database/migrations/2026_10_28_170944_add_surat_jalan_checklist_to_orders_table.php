<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('surat_jalan_checklist')->nullable()->after('notes');
            $table->string('surat_jalan_public_token', 64)->nullable()->unique()->after('surat_jalan_checklist');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['surat_jalan_public_token']);
            $table->dropColumn(['surat_jalan_checklist', 'surat_jalan_public_token']);
        });
    }
};
