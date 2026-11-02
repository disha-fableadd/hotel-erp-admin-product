<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'product_kind')) {
                $table->string('product_kind', 32)->default('general')->index()->after('status');
            }
            if (!Schema::hasColumn('products', 'food_type')) {
                $table->string('food_type', 32)->default('na')->after('product_kind');
            }
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'tax_id')) {
                $table->unsignedBigInteger('tax_id')->nullable()->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'tax_inclusive')) {
                $table->boolean('tax_inclusive')->default(0)->after('tax_id');
            }
            if (!Schema::hasColumn('products', 'is_pos_available')) {
                $table->boolean('is_pos_available')->default(1)->index()->after('tax_inclusive');
            }
            if (!Schema::hasColumn('products', 'track_inventory')) {
                $table->boolean('track_inventory')->default(1)->after('is_pos_available');
            }
            if (!Schema::hasColumn('products', 'recipe_required')) {
                $table->boolean('recipe_required')->default(0)->after('track_inventory');
            }
            if (!Schema::hasColumn('products', 'kitchen_station')) {
                $table->string('kitchen_station', 100)->nullable()->after('recipe_required');
            }
            if (!Schema::hasColumn('products', 'prep_time_minutes')) {
                $table->unsignedSmallInteger('prep_time_minutes')->nullable()->after('kitchen_station');
            }
            if (!Schema::hasColumn('products', 'display_order')) {
                $table->integer('display_order')->default(0)->index()->after('prep_time_minutes');
            }
            if (!Schema::hasColumn('products', 'min_stock')) {
                $table->decimal('min_stock', 12, 4)->nullable()->after('quantity');
            }
        });

        // Optional FK to taxes — only if taxes table exists
        if (Schema::hasTable('taxes') && Schema::hasColumn('products', 'tax_id')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->foreign('tax_id')->references('id')->on('taxes')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // FK may already exist
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'product_kind',
                'food_type',
                'cost_price',
                'tax_id',
                'tax_inclusive',
                'is_pos_available',
                'track_inventory',
                'recipe_required',
                'kitchen_station',
                'prep_time_minutes',
                'display_order',
                'min_stock',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    try {
                        if ($column === 'tax_id') {
                            $table->dropForeign(['tax_id']);
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
