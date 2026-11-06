<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'kot_remarks')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->text('kot_remarks')->nullable()->after('kot_pending_cancels');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'kot_remarks')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('kot_remarks');
            });
        }
    }
};
