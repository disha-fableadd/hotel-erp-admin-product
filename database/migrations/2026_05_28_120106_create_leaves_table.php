<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('firstname', 255)->default('');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('no_of_day');
            $table->text('reason');
            $table->integer('leave_id');
            $table->integer('created_by');
            $table->integer('user_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('paid_days')->default(0);
            $table->integer('unpaid_days')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
