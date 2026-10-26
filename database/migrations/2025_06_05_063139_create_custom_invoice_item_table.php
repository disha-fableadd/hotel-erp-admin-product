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
        Schema::create('custom_invoice_item', function (Blueprint $table) {
            $table->id();
            $table->string('item')->index();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0.00); // Corrected from text
            $table->enum('purchase_status', ['pending', 'completed', 'cancelled'])->default('pending')->index();
            $table->decimal('amount_total', 10, 2)->default(0.00);
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('custom_invoice_id')->nullable()->constrained('custom_invoice')->cascadeOnDelete(); // Normalized
            $table->string('payment_status')->default('unpaid')->index();
            $table->boolean('isDeleted')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['custom_invoice_id', 'item']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_invoice_item');
    }
};
