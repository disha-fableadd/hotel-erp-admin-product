<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'kot_printed_qty')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedInteger('kot_printed_qty')->default(0)->after('quantity');
            });

            // Existing lines are treated as already sent so Update does not reprint the whole order once.
            DB::table('order_items')->update([
                'kot_printed_qty' => DB::raw('GREATEST(COALESCE(quantity, 0), 0)'),
            ]);
        }

        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'kot_pending_cancels')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->json('kot_pending_cancels')->nullable()->after('table_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'kot_printed_qty')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('kot_printed_qty');
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'kot_pending_cancels')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('kot_pending_cancels');
            });
        }
    }
};
