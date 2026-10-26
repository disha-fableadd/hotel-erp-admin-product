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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->enum('order_type', ['self_pickup', 'delivery'])->default('self_pickup');
            }
            if (!Schema::hasColumn('orders', 'delivery_status')) {
                $table->enum('delivery_status', ['pending', 'delivered', 'partially_delivered', 'cancelled'])->default('pending');
            }
            if (!Schema::hasColumn('orders', 'delivered_by')) {
                $table->string('delivered_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_type')) {
                $table->dropColumn('order_type');
            }
            if (Schema::hasColumn('orders', 'delivery_status')) {
                $table->dropColumn('delivery_status');
            }
            if (Schema::hasColumn('orders', 'delivered_by')) {
                $table->dropColumn('delivered_by');
            }
        });
    }
};
