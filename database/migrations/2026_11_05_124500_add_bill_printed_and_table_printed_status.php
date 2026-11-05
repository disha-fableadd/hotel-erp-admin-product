<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'bill_printed')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('bill_printed')->default(0)->after('table_status');
            });
        }

        if (Schema::hasTable('table_no') && ! Schema::hasColumn('table_no', 'status')) {
            Schema::table('table_no', function (Blueprint $table) {
                $table->string('status', 20)->nullable()->after('table_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'bill_printed')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('bill_printed');
            });
        }

        if (Schema::hasTable('table_no') && Schema::hasColumn('table_no', 'status')) {
            Schema::table('table_no', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
