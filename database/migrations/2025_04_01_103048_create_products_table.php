<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
                $table->foreignId('quantity_type_id')->nullable()->constrained('quantity_types')->nullOnDelete();
                $table->string('name')->nullable()->index();
                $table->string('SKU')->nullable()->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->nullable()->default(0.00);
                $table->integer('quantity')->nullable()->default(0);
                $table->json('images')->nullable();
                $table->enum('availablility', ['in_stock', 'out_stock'])->default('in_stock')->index();
                $table->enum('status', ['active', 'inactive'])->default('active')->index();
                $table->boolean('isDeleted')->default(0)->index();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
