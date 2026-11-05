<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'menu_public_enabled')) {
                $table->boolean('menu_public_enabled')->default(0);
            }
            if (! Schema::hasColumn('settings', 'menu_public_token')) {
                $table->string('menu_public_token', 64)->nullable()->index();
            }
        });

        // Backfill tokens for existing shop settings
        if (Schema::hasColumn('settings', 'menu_public_token')) {
            $rows = \Illuminate\Support\Facades\DB::table('settings')
                ->whereNull('menu_public_token')
                ->orWhere('menu_public_token', '')
                ->select('id')
                ->get();
            foreach ($rows as $row) {
                \Illuminate\Support\Facades\DB::table('settings')->where('id', $row->id)->update([
                    'menu_public_token' => Str::lower(Str::random(32)),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }
        Schema::table('settings', function (Blueprint $table) {
            foreach (['menu_public_token', 'menu_public_enabled'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
