<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('item')->index();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('purchase_status', ['pending', 'completed', 'cancelled'])->default('pending')->index();
            $table->decimal('amount_total', 10, 2)->default(0.00);
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quantity_type_id')->nullable()->constrained('quantity_types')->nullOnDelete();
            $table->string('invoice_id')->nullable()->index();
            $table->string('payment_status')->default('unpaid')->index();
            $table->boolean('isDeleted')->default(0)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
