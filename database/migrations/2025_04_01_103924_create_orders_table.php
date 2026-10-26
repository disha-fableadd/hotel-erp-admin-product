<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->string('tax_id')->nullable()->index();
            $table->decimal('total_amount', 12, 2)->nullable()->default(0.00);
            $table->decimal('remaining_amount', 12, 2)->nullable()->default(0.00);
            $table->string('payment_status')->default('pending')->index();
            $table->string('payment_method')->default('cash')->index();
            $table->string('order_invoice')->nullable();
            $table->unsignedBigInteger('table_no')->nullable()->default(0);
            $table->string('type')->default('dinein')->index();
            $table->integer('table_status')->default(1)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('isDeleted')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
