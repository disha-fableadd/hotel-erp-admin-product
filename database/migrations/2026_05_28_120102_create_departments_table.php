<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('department_name', 100);
            $table->boolean('enable_overtime')->default(0);
            $table->string('overtime_rate_type', 255)->default('multiplier');
            $table->decimal('overtime_multiplier', 8, 2)->default(1.00);
            $table->integer('min_overtime_count_in_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department');
    }
};
