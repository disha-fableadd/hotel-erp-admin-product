<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_calendar', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('title', 255);
            $table->date('holiday_date');
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_calendar');
    }
};
