<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payment_store')) {
            Schema::table('payment_store', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_store', 'custom_invoice_id')) {
                    $table->foreignId('custom_invoice_id')->nullable()->after('order_id')->constrained('custom_invoice')->nullOnDelete();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('payment_store')) {
            Schema::table('payment_store', function (Blueprint $table) {
                if (Schema::hasColumn('payment_store', 'custom_invoice_id')) {
                    $table->dropForeign(['custom_invoice_id']);
                    $table->dropColumn('custom_invoice_id');
                }
            });
        }
    }
};
