<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_type', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('leave_type', 100);
            $table->integer('number_of_leaves')->default(0);
            $table->tinyInteger('allow_half_day')->default(0);
            $table->tinyInteger('requires_approval')->default(1);
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_type');
    }
};
