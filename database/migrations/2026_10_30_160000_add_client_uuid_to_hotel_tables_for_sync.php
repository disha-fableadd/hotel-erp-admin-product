<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'categories',
            'brands',
            'quantity_types',
            'products',
            'table_no',
            'table_reservations',
            'orders',
            'order_items',
            'users',
            'user_details',
            'expenses',
            'expense_types',
            'payment_store',
            'taxes',
            'purchases',
            'purchase_invoice',
            'custom_invoice',
            'custom_invoice_item',
            'deliveries',
            'delivery_items',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'client_uuid')) {
                        $table->string('client_uuid', 100)->nullable()->index('idx_' . substr($tableName, 0, 40) . '_client_uuid');
                    }
                });

                // Backfill existing rows where client_uuid is NULL
                try {
                    $rows = DB::table($tableName)->whereNull('client_uuid')->orWhere('client_uuid', '')->select('id')->get();
                    foreach ($rows as $row) {
                        DB::table($tableName)->where('id', $row->id)->update([
                            'client_uuid' => (string) Str::uuid(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Ignore backfill errors if any
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'categories',
            'brands',
            'quantity_types',
            'products',
            'table_no',
            'table_reservations',
            'orders',
            'order_items',
            'users',
            'user_details',
            'expenses',
            'expense_types',
            'payment_store',
            'taxes',
            'purchases',
            'purchase_invoice',
            'custom_invoice',
            'custom_invoice_item',
            'deliveries',
            'delivery_items',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'client_uuid')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('client_uuid');
                });
            }
        }
    }
};
