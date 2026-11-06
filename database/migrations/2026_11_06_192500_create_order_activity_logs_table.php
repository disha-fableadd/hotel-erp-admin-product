<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_activity_logs')) {
            return;
        }

        Schema::create('order_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('order_number', 64)->nullable()->index();
            $table->string('action', 50)->index(); // bill_created, item_added, item_removed, item_qty_reduced, item_qty_increased, bill_deleted, bill_restored, bill_cleared
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->decimal('old_quantity', 12, 2)->nullable();
            $table->decimal('new_quantity', 12, 2)->nullable();
            $table->string('table_no', 50)->nullable();
            $table->text('details')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 50)->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_activity_logs');
    }
};
