<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'variant_id')) {
                    $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('order_items', 'addons')) {
                    $table->json('addons')->nullable()->after('variant_id');
                }
                if (!Schema::hasColumn('order_items', 'unit_price')) {
                    $table->decimal('unit_price', 12, 2)->nullable()->after('price');
                }
            });

            if (Schema::hasTable('product_variants') && Schema::hasColumn('order_items', 'variant_id')) {
                try {
                    Schema::table('order_items', function (Blueprint $table) {
                        $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        if (Schema::hasTable('product_inventory')) {
            Schema::table('product_inventory', function (Blueprint $table) {
                if (!Schema::hasColumn('product_inventory', 'reference_type')) {
                    $table->string('reference_type', 50)->nullable()->index()->after('type');
                }
                if (!Schema::hasColumn('product_inventory', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable()->index()->after('reference_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                foreach (['variant_id', 'addons', 'unit_price'] as $column) {
                    if (Schema::hasColumn('order_items', $column)) {
                        try {
                            if ($column === 'variant_id') {
                                $table->dropForeign(['variant_id']);
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('product_inventory')) {
            Schema::table('product_inventory', function (Blueprint $table) {
                foreach (['reference_type', 'reference_id'] as $column) {
                    if (Schema::hasColumn('product_inventory', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
