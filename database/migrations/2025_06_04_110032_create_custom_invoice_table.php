<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('products');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid', 10, 2)->nullable()->default(0.00);
            $table->decimal('discount', 10, 2)->nullable()->default(0.00);
            $table->decimal('shipping', 10, 2)->nullable()->default(0.00);
            $table->text('taxes')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->string('status')->default('pending')->index();
            $table->boolean('isDeleted')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_invoice');
    }
};
