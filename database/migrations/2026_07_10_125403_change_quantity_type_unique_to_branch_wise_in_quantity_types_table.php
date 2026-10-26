<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quantity_types', function (Blueprint $table) {
            // Remove old global unique constraint
            $table->dropUnique('quantity_types_quantity_type_unique');

            // Add branch-wise unique constraint
            $table->unique(
                ['quantity_type', 'branch_id'],
                'quantity_types_quantity_type_branch_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('quantity_types', function (Blueprint $table) {
            $table->dropUnique(
                'quantity_types_quantity_type_branch_unique'
            );

            $table->unique(
                'quantity_type',
                'quantity_types_quantity_type_unique'
            );
        });
    }
};