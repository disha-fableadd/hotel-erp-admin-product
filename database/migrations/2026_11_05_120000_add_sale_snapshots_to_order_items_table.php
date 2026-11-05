<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('order_items', 'tax_id')) {
                $table->unsignedBigInteger('tax_id')->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('order_items', 'tax_name')) {
                $table->string('tax_name', 100)->nullable()->after('tax_id');
            }
            if (! Schema::hasColumn('order_items', 'tax_rate')) {
                $table->decimal('tax_rate', 8, 2)->nullable()->after('tax_name');
            }
            if (! Schema::hasColumn('order_items', 'tax_inclusive')) {
                $table->boolean('tax_inclusive')->nullable()->after('tax_rate');
            }
        });

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'tax_breakdown')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('tax_breakdown')->nullable()->after('tax_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                foreach (['product_name', 'tax_id', 'tax_name', 'tax_rate', 'tax_inclusive'] as $col) {
                    if (Schema::hasColumn('order_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'tax_breakdown')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('tax_breakdown');
            });
        }
    }
};
