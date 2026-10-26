<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->nullable()->default(0.00);
            $table->enum('status', ['pending', 'cleared'])->default('pending')->index();
            $table->date('date')->index();
            $table->string('method')->index();
            $table->string('reason')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->boolean('isDeleted')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_payments');
    }
};
