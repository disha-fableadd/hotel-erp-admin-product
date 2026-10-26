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
        Schema::create('payment_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchase_invoice')->nullOnDelete();
            $table->decimal('payment_amount', 12, 2)->default(0.00);
            $table->date('payment_date')->index();
            $table->string('payment_type')->index();
            $table->string('payment_method')->index();
            $table->decimal('cash_amount', 12, 2)->default(0.00);
            $table->decimal('upi_amount', 12, 2)->default(0.00);
            $table->integer('emi_month')->nullable()->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0.00);
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
        Schema::dropIfExists('payment_store');
    }
};
