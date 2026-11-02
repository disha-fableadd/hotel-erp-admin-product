<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_ledger_entries')) {
            Schema::create('stock_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->enum('direction', ['in', 'out']);
                $table->decimal('quantity', 14, 4)->default(0);
                $table->decimal('unit_cost', 14, 4)->nullable();
                $table->decimal('balance_after', 14, 4)->nullable();
                $table->string('entry_type', 50)->index(); // Purchase, Sale, RecipeConsume, Adjust, Reverse
                $table->string('reference_type', 50)->nullable()->index();
                $table->unsignedBigInteger('reference_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['product_id', 'created_at']);
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'item_discount_percent')) {
                    $table->decimal('item_discount_percent', 8, 2)->default(0)->after('price');
                }
                if (!Schema::hasColumn('order_items', 'item_discount_amount')) {
                    $table->decimal('item_discount_amount', 12, 2)->default(0)->after('item_discount_percent');
                }
            });
        }

        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'allow_item_level_discount')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('allow_item_level_discount')->default(0)->after('low_stock');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger_entries');

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                foreach (['item_discount_percent', 'item_discount_amount'] as $col) {
                    if (Schema::hasColumn('order_items', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'allow_item_level_discount')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('allow_item_level_discount');
            });
        }
    }
};
