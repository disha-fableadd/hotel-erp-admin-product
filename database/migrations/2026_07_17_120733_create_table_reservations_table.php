<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('table_no')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Customer info (walk-in, no user account required)
            $table->string('customer_name', 255);
            $table->string('customer_phone', 20)->nullable();

            // Reservation slot
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->unsignedSmallInteger('guests')->default(1);

            // Lifecycle: pending → confirmed → completed | cancelled
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->index();

            // Filled when guest arrives and a dine-in order is created
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
    }
};
