<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (! Schema::hasColumn('settings', 'gst_apply_mode')) {
                    if (Schema::hasColumn('settings', 'default_gst_option')) {
                        $table->string('gst_apply_mode', 20)->default('product')->after('default_gst_option');
                    } else {
                        $table->string('gst_apply_mode', 20)->default('product');
                    }
                }
                if (! Schema::hasColumn('settings', 'overall_gst_rate')) {
                    if (Schema::hasColumn('settings', 'gst_apply_mode')) {
                        $table->decimal('overall_gst_rate', 8, 2)->nullable()->after('gst_apply_mode');
                    } else {
                        $table->decimal('overall_gst_rate', 8, 2)->nullable();
                    }
                }
                if (! Schema::hasColumn('settings', 'overall_gst_name')) {
                    if (Schema::hasColumn('settings', 'overall_gst_rate')) {
                        $table->string('overall_gst_name', 100)->nullable()->after('overall_gst_rate');
                    } else {
                        $table->string('overall_gst_name', 100)->nullable();
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'gst_apply_mode')) {
                    if (Schema::hasColumn('orders', 'gst_option')) {
                        $table->string('gst_apply_mode', 20)->nullable()->after('gst_option');
                    } else {
                        $table->string('gst_apply_mode', 20)->nullable();
                    }
                }
                if (! Schema::hasColumn('orders', 'overall_gst_rate')) {
                    if (Schema::hasColumn('orders', 'gst_apply_mode')) {
                        $table->decimal('overall_gst_rate', 8, 2)->nullable()->after('gst_apply_mode');
                    } else {
                        $table->decimal('overall_gst_rate', 8, 2)->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                foreach (['overall_gst_name', 'overall_gst_rate', 'gst_apply_mode'] as $col) {
                    if (Schema::hasColumn('settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['overall_gst_rate', 'gst_apply_mode'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
