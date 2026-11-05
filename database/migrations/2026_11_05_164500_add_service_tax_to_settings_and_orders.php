<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (! Schema::hasColumn('settings', 'service_tax_enabled')) {
                    $table->boolean('service_tax_enabled')->default(0)->after('overall_gst_name');
                }
                if (! Schema::hasColumn('settings', 'service_tax_rate')) {
                    $table->decimal('service_tax_rate', 8, 2)->nullable()->after('service_tax_enabled');
                }
                if (! Schema::hasColumn('settings', 'service_tax_name')) {
                    $table->string('service_tax_name', 100)->nullable()->after('service_tax_rate');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'service_tax_rate')) {
                    $table->decimal('service_tax_rate', 8, 2)->nullable()->after('overall_gst_rate');
                }
                if (! Schema::hasColumn('orders', 'service_tax_amount')) {
                    $table->decimal('service_tax_amount', 12, 2)->nullable()->after('service_tax_rate');
                }
                if (! Schema::hasColumn('orders', 'service_tax_name')) {
                    $table->string('service_tax_name', 100)->nullable()->after('service_tax_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                foreach (['service_tax_name', 'service_tax_rate', 'service_tax_enabled'] as $col) {
                    if (Schema::hasColumn('settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                foreach (['service_tax_name', 'service_tax_amount', 'service_tax_rate'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
