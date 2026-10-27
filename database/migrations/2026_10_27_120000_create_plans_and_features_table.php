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
        if (!Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('discount_percent', 5, 2)->nullable()->default(0.00);
                $table->decimal('final_price', 10, 2)->nullable();
                $table->decimal('total_amount', 10, 2)->nullable();
                $table->decimal('total_price', 10, 2)->nullable();
                $table->decimal('renewal_price', 10, 2)->nullable();
                $table->string('duration')->nullable()->default('month');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('subtitle')->nullable();
                $table->integer('user_limit')->nullable();
                $table->integer('branch_limit')->nullable();
                $table->integer('storage_limit')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('sub_branch_id')->nullable();
                $table->json('features')->nullable();
                $table->json('sub_modules')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('plan_features')) {
            Schema::create('plan_features', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('plan_id');
                $table->string('feature');
                $table->timestamps();

                $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'plan_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('branch_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'plan_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('plan_id');
            });
        }

        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }
};
