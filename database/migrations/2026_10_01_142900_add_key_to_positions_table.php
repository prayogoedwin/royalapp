<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('key')->nullable()->after('nama');
        });

        $positions = DB::table('positions')->select('id', 'nama', 'key')->get();
        $used = [];

        foreach ($positions as $position) {
            $base = Str::slug((string) $position->nama, '_') ?: 'position';
            $key = $base;
            $suffix = 2;

            while (in_array($key, $used, true)) {
                $key = $base.'_'.$suffix;
                $suffix++;
            }

            $used[] = $key;
            DB::table('positions')->where('id', $position->id)->update(['key' => $key]);
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->string('key')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
