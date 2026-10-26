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
        if (Schema::hasTable('quantity_types')) {
            return;
        }

        Schema::create('quantity_types', function (Blueprint $table) {
            $table->id();
            $table->string('quantity_type');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantity_types');
    }
};
