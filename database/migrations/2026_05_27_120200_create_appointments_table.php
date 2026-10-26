<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->unsignedBigInteger('vehicle_brand')->nullable();
            $table->unsignedBigInteger('vehicle_model')->nullable();
            $table->date('appointment_date')->nullable()->index();
            $table->time('appointment_time')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->index();
            $table->text('remarks')->nullable();
            $table->boolean('is_deleted')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
