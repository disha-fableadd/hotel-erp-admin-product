<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            // Make user_id nullable so MySQL strict mode doesn't reject items
            if (Schema::hasColumn('order_items', 'user_id')) {
                DB::statement('ALTER TABLE `order_items` MODIFY COLUMN `user_id` BIGINT UNSIGNED NULL DEFAULT NULL');
            }

            // Make category_id nullable for flexibility
            if (Schema::hasColumn('order_items', 'category_id')) {
                DB::statement('ALTER TABLE `order_items` MODIFY COLUMN `category_id` BIGINT UNSIGNED NULL DEFAULT NULL');
            }

            // Ensure client_uuid exists
            if (!Schema::hasColumn('order_items', 'client_uuid')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->string('client_uuid', 100)->nullable()->index('idx_order_items_client_uuid');
                });
            }
        }

        // Support KOT kitchen statuses ('running', 'ready', 'served', 'delivered') in orders table
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'delivery_status')) {
            try {
                DB::statement("ALTER TABLE `orders` MODIFY COLUMN `delivery_status` VARCHAR(50) NULL DEFAULT 'pending'");
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            try {
                DB::statement('ALTER TABLE `order_items` MODIFY COLUMN `user_id` BIGINT UNSIGNED NOT NULL');
            } catch (\Throwable $e) {
                // Ignore if rows have NULL values
            }
        }
    }
};
