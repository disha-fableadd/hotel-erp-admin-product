<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name', 100);
                $table->string('sku', 100)->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->boolean('isDeleted')->default(0)->index();
                $table->timestamps();
                $table->index(['product_id', 'isDeleted']);
            });
        }

        if (!Schema::hasTable('product_addons')) {
            Schema::create('product_addons', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->decimal('price', 12, 2)->default(0);
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('isDeleted')->default(0)->index();
                $table->timestamps();
            });

            if (Schema::hasTable('branches')) {
                try {
                    Schema::table('product_addons', function (Blueprint $table) {
                        $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        if (!Schema::hasTable('product_addon_product')) {
            Schema::create('product_addon_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('addon_id')->constrained('product_addons')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['product_id', 'addon_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_addon_product');
        Schema::dropIfExists('product_addons');
        Schema::dropIfExists('product_variants');
    }
};
