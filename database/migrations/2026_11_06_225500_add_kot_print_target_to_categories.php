<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'kot_print_target')) {
                // kitchen = kitchen KOT printer (default)
                // billing = same printer as bill / counter
                $table->string('kot_print_target', 20)->default('kitchen')->after('gst_applicable');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'kot_print_target')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('kot_print_target');
        });
    }
};
