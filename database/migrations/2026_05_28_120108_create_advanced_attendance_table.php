<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('branch_id')->nullable();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->string('check_in_location_name', 255)->nullable();
            $table->string('check_in_ip_address', 45)->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->string('check_out_location_name', 255)->nullable();
            $table->string('check_out_ip_address', 45)->nullable();
            $table->time('meal_break')->default('00:30:00');
            $table->time('work_hours')->nullable();
            $table->time('overtime')->nullable();
            $table->enum('status', ['absent', 'present', 'half-day']);
            $table->string('checkin_method', 50)->default('manual');
            $table->tinyInteger('is_late')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
