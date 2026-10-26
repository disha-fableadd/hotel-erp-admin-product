<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('bank_name');
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('branch_name')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_master');
    }
};
