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
        Schema::create('log_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_date')->index();
            $table->time('check_in')->nullable();
            $table->time('checkout_out')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            
            $table->timestamps();

            $table->index(['user_id', 'check_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_attendance');
    }
};
