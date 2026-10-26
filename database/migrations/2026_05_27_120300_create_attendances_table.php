<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->string('status')->default('-')->index();
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('extraday')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Prevent duplicate Daily check-in records for same user
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
