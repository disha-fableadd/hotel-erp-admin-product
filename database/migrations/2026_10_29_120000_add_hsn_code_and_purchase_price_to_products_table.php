<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'hsn_code')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('hsn_code')->nullable()->after('SKU');
            });
        }

        if (! Schema::hasColumn('products', 'purchase_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('purchase_price', 10, 2)->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        foreach (['hsn_code', 'purchase_price'] as $column) {
            if (Schema::hasColumn('products', $column)) {
                Schema::table('products', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
