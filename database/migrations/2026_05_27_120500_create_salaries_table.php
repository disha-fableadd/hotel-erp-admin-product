<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('month')->nullable()->index();
            $table->string('year')->nullable()->index();
            $table->decimal('present', 5, 2)->default(0.00);
            $table->integer('absent')->default(0);
            $table->integer('extra_present')->default(0);
            $table->decimal('advance_pay', 8, 2)->default(0.00);
            $table->decimal('salary', 8, 2)->default(0.00);
            $table->decimal('extra_amount', 8, 2)->default(0.00);
            $table->decimal('total_salary', 8, 2)->default(0.00);
            $table->decimal('old_advance_pay', 8, 2)->nullable()->default(0.00);
            $table->string('status')->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
