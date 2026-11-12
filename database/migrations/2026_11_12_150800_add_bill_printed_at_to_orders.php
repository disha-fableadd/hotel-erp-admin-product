<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'bill_printed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('bill_printed_at')->nullable()->after('bill_printed');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'bill_printed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('bill_printed_at');
            });
        }
    }
};
