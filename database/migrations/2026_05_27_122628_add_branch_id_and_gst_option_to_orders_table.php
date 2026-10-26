<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('user_id')->constrained('branches')->nullOnDelete();
                }
                if (!Schema::hasColumn('orders', 'gst_option')) {
                    $table->string('gst_option')->nullable()->after('tax_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('orders', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $columns[] = 'branch_id';
                }
                if (Schema::hasColumn('orders', 'gst_option')) {
                    $columns[] = 'gst_option';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
