<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'default_gst_option')) {
            Schema::table('settings', function (Blueprint $table) {
                if (Schema::hasColumn('settings', 'allow_item_level_discount')) {
                    $table->string('default_gst_option', 20)->default('without')->after('allow_item_level_discount');
                } elseif (Schema::hasColumn('settings', 'low_stock')) {
                    $table->string('default_gst_option', 20)->default('without')->after('low_stock');
                } else {
                    $table->string('default_gst_option', 20)->default('without');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'default_gst_option')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('default_gst_option');
            });
        }
    }
};
