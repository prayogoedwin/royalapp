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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('key')->nullable()->after('name');
        });

        $roles = DB::table('roles')->select('id', 'name', 'key')->get();
        $used = [];

        foreach ($roles as $role) {
            $base = Str::slug((string) $role->name, '_') ?: 'role';
            $key = $base;
            $suffix = 2;

            while (in_array($key, $used, true)) {
                $key = $base.'_'.$suffix;
                $suffix++;
            }

            $used[] = $key;

            DB::table('roles')->where('id', $role->id)->update(['key' => $key]);
        }

        Schema::table('roles', function (Blueprint $table) {
            $table->string('key')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
