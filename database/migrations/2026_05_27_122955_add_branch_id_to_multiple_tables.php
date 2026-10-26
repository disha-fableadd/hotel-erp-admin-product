<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'purchase_invoice' => 'vendor_id',
            'expenses' => null,
            'users' => null,
            'products' => null,
            'settings' => null,
            'categories' => null,
            'brands' => null
        ];

        foreach ($tables as $tableName => $afterColumn) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($afterColumn) {
                    $column = $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                    if ($afterColumn) {
                        $column->after($afterColumn);
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['purchase_invoice', 'expenses', 'users', 'products', 'settings', 'categories', 'brands'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                });
            }
        }
    }
};
