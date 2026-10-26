<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designation', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('department_id');
            $table->string('designation_name', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designation');
    }
};
