<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_recipes')) {
            Schema::create('product_recipes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('yield_qty', 12, 4)->default(1);
                $table->text('notes')->nullable();
                $table->boolean('isDeleted')->default(0)->index();
                $table->timestamps();
                $table->unique(['product_id']);
            });
        }

        if (!Schema::hasTable('product_recipe_items')) {
            Schema::create('product_recipe_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recipe_id')->constrained('product_recipes')->cascadeOnDelete();
                $table->foreignId('ingredient_product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('quantity', 12, 4)->default(0);
                $table->foreignId('quantity_type_id')->nullable()->constrained('quantity_types')->nullOnDelete();
                $table->boolean('isDeleted')->default(0)->index();
                $table->timestamps();
                $table->index(['recipe_id', 'ingredient_product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_recipe_items');
        Schema::dropIfExists('product_recipes');
    }
};
